<!-- user_register.php -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>User Registration</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php session_start(); include 'security_functions.php'; ?>
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
        <li class="nav-item"><a class="nav-link active" href="user_register.php">Register</a></li>
        <li class="nav-item"><a class="nav-link" href="admin_login.php">Admin Login</a></li>
      </ul>
    </div>
  </div>
</nav>
<span class="cyber-webname">Cyber Threat Tracker</span>
<div class="cyber-welcome">Welcome! Register to join Cyber Threat Tracker</div>
<div class="cyber-container" style="max-width:500px;">
  <h2 class="mb-4 text-center" style="color:#00ffe7;">User Registration</h2>
  <form action="register_user_process.php" method="POST">
    <div class="mb-3">
      <label>Full Name</label>
      <input type="text" name="full_name" class="form-control" placeholder="Full Name" required />
    </div>
    <div class="mb-3">
      <label>Username</label>
      <input type="text" name="username" class="form-control" placeholder="Username" required />
    </div>
    <div class="mb-3">
      <label>Email</label>
      <input type="email" name="email" class="form-control" placeholder="Email" required />
    </div>
    <div class="mb-3">
      <label>Password</label>
      <input type="password" name="password" class="form-control" placeholder="Password" required />
      <small class="text-muted">Min 8 characters, uppercase, lowercase, and numbers required</small>
    </div>
    <div class="mb-3">
      <label>User Role</label>
      <select name="user_role" class="form-select" required>
        <option value="">Select Role</option>
        <option value="student">Student</option>
        <option value="govt_emp">Government Employee</option>
        <option value="it_cs">IT/CS</option>
        <option value="analyzer">Analyzer</option>
      </select>
    </div>
    <?php csrfField(); ?>
    <button type="submit" class="btn cyber-btn w-100">Register</button>
  </form>
  <p class="mt-3 text-center">Already have an account? <a href="user_login.php" class="btn-link">Login Here</a></p>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<footer class="text-center mt-5 py-3" style="background:#181f2a; color:#00ffe7; border-top:1px solid #00ffe7;">
  &copy; <?php echo date('Y'); ?> Cyber Threat Tracker &mdash; Stay Secure, Stay Ahead.
</footer>
</body>
</html>
