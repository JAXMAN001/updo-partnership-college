<?php
session_start();
include 'config.php';

// Check if the supervisor is logged in
if (!isset($_SESSION['sup_id'])) {
    header("Location: student_login.php");
    exit();
}

// Check if document_id is set via POST
if (isset($_POST['document_id'])) {
    $document_id = $_POST['document_id'];

    // Update the document status to 'completed'
    $sql = "UPDATE document SET status = 'completed' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $document_id);
        if ($stmt->execute()) {
            // Optionally, update the student table's project column if you have a reference
            // For example, if you store student_id in the document table:
            // $student_id = ...; // fetch or join to get student_id
            // $conn->query("UPDATE student SET project = 'completed' WHERE id = $student_id");

            // Redirect back with a success message
            header("Location: supervisor_student_status.php?document_id=" . urlencode($document_id) . "&completed=1");
            exit();
        }
        $stmt->close();
    }
    // If update fails
    header("Location: supervisor_student_status.php?document_id=" . urlencode($document_id) . "&completed=0");
    exit();
} else {
    echo "Invalid request.";
}
$conn->close();
?>
