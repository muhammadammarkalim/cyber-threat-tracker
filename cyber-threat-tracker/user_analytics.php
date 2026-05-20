<?php
session_start();
include 'db_connect.php';
include 'security_functions.php';

// Require auth (any authenticated user can view)
requireAuth();

$user_id = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['user_role'] ?? null;

// Get user info
$user_sql = "SELECT user_id, username, user_role FROM users WHERE user_id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param('i', $user_id);
$user_stmt->execute();
$user_info = $user_stmt->get_result()->fetch_assoc();
$user_stmt->close();

// Build role-based queries
$threat_filter = "";
if ($user_role === 'student') {
    // Students only see threats they submitted
    $threat_filter = "WHERE submitted_by = (SELECT username FROM users WHERE user_id = ?)";
} elseif ($user_role === 'analyzer') {
    // Analyzers see all threats (they analyze them)
    $threat_filter = "";
} elseif ($user_role === 'govt_emp') {
    // Govt employees see all threats for their industry/region
    $threat_filter = "";
}

// Get threat statistics
$stats_sql = "
    SELECT 
        COUNT(*) as total_threats,
        SUM(CASE WHEN severity='Critical' THEN 1 ELSE 0 END) as critical_count,
        SUM(CASE WHEN severity='High' THEN 1 ELSE 0 END) as high_count,
        SUM(CASE WHEN severity='Medium' THEN 1 ELSE 0 END) as medium_count,
        SUM(CASE WHEN severity='Low' THEN 1 ELSE 0 END) as low_count
    FROM threats 
    $threat_filter
";

if ($user_role === 'student') {
    $stats_stmt = $conn->prepare($stats_sql);
    $stats_stmt->bind_param('i', $user_id);
    $stats_stmt->execute();
    $stats_result = $stats_stmt->get_result();
    $stats_stmt->close();
} else {
    $stats_result = $conn->query($stats_sql);
}
$stats = $stats_result->fetch_assoc();

// Get threat by severity data for pie chart
$severity_sql = "
    SELECT severity, COUNT(*) as count 
    FROM threats 
    WHERE severity IS NOT NULL AND severity != ''
    $threat_filter
    GROUP BY severity
";

if ($user_role === 'student') {
    $severity_stmt = $conn->prepare(str_replace($threat_filter, "WHERE submitted_by = (SELECT username FROM users WHERE user_id = ?)", $severity_sql));
    $severity_stmt->bind_param('i', $user_id);
    $severity_stmt->execute();
    $severity_result = $severity_stmt->get_result();
    $severity_stmt->close();
} else {
    $severity_result = $conn->query($severity_sql);
}

$severity_data = [];
while ($row = $severity_result->fetch_assoc()) {
    $severity_data[] = $row;
}

// Get threats by industry for bar chart
$industry_sql = "
    SELECT affected_industry, COUNT(*) as count 
    FROM threats 
    WHERE affected_industry IS NOT NULL AND affected_industry != ''
    $threat_filter
    GROUP BY affected_industry 
    ORDER BY count DESC 
    LIMIT 10
";

if ($user_role === 'student') {
    $industry_stmt = $conn->prepare(str_replace($threat_filter, "WHERE submitted_by = (SELECT username FROM users WHERE user_id = ?) AND affected_industry IS NOT NULL", $industry_sql));
    $industry_stmt->bind_param('i', $user_id);
    $industry_stmt->execute();
    $industry_result = $industry_stmt->get_result();
    $industry_stmt->close();
} else {
    $industry_result = $conn->query($industry_sql);
}

$industry_data = [];
while ($row = $industry_result->fetch_assoc()) {
    $industry_data[] = $row;
}

// Get threats over time (last 30 days)
$timeline_sql = "
    SELECT DATE(reported_date) as report_date, COUNT(*) as count 
    FROM threats 
    WHERE reported_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    $threat_filter
    GROUP BY DATE(reported_date)
    ORDER BY report_date ASC
";

if ($user_role === 'student') {
    $timeline_stmt = $conn->prepare(str_replace($threat_filter, "WHERE submitted_by = (SELECT username FROM users WHERE user_id = ?) AND reported_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)", $timeline_sql));
    $timeline_stmt->bind_param('i', $user_id);
    $timeline_stmt->execute();
    $timeline_result = $timeline_stmt->get_result();
    $timeline_stmt->close();
} else {
    $timeline_result = $conn->query($timeline_sql);
}

$timeline_data = [];
while ($row = $timeline_result->fetch_assoc()) {
    $timeline_data[] = $row;
}

// Get recent threats for table
$recent_sql = "
    SELECT id, threat_name, severity, affected_industry, reported_date, submitted_by 
    FROM threats 
    $threat_filter
    ORDER BY reported_date DESC 
    LIMIT 10
";

if ($user_role === 'student') {
    $recent_stmt = $conn->prepare(str_replace($threat_filter, "WHERE submitted_by = (SELECT username FROM users WHERE user_id = ?)", $recent_sql));
    $recent_stmt->bind_param('i', $user_id);
    $recent_stmt->execute();
    $recent_result = $recent_stmt->get_result();
    $recent_stmt->close();
} else {
    $recent_result = $conn->query($recent_sql);
}

