<?php
session_start();
include 'config.php'; // Include database configuration

// Check if student is logged in
if (!isset($_SESSION['matric'])) {
    // It's better to return an error message than die() in an AJAX context
    header('Content-Type: text/html'); // Ensure correct content type
    echo "<p style='color: red;'>Error: Not logged in.</p>";
    exit();
}

$matric = $_SESSION['matric'];

// Initialize output
$output = "<p>No supervisor assigned yet or details unavailable.</p>"; // Default message

// Use prepared statements for security
// Fetch the supervisor's details by joining student and supervisors tables using sup_id (string)
$sql = "SELECT sup.fullname, sup.email, sup.phone, sup.department, sup.faculty, sup.sup_id
        FROM supervisors sup
        JOIN student st ON sup.sup_id = st.sup_id
        WHERE st.matric = ?";

$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("s", $matric);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $supervisor = $result->fetch_assoc();

        // Build the HTML output
        $output = "<h4>Supervisor Details:</h4>"; // Use h4 for semantics
        $output .= "<p><strong>Name:</strong> " . htmlspecialchars($supervisor['fullname']) . "</p>";
        $output .= "<p><strong>User ID:</strong> " . htmlspecialchars($supervisor['sup_id']) . "</p>"; // Display the user-friendly ID
        $output .= "<p><strong>Email:</strong> " . htmlspecialchars($supervisor['email']) . "</p>";
        $output .= "<p><strong>Phone:</strong> " . htmlspecialchars($supervisor['phone']) . "</p>";
        $output .= "<p><strong>Department:</strong> " . htmlspecialchars($supervisor['department']) . "</p>";
        $output .= "<p><strong>Faculty:</strong> " . htmlspecialchars($supervisor['faculty']) . "</p>";

    } else {
        // Keep the default message if no supervisor is found or assigned
        // You could add more specific checks, e.g., check if student.sup_id is NULL
        $check_assigned_sql = "SELECT sup_id FROM student WHERE matric = ?";
        $check_stmt = $conn->prepare($check_assigned_sql);
        if($check_stmt){
            $check_stmt->bind_param("s", $matric);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            if($check_row = $check_result->fetch_assoc()){
                if(is_null($check_row['sup_id'])){
                     $output = "<p>No supervisor has been assigned to you yet.</p>";
                } else {
                     // Supervisor ID exists but wasn't found in supervisors table (data integrity issue)
                     $output = "<p>Supervisor details could not be retrieved. Please contact support.</p>";
                     error_log("Data integrity issue: Student matric '{$matric}' has sup_id '{$check_row['sup_id']}' but no matching supervisor found.");
                }
            }
            $check_stmt->close();
        }
    }
    $stmt->close();
} else {
    // Handle statement preparation error
    error_log("Error preparing statement in student_get_supervisor.php: " . $conn->error);
    $output = "<p style='color: red;'>Error retrieving supervisor details. Please try again later.</p>";
}

$conn->close();

// Set content type and echo the result for AJAX
header('Content-Type: text/html');
echo $output;
?>
