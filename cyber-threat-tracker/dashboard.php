<?php
session_start();
include 'db_connect.php';

// Determine user type
$is_admin = isset($_SESSION['admin']);
$is_user = isset($_SESSION['user_id']) && !$is_admin;

if (!$is_admin && !$is_user) {
    header("Location: index.php");
    exit();
}

// Fetch stats
$userCount = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];
$threatCount = $conn->query("SELECT COUNT(*) AS total FROM threats")->fetch_assoc()['total'];
$alertCount = $conn->query("SELECT COUNT(*) AS total FROM alerts")->fetch_assoc()['total'];

$username = $is_user ? $_SESSION['username'] : null;
$userThreatCount = 0;
if ($is_user) {
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM threats WHERE submitted_by = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $userThreatCount = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Cyber Threat Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary ctt-navbar">
  <div class="container-fluid">
    <a class="navbar-brand" href="dashboard.php">Cyber Threat Tracker</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <?php if ($is_admin): ?>
          <li class="nav-item"><a class="nav-link active" href="dashboard.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="manage_threats_admin.php">Manage Threats</a></li>
          <li class="nav-item"><a class="nav-link" href="manage_alerts_admin.php">Manage Alerts</a></li>
          <li class="nav-item"><a class="nav-link" href="user_register.php">Add User</a></li>
          <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link active" href="dashboard.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="insert_threat.php">Add Threat</a></li>
          <li class="nav-item"><a class="nav-link" href="view_threats.php">My Threats</a></li>
          <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<span class="cyber-webname">Cyber Threat Tracker</span>
<div class="cyber-welcome">
  <?php
    if ($is_admin) {
      echo "Welcome, Admin!";
    } elseif ($is_user) {
      echo "Welcome, " . htmlspecialchars($username) . "!";
    }
  ?>
</div>
<div class="container mt-4">
    <h1 class="mb-4">Dashboard</h1>
    <?php if ($is_admin): ?>
      <div class="row g-4 mb-4">
        <div class="col-md-4">
          <div class="card text-center">
            <div class="card-body">
              <h5 class="card-title">Total Users</h5>
              <p class="display-6"><?php echo $userCount; ?></p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card text-center">
            <div class="card-body">
              <h5 class="card-title">Total Threats</h5>
              <p class="display-6"><?php echo $threatCount; ?></p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card text-center">
            <div class="card-body">
              <h5 class="card-title">Total Alerts</h5>
              <p class="display-6"><?php echo $alertCount; ?></p>
            </div>
          </div>
        </div>
      </div>
      <div class="row g-4">
        <div class="col-md-4">
          <a href="manage_threats_admin.php" class="btn btn-primary w-100 mb-2">Manage Threats</a>
        </div>
        <div class="col-md-4">
          <a href="manage_alerts_admin.php" class="btn btn-primary w-100 mb-2">Manage Alerts</a>
        </div>
        <div class="col-md-4">
          <a href="user_register.php" class="btn btn-primary w-100 mb-2">Add New User</a>
        </div>
      </div>
      <div class="row mt-4">
        <div class="col-12">
          <a href="logout.php" class="btn btn-secondary">Logout</a>
        </div>
      </div>
    <?php elseif ($is_user): ?>
      <div class="row g-4 mb-4">
        <div class="col-md-4">
          <div class="card text-center">
            <div class="card-body">
              <h5 class="card-title">My Threats</h5>
              <p class="display-6"><?php echo $userThreatCount; ?></p>
              <a href="view_threats.php" class="btn btn-primary">View</a>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card text-center">
            <div class="card-body">
              <h5 class="card-title">Add Threat</h5>
              <a href="insert_threat.php" class="btn btn-primary">Add</a>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card text-center">
            <div class="card-body">
              <h5 class="card-title">Search Threats</h5>
              <a href="search_threats_user.php" class="btn btn-primary">Search</a>
            </div>
          </div>
        </div>
      </div>
      <div class="row g-4">
        <div class="col-md-4">
          <div class="card text-center">
            <div class="card-body">
              <h5 class="card-title">Total Users</h5>
              <p class="display-6"><?php echo $userCount; ?></p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card text-center">
            <div class="card-body">
              <h5 class="card-title">Total Threats</h5>
              <p class="display-6"><?php echo $threatCount; ?></p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card text-center">
            <div class="card-body">
              <h5 class="card-title">Total Alerts</h5>
              <p class="display-6"><?php echo $alertCount; ?></p>
            </div>
          </div>
        </div>
      </div>
      <div class="row mt-4">
        <div class="col-12">
          <a href="logout.php" class="btn btn-secondary">Logout</a>
        </div>
      </div>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