$recent_threats = [];
while ($row = $recent_result->fetch_assoc()) {
    $recent_threats[] = $row;
}

// Encode data for JavaScript
$severity_json = json_encode($severity_data);
$industry_json = json_encode($industry_data);
$timeline_json = json_encode($timeline_data);

// Determine dashboard title based on role
$dashboard_title = "Threat Analytics";
$role_label = ucfirst(str_replace('_', ' ', $user_role));
if ($user_role === 'student') {
    $dashboard_title = "My Threat Submissions";
} elseif ($user_role === 'analyzer') {
    $dashboard_title = "Threat Analysis Dashboard";
} elseif ($user_role === 'govt_emp') {
    $dashboard_title = "Government Threat Intelligence";
} elseif ($user_role === 'admin') {
    $dashboard_title = "System Analytics Dashboard";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $dashboard_title; ?> - Cyber Threat Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    <style>
        body {
            background: #10141a;
            color: #e0e6ed;
            font-family: 'Segoe UI', 'Roboto', Arial, sans-serif;
        }
        .navbar {
            background: #181f2a !important;
            border-bottom: 2px solid #00ffe7;
        }
        .container-fluid {
            padding: 20px;
        }
        h1 {
            color: #00ffe7;
            text-shadow: 0 0 10px #00ffe7;
            margin-bottom: 30px;
            font-weight: bold;
        }
        .user-badge {
            background: #232b39;
            padding: 5px 15px;
            border-radius: 20px;
            color: #00ffe7;
            font-size: 12px;
            border: 1px solid #00ffe7;
        }
        .stat-card {
            background: linear-gradient(135deg, #181f2a 0%, #232b39 100%);
            border: 2px solid #00ffe7;
            border-radius: 12px;
            padding: 20px;
            margin: 15px 0;
            text-align: center;
            box-shadow: 0 0 15px rgba(0, 255, 231, 0.1);
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0 25px rgba(0, 255, 231, 0.3);
        }
        .stat-card h3 {
            color: #00ffe7;
            font-size: 28px;
            font-weight: bold;
            margin: 10px 0;
        }
        .stat-card p {
            color: #b0b8c6;
            margin: 0;
            font-size: 14px;
        }
        .stat-critical {
            border-color: #ff4444;
        }
        .stat-critical h3 {
            color: #ff4444;
        }
        .stat-high {
            border-color: #ff9900;
        }
        .stat-high h3 {
            color: #ff9900;
        }
        .stat-medium {
            border-color: #ffdd00;
        }
        .stat-medium h3 {
            color: #ffdd00;
        }
        .stat-low {
            border-color: #00dd00;
        }
        .stat-low h3 {
            color: #00dd00;
        }
        .chart-container {
            background: #181f2a;
            border: 2px solid #232b39;
            border-radius: 12px;
            padding: 20px;
            margin: 15px 0;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
        }
        .chart-title {
            color: #00ffe7;
            font-weight: bold;
            margin-bottom: 15px;
            font-size: 18px;
        }
        .table-container {
            background: #181f2a;
            border: 2px solid #232b39;
            border-radius: 12px;
            padding: 20px;
            margin: 15px 0;
            overflow-x: auto;
        }
        table {
            color: #e0e6ed;
        }
        thead {
            background: #232b39;
            border-bottom: 2px solid #00ffe7;
        }
        th {
            color: #00ffe7;
            font-weight: bold;
            padding: 12px;
        }
        td {
            padding: 10px 12px;
            border-bottom: 1px solid #232b39;
        }
        tbody tr:hover {
            background: #232b39;
        }
        .severity-critical {
            color: #ff4444;
            font-weight: bold;
        }
        .severity-high {
            color: #ff9900;
            font-weight: bold;
        }
        .severity-medium {
            color: #ffdd00;
            font-weight: bold;
        }
        .severity-low {
            color: #00dd00;
            font-weight: bold;
        }
        .btn-back {
            background: linear-gradient(90deg, #00ffe7 0%, #00bfae 100%);
            color: #10141a !important;
            border: none;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-back:hover {
            transform: scale(1.05);
            box-shadow: 0 0 15px #00ffe7;
        }
        .row {
            margin: 0 -15px;
        }
        .col-md-3, .col-md-6, .col-md-12 {
            padding: 0 15px;
        }
        @media (max-width: 768px) {
            .stat-card h3 {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?php echo ($user_role === 'admin') ? 'admin_dashboard.php' : 'user_dashboard.php'; ?>" style="color: #00ffe7; font-weight: bold;">📊 Analytics</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="<?php echo ($user_role === 'admin') ? 'admin_dashboard.php' : 'user_dashboard.php'; ?>">Dashboard</a></li>
                <li class="nav-item"><span class="user-badge"><?php echo $role_label; ?></span></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <h1>📊 <?php echo $dashboard_title; ?></h1>
    
    <!-- Key Statistics -->
    <div class="row">
        <div class="col-md-3">
            <div class="stat-card stat-critical">
                <p>Critical Threats</p>
                <h3><?php echo $stats['critical_count'] ?? 0; ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card stat-high">
                <p>High Severity</p>
                <h3><?php echo $stats['high_count'] ?? 0; ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card stat-medium">
                <p>Medium Severity</p>
                <h3><?php echo $stats['medium_count'] ?? 0; ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card stat-low">
                <p>Low Severity</p>
                <h3><?php echo $stats['low_count'] ?? 0; ?></h3>
            </div>
        </div>
    </div>

    <!-- Overview Statistics -->
    <div class="row">
        <div class="col-md-4">
            <div class="stat-card">
                <p>Total Threats</p>
                <h3><?php echo $stats['total_threats'] ?? 0; ?></h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <p>Current User</p>
                <h3><?php echo htmlspecialchars($user_info['username'] ?? 'N/A'); ?></h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <p>User Role</p>
                <h3><?php echo $role_label; ?></h3>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row">
        <!-- Severity Distribution Pie Chart -->
        <div class="col-md-6">
            <div class="chart-container">
                <div class="chart-title">⚠️ Threat Severity Distribution</div>
                <canvas id="severityChart"></canvas>
            </div>
        </div>

        <!-- Top Industries Bar Chart -->
        <div class="col-md-6">
            <div class="chart-container">
                <div class="chart-title">🏢 Top Affected Industries</div>
                <canvas id="industryChart"></canvas>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Threats Over Time Line Chart -->
        <div class="col-md-12">
            <div class="chart-container">
                <div class="chart-title">📈 Threats Over Time (Last 30 Days)</div>
                <canvas id="timelineChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Threats Table -->
    <div class="table-container">
        <div class="chart-title">🔴 Recent Threats</div>
        <table class="table table-dark">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Threat Name</th>
                    <th>Severity</th>
                    <th>Industry</th>
                    <th>Reported Date</th>
                    <th>Submitted By</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recent_threats)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; color: #00ffe7;">No threats found</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($recent_threats as $threat): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($threat['id']); ?></td>
                        <td><?php echo htmlspecialchars($threat['threat_name']); ?></td>
                        <td class="severity-<?php echo strtolower($threat['severity'] ?? 'low'); ?>">
                            <?php echo htmlspecialchars($threat['severity'] ?? 'N/A'); ?>
                        </td>
                        <td><?php echo htmlspecialchars($threat['affected_industry']); ?></td>
                        <td><?php echo htmlspecialchars($threat['reported_date']); ?></td>
                        <td><?php echo htmlspecialchars($threat['submitted_by']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Color palette
    const colors = {
        cyan: '#00ffe7',
        critical: '#ff4444',
        high: '#ff9900',
        medium: '#ffdd00',
        low: '#00dd00',
        bg1: '#181f2a',
        bg2: '#232b39'
    };

    // 1. Severity Distribution Pie Chart
    const severityData = <?php echo $severity_json; ?>;
    const severityCtx = document.getElementById('severityChart').getContext('2d');
    const severityChart = new Chart(severityCtx, {
        type: 'doughnut',
        data: {
            labels: severityData.map(d => d.severity),
            datasets: [{
                data: severityData.map(d => d.count),
                backgroundColor: [
                    colors.critical,
                    colors.high,
                    colors.medium,
                    colors.low
                ],
                borderColor: colors.bg2,
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    labels: { color: colors.cyan }
                }
            }
        }
    });

    // 2. Top Industries Bar Chart
    const industryData = <?php echo $industry_json; ?>;
    const industryCtx = document.getElementById('industryChart').getContext('2d');
    const industryChart = new Chart(industryCtx, {
        type: 'bar',
        data: {
            labels: industryData.map(d => d.affected_industry.substring(0, 15)),
            datasets: [{
                label: 'Number of Threats',
                data: industryData.map(d => d.count),
                backgroundColor: colors.cyan,
                borderColor: colors.cyan,
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'x',
            responsive: true,
            plugins: {
                legend: { labels: { color: colors.cyan } }
            },
            scales: {
                x: { ticks: { color: colors.cyan } },
                y: { ticks: { color: colors.cyan } }
            }
        }
    });

    // 3. Threats Over Time Line Chart
    const timelineData = <?php echo $timeline_json; ?>;
    const timelineCtx = document.getElementById('timelineChart').getContext('2d');
    const timelineChart = new Chart(timelineCtx, {
        type: 'line',
        data: {
            labels: timelineData.map(d => d.report_date),
            datasets: [{
                label: 'Threats Reported',
                data: timelineData.map(d => d.count),
                borderColor: colors.critical,
                backgroundColor: 'rgba(255, 68, 68, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { labels: { color: colors.cyan } }
            },
            scales: {
                x: { ticks: { color: colors.cyan } },
                y: { ticks: { color: colors.cyan } }
            }
        }
    });
</script>

<footer class="text-center mt-5 py-3" style="background:#181f2a; color:#00ffe7; border-top:1px solid #00ffe7;">
  &copy; <?php echo date('Y'); ?> Cyber Threat Tracker — Analytics & Insights
</footer>

</body>
</html>
