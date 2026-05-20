<?php
session_start();
include 'db_connect.php';
include 'security_functions.php';

requireAdmin();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        die('CSRF token validation failed');
    }
    
    // Form fields are alert_title and alert_message, but DB columns are title and message
    $title = sanitizeInput($_POST['alert_title']);
    $message = sanitizeInput($_POST['alert_message']);
    $type = sanitizeInput($_POST['severity']); // Use 'type' for the DB column

    $stmt = $conn->prepare("INSERT INTO alerts (title, message, type, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sss", $title, $message, $type);

    if ($stmt->execute()) {
        // Log the alert creation
        $admin_username = $_SESSION['admin'];
        $admin_id = null;
        $res = $conn->prepare("SELECT user_id FROM users WHERE username=?");
        $res->bind_param("s", $admin_username);
        $res->execute();
        $res->bind_result($admin_id);
        $res->fetch();
        $res->close();
        if ($admin_id !== null) {
            $log_stmt = $conn->prepare("INSERT INTO logs (user_id, action) VALUES (?, ?)");
            $action = "Added new alert: $title";
            $log_stmt->bind_param("is", $admin_id, $action);
            $log_stmt->execute();
            $log_stmt->close();
        }
        header("Location: manage_alerts_admin.php?success=Alert added successfully");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Alert</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg cyber-navbar">
  <div class="container-fluid">
    <a class="navbar-brand" href="admin_dashboard.php">Cyber Threat Tracker</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="manage_alerts_admin.php">Manage Alerts</a></li>
        <li class="nav-item"><a class="nav-link active" href="#">Add Alert</a></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>
<div class="cyber-container" style="max-width:600px;">
  <h1 class="mb-4">Add New Alert</h1>
  <form action="" method="POST">
    <div class="mb-3">
      <label>Alert Title</label>
      <input type="text" name="alert_title" class="form-control" required />
    </div>
    <div class="mb-3">
      <label>Alert Message</label>
      <textarea name="alert_message" class="form-control" required></textarea>
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
    <?php csrfField(); ?>
    <button type="submit" class="btn cyber-btn">Add Alert</button>
  </form>
  <a href="manage_alerts_admin.php" class="btn btn-secondary mt-3">← Back to Alerts</a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
