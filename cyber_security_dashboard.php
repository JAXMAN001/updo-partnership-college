<?php
session_start();
include 'config.php'; // Database connection

// Security check: Ensure cyber user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'cyber') {
    header("Location: cyber_secure_login.php");
    exit();
}

$cyber_id = htmlspecialchars($_SESSION['user_id']);

// Fetch cyber user details from database
$stmt = $conn->prepare("SELECT name, email, phone FROM cyber_security WHERE cyber_id = ?");
if (!$stmt) {
    // Handle prepare error
    die("Database error: " . htmlspecialchars($conn->error));
}
$stmt->bind_param("s", $cyber_id);
$stmt->execute();
$result = $stmt->get_result();

$cyber_name = '';
$cyber_email = '';
$cyber_phone = '';

if ($result && $result->num_rows === 1) {
    $row = $result->fetch_assoc();
    $cyber_name = htmlspecialchars($row['name']);
    $cyber_email = htmlspecialchars($row['email']);
    $cyber_phone = htmlspecialchars($row['phone']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cyber Security Dashboard</title>
    <link rel="stylesheet" href="../css/cyber.css">
</head>
<body>
    <div class="cyber-container">
        <h1>Welcome to the Cyber Security Dashboard</h1>
        <p>Hello, <strong><?php echo $cyber_name; ?></strong>.</p>
        <p>Email: <strong><?php echo $cyber_email; ?></strong></p>
        <p>Phone: <strong><?php echo $cyber_phone; ?></strong></p>
        <p>Your Cyber ID is: <strong><?php echo $cyber_id; ?></strong></p>
        <a href="cyber_secure_logout.php" class="logout-btn">Logout</a>
    </div>
</body>
</html>
