<?php
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Invalid request.'];

if (isset($_POST['staff_id'])) {
    // --- Database Configuration ---
    $host = 'localhost';
    $db   = 'documents';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';

    try {
        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

        $staff_id = trim($_POST['staff_id']);
        if (!empty($staff_id)) {
            $stmt = $pdo->prepare("SELECT id FROM cyber_security WHERE staff_id = ?");
            $stmt->execute([$staff_id]);
            $response['status'] = $stmt->fetch() ? 'exists' : 'not_exists';
        }
    } catch (PDOException $e) {
        // In a real app, you'd log this error instead of exposing details
        $response['message'] = 'Database error.';
        error_log("check_cyber_id.php DB error: " . $e->getMessage());
    }
}

echo json_encode($response);
?>