<?php
session_start();
include("db_connect.php");
include("security_functions.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        die('CSRF token validation failed');
    }
    
    // Check rate limiting
    $ip_address = $_SERVER['REMOTE_ADDR'];
    if (isRateLimited($conn, $ip_address)) {
        die("<script>alert('Too many login attempts. Please try again later.'); window.location.href='admin_login.php';</script>");
    }
    
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Get user with admin role
    $stmt = $conn->prepare("SELECT user_id, password, user_role FROM users WHERE username = ? AND user_role = 'admin'");
    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password using secure hashing
        if (verifyPassword($password, $user['password'])) {
            $_SESSION['admin'] = $username;
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_role'] = $user['user_role'];
            regenerateSessionID();
            
            // Log successful login
            logSecurityEvent($conn, $user['user_id'], 'LOGIN', 'Admin login successful', $ip_address);
            
            header("Location: admin_dashboard.php");
            exit();
        } else {
            // Log failed login attempt
            logFailedLogin($conn, $username, $ip_address);
            echo "<script>alert('Invalid username or password.'); window.location.href='admin_login.php';</script>";
        }
    } else {
        // Log failed login attempt
        logFailedLogin($conn, $username, $ip_address);
        echo "<script>alert('Invalid username or password.'); window.location.href='admin_login.php';</script>";
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login - Cyber Threat Tracker</title>
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
        <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="user_login.php">User Login</a></li>
        <li class="nav-item"><a class="nav-link" href="user_register.php">Register</a></li>
        <li class="nav-item"><a class="nav-link active" href="admin_login.php">Admin Login</a></li>
      </ul>
    </div>
  </div>
</nav>
<span class="cyber-webname">Cyber Threat Tracker</span>
<div class="cyber-welcome">Welcome, Admin! Please log in.</div>
<div class="cyber-container" style="max-width:400px;">
    <h2 class="mb-4 text-center" style="color:#00ffe7;">Admin Login</h2>
    <form method="POST" action="admin_login.php">
      <div class="mb-3">
        <label>Username</label>
        <input type="text" name="username" class="form-control" required>
      </div>
      <div class="mb-3">
        <label>Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <?php csrfField(); ?>
      <button type="submit" class="btn cyber-btn w-100">Login</button>
    </form>
    <a href="index.php" class="btn btn-secondary w-100 mt-3">Back to Home</a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<footer class="text-center mt-5 py-3" style="background:#181f2a; color:#00ffe7; border-top:1px solid #00ffe7;">
  &copy; <?php echo date('Y'); ?> Cyber Threat Tracker &mdash; Stay Secure, Stay Ahead.
</footer>
</body>
</html>
