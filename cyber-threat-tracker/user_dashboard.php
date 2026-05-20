<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Fetch total threats by this user
$threat_query = "SELECT COUNT(*) as total FROM threats WHERE submitted_by = ?";
$stmt = $conn->prepare($threat_query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$my_threat_count = $result->fetch_assoc()['total'] ?? 0;

// Fetch total threats (all users)
$all_threats_result = $conn->query("SELECT COUNT(*) as total FROM threats");
$all_threat_count = $all_threats_result->fetch_assoc()['total'] ?? 0;

// Fetch total alerts (visible to all)
$alert_result = $conn->query("SELECT COUNT(*) as total FROM alerts");
$alert_count = $alert_result->fetch_assoc()['total'] ?? 0;

// Fetch latest admin alerts (show 5)
$alerts = $conn->query("SELECT * FROM alerts ORDER BY created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Dashboard - Cyber Threat Tracker</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
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
        <li class="nav-item"><a class="nav-link active" href="user_dashboard.php">Dashboard</a></li>
        <?php
        // Add role-specific analytics link
        if ($_SESSION['user_role'] === 'analyzer') {
            echo '<li class="nav-item"><a class="nav-link" href="analyzer_analytics.php">📊 Threat Analysis</a></li>';
        } elseif ($_SESSION['user_role'] === 'govt_emp') {
            echo '<li class="nav-item"><a class="nav-link" href="govt_analytics.php">📊 Threat Intelligence</a></li>';
        }
        ?>
        <li class="nav-item"><a class="nav-link" href="insert_threat.php">Add Threat</a></li>
        <li class="nav-item"><a class="nav-link" href="view_threats.php">My Threats</a></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>
<span class="cyber-webname">Cyber Threat Tracker</span>
<div class="cyber-welcome">Welcome, <?php echo htmlspecialchars($username); ?>!</div>
<div class="cyber-container">
  <h1 class="mb-4">User Dashboard</h1>
  <div class="row g-4">
    <div class="col-md-4">
      <div class="card cyber-card text-center">
        <div class="card-body">
          <h5 class="card-title">All Threats</h5>
          <p class="display-6"><?php echo $all_threat_count; ?></p>
          <div style="font-size:1rem;color:#00ffe7;">My Threats: <?php echo $my_threat_count; ?></div>
          <a href="view_threats.php" class="btn cyber-btn mt-2">View</a>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card cyber-card text-center">
        <div class="card-body">
          <h5 class="card-title">Add Threat</h5>
          <a href="insert_threat.php" class="btn cyber-btn">Add</a>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card cyber-card text-center">
        <div class="card-body">
          <h5 class="card-title">Search Threats</h5>
          <a href="search_threats_user.php" class="btn cyber-btn">Search</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Alerts from Admin Section -->
  <div class="card cyber-card mt-4 mb-4">
    <div class="card-body">
      <h4 class="card-title mb-3" style="color:#00ffe7;">Latest Alerts (by Admin)</h4>
      <?php if ($alerts->num_rows > 0): ?>
        <ul class="list-group list-group-flush">
          <?php while($alert = $alerts->fetch_assoc()): ?>
            <li class="list-group-item" style="background:#232b39; color:#e0e6ed; border:0;">
              <strong><?php echo htmlspecialchars($alert['title']); ?>:</strong>
              <?php echo htmlspecialchars($alert['message']); ?>
              <span class="badge" style="background:#00ffe7; color:#181f2a;"><?php echo $alert['created_at']; ?></span>
            </li>
          <?php endwhile; ?>
        </ul>
      <?php else: ?>
        <div class="cyber-alert-error">No alerts from admin yet.</div>
      <?php endif; ?>
    </div>
  </div>

  <!-- About Section -->
  <div class="card cyber-card mt-5">
    <div class="card-body">
      <h3 class="card-title mb-3">About Cyber Threat Tracker</h3>
      <p>
        Cyber Threat Tracker is a collaborative platform for reporting, tracking, and analyzing cyber threats across industries. Our mission is to empower users and organizations to stay informed and respond quickly to emerging cyber risks.
      </p>
    </div>
  </div>

  <!-- How It Works Section -->
  <div class="card cyber-card mt-4">
    <div class="card-body">
      <h3 class="card-title mb-3">How It Works</h3>
      <ol>
        <li><strong>Report:</strong> Submit new cyber threats you encounter.</li>
        <li><strong>Track:</strong> View and search threats reported by you and others.</li>
        <li><strong>Respond:</strong> Use the information to protect your organization and share knowledge.</li>
      </ol>
    </div>
  </div>

  <!-- Team Section -->
  <div class="card cyber-card mt-4">
    <div class="card-body">
      <h3 class="card-title mb-3">Our Team</h3>
      <div class="row justify-content-center align-items-stretch">
        <div class="col-md-4 mb-3 d-flex">
          <div class="card cyber-card text-center flex-fill" style="min-height:170px; height:100%; display:flex; flex-direction:column; justify-content:center;">
            <div class="card-body d-flex flex-column justify-content-center align-items-center" style="height:100%;">
              <h5 class="mb-2" style="color:#00ffe7;">Ammar Kaleem</h5>
              <div>BS in Computer Science</div>
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-3 d-flex">
          <div class="card cyber-card text-center flex-fill" style="min-height:170px; height:100%; display:flex; flex-direction:column; justify-content:center;">
            <div class="card-body d-flex flex-column justify-content-center align-items-center" style="height:100%;">
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
