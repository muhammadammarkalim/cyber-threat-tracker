<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

if (isset($_GET['id'])) {
    $threat_id = $_GET['id'];

    // Get threat name for logging
    $threat_name = '';
    $get_stmt = $conn->prepare("SELECT threat_name FROM threats WHERE id = ?");
    $get_stmt->bind_param("i", $threat_id);
    $get_stmt->execute();
    $get_stmt->bind_result($threat_name);
    $get_stmt->fetch();
    $get_stmt->close();

    // Delete the threat
    $sql = "DELETE FROM threats WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $threat_id);

    if ($stmt->execute()) {
        // Log the delete action
        $admin_username = $_SESSION['admin'];
        $admin_id = null;
        $res = $conn->prepare("SELECT user_id FROM users WHERE username=?");
        $res->bind_param("s", $admin_username);
        $res->execute();
        $res->bind_result($admin_id);
        $res->fetch();
        $res->close(); // <-- CLOSE before next query!

        if ($admin_id !== null) {
            $log_stmt = $conn->prepare("INSERT INTO logs (user_id, action) VALUES (?, ?)");
            $action = "Deleted threat: $threat_name (ID: $threat_id)";
            $log_stmt->bind_param("is", $admin_id, $action);
            $log_stmt->execute();
            $log_stmt->close();
        }
        header("Location: manage_threats_admin.php?success=Threat+deleted+successfully");
        exit();
    } else {
        echo "Error deleting threat: " . $conn->error;
    }
} else {
    echo "No threat ID provided.";
}
?>
