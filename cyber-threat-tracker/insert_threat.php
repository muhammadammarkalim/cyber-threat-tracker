<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

$success = $error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $threat_name = trim($_POST['threat_name']);
    $description = trim($_POST['description']);
    $severity = trim($_POST['severity']);
    $affected_industry = trim($_POST['affected_industry']);
    $submitted_by = $_SESSION['username'];
    $reported_date = date('Y-m-d');

    if ($threat_name && $description && $severity && $affected_industry) {
        $stmt = $conn->prepare("INSERT INTO threats (threat_name, description, severity, affected_industry, reported_date, submitted_by) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $threat_name, $description, $severity, $affected_industry, $reported_date, $submitted_by);

        if ($stmt->execute()) {
            // Log the submission
            $user_id = $_SESSION['user_id'];
            $log_stmt = $conn->prepare("INSERT INTO logs (user_id, action) VALUES (?, ?)");
            $action = "Submitted new threat: $threat_name";
            $log_stmt->bind_param("is", $user_id, $action);
            $log_stmt->execute();
            $log_stmt->close();
            $success = "Threat successfully submitted.";
        } else {
            $error = "Database error. Try again.";
        }
    } else {
        $error = "All fields are required.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Submit Threat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary ctt-navbar">
  <div class="container-fluid">
    <a class="navbar-brand" href="user_dashboard.php">Cyber Threat Tracker</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="user_dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link active" href="insert_threat.php">Add Threat</a></li>
        <li class="nav-item"><a class="nav-link" href="view_threats.php">My Threats</a></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>
<span class="cyber-webname">Cyber Threat Tracker</span>
<div class="cyber-welcome">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>! Submit a new threat below.</div>
<div class="cyber-container">
  <h1 class="mb-4">Submit New Threat</h1>
  <?php if ($success): ?>
      <div class="cyber-alert-success"><?php echo $success; ?></div>
  <?php elseif ($error): ?>
      <div class="cyber-alert-error"><?php echo $error; ?></div>
  <?php endif; ?>
  <form action="insert_threat.php" method="POST">
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
      <button type="submit" class="btn cyber-btn">Submit Threat</button>
  </form>
  <a href="user_dashboard.php" class="btn btn-secondary mt-3">&larr; Back to Dashboard</a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<footer class="text-center mt-5 py-3" style="background:#181f2a; color:#00ffe7; border-top:1px solid #00ffe7;">
  &copy; <?php echo date('Y'); ?> Cyber Threat Tracker &mdash; Stay Secure, Stay Ahead.
</footer>
</body>
</html>
