<?php
session_start(); // Start the session to access session variables
include 'config.php'; // Include the database configuration file

// Check if the supervisor is logged in
if (!isset($_SESSION['sup_id'])) { // Check if the supervisor_id session variable is set
    header("Location: student_login.php"); // Redirect to the login page if not logged in
    exit(); // Stop further execution
}

// Check if the document ID and action are set
if (isset($_POST['document_id']) && isset($_POST['action'])) { // Check if both document_id and action are set in the POST request
    $document_id = $_POST['document_id']; // Retrieve the document ID from the POST request
    $action = $_POST['action']; // Retrieve the action from the POST request

    // Perform the appropriate action based on the button clicked
    if ($action == 'approve') { // If the action is 'approve'
        $status = 'approved'; // Set the status to 'approved'
    } elseif ($action == 'unapprove') { // If the action is 'unapprove'
        $status = 'unapproved'; // Set the status to 'unapproved'
    } elseif ($action == 'correction') { // If the action is 'correction'
        $status = 'correction'; // Set the status to 'correction'
    } else { // If the action is none of the above
        $status = 'pending'; // Default status // Set the status to 'pending' as the default
    }

    // Update the document status in the database
    $sql = "UPDATE document SET status = '$status' WHERE id = '$document_id'"; // SQL query to update the document status in the documents table
    if ($conn->query($sql) === TRUE) { // Execute the SQL query
        // Redirect back to the status.php page
        header("Location: supervisor_student_status.php?document_id=" . urlencode($document_id)); // Redirect to the status.php page with the document ID
        exit(); // Stop further execution
    } else { // If the query fails
        echo "Error updating document status: " . $conn->error; // Display an error message
    }
} else { // If document_id or action is not set
    echo "Invalid request."; // Display an error message
}

$conn->close(); // Close the database connection
?>