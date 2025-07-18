<?php
// filepath: c:\xampp\htdocs\web\supervisor\delete_document.php
session_start(); // Start the session to access session variables
include 'config.php'; // Include the database configuration file

// Check if the supervisor is logged in
if (!isset($_SESSION['sup_id'])) {
    header("Location: student_login.php"); // Redirect to the login page if not logged in
    exit(); // Stop further execution
}

// Check if document ID is set
if (isset($_POST['document_id'])) {
    $document_id = $_POST['document_id']; // Get the document ID from the POST request

    // Fetch the document path from the database
    $sql = "SELECT path FROM document WHERE id = '$document_id'"; // SQL query to select the document path based on the ID
    $result = $conn->query($sql); // Execute the query

    if ($result && $result->num_rows > 0) {
        $document_data = $result->fetch_assoc(); // Fetch the result as an associative array
        $document_path = $document_data['path']; // Get the document path from the array

        // Delete the file from the server
        if (($document_path)) { // Attempt to delete the file from the server
            // Delete the document record from the database
            $delete_sql = "DELETE FROM document WHERE id = '$document_id'"; // SQL query to delete the document record
            if ($conn->query($delete_sql) === TRUE) { // Execute the delete query
                $message = "Document deleted successfully."; // Set success message
            } else {
                $message = "Error deleting document record: " . $conn->error; // Set error message if the delete query fails
            }
        } else {
            $message = "Error deleting file from server."; // Set error message if the file deletion fails
        }
    } else {
        $message = "Document not found."; // Set message if the document is not found
    }
} else {
    $message = "Document ID not provided."; // Set message if the document ID is not provided
}

$conn->close(); // Close the database connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Deletion</title>
    <script>
        function redirect() {
            window.location.href = "supervisor_student_status.php"; // Redirect to the status page
        }
        setTimeout(redirect, 5000); // Redirect after 5 seconds
    </script>
</head>
<body>
    <p><?php echo htmlspecialchars($message); ?></p>
</body>
</html>