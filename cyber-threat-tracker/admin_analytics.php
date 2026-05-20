<?php
session_start();
include 'db_connect.php';
include 'security_functions.php';

// STRICT: Only admin can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: user_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get threat statistics
$stats_sql = "
    SELECT 
        COUNT(*) as total_threats,
        SUM(CASE WHEN severity='Critical' THEN 1 ELSE 0 END) as critical_count,
        SUM(CASE WHEN severity='High' THEN 1 ELSE 0 END) as high_count,
        SUM(CASE WHEN severity='Medium' THEN 1 ELSE 0 END) as medium_count,
        SUM(CASE WHEN severity='Low' THEN 1 ELSE 0 END) as low_count
    FROM threats
";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();

// Get threat by severity data for pie chart
$severity_sql = "
    SELECT severity, COUNT(*) as count 
    FROM threats 
    WHERE severity IS NOT NULL AND severity != ''
    GROUP BY severity
";
$severity_result = $conn->query($severity_sql);
$severity_data = [];
while ($row = $severity_result->fetch_assoc()) {
    $severity_data[] = $row;
}

// Get threats by industry for bar chart
$industry_sql = "
    SELECT affected_industry, COUNT(*) as count 
    FROM threats 
    WHERE affected_industry IS NOT NULL AND affected_industry != ''
    GROUP BY affected_industry 
    ORDER BY count DESC 
    LIMIT 10
";
$industry_result = $conn->query($industry_sql);
$industry_data = [];
while ($row = $industry_result->fetch_assoc()) {
    $industry_data[] = $row;
}

// Get threats over time (last 30 days)
$timeline_sql = "
    SELECT DATE(reported_date) as report_date, COUNT(*) as count 
    FROM threats 
    WHERE reported_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY DATE(reported_date)
    ORDER BY report_date ASC
";
$timeline_result = $conn->query($timeline_sql);
$timeline_data = [];
while ($row = $timeline_result->fetch_assoc()) {
    $timeline_data[] = $row;
}

// Get user statistics
$user_sql = "
    SELECT 
        COUNT(*) as total_users,
        SUM(CASE WHEN user_role='admin' THEN 1 ELSE 0 END) as admin_count,
        SUM(CASE WHEN user_role='student' THEN 1 ELSE 0 END) as student_count,
        SUM(CASE WHEN user_role='analyzer' THEN 1 ELSE 0 END) as analyzer_count,
        SUM(CASE WHEN user_role='govt_emp' THEN 1 ELSE 0 END) as govt_count,
        SUM(CASE WHEN user_role='it_cs' THEN 1 ELSE 0 END) as it_cs_count
    FROM users
";
$user_result = $conn->query($user_sql);
$user_stats = $user_result->fetch_assoc();

// Get alert statistics
$alert_sql = "SELECT COUNT(*) as total_alerts FROM alerts";
$alert_result = $conn->query($alert_sql);
$alert_stats = $alert_result->fetch_assoc();

// Get recent threats for table
$recent_sql = "
    SELECT id, threat_name, severity, affected_industry, reported_date, submitted_by 
    FROM threats 
    ORDER BY reported_date DESC 
    LIMIT 10
";
$recent_result = $conn->query($recent_sql);
$recent_threats = [];
while ($row = $recent_result->fetch_assoc()) {
    $recent_threats[] = $row;
}

// Encode data for JavaScript
$severity_json = json_encode($severity_data);
$industry_json = json_encode($industry_data);
$timeline_json = json_encode($timeline_data);

