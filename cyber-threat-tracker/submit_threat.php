<?php
session_start();
include 'db_connect.php';

// Access control
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $threat_name = $_POST['threat_name'];
    $severity = $_POST['severity'];
    $affected_industry = $_POST['industry'];
    $description = $_POST['description'];
    $submitted_by = $_SESSION['username']; // Use username as in DB
    $reported_date = date('Y-m-d');

    $stmt = $conn->prepare("INSERT INTO threats (threat_name, description, severity, affected_industry, reported_date, submitted_by) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $threat_name, $description, $severity, $affected_industry, $reported_date, $submitted_by);
    if ($stmt->execute()) {
        header("Location: user_dashboard.php?success=1");
        exit();
    } else {
        echo "<p>Failed to submit threat. Please try again.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Submit Threat</title>
</head>
<body>
  <div class="form-container">
    <h1 class="site-title">Cyber Threat Tracker</h1>
    <h2>Submit a New Threat</h2>
    <form action="submit_threat.php" method="POST">
      <div class="form-group">
        <input type="text" name="threat_name" placeholder="Threat Name" required>
      </div>
      <div class="form-group">
        <select name="severity" required>
          <option value="">Select Severity</option>
          <option value="Low">Low</option>
          <option value="Medium">Medium</option>
          <option value="High">High</option>
          <option value="Critical">Critical</option>
        </select>
      </div>
      <div class="form-group">
        <select name="industry" required>
          <option value="">Select Industry</option>
          <option value="Finance">Finance</option>
          <option value="Healthcare">Healthcare</option>
          <option value="Education">Education</option>
          <option value="Government">Government</option>
          <option value="Technology">Technology</option>
        </select>
      </div>
      <div class="form-group">
        <textarea name="description" placeholder="Detailed Description" rows="5" required></textarea>
      </div>
      <div class="form-group">
        <button type="submit">Submit Threat</button>
      </div>
    </form>
    <a href="user_dashboard.php">Back to Dashboard</a>
  </div>
</body>
</html>
