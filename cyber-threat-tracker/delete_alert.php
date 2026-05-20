<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

if (isset($_GET['id'])) {
    $alert_id = intval($_GET['id']);

    // Get alert title for logging (optional)
    $alert_title = '';
    $get_stmt = $conn->prepare("SELECT title FROM alerts WHERE id = ?");
    $get_stmt->bind_param("i", $alert_id);
    $get_stmt->execute();
    $get_stmt->bind_result($alert_title);
    $get_stmt->fetch();
    $get_stmt->close();

    // Delete the alert
    $stmt = $conn->prepare("DELETE FROM alerts WHERE id = ?");
    $stmt->bind_param("i", $alert_id);

    if ($stmt->execute()) {
        // Log the delete action
        $admin_username = $_SESSION['admin'];
        $admin_id = null;
        $res = $conn->prepare("SELECT user_id FROM users WHERE username=?");
        $res->bind_param("s", $admin_username);
        $res->execute();
        $res->bind_result($admin_id);
        $res->fetch();
        $res->close();
        if ($admin_id !== null) {
            $log_stmt = $conn->prepare("INSERT INTO logs (user_id, action) VALUES (?, ?)");
            $action = "Deleted alert: $alert_title (ID: $alert_id)";
            $log_stmt->bind_param("is", $admin_id, $action);
            $log_stmt->execute();
            $log_stmt->close();
        }
        header("Location: manage_alerts_admin.php?success=Alert+deleted+successfully");
        exit();
    } else {
        echo "Error deleting alert: " . $conn->error;
    }
} else {
    echo "No alert ID provided.";
}
?>
