<?php
include 'config.php';

$matric = $_GET['matric'];

$sql = "SELECT * FROM student WHERE matric = '$matric'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "found";
} else {
    echo "not_found";
}

$conn->close();
?>
