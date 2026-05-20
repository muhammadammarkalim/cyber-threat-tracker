<?php
/**
 * Direct Password Hashing Script
 * This will properly hash the passwords with the correct algorithm
 */

include 'db_connect.php';
include 'security_functions.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fix Admin Login</title>
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
            max-width: 800px;
            background: #181f2a;
            padding: 30px;
            border-radius: 12px;
            border: 2px solid #00ffe7;
            margin-top: 20px;
        }
        h1 {
            color: #00ffe7;
            text-align: center;
            margin-bottom: 30px;
            text-shadow: 0 0 10px #00ffe7;
        }
        .section {
            margin: 20px 0;
            padding: 20px;
            background: #232b39;
            border-radius: 8px;
            border-left: 4px solid #00ffe7;
        }
        .btn-fix {
            background: linear-gradient(90deg, #00ffe7 0%, #00bfae 100%);
            color: #10141a !important;
            border: none;
            font-weight: 600;
            width: 100%;
            padding: 15px;
            border-radius: 6px;
            font-size: 16px;
            margin: 10px 0;
        }
        .btn-fix:hover {
            transform: scale(1.02);
            box-shadow: 0 0 20px #00ffe7;
        }
        .success {
            color: #00ff00;
            font-weight: bold;
        }
        .error {
            color: #ff0000;
            font-weight: bold;
        }
        .info {
            color: #00ffe7;
            font-weight: bold;
        }
        .result-box {
            background: #10141a;
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
            border: 1px solid #232b39;
            word-break: break-all;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>🔧 Fix Admin Login Issue</h1>

    <?php
    
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    // Note: 'Fix all passwords' action removed to avoid double-hashing.
    // Passwords should only be hashed at creation or when explicitly migrated.

    // ACTION 2: Clear rate limit
    if ($action === 'clear_rate') {
        echo "<div class='section'>";
        echo "<h2 style='color: #00ffe7;'>Clearing rate limit...</h2>";
        
        $delete_sql = "DELETE FROM failed_login_attempts";
        if ($conn->query($delete_sql)) {
            echo "<p class='success'>✓ Rate limit cleared! You can now login</p>";
        } else {
            echo "<p class='error'>✗ Error clearing rate limit</p>";
        }
        echo "</div>";
    }

    // ACTION: Remove all admins (dangerous - requires confirmation)
    if ($action === 'remove_admins') {
        echo "<div class='section'>";
        echo "<h2 style='color: #00ffe7;'>Remove All Admin Accounts</h2>";
        $confirm = isset($_POST['confirm_remove']) ? trim($_POST['confirm_remove']) : '';

        if ($confirm !== 'CONFIRM') {
            echo "<p class='error'>You must type <strong>CONFIRM</strong> to proceed.</p>";
        } else {
            // Collect admin IDs and usernames
            $admins = [];
            $usernames = [];
            $sql_admins = "SELECT user_id, username FROM users WHERE user_role = 'admin'";
            $res_admins = $conn->query($sql_admins);
            if ($res_admins) {
                while ($r = $res_admins->fetch_assoc()) {
                    $admins[] = (int)$r['user_id'];
                    $usernames[] = $conn->real_escape_string($r['username']);
                }
            }

            if (empty($admins)) {
                echo "<p class='info'>No admin accounts found to remove.</p>";
            } else {
                // Build lists for IN() clauses
                $id_list = implode(',', $admins);
                $username_list = implode("','", $usernames);

                // Start transaction
                $conn->begin_transaction();
                $error = false;

                // Delete dependent records first to satisfy FK constraints
                $queries = [];
                $queries[] = "DELETE FROM logs WHERE user_id IN ($id_list)";
                $queries[] = "DELETE FROM security_logs WHERE user_id IN ($id_list)";
                // failed_login_attempts may reference username
                if (!empty($usernames)) {
                    $queries[] = "DELETE FROM failed_login_attempts WHERE username IN ('$username_list')";
                }

                foreach ($queries as $q) {
                    if ($conn->query($q) === false) {
                        $error = true;
                        $err = $conn->error;
                        break;
                    }
                }

                if ($error) {
                    $conn->rollback();
                    echo "<p class='error'>✗ Error deleting dependent records: " . htmlspecialchars($err) . "</p>";
                } else {
                    // Now delete users
                    $del_sql = "DELETE FROM users WHERE user_id IN ($id_list)";
                    if ($conn->query($del_sql) === false) {
                        $conn->rollback();
                        echo "<p class='error'>✗ Error removing admin accounts: " . htmlspecialchars($conn->error) . "</p>";
                    } else {
                        $conn->commit();
                        echo "<p class='success'>✓ All admin accounts removed along with related records.</p>";
                    }
                }
            }
        }

        echo "</div>";
    }

    // ACTION 3: Test login
    if ($action === 'test_login') {
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        
        echo "<div class='section'>";
        echo "<h2 style='color: #00ffe7;'>Testing Login...</h2>";
        
        if (empty($username) || empty($password)) {
            echo "<p class='error'>Please enter username and password</p>";
        } else {
            // Get user from database
            $test_sql = "SELECT user_id, password FROM users WHERE username = ?";
            $test_stmt = $conn->prepare($test_sql);
            $test_stmt->bind_param("s", $username);
            $test_stmt->execute();
            $test_result = $test_stmt->get_result();
            
            if ($test_result->num_rows === 1) {
                $user = $test_result->fetch_assoc();
                $stored_hash = $user['password'];
                
                echo "<p>Username: <span class='info'>$username</span></p>";
                echo "<p>Password entered: <span class='info'>" . str_repeat("*", strlen($password)) . "</span></p>";
                echo "<p>Stored hash: <span class='info'>" . substr($stored_hash, 0, 30) . "...</span></p>";
                echo "<hr>";
                
                // Test if password verifies
                if (verifyPassword($password, $stored_hash)) {
                    echo "<p class='success'>✓✓✓ PASSWORD CORRECT! ✓✓✓</p>";
                    echo "<p>You should be able to login now at <a href='admin_login.php'>Admin Login</a></p>";
                } else {
                    echo "<p class='error'>✗✗✗ PASSWORD WRONG! ✗✗✗</p>";
                    echo "<p>The password you entered does NOT match the one in database.</p>";
                    echo "<p style='color: #ffaa00;'><strong>Try using: zaib123</strong></p>";
                }
            } else {
                echo "<p class='error'>Username not found!</p>";
            }
            
            $test_stmt->close();
        }
        echo "</div>";
    }

    // ACTION: Create a new admin user
    if ($action === 'add_admin') {
        echo "<div class='section'>";
        echo "<h2 style='color: #00ffe7;'>Create New Admin</h2>";

        $new_user = isset($_POST['new_username']) ? trim($_POST['new_username']) : '';
        $new_pass = isset($_POST['new_password']) ? $_POST['new_password'] : '';
        $new_email = isset($_POST['new_email']) ? trim($_POST['new_email']) : '';

        if (empty($new_user) || empty($new_pass)) {
            echo "<p class='error'>Please provide username and password.</p>";
        } else {
            // ensure username not exists
            $chk_sql = "SELECT user_id FROM users WHERE username = ? LIMIT 1";
            $chk_stmt = $conn->prepare($chk_sql);
            $chk_stmt->bind_param('s', $new_user);
            $chk_stmt->execute();
            $chk_stmt->store_result();

            if ($chk_stmt->num_rows > 0) {
                echo "<p class='error'>Username already exists. Choose another.</p>";
            } else {
                $hashed = hashPassword($new_pass);

                // If no email provided, generate a unique placeholder to avoid UNIQUE constraint on email
                if (empty($new_email)) {
                    try {
                        $rand = bin2hex(random_bytes(4));
                    } catch (Exception $e) {
                        $rand = uniqid();
                    }
                    $new_email = $new_user . '.' . $rand . '@local';
                }

                // Insert with email field to avoid duplicate-empty-email errors
                $ins_sql = "INSERT INTO users (username, password, user_role, email) VALUES (?, ?, 'admin', ?)";
                $ins_stmt = $conn->prepare($ins_sql);
                if ($ins_stmt) {
                    $ins_stmt->bind_param('sss', $new_user, $hashed, $new_email);
                    if ($ins_stmt->execute()) {
                        echo "<p class='success'>✓ Admin user <strong>" . htmlspecialchars($new_user) . "</strong> created.</p>";
                    } else {
                        echo "<p class='error'>✗ Error creating admin: " . htmlspecialchars($conn->error) . "</p>";
                    }
                    $ins_stmt->close();
                } else {
                    echo "<p class='error'>✗ Error preparing insert: " . htmlspecialchars($conn->error) . "</p>";
                }
            }
            $chk_stmt->close();
        }

        echo "</div>";
    }

    // ACTION 4: Show current database status
    if ($action === 'show_status') {
        echo "<div class='section'>";
        echo "<h2 style='color: #00ffe7;'>Database Status</h2>";
        
        $status_sql = "SELECT user_id, username, user_role, password FROM users";
        $status_result = $conn->query($status_sql);
        
        echo "<table style='width: 100%; border-collapse: collapse;'>";
        echo "<tr style='background: #232b39; color: #00ffe7;'>";
        echo "<th style='padding: 10px; border: 1px solid #00ffe7;'>ID</th>";
        echo "<th style='padding: 10px; border: 1px solid #00ffe7;'>Username</th>";
        echo "<th style='padding: 10px; border: 1px solid #00ffe7;'>Role</th>";
        echo "<th style='padding: 10px; border: 1px solid #00ffe7;'>Password Status</th>";
        echo "</tr>";
        
        while ($row = $status_result->fetch_assoc()) {
            $pass = $row['password'];
            // Detect common PHP password hash formats (bcrypt / argon2 / others)
            $is_hashed = false;
            if (is_string($pass) && strlen($pass) > 0) {
                $prefixes = array('$2y$', '$2a$', '$2b$', '$argon2$', '$argon2i$', '$argon2id$');
                foreach ($prefixes as $p) {
                    if (strpos($pass, $p) === 0) { $is_hashed = true; break; }
                }
                // Fallback heuristic: many password hashes start with '$' and are fairly long
                if (!$is_hashed && substr($pass,0,1) === '$' && strlen($pass) > 30) {
                    $is_hashed = true;
                }
            }
            $pass_status = $is_hashed ? "Hashed" : "PLAIN TEXT";
            $color = strpos($pass_status, 'Hashed') !== false ? '#00ff00' : '#ff0000';
            
            echo "<tr>";
            echo "<td style='padding: 10px; border: 1px solid #232b39;'>{$row['user_id']}</td>";
            echo "<td style='padding: 10px; border: 1px solid #232b39;'>{$row['username']}</td>";
            echo "<td style='padding: 10px; border: 1px solid #232b39;'>{$row['user_role']}</td>";
            echo "<td style='padding: 10px; border: 1px solid #232b39; color: $color;'><strong>$pass_status</strong></td>";
            echo "</tr>";
        }
        
        echo "</table>";
        echo "</div>";
    }

    ?>

    <!-- MAIN INTERFACE -->
    <div class="section">
        <h2 style="color: #00ffe7;">Fix Admin Login - Step by Step</h2>
        <p>Follow these steps in order:</p>
    </div>

    <!-- STEP 1: (Removed) Password mass-hash was removed to avoid double-hashing -->

    <!-- STEP 2: Clear Rate Limit -->
    <div class="section">
        <h3 style="color: #00ff00;">Step 2: Clear Rate Limiting</h3>
        <p>Remove the "too many attempts" block</p>
        <form method="POST">
            <input type="hidden" name="action" value="clear_rate">
            <button type="submit" class="btn-fix">🔓 Clear Rate Limit</button>
        </form>
    </div>

    <!-- STEP 2.5: Remove All Admins -->
    <div class="section">
        <h3 style="color: #ff6666;">Danger: Remove All Admins</h3>
        <p>Careful — this will delete all accounts with role <code>admin</code>. Type <strong>CONFIRM</strong> to proceed.</p>
        <form method="POST">
            <input type="hidden" name="action" value="remove_admins">
            <div style="margin: 15px 0;">
                <label style="display:block; margin-bottom:8px;">Type CONFIRM to delete:</label>
                <input type="text" name="confirm_remove" class="form-control" placeholder="Type CONFIRM to proceed" required style="background: #10141a; color: #e0e6ed; border: 1px solid #ff6666;">
            </div>
            <button type="submit" class="btn-fix" style="background: linear-gradient(90deg, #ff6666 0%, #ff3b3b 100%);">🗑️ Remove All Admins</button>
        </form>
    </div>

    <!-- STEP 2.6: Create New Admin -->
    <div class="section">
        <h3 style="color: #00ff00;">Create A New Admin</h3>
        <p>Create a fresh admin account (username, password, optional email)</p>
        <form method="POST">
            <input type="hidden" name="action" value="add_admin">
            <div style="margin: 12px 0;">
                <label style="display:block; margin-bottom:8px;">New Username:</label>
                <input type="text" name="new_username" class="form-control" placeholder="e.g., zaib" required style="background: #10141a; color: #e0e6ed; border: 1px solid #00ffe7;">
            </div>
            <div style="margin: 12px 0;">
                <label style="display:block; margin-bottom:8px;">New Password:</label>
                <input type="password" name="new_password" class="form-control" placeholder="e.g., zaib123" required style="background: #10141a; color: #e0e6ed; border: 1px solid #00ffe7;">
            </div>
            <div style="margin: 12px 0;">
                <label style="display:block; margin-bottom:8px;">Email (optional):</label>
                <input type="email" name="new_email" class="form-control" placeholder="e.g., admin@example.com" style="background: #10141a; color: #e0e6ed; border: 1px solid #00ffe7;">
            </div>
            <button type="submit" class="btn-fix">➕ Create Admin</button>
        </form>
    </div>

    <!-- STEP 3: Check Status -->
    <div class="section">
        <h3 style="color: #00ff00;">Step 3: Check Database Status</h3>
        <p>Verify all passwords are hashed</p>
        <form method="POST">
            <input type="hidden" name="action" value="show_status">
            <button type="submit" class="btn-fix">📊 Show Status</button>
        </form>
    </div>

    <!-- STEP 4: Test Password -->
    <div class="section">
        <h3 style="color: #00ff00;">Step 4: Test Password Before Login</h3>
        <p>Test if your password works</p>
        <form method="POST">
            <input type="hidden" name="action" value="test_login">
            <div style="margin: 15px 0;">
                <label style="display: block; margin-bottom: 8px;">Username:</label>
                <input type="text" name="username" class="form-control" placeholder="e.g., zaib" required style="background: #10141a; color: #e0e6ed; border: 1px solid #00ffe7;">
            </div>
            <div style="margin: 15px 0;">
                <label style="display: block; margin-bottom: 8px;">Password:</label>
                <input type="password" name="password" class="form-control" placeholder="e.g., zaib123" required style="background: #10141a; color: #e0e6ed; border: 1px solid #00ffe7;">
            </div>
            <button type="submit" class="btn-fix">🧪 Test Password</button>
        </form>
    </div>

    <!-- FINAL STEP -->
    <div class="section" style="background: #1a7f7e; border-left-color: #00ff00; text-align: center;">
        <h2 style="color: #00ff00;">✅ All Done!</h2>
        <p>If password test shows <span class="success">PASSWORD CORRECT</span>, then:</p>
        <a href="admin_login.php" style="display: inline-block; background: linear-gradient(90deg, #00ffe7 0%, #00bfae 100%); color: #10141a; padding: 12px 30px; border-radius: 6px; text-decoration: none; font-weight: bold; margin: 10px;">
            🔐 Go to Admin Login
        </a>
    </div>

</div>

</body>
</html>
