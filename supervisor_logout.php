<?php
session_start();
include 'config.php';

// Check if supervisor is logged in
if (isset($_SESSION['supervisor_id'])) {
    $supervisor_id = $_SESSION['supervisor_id'];

    // Update last_login to NULL (or a specific value like '0000-00-00 00:00:00')
    // to indicate the supervisor is logged out
    $update_sql = "UPDATE supervisors SET last_login = NULL WHERE id = $supervisor_id";

    if ($conn->query($update_sql) === TRUE) {
        echo "Last login reset successfully";
    } else {
        echo "Error resetting last login: " . $conn->error;
    }

    // Unset all session variables
    $_SESSION = array();

    // Destroy the session
    session_destroy();

    // Redirect to login page
    header("Location: student_login.php");
    exit();
} else {
    // If not logged in, redirect to login page
    header("Location: student_login.php");
    exit();
}

$conn->close();
?>
