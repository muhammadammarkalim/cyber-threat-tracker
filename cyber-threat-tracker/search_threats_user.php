<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

$search_results = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $keyword = '%' . $_POST['keyword'] . '%';

    $stmt = $conn->prepare("SELECT * FROM threats WHERE threat_name LIKE ? OR affected_industry LIKE ?");
    $stmt->bind_param("ss", $keyword, $keyword);
    $stmt->execute();
    $search_results = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Search Threats</title>
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
        <li class="nav-item"><a class="nav-link" href="insert_threat.php">Add Threat</a></li>
        <li class="nav-item"><a class="nav-link active" href="search_threats_user.php">Search Threats</a></li>
        <li class="nav-item"><a class="nav-link" href="view_threats.php">My Threats</a></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>
<span class="cyber-webname">Cyber Threat Tracker</span>
<div class="cyber-welcome">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>! Search for threats below.</div>
<div class="cyber-container">
  <!-- Common Attacks Card -->
  <div class="card cyber-card mb-4" style="background:#232b39;">
    <div class="card-body">
      <h2 class="card-title mb-3" style="color:#00ffe7;">Common Cyber Attacks &amp; Protection Tips</h2>
      <ul style="font-size:1.08rem; margin-bottom:0;">
        <li><b>Malware:</b> Malicious software that damages or disables computers. <span style="color:#00ffe7;">Tip:</span> Keep your antivirus updated and avoid suspicious downloads.</li>
        <li><b>Phishing:</b> Fraudulent attempts to steal sensitive info via fake emails or sites. <span style="color:#00ffe7;">Tip:</span> Never click unknown links or share credentials.</li>
        <li><b>Ransomware:</b> Malware that encrypts data and demands payment for release. <span style="color:#00ffe7;">Tip:</span> Regularly back up data and avoid opening unknown attachments.</li>
        <li><b>DDoS Attack:</b> Overwhelms a service with traffic to make it unavailable. <span style="color:#00ffe7;">Tip:</span> Use firewalls and monitor network traffic for anomalies.</li>
        <li><b>SQL Injection:</b> Injecting malicious SQL to manipulate databases. <span style="color:#00ffe7;">Tip:</span> Always validate and sanitize user inputs.</li>
      </ul>
    </div>
  </div>

  <h1 class="mb-4">Search Threats</h1>
  <form action="" method="POST" class="row g-3 mb-4">
    <div class="col-md-8">
      <input type="text" name="keyword" class="form-control" placeholder="Search by title, type, or industry..." required>
    </div>
    <div class="col-md-4">
      <button type="submit" class="btn cyber-btn w-100">Search</button>
    </div>
  </form>

  <?php if (!empty($search_results) && $search_results->num_rows > 0): ?>
    <div class="table-responsive">
      <table class="table cyber-table table-bordered">
          <thead>
              <tr>
                  <th>ID</th>
                  <th>Threat Name</th>
                  <th>Industry</th>
                  <th>Severity</th>
                  <th>Date</th>
              </tr>
          </thead>
          <tbody>
          <?php while ($row = $search_results->fetch_assoc()): ?>
              <tr>
                  <td><?php echo $row['id']; ?></td>
                  <td><?php echo $row['threat_name']; ?></td>
                  <td><?php echo $row['affected_industry']; ?></td>
                  <td><?php echo $row['severity']; ?></td>
                  <td><?php echo $row['reported_date']; ?></td>
              </tr>
          <?php endwhile; ?>
          </tbody>
      </table>
    </div>
  <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
    <div class="cyber-alert-error">No threats found matching your search.</div>
  <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<footer class="text-center mt-5 py-3" style="background:#181f2a; color:#00ffe7; border-top:1px solid #00ffe7;">
  &copy; <?php echo date('Y'); ?> Cyber Threat Tracker &mdash; Stay Secure, Stay Ahead.
</footer>
</body>
</html>
