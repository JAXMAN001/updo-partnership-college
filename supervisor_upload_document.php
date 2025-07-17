<?php
session_start();
include 'config.php';

// Check if the supervisor is logged in
if (!isset($_SESSION['sup_id'])) {
    header("Location: student_login.php");
    exit();
}

// Check if document ID is set
if (!isset($_POST['document_id']) || !isset($_POST['action'])) {
    echo "Document ID or action not provided.";
    exit();
}

$document_id = $_POST['document_id'];
$action = $_POST['action'];

// Handle file upload
if ($action == 'approve') {
    // Update document status in the database
    $sql = "UPDATE document SET status = 'approved' WHERE id = '$document_id'";

    if ($conn->query($sql) === TRUE) {
        // Display success message and redirect after 2 seconds
        echo "<script>
                alert('Document has been approved successfully!');
                setTimeout(function() {
                    window.location.href = 'supervisor_student_status.php';
                }, 2000); // Redirect after 2 seconds
              </script>";
        exit();
    } else {
        echo "Error updating document path: " . $conn->error;
    }
} else if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['new_document'])) {
    $target_dir = "../uploads/documents/";
    $uploadOk = 1;
    $allowedFileTypes = ["pdf", "doc", "docx"];

    // Generate a unique filename
    $safe_filename = $_FILES["new_document"]["name"]; // Use the original filename
    $target_file = $target_dir . $safe_filename;
    $documentFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check file size
    if ($_FILES["new_document"]["size"] > 10000000) { // Increased file size to 10MB
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if (!in_array($documentFileType, $allowedFileTypes)) {
        echo "Sorry, only PDF, DOC & DOCX files are allowed.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        // Set appropriate error message
    // if everything is ok, try to upload file
    } else {
        if (move_uploaded_file($_FILES["new_document"]["tmp_name"], $target_file)) {
            // Determine the status based on the action
            $status = $action;

            // Update the document path in the database
            $sql = "UPDATE document SET path = '$target_file', name = '$safe_filename', 
                    status = '$status' WHERE id = '$document_id'";
            
            if ($conn->query($sql) === TRUE) {
                // Display success message and redirect after 2 seconds
                echo "<script>
                        alert('Document uploaded and updated successfully.');
                        setTimeout(function() {
                            window.location.href = 'supervisor_student_status.php';
                        }, 2000); // Redirect after 2 seconds
                      </script>";
                exit();
            } else {
                echo "Error updating document path: " . $conn->error;
            }
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
} else {
    echo "No file uploaded.";
}

$conn->close();
?>
