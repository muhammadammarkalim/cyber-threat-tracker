<?php
session_start();
include 'db_connect.php';
include 'security_functions.php';

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

$sql = "SELECT t.*, u.full_name FROM threats t 
        JOIN users u ON t.submitted_by = u.username 
        ORDER BY t.reported_date DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Threats</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <style>
    .risk-score {
      display: inline-block;
      padding: 5px 12px;
      border-radius: 20px;
      font-weight: bold;
      font-size: 12px;
      white-space: nowrap;
    }
    .risk-minimal { background: #1a5f1a; color: #00ff00; }
    .risk-low { background: #5f5f1a; color: #ffff00; }
    .risk-medium { background: #5f4a1a; color: #ffaa00; }
    .risk-high { background: #5f2a1a; color: #ff6600; }
    .risk-critical { background: #5f1a1a; color: #ff4444; }
  </style>
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
        <li class="nav-item"><a class="nav-link active" href="manage_threats_admin.php">Manage Threats</a></li>
        <li class="nav-item"><a class="nav-link" href="manage_alerts_admin.php">Manage Alerts</a></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>
<span class="cyber-webname">Cyber Threat Tracker</span>
<div class="cyber-welcome">Welcome, Admin! Manage all reported threats below.</div>
<div class="cyber-container">
  <h1 class="mb-4">Manage Threats</h1>
  <?php if (isset($success)) { echo '<div class="cyber-alert-success">'.$success.'</div>'; } ?>
  <a href="add_threat.php" class="btn cyber-btn mb-3">Add New Threat</a>
  <div class="table-responsive">
    <table class="table cyber-table table-bordered">
        <thead>
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Severity</th>
                <th>Industry</th>
                <th>Risk Score</th>
                <th>Date Reported</th>
                <th>Reported By</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
              <?php while ($row = $result->fetch_assoc()): 
                $risk_score = calculateRiskScore($row['severity'], $row['reported_date'], $row['affected_industry']);
                $risk_level = getRiskLevel($risk_score);
                $risk_color = getRiskColor($risk_score);
              ?>
                <tr>
                  <td><?php echo htmlspecialchars($row['threat_name']); ?></td>
                  <td><?php echo htmlspecialchars($row['description']); ?></td>
                  <td><?php echo htmlspecialchars($row['severity']); ?></td>
                  <td><?php echo htmlspecialchars($row['affected_industry']); ?></td>
                  <td>
                    <span class="risk-score risk-<?php echo strtolower($risk_level); ?>" style="background: <?php echo $risk_color; ?>; color: #fff;">
                      <?php echo $risk_score; ?>/100
                    </span>
                  </td>
                  <td><?php echo htmlspecialchars($row['reported_date']); ?></td>
                  <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                  <td>
                    <a href="edit_threat.php?id=<?php echo $row['id']; ?>" class="btn cyber-btn btn-sm">Edit</a>
                    <a href="delete_threat.php?id=<?php echo $row['id']; ?>" class="btn btn-secondary btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="8">No threats found.</td></tr>
            <?php endif; ?>
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
