<?php
session_start();
include 'db_connect.php';
include 'security_functions.php';

// STRICT: Only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: user_login.php");
    exit();
}

$admin_id = $_SESSION['user_id'];
$filter_type = isset($_GET['type']) ? sanitizeInput($_GET['type']) : '';
$filter_user = isset($_GET['user']) ? sanitizeInput($_GET['user']) : '';
$filter_date_from = isset($_GET['from']) ? sanitizeInput($_GET['from']) : '';
$filter_date_to = isset($_GET['to']) ? sanitizeInput($_GET['to']) : '';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;

// Build query for security logs
$security_query = "SELECT sl.*, u.username FROM security_logs sl LEFT JOIN users u ON sl.user_id = u.user_id WHERE 1=1";
$params = [];
$types = '';

if ($filter_type) {
    $security_query .= " AND sl.event_type LIKE ?";
    $params[] = "%$filter_type%";
    $types .= 's';
}
if ($filter_user) {
    $security_query .= " AND u.username LIKE ?";
    $params[] = "%$filter_user%";
    $types .= 's';
}
if ($filter_date_from) {
    $security_query .= " AND DATE(sl.timestamp) >= ?";
    $params[] = $filter_date_from;
    $types .= 's';
}
if ($filter_date_to) {
    $security_query .= " AND DATE(sl.timestamp) <= ?";
    $params[] = $filter_date_to;
    $types .= 's';
}

$security_query .= " ORDER BY sl.timestamp DESC LIMIT ?";
$params[] = $limit;
$types .= 'i';

$sec_stmt = $conn->prepare($security_query);
if ($params) {
    $sec_stmt->bind_param($types, ...$params);
}
$sec_stmt->execute();
$security_logs = $sec_stmt->get_result();

// Get failed login attempts (last 100)
$failed_query = "SELECT * FROM failed_login_attempts ORDER BY attempt_time DESC LIMIT 100";
$failed_result = $conn->query($failed_query);

// Get activity logs (registrations, threat submissions, etc)
$activity_query = "SELECT l.*, u.username FROM logs l LEFT JOIN users u ON l.user_id = u.user_id ORDER BY l.action_timestamp DESC LIMIT 100";
$activity_result = $conn->query($activity_query);

// Get summary statistics
$event_types_query = "SELECT event_type, COUNT(*) as count FROM security_logs GROUP BY event_type ORDER BY count DESC";
$event_types_result = $conn->query($event_types_query);
$event_types = [];
while ($row = $event_types_result->fetch_assoc()) {
    $event_types[] = $row;
}

// Get top users by activity
$top_users_query = "
    SELECT u.username, COUNT(sl.log_id) as activity_count 
    FROM security_logs sl 
    JOIN users u ON sl.user_id = u.user_id 
    GROUP BY sl.user_id 
    ORDER BY activity_count DESC 
    LIMIT 10
";
$top_users_result = $conn->query($top_users_query);
$top_users = [];
while ($row = $top_users_result->fetch_assoc()) {
    $top_users[] = $row;
}

