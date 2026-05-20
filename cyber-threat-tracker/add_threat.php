<?php
session_start();
include 'db_connect.php';
include 'security_functions.php';

requireAdmin();

$success = $error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        die('CSRF token validation failed');
    }
    
    $threat_name = sanitizeInput(trim($_POST['threat_name']));
    $description = sanitizeInput(trim($_POST['description']));
    $severity = sanitizeInput(trim($_POST['severity']));
    $affected_industry = sanitizeInput(trim($_POST['affected_industry']));
    $reported_date = date('Y-m-d');
    $admin_username = $_SESSION['admin'];
    // Get admin's username as submitted_by
    $submitted_by = $admin_username;

    $threat_errors = validateThreatData($threat_name, $description, $severity, $affected_industry);
    if (!empty($threat_errors)) {
        $error = implode("<br>", $threat_errors);
    } else {
        $stmt = $conn->prepare("INSERT INTO threats (threat_name, description, severity, affected_industry, reported_date, submitted_by) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $threat_name, $description, $severity, $affected_industry, $reported_date, $submitted_by);

        if ($stmt->execute()) {
            // Log the submission
            $admin_id = null;
            $res = $conn->prepare("SELECT user_id FROM users WHERE username=?");
            $res->bind_param("s", $admin_username);
            $res->execute();
            $res->bind_result($admin_id);
            $res->fetch();
            $res->close();
            if ($admin_id !== null) {
                $log_stmt = $conn->prepare("INSERT INTO logs (user_id, action) VALUES (?, ?)");
                $action = "Admin added new threat: $threat_name";
                $log_stmt->bind_param("is", $admin_id, $action);
                $log_stmt->execute();
                $log_stmt->close();
            }
            $success = "Threat successfully added.";
        } else {
            $error = "Database error. Try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Threat</title>
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
        <li class="nav-item"><a class="nav-link active" href="#">Add Threat</a></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>
<span class="cyber-webname">Cyber Threat Tracker</span>
<div class="cyber-welcome">Add a new threat below.</div>
<div class="cyber-container" style="max-width:600px;">
  <h1 class="mb-4">Add New Threat</h1>
  <?php if ($success): ?>
      <div class="cyber-alert-success"><?php echo $success; ?></div>
  <?php elseif ($error): ?>
      <div class="cyber-alert-error"><?php echo $error; ?></div>
  <?php endif; ?>
  <form action="add_threat.php" method="POST">
      <div class="mb-3">
        <label>Threat Name</label>
        <input type="text" name="threat_name" class="form-control" required />
      </div>
      <div class="mb-3">
        <label>Description</label>
        <textarea name="description" class="form-control" required></textarea>
      </div>
      <div class="mb-3">
        <label>Severity</label>
        <select name="severity" class="form-select" required>
            <option value="">Select Severity</option>
            <option value="Low">Low</option>
            <option value="Medium">Medium</option>
            <option value="High">High</option>
            <option value="Critical">Critical</option>
        </select>
      </div>
      <div class="mb-3">
        <label>Affected Industry</label>
        <input type="text" name="affected_industry" class="form-control" required />
      </div>
      <?php csrfField(); ?>
      <button type="submit" class="btn cyber-btn">Add Threat</button>
  </form>
  <a href="manage_threats_admin.php" class="btn btn-secondary mt-3">&larr; Back to Threat Management</a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
