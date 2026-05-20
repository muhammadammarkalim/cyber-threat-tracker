<?php
include 'db_connect.php';
// Fetch stats
$userCount = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];
$threatCount = $conn->query("SELECT COUNT(*) AS total FROM threats")->fetch_assoc()['total'];
$alertCount = $conn->query("SELECT COUNT(*) AS total FROM alerts")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cyber Threat Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <!-- Add Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary ctt-navbar">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">Cyber Threat Tracker</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="user_login.php">User Login</a></li>
        <li class="nav-item"><a class="nav-link" href="user_register.php">Register</a></li>
        <li class="nav-item"><a class="nav-link" href="admin_login.php">Admin Login</a></li>
      </ul>
    </div>
  </div>
</nav>
<span class="cyber-webname">Cyber Threat Tracker</span>
<div class="cyber-welcome">Welcome to Cyber Threat Tracker</div>
<div class="cyber-container">
    <h1 class="mb-4 text-center" style="color:#00ffe7;">Monitor. Detect. Respond.</h1>

    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="card cyber-card text-center">
                <div class="card-body">
                    <h2 class="card-title">Admin Login</h2>
                    <p>Admins have full access to all data and controls.</p>
                    <a href="admin_login.php" class="btn cyber-btn">Login as Admin</a>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card cyber-card text-center">
                <div class="card-body">
                    <h2 class="card-title">User Access</h2>
                    <p>Register or log in to add and view threats.</p>
                    <a href="user_login.php" class="btn cyber-btn mb-2">Login as User</a>
                    <a href="user_register.php" class="btn btn-secondary">Register as User</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Boxes Section (moved below login cards, only one set) -->
    <div class="row mb-4 justify-content-center">
      <div class="col-md-4 mb-3">
        <div class="card cyber-card text-center py-3">
          <div class="card-body">
            <i class="bi bi-people-fill" style="font-size:2.5rem;color:#00ffe7;"></i>
            <h5 class="card-title mt-2">Total Users</h5>
            <p class="display-6"><?php echo $userCount; ?></p>
          </div>
        </div>
      </div>
      <div class="col-md-4 mb-3">
        <div class="card cyber-card text-center py-3">
          <div class="card-body">
            <i class="bi bi-shield-lock-fill" style="font-size:2.5rem;color:#00ffe7;"></i>
            <h5 class="card-title mt-2">Total Threats</h5>
            <p class="display-6"><?php echo $threatCount; ?></p>
          </div>
        </div>
      </div>
      <div class="col-md-4 mb-3">
        <div class="card cyber-card text-center py-3">
          <div class="card-body">
            <i class="bi bi-bell-fill" style="font-size:2.5rem;color:#00ffe7;"></i>
            <h5 class="card-title mt-2">Total Alerts</h5>
            <p class="display-6"><?php echo $alertCount; ?></p>
          </div>
        </div>
      </div>
    </div>

    <!-- Stylish About Section -->
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="card cyber-card mb-4" style="border-left: 6px solid #00ffe7; box-shadow: 0 0 24px #00ffe733;">
          <div class="card-body">
            <h2 class="card-title mb-3" style="color:#00ffe7;"><i class="bi bi-info-circle-fill me-2"></i>About Cyber Threat Tracker</h2>
            <p style="font-size:1.15rem;">
              <strong>Cyber Threat Tracker</strong> is a modern platform for reporting, tracking, and analyzing cyber threats across industries. Our mission is to empower users and organizations to stay informed, collaborate, and respond quickly to emerging cyber risks.
            </p>
            <ul style="font-size:1.05rem;">
              <li><i class="bi bi-check-circle-fill" style="color:#00ffe7;"></i> Real-time threat reporting and alerts</li>
              <li><i class="bi bi-check-circle-fill" style="color:#00ffe7;"></i> Secure, role-based dashboards for users and admins</li>
              <li><i class="bi bi-check-circle-fill" style="color:#00ffe7;"></i> Community-driven intelligence sharing</li>
              <li><i class="bi bi-check-circle-fill" style="color:#00ffe7;"></i> Designed and developed by <b>Ammar Kaleem</b> &amp; <b>Abdullah Nasir</b></li>
            </ul>
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
