<?php
session_start();
include '../includes/db_connect.php';

// Optional: Log the logout event
if(isset($_SESSION['admin_id'])) {
    $admin_id = $_SESSION['admin_id'];
    $conn->query("INSERT INTO security_logs (admin_id, event, ip_address, timestamp) VALUES ('$admin_id', 'Logout Success', '{$_SERVER['REMOTE_ADDR']}', NOW())");
}

// Destroy all session data
session_unset();
session_destroy();

// Redirect to Login
header("Location: login.php");
exit();
?>