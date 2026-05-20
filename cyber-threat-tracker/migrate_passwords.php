<?php
/**
 * Password Migration Script
 * This script converts plain text admin passwords to hashed passwords
 * RUN THIS ONCE to secure existing admin accounts
 */

include 'db_connect.php';
include 'security_functions.php';

echo "=== Cyber Threat Tracker - Password Migration ===\n\n";

// Get all users with plain text passwords (those that don't look like hashes)
$stmt = $conn->prepare("SELECT user_id, username, password, user_role FROM users WHERE LENGTH(password) < 40 AND password NOT LIKE '$2%'");
$stmt->execute();
$result = $stmt->get_result();

$migrated = 0;
$errors = 0;

while ($user = $result->fetch_assoc()) {
    $old_password = $user['password'];
    $new_password = hashPassword($old_password);
    
    $update = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
    $update->bind_param("si", $new_password, $user['user_id']);
    
    if ($update->execute()) {
        echo "✓ Migrated: {$user['username']} ({$user['user_role']})\n";
        $migrated++;
    } else {
        echo "✗ Error migrating: {$user['username']}\n";
        $errors++;
    }
}

echo "\n=== Migration Complete ===\n";
echo "Migrated: $migrated\n";
echo "Errors: $errors\n";
echo "\nAll passwords are now securely hashed!\n";
?>
