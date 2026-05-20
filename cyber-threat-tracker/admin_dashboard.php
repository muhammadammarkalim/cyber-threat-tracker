<?php
session_start();
include 'security_functions.php';
requireAdmin();

include 'db_connect.php';

// Fetch stats
$threatCount = $conn->query("SELECT COUNT(*) AS total FROM threats")->fetch_assoc()['total'];
$userCount = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];
$alertResult = $conn->query("SELECT * FROM alerts ORDER BY created_at DESC LIMIT 5");

// Filter logic
$where = [];
$params = [];
$types = '';

if (isset($_GET['industry']) && $_GET['industry'] !== '') {
    $where[] = 'affected_industry = ?';
    $params[] = $_GET['industry'];
    $types .= 's';
}
if (isset($_GET['severity']) && $_GET['severity'] !== '') {
    $where[] = 'severity = ?';
    $params[] = $_GET['severity'];
    $types .= 's';
}
if (isset($_GET['threat_name']) && $_GET['threat_name'] !== '') {
    $where[] = 'threat_name LIKE ?';
    $params[] = '%' . $_GET['threat_name'] . '%';
    $types .= 's';
}
if (isset($_GET['user_name']) && $_GET['user_name'] !== '') {
    $where[] = 'submitted_by LIKE ?';
    $params[] = '%' . $_GET['user_name'] . '%';
    $types .= 's';
}

$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
$threatSql = "SELECT * FROM threats $whereSql ORDER BY reported_date DESC LIMIT 10";
$threatStmt = $conn->prepare($threatSql);
if ($params) $threatStmt->bind_param($types, ...$params);
$threatStmt->execute();
$threats = $threatStmt->get_result();

