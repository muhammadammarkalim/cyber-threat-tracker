<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

if (!isset($_GET['id'])) {
    echo "No threat ID provided.";
    exit();
}

$threat_id = $_GET['id'];

$sql = "SELECT * FROM threats WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $threat_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo "Threat not found.";
    exit();
}

$threat = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Threat</title>
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
        <li class="nav-item"><a class="nav-link" href="manage_threats_admin.php">Manage Threats</a></li>
        <li class="nav-item"><a class="nav-link active" href="#">Edit Threat</a></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>
<div class="cyber-container" style="max-width:600px;">
  <h1 class="mb-4">Edit Threat</h1>
  <form action="update_threat.php" method="POST">
    <input type="hidden" name="threat_id" value="<?php echo $threat['id']; ?>" />
    <div class="mb-3">
      <label for="threat_name">Threat Name</label>
      <input type="text" name="threat_name" class="form-control" value="<?php echo htmlspecialchars($threat['threat_name']); ?>" required />
    </div>
    <div class="mb-3">
      <label for="description">Description</label>
      <textarea name="description" class="form-control" required><?php echo htmlspecialchars($threat['description']); ?></textarea>
    </div>
    <div class="mb-3">
      <label for="severity">Severity</label>
      <select name="severity" class="form-select" required>
        <option value="Low" <?php if($threat['severity'] === 'Low') echo 'selected'; ?>>Low</option>
        <option value="Medium" <?php if($threat['severity'] === 'Medium') echo 'selected'; ?>>Medium</option>
        <option value="High" <?php if($threat['severity'] === 'High') echo 'selected'; ?>>High</option>
        <option value="Critical" <?php if($threat['severity'] === 'Critical') echo 'selected'; ?>>Critical</option>
      </select>
    </div>
    <div class="mb-3">
      <label for="affected_industry">Industry</label>
      <input type="text" name="affected_industry" class="form-control" value="<?php echo htmlspecialchars($threat['affected_industry']); ?>" required />
    </div>
    <button type="submit" class="btn cyber-btn">Update Threat</button>
  </form>
  <a href="manage_threats_admin.php" class="btn btn-secondary mt-3">← Back to Threat Management</a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
