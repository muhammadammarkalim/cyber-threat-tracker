<?php
session_start();
include 'db_connect.php';
include 'security_functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        die('CSRF token validation failed');
    }
    
    $full_name = sanitizeInput($_POST['full_name']);
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $user_role = sanitizeInput($_POST['user_role']);

    // Validate inputs
    if (strlen($username) < 3) {
        die('Username must be at least 3 characters long.');
    }
    
    if (!validateEmail($email)) {
        die('Please enter a valid email address.');
    }
    
    if (!validatePasswordStrength($password)) {
        die('Password must be at least 8 characters with uppercase, lowercase, and numbers.');
    }

    // Check for existing username/email
    $check = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $check->bind_param("ss", $username, $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        echo "Username or Email already exists.";
    } else {
        // Hash password securely
        $hashed_password = hashPassword($password);
        
        $stmt = $conn->prepare("INSERT INTO users (full_name, username, email, password, user_role) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $full_name, $username, $email, $hashed_password, $user_role);

        if ($stmt->execute()) {
            // Log registration
            $user_id = $conn->insert_id;
            logSecurityEvent($conn, $user_id, 'REGISTRATION', 'New user registered: ' . $username, $_SERVER['REMOTE_ADDR']);
            
            // Redirect to login
            header("Location: user_login.php?message=registered");
            exit();
        } else {
            echo "Registration failed.";
        }
    }
    $conn->close();
}
?>


