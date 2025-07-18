<?php
include 'config.php';

$matric = isset($_POST['matric']) ? trim($_POST['matric']) : '';
$sup_id = isset($_POST['sup_id']) ? trim($_POST['sup_id']) : '';
$amount = isset($_POST['amount']) ? intval($_POST['amount']) : 0;

if ($matric) {
    $stmt = $conn->prepare("UPDATE student SET reason = '' WHERE matric = ?");
    $stmt->bind_param("s", $matric);
    $stmt->execute();
    $stmt->close();
}
if ($sup_id) {
    $stmt = $conn->prepare("UPDATE supervisors SET reason = '' WHERE sup_id = ?");
    $stmt->bind_param("s", $sup_id);
    $stmt->execute();
    $stmt->close();
}

// Optionally, you can log the payment or update another table here

echo "success";
