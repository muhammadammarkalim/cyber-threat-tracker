<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

$sql = "SELECT * FROM alerts ORDER BY created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Alerts - Admin</title>
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
        <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="manage_threats_admin.php">Manage Threats</a></li>
        <li class="nav-item"><a class="nav-link active" href="manage_alerts_admin.php">Manage Alerts</a></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>
<span class="cyber-webname">Cyber Threat Tracker</span>
<div class="cyber-welcome">Welcome, Admin! Manage all alerts below.</div>
<div class="cyber-container">
    <h1 class="mb-4">Manage Alerts</h1>
    <?php if (isset($_GET['success'])): ?>
        <div class="cyber-alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
    <?php endif; ?>
    <a href="add_alert.php" class="btn cyber-btn mb-3">Add New Alert</a>
    <div class="table-responsive">
      <table class="table cyber-table table-bordered">
          <thead>
              <tr>
                  <th>Alert</th>
                  <th>Severity</th>
                  <th>Date Created</th>
                  <th>Actions</th>
              </tr>
          </thead>
          <tbody>
              <?php while ($alert = $result->fetch_assoc()): ?>
              <tr>
                  <td><?php echo htmlspecialchars($alert['title']); ?></td>
                  <td><?php echo htmlspecialchars($alert['type']); ?></td>
                  <td><?php echo htmlspecialchars($alert['created_at']); ?></td>
                  <td>
                      <a href="edit_alert.php?id=<?php echo $alert['id']; ?>" class="btn cyber-btn btn-sm">Edit</a>
                      <a href="delete_alert.php?id=<?php echo $alert['id']; ?>" class="btn btn-secondary btn-sm" onclick="return confirm('Are you sure?');">Delete</a>
                  </td>
              </tr>
              <?php endwhile; ?>
          </tbody>
      </table>
    </div>
    <a href="admin_dashboard.php" class="btn btn-secondary mt-3">&larr; Back to Dashboard</a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<footer class="text-center mt-5 py-3" style="background:#181f2a; color:#00ffe7; border-top:1px solid #00ffe7;">
  &copy; <?php echo date('Y'); ?> Cyber Threat Tracker &mdash; Stay Secure, Stay Ahead.
</footer>
</body>
</html>
