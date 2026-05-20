<?php
/**
 * Password Hashing & Login Reset Script
 * This will properly hash all passwords and fix login issues
 */

include 'db_connect.php';

// Check if form was submitted to reset
$reset_action = isset($_POST['action']) ? $_POST['action'] : '';

if ($reset_action === 'hash_passwords') {
    echo "<h2 style='color: #00ffe7;'>🔐 Hashing Passwords...</h2>";
    
    // Get all users
    $stmt = $conn->prepare("SELECT user_id, username, password FROM users");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $count = 0;
    $errors = 0;
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #232b39; color: #00ffe7;'><th>Username</th><th>Status</th></tr>";
    
    while ($user = $result->fetch_assoc()) {
        $plain_password = $user['password'];
        $user_id = $user['user_id'];
        $username = $user['username'];
        
        // Check if already hashed
        if (strlen($plain_password) > 50 && strpos($plain_password, '$2') === 0) {
            echo "<tr><td>$username</td><td style='color: #ffaa00;'>Already Hashed</td></tr>";
            continue;
        }
        
        // Hash the password using Bcrypt
        $hashed = password_hash($plain_password, PASSWORD_BCRYPT, ['cost' => 12]);
        
        // Update in database
        $update = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $update->bind_param("si", $hashed, $user_id);
        
        if ($update->execute()) {
            echo "<tr><td>$username</td><td style='color: #00ff00;'>✓ Hashed Successfully</td></tr>";
            $count++;
        } else {
            echo "<tr><td>$username</td><td style='color: #ff0000;'>✗ Error</td></tr>";
            $errors++;
        }
    }
    
    echo "</table>";
    echo "<br><h3 style='color: #00ff00;'>✅ Done! $count passwords hashed. Errors: $errors</h3>";
    echo "<hr>";
}

if ($reset_action === 'clear_rate_limit') {
    echo "<h2 style='color: #00ffe7;'>🔓 Clearing Rate Limiting...</h2>";
    
    $stmt = $conn->prepare("DELETE FROM failed_login_attempts");
    if ($stmt->execute()) {
        $count = $stmt->affected_rows;
        echo "<h3 style='color: #00ff00;'>✅ Cleared! Deleted $count failed attempts</h3>";
    } else {
        echo "<h3 style='color: #ff0000;'>✗ Error clearing rate limit</h3>";
    }
    echo "<hr>";
}

if ($reset_action === 'verify_db') {
    echo "<h2 style='color: #00ffe7;'>📊 Database Verification</h2>";
    
    echo "<h3>Users in Database:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #232b39; color: #00ffe7;'><th>ID</th><th>Username</th><th>Role</th><th>Password Status</th></tr>";
    
    $stmt = $conn->prepare("SELECT user_id, username, user_role, password FROM users");
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($user = $result->fetch_assoc()) {
        $pass_status = (strlen($user['password']) > 50 && strpos($user['password'], '$2') === 0) 
                       ? "✓ Hashed" 
                       : "✗ Plain Text";
        $pass_color = strpos($pass_status, '✓') !== false ? '#00ff00' : '#ff0000';
        
        echo "<tr>";
        echo "<td>{$user['user_id']}</td>";
        echo "<td>{$user['username']}</td>";
        echo "<td>{$user['user_role']}</td>";
        echo "<td style='color: $pass_color;'>$pass_status</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<h3 style='margin-top: 20px;'>Security Logs:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #232b39; color: #00ffe7;'><th>Event Type</th><th>User</th><th>Details</th><th>Time</th></tr>";
    
    $stmt = $conn->prepare("SELECT event_type, user_id, details, event_timestamp FROM security_logs ORDER BY event_timestamp DESC LIMIT 10");
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo "<tr><td colspan='4' style='text-align: center; color: #ffaa00;'>No logs yet</td></tr>";
    } else {
        while ($log = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$log['event_type']}</td>";
            echo "<td>{$log['user_id']}</td>";
            echo "<td>{$log['details']}</td>";
            echo "<td>{$log['event_timestamp']}</td>";
            echo "</tr>";
        }
    }
    
    echo "</table>";
    echo "<hr>";
}