// Log access to audit logs
logSecurityEvent($conn, $admin_id, 'AUDIT_LOGS_ACCESSED', 'Viewed audit logs', $_SERVER['REMOTE_ADDR']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Logs Viewer - Cyber Threat Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
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
        h1, h2 {
            color: #00ffe7;
            text-shadow: 0 0 10px #00ffe7;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .filter-box {
            background: #181f2a;
            border: 2px solid #232b39;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
        }
        .filter-box .form-label {
            color: #00ffe7;
            font-weight: bold;
        }
        .filter-box .form-control {
            background: #232b39;
            border: 1px solid #00ffe7;
            color: #e0e6ed;
        }
        .filter-box .form-control:focus {
            background: #232b39;
            border-color: #00ffe7;
            color: #e0e6ed;
            box-shadow: 0 0 5px #00ffe7;
        }
        .stat-box {
            background: linear-gradient(135deg, #181f2a 0%, #232b39 100%);
            border: 2px solid #00ffe7;
            border-radius: 12px;
            padding: 15px;
            text-align: center;
            margin: 10px 0;
        }
        .stat-box h5 {
            color: #b0b8c6;
            font-size: 12px;
            margin-bottom: 5px;
        }
        .stat-box .number {
            color: #00ffe7;
            font-size: 24px;
            font-weight: bold;
        }
        .table-container {
            background: #181f2a;
            border: 2px solid #232b39;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            overflow-x: auto;
        }
        table {
            color: #e0e6ed;
            margin: 0;
        }
        thead {
            background: #232b39;
            border-bottom: 2px solid #00ffe7;
        }
        th {
            color: #00ffe7;
            font-weight: bold;
            padding: 12px;
            white-space: nowrap;
        }
        td {
            padding: 10px 12px;
            border-bottom: 1px solid #232b39;
        }
        tbody tr:hover {
            background: #232b39;
        }
        .event-type {
            background: #232b39;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            display: inline-block;
        }
        .event-security { background: #5f1a1a; color: #ff4444; }
        .event-login { background: #1a3a5f; color: #44aaff; }
        .event-user { background: #3a1a5f; color: #dd44ff; }
        .event-threat { background: #5f3a1a; color: #ffaa44; }
        .event-admin { background: #1a5f1a; color: #44ff44; }
        .ip-address {
            font-family: 'Courier New', monospace;
            color: #ffdd00;
        }
        .tab-content {
            background: #181f2a;
            border: 2px solid #232b39;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
        }
        .nav-tabs .nav-link {
            color: #b0b8c6;
            border: 1px solid #232b39;
        }
        .nav-tabs .nav-link.active {
            background: #232b39;
            color: #00ffe7;
            border: 2px solid #00ffe7;
        }
        .btn-filter {
            background: #00ffe7;
            color: #10141a;
            border: none;
            font-weight: bold;
        }
        .btn-filter:hover {
            background: #00ddcc;
            color: #10141a;
        }
        .btn-reset {
            background: #232b39;
            color: #00ffe7;
            border: 1px solid #00ffe7;
        }
        .btn-reset:hover {
            background: #181f2a;
            color: #00ffe7;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="admin_dashboard.php" style="color: #00ffe7; font-weight: bold;">📋 AUDIT LOGS</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="admin_panel.php">🔧 Advanced Panel</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <h1>📋 Audit Logs Viewer</h1>

    <!-- Summary Statistics -->
    <h2>📊 Activity Summary</h2>
    <div class="row">
        <div class="col-md-3">
            <div class="stat-box">
                <h5>Event Types Logged</h5>
                <div class="number"><?php echo count($event_types); ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-box">
                <h5>Top Active Users</h5>
                <div class="number"><?php echo count($top_users); ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-box">
                <h5>Failed Login Attempts</h5>
                <div class="number" style="color: #ff4444;">
                    <?php 
                    $failed_count = $conn->query("SELECT COUNT(*) as count FROM failed_login_attempts WHERE attempt_time > DATE_SUB(NOW(), INTERVAL 24 HOUR)")->fetch_assoc()['count'];
                    echo $failed_count;
                    ?>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-box">
                <h5>Last 24h Activity</h5>
                <div class="number" style="color: #00dd00;">
                    <?php 
                    $recent_count = $conn->query("SELECT COUNT(*) as count FROM security_logs WHERE timestamp > DATE_SUB(NOW(), INTERVAL 24 HOUR)")->fetch_assoc()['count'];
                    echo $recent_count;
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#security">🔒 Security Events</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#failed">⚠️ Failed Logins</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#activity">📝 Activity Logs</a></li>
    </ul>

    <div class="tab-content">
        <!-- Security Events Tab -->
        <div id="security" class="tab-pane fade show active">
            <h2 style="margin-top: 20px;">Security Events Log</h2>

            <!-- Filters -->
            <div class="filter-box">
                <h5>🔍 Filter Events</h5>
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Event Type</label>
                        <input type="text" class="form-control" name="type" value="<?php echo htmlspecialchars($filter_type); ?>" placeholder="e.g., LOGIN, LOGOUT">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="user" value="<?php echo htmlspecialchars($filter_user); ?>" placeholder="Username">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">From Date</label>
                        <input type="date" class="form-control" name="from" value="<?php echo htmlspecialchars($filter_date_from); ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">To Date</label>
                        <input type="date" class="form-control" name="to" value="<?php echo htmlspecialchars($filter_date_to); ?>">
                    </div>
                    <div class="col-md-2" style="display: flex; align-items: flex-end; gap: 10px;">
                        <button type="submit" class="btn btn-filter w-100">Search</button>
                        <a href="audit_logs_viewer.php" class="btn btn-reset w-100">Reset</a>
                    </div>
                </form>
            </div>

            <!-- Event Type Summary -->
            <h5 style="color: #00ffe7; margin-top: 20px;">Event Types Summary:</h5>
            <div style="margin: 15px 0; display: flex; flex-wrap: wrap; gap: 10px;">
                <?php foreach ($event_types as $et): ?>
                    <span class="event-type">
                        <?php echo htmlspecialchars($et['event_type']); ?>: <strong><?php echo $et['count']; ?></strong>
                    </span>
                <?php endforeach; ?>
            </div>

            <!-- Security Logs Table -->
            <div class="table-container">
                <table class="table table-dark">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>User</th>
                            <th>Event</th>
                            <th>Details</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($log = $security_logs->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo substr($log['timestamp'], 0, 19); ?></td>
                            <td><?php echo htmlspecialchars($log['username'] ?? 'System'); ?></td>
                            <td>
                                <span class="event-type event-security">
                                    <?php echo htmlspecialchars($log['event_type']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars(substr($log['details'], 0, 60)); ?></td>
                            <td class="ip-address"><?php echo htmlspecialchars($log['ip_address']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Failed Logins Tab -->
        <div id="failed" class="tab-pane fade">
            <h2 style="margin-top: 20px;">Failed Login Attempts</h2>
            <div class="table-container">
                <table class="table table-dark">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>Username</th>
                            <th>IP Address</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        while ($fail = $failed_result->fetch_assoc()):
                        ?>
                        <tr style="background: #3a1a1a;">
                            <td><?php echo substr($fail['attempt_time'], 0, 19); ?></td>
                            <td><?php echo htmlspecialchars($fail['username']); ?></td>
                            <td class="ip-address"><?php echo htmlspecialchars($fail['ip_address']); ?></td>
                            <td>
                                <span class="event-type event-security">
                                    <?php echo htmlspecialchars($fail['reason'] ?? 'Invalid password'); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Activity Logs Tab -->
        <div id="activity" class="tab-pane fade">
            <h2 style="margin-top: 20px;">User Activity Logs</h2>
            <div class="table-container">
                <table class="table table-dark">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        while ($act = $activity_result->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?php echo substr($act['action_timestamp'], 0, 19); ?></td>
                            <td><?php echo htmlspecialchars($act['username'] ?? 'System'); ?></td>
                            <td>
                                <span class="event-type event-admin">
                                    <?php echo htmlspecialchars($act['action_type']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars(substr($act['description'], 0, 60)); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<footer class="text-center mt-5 py-3" style="background:#181f2a; color:#00ffe7; border-top:1px solid #00ffe7;">
  &copy; <?php echo date('Y'); ?> Cyber Threat Tracker — Audit Logs Viewer
</footer>

</body>
</html>
