<?php
session_start();

// Check if the supervisor is logged in
if (!isset($_SESSION['sup_id'])) {
    header("Location: student_login.php"); // Redirect to login page if not logged in
    exit();
}

// Include database configuration
include 'config.php';

// Retrieve matric number from the query string
if (isset($_GET['matric'])) {
    $matric = $_GET['matric'];

    // SQL query to fetch student data based on matric number
    $sql = "SELECT * FROM student WHERE matric = '$matric'";
    // Execute the query
    $result = $conn->query($sql);

    // Check if a student with the given matric number exists
    if ($result && $result->num_rows > 0) {
        $student = $result->fetch_assoc();

        // Debugging: Output the entire student array
        echo "<!-- Student Data: " . print_r($student, true) . " -->";

        // Generate HTML for the student profile
        $output = '<h2>' . htmlspecialchars($student['fullname']) . '</h2>';

        // Check if profile_pic exists and is not empty
        if (isset($student['profile_pic']) && !empty($student['profile_pic'])) {
            $imagePath = '../uploads/' . htmlspecialchars($student['profile_pic']);
            $output .= '<img src="' . $imagePath . '" alt="Profile Picture" style="width:150px; height:150px;">';
        } else {
            // Display a default image if profile_pic is not available
            $output .= '<img src="../image/default.png" alt="Profile Picture" style="width:150px; height:150px;">';
        }

        $output .= '<p><strong>MATRIC NUMBER:</strong> ' . htmlspecialchars($student['matric']) . '</p>';
        $output .= '<p><strong>EMAIL:</strong> ' . htmlspecialchars($student['email']) . '</p>';
        $output .= '<p><strong>PHONE NUMBER:</strong> ' . htmlspecialchars($student['phone_number']) . '</p>';
        $output .= '<p><strong>DEPARTMENT:</strong> ' . htmlspecialchars($student['department']) . '</p>';
        $output .= '<p><strong>INSTITUTUION:</strong> ' . htmlspecialchars($student['institution']) . '</p>';

        // Use $output as needed
        echo $output;
    } else {
        echo '<p>Student not found.</p>';
    }
} else {
    echo '<p>Matric number not provided.</p>';
}

$conn->close();
?>
