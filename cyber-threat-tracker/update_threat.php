<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $threat_id = $_POST['threat_id'];
    $threat_name = $_POST['threat_name'];
    $description = $_POST['description'];
    $severity = $_POST['severity'];
    $affected_industry = $_POST['affected_industry'];

    $sql = "UPDATE threats 
            SET threat_name = ?, description = ?, severity = ?, affected_industry = ? 
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $threat_name, $description, $severity, $affected_industry, $threat_id);

    if ($stmt->execute()) {
        // Log the update action
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
            $action = "Updated threat: $threat_name (ID: $threat_id)";
            $log_stmt->bind_param("is", $admin_id, $action);
            $log_stmt->execute();
            $log_stmt->close();
        }
        header("Location: manage_threats_admin.php?success=Threat+updated+successfully");
        exit();
    } else {
        echo "Error updating threat: " . $conn->error;
    }
} else {
    echo "Invalid request.";
}
?>
