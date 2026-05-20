<!-- user_login.php -->
<?php
  session_start();
  include 'security_functions.php';
  $error = isset($_GET['error']) ? $_GET['error'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>User Login</title>
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
        <li class="nav-item"><a class="nav-link active" href="user_login.php">User Login</a></li>
        <li class="nav-item"><a class="nav-link" href="user_register.php">Register</a></li>
        <li class="nav-item"><a class="nav-link" href="admin_login.php">Admin Login</a></li>
      </ul>
    </div>
  </div>
</nav>
<span class="cyber-webname">Cyber Threat Tracker</span>
<div class="cyber-welcome">Welcome! Please log in to your account.</div>
<div class="cyber-container" style="max-width:400px;">
  <h2 class="mb-4 text-center" style="color:#00ffe7;">User Login</h2>
  <?php if (!empty($error)): ?>
    <div class="cyber-alert-error"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>
  <form action="user_login_process.php" method="POST">
    <div class="mb-3">
      <label>Username</label>
      <input type="text" name="username" class="form-control" placeholder="Username" required />
    </div>
    <div class="mb-3">
      <label>Password</label>
      <input type="password" name="password" class="form-control" placeholder="Password" required />
    </div>
    <?php csrfField(); ?>
    <button type="submit" class="btn cyber-btn w-100">Login</button>
  </form>
  <p class="mt-3 text-center">Don't have an account? <a href="user_register.php" class="btn-link">Register Here</a></p>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