// Log analytics access
logSecurityEvent($conn, $user_id, 'ANALYTICS_VIEW', 'Admin accessed analytics dashboard', $_SERVER['REMOTE_ADDR']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Analytics Dashboard - Cyber Threat Tracker</title>
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
        .stat-critical { border-color: #ff4444; }
        .stat-critical h3 { color: #ff4444; }
        .stat-high { border-color: #ff9900; }
        .stat-high h3 { color: #ff9900; }
        .stat-medium { border-color: #ffdd00; }
        .stat-medium h3 { color: #ffdd00; }
        .stat-low { border-color: #00dd00; }
        .stat-low h3 { color: #00dd00; }
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
        .severity-critical { color: #ff4444; font-weight: bold; }
        .severity-high { color: #ff9900; font-weight: bold; }
        .severity-medium { color: #ffdd00; font-weight: bold; }
        .severity-low { color: #00dd00; font-weight: bold; }
        .row {
            margin: 0 -15px;
        }
        .col-md-3, .col-md-6, .col-md-12 {
            padding: 0 15px;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="admin_dashboard.php" style="color: #00ffe7; font-weight: bold;">🔒 ADMIN ANALYTICS</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">Admin Dashboard</a></li>
                <li class="nav-item"><a class="nav-link active" href="admin_analytics.php">📊 Analytics</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <h1>🔒 Admin Analytics Dashboard</h1>
    
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
        <div class="col-md-3">
            <div class="stat-card">
                <p>Total Threats</p>
                <h3><?php echo $stats['total_threats'] ?? 0; ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <p>Total Users</p>
                <h3><?php echo $user_stats['total_users'] ?? 0; ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <p>Active Alerts</p>
                <h3><?php echo $alert_stats['total_alerts'] ?? 0; ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <p>Threat Analysts</p>
                <h3><?php echo $user_stats['analyzer_count'] ?? 0; ?></h3>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row">
        <div class="col-md-6">
            <div class="chart-container">
                <div class="chart-title">⚠️ Threat Severity Distribution</div>
                <canvas id="severityChart"></canvas>
            </div>
        </div>
        <div class="col-md-6">
            <div class="chart-container">
                <div class="chart-title">🏢 Top Affected Industries</div>
                <canvas id="industryChart"></canvas>
            </div>
        </div>
    </div>

    <div class="row">
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
                <?php foreach ($recent_threats as $threat): ?>
                <tr>
                    <td><?php echo htmlspecialchars($threat['id']); ?></td>
                    <td><?php echo htmlspecialchars($threat['threat_name']); ?></td>
                    <td class="severity-<?php echo strtolower($threat['severity']); ?>">
                        <?php echo htmlspecialchars($threat['severity']); ?>
                    </td>
                    <td><?php echo htmlspecialchars($threat['affected_industry']); ?></td>
                    <td><?php echo htmlspecialchars($threat['reported_date']); ?></td>
                    <td><?php echo htmlspecialchars($threat['submitted_by']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const colors = {
        cyan: '#00ffe7',
        critical: '#ff4444',
        high: '#ff9900',
        medium: '#ffdd00',
        low: '#00dd00',
        bg2: '#232b39'
    };

    const severityData = <?php echo $severity_json; ?>;
    const severityCtx = document.getElementById('severityChart').getContext('2d');
    new Chart(severityCtx, {
        type: 'doughnut',
        data: {
            labels: severityData.map(d => d.severity),
            datasets: [{
                data: severityData.map(d => d.count),
                backgroundColor: [colors.critical, colors.high, colors.medium, colors.low],
                borderColor: colors.bg2,
                borderWidth: 2
            }]
        },
        options: { responsive: true, plugins: { legend: { labels: { color: colors.cyan } } } }
    });

    const industryData = <?php echo $industry_json; ?>;
    const industryCtx = document.getElementById('industryChart').getContext('2d');
    new Chart(industryCtx, {
        type: 'bar',
        data: {
            labels: industryData.map(d => d.affected_industry.substring(0, 15)),
            datasets: [{ label: 'Threats', data: industryData.map(d => d.count), backgroundColor: colors.cyan, borderColor: colors.cyan, borderWidth: 1 }]
        },
        options: { responsive: true, plugins: { legend: { labels: { color: colors.cyan } } }, scales: { x: { ticks: { color: colors.cyan } }, y: { ticks: { color: colors.cyan } } } }
    });

    const timelineData = <?php echo $timeline_json; ?>;
    const timelineCtx = document.getElementById('timelineChart').getContext('2d');
    new Chart(timelineCtx, {
        type: 'line',
        data: {
            labels: timelineData.map(d => d.report_date),
            datasets: [{ label: 'Threats', data: timelineData.map(d => d.count), borderColor: colors.critical, backgroundColor: 'rgba(255, 68, 68, 0.1)', borderWidth: 2, fill: true, tension: 0.4 }]
        },
        options: { responsive: true, plugins: { legend: { labels: { color: colors.cyan } } }, scales: { x: { ticks: { color: colors.cyan } }, y: { ticks: { color: colors.cyan } } } }
    });
</script>

<footer class="text-center mt-5 py-3" style="background:#181f2a; color:#00ffe7; border-top:1px solid #00ffe7;">
  &copy; <?php echo date('Y'); ?> Cyber Threat Tracker — Admin Analytics
</footer>

</body>
</html>
