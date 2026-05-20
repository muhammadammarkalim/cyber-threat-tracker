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
$action = isset($_POST['action']) ? $_POST['action'] : '';

// Handle user activation/deactivation
if ($action === 'toggle_user' && isset($_POST['user_id'])) {
    $toggle_user_id = (int)$_POST['user_id'];
    
    // Prevent admin from disabling themselves
    if ($toggle_user_id === $admin_id) {
        $error = "You cannot disable your own account";
    } else {
        // Get current status
        $check_sql = "SELECT user_status FROM users WHERE user_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param('i', $toggle_user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result()->fetch_assoc();
        $check_stmt->close();
        
        $new_status = ($check_result['user_status'] ?? 'active') === 'active' ? 'inactive' : 'active';
        
        $update_sql = "UPDATE users SET user_status = ? WHERE user_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param('si', $new_status, $toggle_user_id);
        
        if ($update_stmt->execute()) {
            logSecurityEvent($conn, $admin_id, 'USER_STATUS_CHANGE', "Changed user $toggle_user_id to $new_status", $_SERVER['REMOTE_ADDR']);
            $success = "User status updated to: $new_status";
        } else {
            $error = "Failed to update user status";
        }
        $update_stmt->close();
    }
}

// Handle user deletion
if ($action === 'delete_user' && isset($_POST['user_id']) && isset($_POST['confirm']) && $_POST['confirm'] === 'YES') {
    $delete_user_id = (int)$_POST['user_id'];
    
    if ($delete_user_id === $admin_id) {
        $error = "You cannot delete your own account";
    } else {
        // Start transaction to delete user and related records
        $conn->begin_transaction();
        
        try {
            // Delete logs
            $conn->query("DELETE FROM logs WHERE user_id = $delete_user_id");
            // Delete security logs
            $conn->query("DELETE FROM security_logs WHERE user_id = $delete_user_id");
            // Delete failed login attempts
            $username_sql = "SELECT username FROM users WHERE user_id = $delete_user_id";
            $username_result = $conn->query($username_sql);
            $username_row = $username_result->fetch_assoc();
            if ($username_row) {
                $conn->query("DELETE FROM failed_login_attempts WHERE username = '{$username_row['username']}'");
            }
            // Delete user
            $conn->query("DELETE FROM users WHERE user_id = $delete_user_id");
            
            $conn->commit();
            logSecurityEvent($conn, $admin_id, 'USER_DELETED', "Deleted user $delete_user_id", $_SERVER['REMOTE_ADDR']);
            $success = "User deleted successfully";
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Failed to delete user";
        }
    }
}

// Get all users
$users_sql = "SELECT user_id, username, full_name, email, user_role, COALESCE(user_status, 'active') as user_status, created_at FROM users ORDER BY created_at DESC";
$users_result = $conn->query($users_sql);
$all_users = [];
while ($row = $users_result->fetch_assoc()) {
    $all_users[] = $row;
}

$logs_sql = "
    SELECT sl.*, u.username 
    FROM security_logs sl 
    LEFT JOIN users u ON sl.user_id = u.user_id 
    ORDER BY sl.event_timestamp DESC 
    LIMIT 50
";
$logs_result = $conn->query($logs_sql);
$security_logs = [];
while ($row = $logs_result->fetch_assoc()) {
    $security_logs[] = $row;
}

// Get user statistics
$user_stats_sql = "
    SELECT 
        COUNT(*) as total_users,
        SUM(CASE WHEN user_role='admin' THEN 1 ELSE 0 END) as admin_count,
        SUM(CASE WHEN user_role='student' THEN 1 ELSE 0 END) as student_count,
        SUM(CASE WHEN user_role='analyzer' THEN 1 ELSE 0 END) as analyzer_count,
        SUM(CASE WHEN user_role='govt_emp' THEN 1 ELSE 0 END) as govt_count,
        SUM(CASE WHEN user_role='it_cs' THEN 1 ELSE 0 END) as it_cs_count,
        SUM(CASE WHEN user_status='active' THEN 1 ELSE 0 END) as active_users,
        SUM(CASE WHEN user_status='inactive' THEN 1 ELSE 0 END) as inactive_users
    FROM users
";
$user_stats_result = $conn->query($user_stats_sql);
$user_stats = $user_stats_result->fetch_assoc();

