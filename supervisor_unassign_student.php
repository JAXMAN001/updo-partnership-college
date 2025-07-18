<?php
session_start();

// Check if the user is an admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: student_login.php"); // Redirect to login page if not admin
    exit();
}

include 'config.php';

if (isset($_GET['student_id'])) {
    $student_id = $_GET['student_id'];

    // Prepare and execute the query to update the student's supervisor_id to NULL
    $sql = "UPDATE student SET supervisor_id = NULL WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("i", $student_id);
        if ($stmt->execute()) {
            echo "Student unassigned successfully!";
        } else {
            echo "Error unassigning student: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }

    $conn->close();
} else {
    echo "Student ID not provided.";
}
?>
