<?php
session_start();
include 'config.php';

if (!isset($_SESSION['hod_id'])) {
    echo "fail";
    exit;
}

$hod_id = $_SESSION['hod_id'];
$password = isset($_POST['password']) ? $_POST['password'] : '';

if (!$password) {
    echo "fail";
    exit;
}

// Fetch the password for the current hod_id
$stmt = $conn->prepare("SELECT password FROM admin WHERE hod_id = ?");
$stmt->bind_param("s", $hod_id);
$stmt->execute();
$stmt->bind_result($db_password);
if ($stmt->fetch()) {
    if ($password === $db_password) { // Plain text comparison
        echo "success";
    } else {
        echo "fail";
    }
} else {
    echo "fail";
}
$stmt->close();
exit;
exit;