// Log access to admin panel
logSecurityEvent($conn, $admin_id, 'ADMIN_PANEL_ACCESS', 'Accessed advanced admin panel', $_SERVER['REMOTE_ADDR']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Admin Panel - Cyber Threat Tracker</title>
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
        .alert-success {
            background: #1a5f1a;
            color: #00ff00;
            border: 1px solid #00ff00;
        }
        .alert-danger {
            background: #5f1a1a;
            color: #ff4444;
            border: 1px solid #ff4444;
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
            font-size: 28px;
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
        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
            margin: 2px;
        }
        .btn-active {
            background: #00dd00;
            color: #10141a;
            border: none;
        }
        .btn-inactive {
            background: #ff6600;
            color: #10141a;
            border: none;
        }
        .btn-delete {
            background: #ff4444;
            color: #fff;
            border: none;
        }
        .status-active {
            color: #00dd00;
            font-weight: bold;
        }
        .status-inactive {
            color: #ff6600;
            font-weight: bold;
        }
        .role-admin { color: #ff4444; }
        .role-analyzer { color: #00ffe7; }
        .role-student { color: #00dd00; }
        .role-govt_emp { color: #ff9900; }
        .role-it_cs { color: #ffdd00; }
        .event-type {
            background: #232b39;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
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
        .modal-content {
            background: #181f2a;
            border: 2px solid #00ffe7;
            color: #e0e6ed;
        }
        .modal-header {
            border-bottom: 1px solid #232b39;
        }
        .modal-footer {
            border-top: 1px solid #232b39;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="admin_dashboard.php" style="color: #00ffe7; font-weight: bold;">⚙️ ADMIN PANEL</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="admin_analytics.php">Analytics</a></li>
                <li class="nav-item"><a class="nav-link active" href="admin_panel.php">🔧 Advanced Panel</a></li>
                <li class="nav-item"><a class="nav-link" href="audit_logs_viewer.php">📋 Audit Logs</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <h1>⚙️ Advanced Admin Panel</h1>

    <!-- Alerts -->
    <?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- User Statistics -->
    <h2>📊 User Statistics</h2>
    <div class="row">
        <div class="col-md-2">
            <div class="stat-box">
                <h5>Total Users</h5>
                <div class="number"><?php echo $user_stats['total_users']; ?></div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-box">
                <h5>Active</h5>
                <div class="number" style="color: #00dd00;"><?php echo $user_stats['active_users']; ?></div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-box">
                <h5>Inactive</h5>
                <div class="number" style="color: #ff6600;"><?php echo $user_stats['inactive_users']; ?></div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-box">
                <h5>Admins</h5>
                <div class="number"><?php echo $user_stats['admin_count']; ?></div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-box">
                <h5>Analyzers</h5>
                <div class="number"><?php echo $user_stats['analyzer_count']; ?></div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-box">
                <h5>Students</h5>
                <div class="number"><?php echo $user_stats['student_count']; ?></div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#users">👥 User Management</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#audit">📋 Audit Logs</a></li>
    </ul>

    <div class="tab-content">
        <!-- User Management Tab -->
        <div id="users" class="tab-pane fade show active">
            <h2 style="margin-top: 20px;">User Management</h2>
            <div class="table-container">
                <table class="table table-dark">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_users as $user): ?>
                        <tr>
                            <td><?php echo $user['user_id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td class="role-<?php echo $user['user_role']; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $user['user_role'])); ?>
                            </td>
                            <td>
                                <span class="status-<?php echo ($user['user_status'] ?? 'active'); ?>">
                                    <?php echo strtoupper($user['user_status'] ?? 'active'); ?>
                                </span>
                            </td>
                            <td><?php echo substr($user['created_at'], 0, 10); ?></td>
                            <td>
                                <?php if ($user['user_id'] !== $admin_id): ?>
                                    <!-- Toggle Status Button -->
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="toggle_user">
                                        <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                        <button type="submit" class="btn btn-sm <?php echo ($user['user_status'] ?? 'active') === 'active' ? 'btn-inactive' : 'btn-active'; ?>">
                                            <?php echo ($user['user_status'] ?? 'active') === 'active' ? 'Disable' : 'Enable'; ?>
                                        </button>
                                    </form>

                                    <!-- Delete Button (with modal confirmation) -->
                                    <button class="btn btn-sm btn-delete" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $user['user_id']; ?>">
                                        Delete
                                    </button>

                                    <!-- Delete Confirmation Modal -->
                                    <div class="modal fade" id="deleteModal<?php echo $user['user_id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Confirm Delete</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Are you sure you want to delete user <strong><?php echo htmlspecialchars($user['username']); ?></strong>?</p>
                                                    <p>This action is <strong>IRREVERSIBLE</strong> and will also delete:</p>
                                                    <ul>
                                                        <li>All activity logs</li>
                                                        <li>All security logs</li>
                                                        <li>All failed login attempts</li>
                                                    </ul>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="action" value="delete_user">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                                        <input type="hidden" name="confirm" value="YES">
                                                        <button type="submit" class="btn btn-danger">Delete Permanently</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span style="color: #00ffe7;">(Your Account)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Audit Logs Tab -->
        <div id="audit" class="tab-pane fade">
            <h2 style="margin-top: 20px;">Security Audit Logs</h2>
            <div class="table-container">
                <table class="table table-dark">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>User</th>
                            <th>Event Type</th>
                            <th>Details</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($security_logs as $log): ?>
                        <tr>
                            <td><?php echo substr($log['event_timestamp'], 0, 19); ?></td>
                            <td><?php echo htmlspecialchars($log['username'] ?? 'System'); ?></td>
                            <td>
                                <span class="event-type"><?php echo htmlspecialchars($log['event_type']); ?></span>
                            </td>
                            <td><?php echo htmlspecialchars(substr($log['details'], 0, 50)); ?></td>
                            <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<footer class="text-center mt-5 py-3" style="background:#181f2a; color:#00ffe7; border-top:1px solid #00ffe7;">
  &copy; <?php echo date('Y'); ?> Cyber Threat Tracker — Advanced Admin Panel
</footer>

</body>
</html>
