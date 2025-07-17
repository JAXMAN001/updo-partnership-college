<?php
session_start();
$host = "localhost"; // Database host
$username = "root"; // Database username
$password = ""; // Database password
$dbname = "documents"; // Database name

// Create a database connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error); // If connection fails, terminate script and display error
}

$sup_id = isset($_GET['sup_id']) ? trim($_GET['sup_id']) : ''; // Get the sup_id from the GET request
$student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0; // Get the student_id from the GET request

header('Content-Type: application/json');

if (!$sup_id || !$student_id) {
    echo json_encode(['status' => 'not_found']);
    exit();
}

// Get supervisor details
$sup_sql = "SELECT fullname, email, institution, department, faculty FROM supervisors WHERE sup_id = ?";
$stmt = $conn->prepare($sup_sql);
$stmt->bind_param("s", $sup_id);
$stmt->execute();
$sup_result = $stmt->get_result();
if ($sup_result->num_rows === 0) {
    echo json_encode(['status' => 'not_found']);
    exit();
}
$sup = $sup_result->fetch_assoc();

// Get student details
$stu_sql = "SELECT institution, department, faculty FROM student WHERE id = ?";
$stmt2 = $conn->prepare($stu_sql);
$stmt2->bind_param("i", $student_id);
$stmt2->execute();
$stu_result = $stmt2->get_result();
if ($stu_result->num_rows === 0) {
    echo json_encode(['status' => 'not_found']);
    exit();
}
$stu = $stu_result->fetch_assoc();

// Compare institution, department, faculty
if (
    $sup['institution'] !== $stu['institution'] ||
    $sup['department'] !== $stu['department'] ||
    $sup['faculty'] !== $stu['faculty']
) {
    echo json_encode(['status' => 'mismatch']);
    exit();
}

// If all match, return supervisor details
echo json_encode([
    'status' => 'ok',
    'fullname' => $sup['fullname'],
    'email' => $sup['email']
]);

$conn->close(); // Close the database connection
?>
