<?php
header('Content-Type: application/json');

// Include database configuration
$host = "localhost";
$username = "root";
$password = "";
$dbname = "documents";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit();
}

$response = ['status' => 'error', 'message' => 'Invalid request.'];

if (isset($_POST['sup_id'])) {
    $sup_id = trim($_POST['sup_id']);

    if (!empty($sup_id)) {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM supervisors WHERE sup_id = ?");
        $stmt->bind_param("s", $sup_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $response = ['status' => ($row['count'] > 0) ? 'exists' : 'not_exists'];
        $stmt->close();
    }
}

$conn->close();
echo json_encode($response);
?>