<?php
session_start();
include 'security_functions.php';

// Log logout event if user exists
if (isset($_SESSION['user_id'])) {
    include 'db_connect.php';
    logSecurityEvent($conn, $_SESSION['user_id'], 'LOGOUT', 'User logged out', $_SERVER['REMOTE_ADDR']);
}

secureLogout();
header("Location: index.php");
exit();
?>
