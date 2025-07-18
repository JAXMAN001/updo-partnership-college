<?php
header('Content-Type: application/json');

include 'config.php';

$response = ['status' => 'error', 'message' => 'Invalid request.'];

if (isset($_POST['matric']) && isset($_POST['institution'])) {
    $matric = trim($_POST['matric']);
    $institution = trim($_POST['institution']);

    if (!empty($matric) && !empty($institution)) {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM student WHERE matric = ? AND institution = ?");
        if ($stmt) {
            $stmt->bind_param("ss", $matric, $institution);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            if ($row['count'] > 0) {
                $response = ['status' => 'exists', 'message' => 'Matric number already exists for this institution.'];
            } else {
                $response = ['status' => 'not_exists', 'message' => 'Matric number is available.'];
            }
            $stmt->close();
        } else {
            $response['message'] = 'Database query failed.';
        }
    }
}

$conn->close();
echo json_encode($response);
?>