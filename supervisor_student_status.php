<?php
session_start();
include 'config.php';

// Check if the supervisor is logged in
if (!isset($_SESSION['sup_id'])) {
    header("Location: student_login.php");
    exit();
}

// Check if document ID is set
if (isset($_GET['document_id'])) {
    $document_id = $_GET['document_id'];

    // Fetch document details from the database
    $sql = "SELECT * FROM document WHERE id = '$document_id'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $document_data = $result->fetch_assoc();
        $document_status = isset($document_data['status']) ? $document_data['status'] : 'pending';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/status.css">
    <title>Document Status</title>
</head>
<body>
    
        <form method='post' action='supervisor_upload_document.php'>
            <input type='hidden' name='document_id' value='<?php echo htmlspecialchars($document_id); ?>'>
            <input type='hidden' name='action' value='approve'>
            <button type='submit'>APPROVE</button>
        </form>

        <form method='get' action='supervisor_upload_form.php'>
            <input type='hidden' name='document_id' value='<?php echo htmlspecialchars($document_id); ?>'>
            <button type='submit' name='action' value='unapprove'>UNAPPROVE</button>
        </form>

        <form method='post' action='supervisor_delete_document.php'>
            <input type='hidden' name='document_id' value='<?php echo htmlspecialchars($document_id); ?>'>
            <button type='submit' style="background-color: yellow; color: black;" onclick="return confirm('Are you sure you want to delete this document?')">DELETE</button>
        </form>
        <form method='post' id='completedForm' action='supervisor_mark_completed.php' onsubmit="return confirmCompletion();">
            <input type='hidden' name='document_id' value='<?php echo htmlspecialchars($document_id); ?>'>
            <button type='submit' style="background-color: blue; color: white;">COMPLETED</button>
        </form>
        <script>
        function confirmCompletion() {
            if (confirm('Are you sure you want to mark this project as completed?')) {
                return true;
            }
            return false;
        }
        // Show success message if redirected with completed=1
        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('completed') === '1') {
                alert('Completed successful');
            }
        }
        </script>
    
</body>
</html>
<?php
    } else {
        echo "<p>Document not found.</p>";
    }
} else {
    echo "<p>Document ID not provided.</p>";
}

$conn->close();
?>