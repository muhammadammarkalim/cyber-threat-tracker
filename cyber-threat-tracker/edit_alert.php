<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

$alert_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $message = $_POST['message'];
    $type = $_POST['severity']; // Use 'type' for the DB column

    $stmt = $conn->prepare("UPDATE alerts SET title = ?, message = ?, type = ? WHERE id = ?");
    $stmt->bind_param("sssi", $title, $message, $type, $alert_id);
    $stmt->execute();

    // Log the alert update
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
        $action = "Updated alert: $title (ID: $alert_id)";
        $log_stmt->bind_param("is", $admin_id, $action);
        $log_stmt->execute();
        $log_stmt->close();
    }

    header("Location: manage_alerts_admin.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM alerts WHERE id = ?");
$stmt->bind_param("i", $alert_id);
$stmt->execute();
$result = $stmt->get_result();
$alert = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Alert</title>
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
        <li class="nav-item"><a class="nav-link active" href="#">Edit Alert</a></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>
<div class="cyber-container" style="max-width:600px;">
  <h1 class="mb-4">Edit Alert</h1>
  <form action="" method="POST">
    <div class="mb-3">
      <label>Alert Title</label>
      <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($alert['title']); ?>" required>
    </div>
    <div class="mb-3">
      <label>Alert Message</label>
      <textarea name="message" class="form-control" required><?php echo htmlspecialchars($alert['message']); ?></textarea>
    </div>
    <div class="mb-3">
      <label>Severity</label>
      <select name="severity" class="form-select" required>
        <option value="Low" <?php if ($alert['type'] == 'Low') echo 'selected'; ?>>Low</option>
        <option value="Medium" <?php if ($alert['type'] == 'Medium') echo 'selected'; ?>>Medium</option>
        <option value="High" <?php if ($alert['type'] == 'High') echo 'selected'; ?>>High</option>
        <option value="Critical" <?php if ($alert['type'] == 'Critical') echo 'selected'; ?>>Critical</option>
      </select>
    </div>
    <button type="submit" class="btn cyber-btn">Update Alert</button>
  </form>
  <a href="manage_alerts_admin.php" class="btn btn-secondary mt-3">← Back to Alerts</a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