if ($reset_action === 'test_password') {
    $test_username = $_POST['test_username'] ?? '';
    $test_password = $_POST['test_password'] ?? '';
    
    echo "<h2 style='color: #00ffe7;'>🧪 Testing Password</h2>";
    
    if (empty($test_username) || empty($test_password)) {
        echo "<h3 style='color: #ff0000;'>❌ Please enter username and password</h3>";
    } else {
        $stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
        $stmt->bind_param("s", $test_username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $stored_hash = $user['password'];
            
            if (password_verify($test_password, $stored_hash)) {
                echo "<h3 style='color: #00ff00;'>✅ Password Correct! You can login now.</h3>";
            } else {
                echo "<h3 style='color: #ff0000;'>❌ Password Incorrect!</h3>";
                echo "<p>Stored Hash: <code>$stored_hash</code></p>";
            }
        } else {
            echo "<h3 style='color: #ff0000;'>Username not found!</h3>";
        }
    }
    echo "<hr>";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Password & Login Reset Tool</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #10141a;
            color: #e0e6ed;
            font-family: 'Segoe UI', 'Roboto', Arial, sans-serif;
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            background: #181f2a;
            padding: 30px;
            border-radius: 12px;
            border: 1px solid #00ffe7;
            margin-top: 20px;
        }
        .btn-action {
            background: linear-gradient(90deg, #00ffe7 0%, #00bfae 100%);
            color: #10141a !important;
            border: none;
            font-weight: 600;
            margin: 10px 5px;
            border-radius: 6px;
            padding: 10px 20px;
        }
        .btn-action:hover {
            transform: scale(1.05);
            box-shadow: 0 0 15px #00ffe7;
        }
        .btn-secondary {
            background: #232b39;
            color: #00ffe7 !important;
            border: 1px solid #00ffe7;
            margin: 10px 5px;
            border-radius: 6px;
            padding: 10px 20px;
        }
        .btn-secondary:hover {
            background: #00ffe7;
            color: #10141a !important;
        }
        h1 {
            color: #00ffe7;
            text-align: center;
            margin-bottom: 30px;
            font-weight: bold;
            text-shadow: 0 0 10px #00ffe7;
        }
        .section {
            margin: 30px 0;
            padding: 20px;
            background: #232b39;
            border-radius: 8px;
            border-left: 4px solid #00ffe7;
        }
        .form-group {
            margin: 15px 0;
        }
        .form-control {
            background: #181f2a;
            color: #e0e6ed;
            border: 1px solid #00ffe7;
            border-radius: 6px;
        }
        .form-control:focus {
            background: #181f2a;
            color: #e0e6ed;
            border-color: #00ffe7;
            box-shadow: 0 0 10px #00ffe7;
        }
        .alert-info {
            background: #232b39;
            color: #00ffe7;
            border: 1px solid #00ffe7;
        }
        code {
            background: #10141a;
            color: #00ff00;
            padding: 5px 10px;
            border-radius: 4px;
            display: block;
            margin: 10px 0;
            word-break: break-all;
        }
        table {
            background: #181f2a;
            color: #e0e6ed;
            margin: 20px 0;
        }
        td, th {
            padding: 12px;
            border: 1px solid #232b39;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>🔐 Admin Password & Login Reset Tool</h1>
    
    <div class="alert alert-info">
        <strong>⚠️ Important:</strong> This tool will help you:
        <ul>
            <li>✅ Hash all plain text passwords</li>
            <li>✅ Clear failed login attempts</li>
            <li>✅ Verify database status</li>
            <li>✅ Test passwords</li>
        </ul>
    </div>

    <!-- SECTION 1: Hash Passwords -->
    <div class="section">
        <h2 style="color: #00ffe7;">1️⃣ Hash All Passwords</h2>
        <p>Converts all plain text passwords to secure Bcrypt hashes.</p>
        <form method="POST">
            <input type="hidden" name="action" value="hash_passwords">
            <button type="submit" class="btn btn-action">🔐 Hash All Passwords Now</button>
        </form>
    </div>

    <!-- SECTION 2: Clear Rate Limiting -->
    <div class="section">
        <h2 style="color: #00ffe7;">2️⃣ Clear Failed Login Attempts</h2>
        <p>Clears the rate limiting so you can try logging in again.</p>
        <form method="POST">
            <input type="hidden" name="action" value="clear_rate_limit">
            <button type="submit" class="btn btn-action">🔓 Clear Failed Attempts</button>
        </form>
    </div>

    <!-- SECTION 3: Verify Database -->
    <div class="section">
        <h2 style="color: #00ffe7;">3️⃣ Verify Database</h2>
        <p>Check all users, their password status, and security logs.</p>
        <form method="POST">
            <input type="hidden" name="action" value="verify_db">
            <button type="submit" class="btn btn-action">📊 Check Database Status</button>
        </form>
    </div>

    <!-- SECTION 4: Test Password -->
    <div class="section">
        <h2 style="color: #00ffe7;">4️⃣ Test Password</h2>
        <p>Test if a password will work before trying to login.</p>
        <form method="POST">
            <input type="hidden" name="action" value="test_password">
            <div class="form-group">
                <label for="test_username">Username:</label>
                <input type="text" id="test_username" name="test_username" class="form-control" placeholder="e.g., zaib" required>
            </div>
            <div class="form-group">
                <label for="test_password">Password:</label>
                <input type="password" id="test_password" name="test_password" class="form-control" placeholder="Enter password" required>
            </div>
            <button type="submit" class="btn btn-action">🧪 Test Password</button>
        </form>
    </div>

    <!-- NEXT STEPS -->
    <div class="section" style="background: #1a7f7e; border-left-color: #00ff00;">
        <h2 style="color: #00ff00;">✅ Next Steps</h2>
        <ol>
            <li>Click "Hash All Passwords Now" above</li>
            <li>Click "Clear Failed Attempts" to reset rate limiting</li>
            <li>Click "Check Database Status" to verify everything</li>
            <li>Use "Test Password" to verify a password works</li>
            <li>Go to <a href="admin_login.php" style="color: #00ffe7;">Admin Login</a> and try logging in</li>
        </ol>
        <hr>
        <p><strong>Default Credentials After Hashing:</strong></p>
        <ul>
            <li><strong>Username:</strong> zaib</li>
            <li><strong>Password:</strong> zaib123</li>
        </ul>
    </div>

    <!-- EMERGENCY RECOVERY -->
    <div class="section" style="background: #7f1a1a; border-left-color: #ff0000;">
        <h2 style="color: #ff0000;">🆘 Emergency Recovery</h2>
        <p>If you forget the admin password, you can reset it manually:</p>
        <p>Open phpMyAdmin and run this SQL:</p>
        <code>UPDATE users SET password = '$2y$12$R9h7cIPz0gi.URNN3kh2OPST9/PgBkqquzi8Ag8Ms4.nHpqQfOCMa' WHERE username = 'zaib';</code>
        <p style="color: #ffaa00;">⚠️ This will reset zaib's password to: <strong>zaib123</strong></p>
    </div>

    <div style="text-align: center; margin-top: 30px;">
        <a href="admin_login.php" class="btn btn-secondary">🔐 Go to Admin Login</a>
        <a href="index.php" class="btn btn-secondary">🏠 Go to Home</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
