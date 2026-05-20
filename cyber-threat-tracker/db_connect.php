<?php
$host = "localhost";
$username = "root";
$password = ""; // default XAMPP password is empty
$database = "cyberthreat_db"; // your database name

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