// Activity log: show last 10 actions
$logResult = $conn->query("SELECT l.*, u.username FROM logs l LEFT JOIN users u ON l.user_id = u.user_id ORDER BY l.action_timestamp DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary ctt-navbar">
  <div class="container-fluid">
    <a class="navbar-brand" href="admin_dashboard.php">Cyber Threat Tracker</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link active" href="admin_dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="admin_analytics.php">📊 Analytics</a></li>
        <li class="nav-item"><a class="nav-link" href="manage_threats_admin.php">Manage Threats</a></li>
        <li class="nav-item"><a class="nav-link" href="manage_alerts_admin.php">Manage Alerts</a></li>
        <li class="nav-item"><a class="nav-link" href="admin_panel.php">🔧 Advanced Panel</a></li>
        <li class="nav-item"><a class="nav-link" href="user_register.php">Add User</a></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>
<div class="container">
  <h1 class="mb-4">Admin Dashboard</h1>
  <div class="row">
    <div class="col-md-6">
      <div class="card cyber-card mb-4">
        <div class="card-header" style="background:#232b39; color:#00ffe7; border-bottom:1px solid #00ffe7;">
          <h5 class="mb-0">System Stats</h5>
        </div>
        <div class="card-body">
          <ul>
            <li>Total Users: <?php echo $userCount; ?></li>
            <li>Reported Threats: <?php echo $threatCount; ?></li>
          </ul>
        </div>
      </div>
      <div class="card cyber-card mb-4">
        <div class="card-header" style="background:#232b39; color:#00ffe7; border-bottom:1px solid #00ffe7;">
          <h5 class="mb-0">Latest Alerts</h5>
        </div>
        <div class="card-body">
          <ul class="list-group list-group-flush">
            <?php while($alert = $alertResult->fetch_assoc()): ?>
              <li class="list-group-item" style="background:#232b39; color:#e0e6ed; border:0;">
                <strong><?= htmlspecialchars($alert['title']) ?>:</strong> <?= htmlspecialchars($alert['message']) ?> 
                <span class="badge" style="background:#00ffe7; color:#181f2a;"><?= $alert['created_at'] ?></span>
              </li>
            <?php endwhile; ?>
          </ul>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card cyber-card mb-4">
        <div class="card-header" style="background:#232b39; color:#00ffe7; border-bottom:1px solid #00ffe7;">
          <h5 class="mb-0">Filter Threats</h5>
        </div>
        <div class="card-body">
          <form method="get">
            <div class="mb-3">
              <label class="form-label">Industry</label>
              <select class="form-select" name="industry">
                <option value="">All Industries</option>
                <option value="Banking" <?= (isset($_GET['industry']) && $_GET['industry'] === 'Banking') ? 'selected' : ''; ?>>Banking</option>
                <option value="Healthcare" <?= (isset($_GET['industry']) && $_GET['industry'] === 'Healthcare') ? 'selected' : ''; ?>>Healthcare</option>
                <option value="E-commerce" <?= (isset($_GET['industry']) && $_GET['industry'] === 'E-commerce') ? 'selected' : ''; ?>>E-commerce</option>
                <option value="Government" <?= (isset($_GET['industry']) && $_GET['industry'] === 'Government') ? 'selected' : ''; ?>>Government</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Severity</label>
              <select class="form-select" name="severity">
                <option value="">All Severities</option>
                <option value="Low" <?= (isset($_GET['severity']) && $_GET['severity'] === 'Low') ? 'selected' : ''; ?>>Low</option>
                <option value="Medium" <?= (isset($_GET['severity']) && $_GET['severity'] === 'Medium') ? 'selected' : ''; ?>>Medium</option>
                <option value="High" <?= (isset($_GET['severity']) && $_GET['severity'] === 'High') ? 'selected' : ''; ?>>High</option>
                <option value="Critical" <?= (isset($_GET['severity']) && $_GET['severity'] === 'Critical') ? 'selected' : ''; ?>>Critical</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Threat Name</label>
              <input type="text" class="form-control" name="threat_name" value="<?= isset($_GET['threat_name']) ? htmlspecialchars($_GET['threat_name']) : '' ?>" placeholder="Search by threat name">
            </div>
            <div class="mb-3">
              <label class="form-label">User Name</label>
              <input type="text" class="form-control" name="user_name" value="<?= isset($_GET['user_name']) ? htmlspecialchars($_GET['user_name']) : '' ?>" placeholder="Search by user name">
            </div>
            <button type="submit" class="btn cyber-btn">Filter</button>
          </form>
        </div>
      </div>
    </div>
  </div>
  <div class="card cyber-card mb-4">
    <div class="card-header" style="background:#232b39; color:#00ffe7; border-bottom:1px solid #00ffe7;">
      <h5 class="mb-0">Threats</h5>
    </div>
    <div class="card-body">
      <table class="table cyber-table table-bordered">
        <thead>
          <tr>
            <th>Threat Name</th>
            <th>Description</th>
            <th>Severity</th>
            <th>Industry</th>
            <th>Date</th>
            <th>Submitted By</th>
          </tr>
        </thead>
        <tbody>
          <?php while($row = $threats->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['threat_name']) ?></td>
              <td><?= htmlspecialchars($row['description']) ?></td>
              <td><?= htmlspecialchars($row['severity']) ?></td>
              <td><?= htmlspecialchars($row['affected_industry']) ?></td>
              <td><?= htmlspecialchars($row['reported_date']) ?></td>
              <td><?= htmlspecialchars($row['submitted_by']) ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="card cyber-card mb-4">
    <div class="card-header" style="background:#232b39; color:#00ffe7; border-bottom:1px solid #00ffe7;">
      <h5 class="mb-0">Recent Activity Logs</h5>
    </div>
    <div class="card-body">
      <table class="table cyber-table table-bordered">
        <thead>
          <tr>
            <th>User</th>
            <th>Action</th>
            <th>Timestamp</th>
          </tr>
        </thead>
        <tbody>
          <?php while($log = $logResult->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($log['username'] ?? 'System') ?></td>
              <td><?= htmlspecialchars($log['action']) ?></td>
              <td><?= htmlspecialchars($log['action_timestamp']) ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- About Section -->
  <div class="card cyber-card mt-5">
    <div class="card-body">
      <h3 class="card-title mb-3">About Cyber Threat Tracker</h3>
      <p>
        Cyber Threat Tracker is a comprehensive platform designed to help organizations and individuals report, track, and analyze cyber threats in real time. The system enables collaborative sharing of threat intelligence, empowering users to stay ahead of evolving cyber risks and respond effectively.
      </p>
      <ul>
        <li>Centralized threat reporting and management</li>
        <li>Real-time alerts and notifications</li>
        <li>Role-based dashboards for users and admins</li>
        <li>Activity logs and analytics</li>
      </ul>
    </div>
  </div>

  <!-- Team Section -->
  <div class="card cyber-card mt-4 mb-4">
    <div class="card-body">
      <h3 class="card-title mb-3">Our Team</h3>
      <div class="row justify-content-center">
        <div class="col-md-4 mb-3">
          <div class="card cyber-card text-center" style="min-height:150px;">
            <div class="card-body d-flex flex-column justify-content-center align-items-center">
              <h5 class="mb-2" style="color:#00ffe7;">Ammar Kaleem</h5>
              <div>BS in Computer Science</div>
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-3">
          <div class="card cyber-card text-center" style="min-height:150px;">
            <div class="card-body d-flex flex-column justify-content-center align-items-center">
              <h5 class="mb-2" style="color:#00ffe7;">Abdullah Nasir</h5>
              <div>BS in Computer Science</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<footer class="text-center mt-5 py-3" style="background:#181f2a; color:#00ffe7; border-top:1px solid #00ffe7;">
  &copy; <?php echo date('Y'); ?> Cyber Threat Tracker &mdash; Stay Secure, Stay Ahead.
</footer>
</body>
</html>
