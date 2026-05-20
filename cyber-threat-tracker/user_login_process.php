<?php
session_start();
include 'db_connect.php';
include 'security_functions.php';

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    die('CSRF token validation failed');
}

$username = sanitizeInput($_POST['username']);
$password = $_POST['password'];

// Check rate limiting
$ip_address = $_SERVER['REMOTE_ADDR'];
if (isRateLimited($conn, $ip_address)) {
    header("Location: user_login.php?error=Too many login attempts. Please try again later.");
    exit();
}

$sql = "SELECT user_id, username, password, user_role FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
  $user = $result->fetch_assoc();

  if (verifyPassword($password, $user['password'])) {
    // Login success
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['user_role'] = $user['user_role'];
    regenerateSessionID();
    
    // Log successful login
    logSecurityEvent($conn, $user['user_id'], 'LOGIN', 'User login successful', $ip_address);
    
    if ($user['user_role'] === 'admin') {
      $_SESSION['admin'] = $user['username'];
      header("Location: admin_dashboard.php");
      exit();
    } else {
      header("Location: user_dashboard.php");
      exit();
    }
  } else {
    // Password incorrect
    logFailedLogin($conn, $username, $ip_address);
    header("Location: user_login.php?error=Incorrect username or password");
    exit();
  }
} else {
  // User not found
  logFailedLogin($conn, $username, $ip_address);
  header("Location: user_login.php?error=User does not exist");
  exit();
}
?>
