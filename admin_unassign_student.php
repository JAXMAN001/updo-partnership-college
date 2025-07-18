<?php
session_start();
include 'config.php'; // Include database configuration

// Check if the admin is logged in
if (!isset($_SESSION['hod_id'])) {
    header("Location: admin_dashboard.php"); // Redirect to admin dashboard if not logged in
    exit();
}

// Check if student_id is provided and is a valid integer
if (isset($_POST['student_id'])) {
    $student_id = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);

    if ($student_id === false || $student_id <= 0) {
        $_SESSION['error_message'] = "Invalid student ID.";
        header("Location: admin_assigned_and_unassigned.php");
        exit();
    }

    // Prepare the SQL statement to check if the student exists
    $check_student_sql = "SELECT id FROM student WHERE id = ?";
    $check_stmt = $conn->prepare($check_student_sql);

    if ($check_stmt) {
        $check_stmt->bind_param("i", $student_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows === 0) {
            $_SESSION['error_message'] = "Student with ID {$student_id} does not exist.";
            $check_stmt->close();
            header("Location: admin_assigned_and_unassigned.php");
            exit();
        }

        $check_stmt->close();
    } else {
        $_SESSION['error_message'] = "Error preparing student existence check statement.";
        header("Location: admin_assigned_and_unassigned.php");
        exit();
    }

    // Unassign the student by setting supervisor_id to NULL
    $unassign_sql = "UPDATE student SET sup_id = NULL WHERE id = ?";
    $stmt = $conn->prepare($unassign_sql);

    if ($stmt) {
        $stmt->bind_param("i", $student_id);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Student unassigned successfully.";
        } else {
            $_SESSION['error_message'] = "Failed to unassign student: " . $stmt->error;
        }
        $stmt->close();
    } else {
        
        $_SESSION['error_message'] = "Error preparing unassign statement: " . $conn->error;
    }
} else {
    $_SESSION['error_message'] = "No student ID provided.";
}

$conn->close();
header("Location: admin_assigned_and_unassigned.php");
exit();
?>
