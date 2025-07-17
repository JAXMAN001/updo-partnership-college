<?php
header('Content-Type: application/json');
session_start();
include 'config.php'; // Your DB connection file

$sql = "SELECT 
    (SELECT COUNT(*) FROM student) AS total_students,
    (SELECT COUNT(*) FROM supervisor) AS total_supervisors,
    (SELECT COUNT(*) FROM student WHERE supervisor_id IS NOT NULL) AS assigned_students";
$result = $conn->query($sql);
echo json_encode($result->fetch_assoc());
?>