<?php
session_start();

// Only allow access for logged-in updo_staff_id users
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'staff') {
    header("Location: cyber_secure_login.php");
    exit();
}

// Use MySQLi connection
require_once 'config.php'; // This file should set up $conn as your MySQLi connection

// Optionally, verify the staff ID exists in the database
$stmt = $conn->prepare("SELECT updo_staff_id FROM partnership_form WHERE updo_staff_id = ?");
$stmt->bind_param("s", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows !== 1) {
    // Invalid session or tampered session
    session_destroy();
    header("Location: cyber_secure_login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>UPDO Staff Features</title>
    <link rel="stylesheet" href="../css/cyber.css">
    <link rel="stylesheet" href="../css/cyber_side.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .features-container {
            display: flex;
            justify-content: center; /* Center all icons horizontally */
            align-items: flex-start;
            gap: 60px;
            min-height: 80vh;
            margin-left: 260px;
            margin-top: 40px;
        }
        .feature-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            padding: 40px 30px;
            transition: box-shadow 0.2s;
            width: 200px;
        }
        .feature-box:hover {
            box-shadow: 0 8px 32px rgba(0,0,0,0.18);
        }
        .feature-icon {
            font-size: 4em;
            margin-bottom: 20px;
            color: #007bff;
        }
        .feature-title {
            font-size: 1.3em;
            font-weight: bold;
            color: #222;
            margin-bottom: 10px;
        }
        .feature-btn {
            margin-top: 18px;
            padding: 8px 16px;
            font-size: 0.95em;
            font-weight: 500;
            border: 3px solid yellow;
            border-radius: 8px;
            background: #000; /* Set background to black */
            color: #222;
            cursor: pointer;
            transition: background 0.2s, color 0.2s, padding 0.2s, font-size 0.2s;
            animation: borderColorChange 9s linear infinite;
            text-decoration: none;
            display: inline-block;
            min-width: 80px;
            max-width: 100%;
            text-align: center;
            word-break: break-word;
        }
        .feature-btn:hover {
            background: #007bff;
            color: #fff;
        }
        @keyframes borderColorChange {
            0%   { border-color: yellow; color: yellow; }
            33%  { border-color: #00ffea; color: #00ffea; }
            66%  { border-color: #ff00c8; color: #ff00c8; }
            100% { border-color: yellow; color: yellow; }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Menu</h2>
        <ul>
             <li><a href="cyber_secure_web.php">DASHBOARD</a></li>
            <li><a href="cyber_secure_settings.php">SETTINGS</a></li>
            <li><a href="cyber_features.php">FEATURES</a></li>
            <li><a href="cyber_secure_logout.php">Logout</a></li>
        </ul>
    </div>
    <div class="features-container">
        <div class="feature-box">
            <i class="fa-solid fa-user-plus feature-icon"></i>
            <div class="feature-title">Registration</div>
            <a href="admin_add_admin.php" class="feature-btn">REGISTER NEW HOD</a>
        </div>
        <div class="feature-box">
            <i class="fa-solid fa-user-graduate feature-icon"></i>
            <div class="feature-title">Student</div>
            <a href="cyber_students.php" class="feature-btn">STUDENT</a>
        </div>
        <div class="feature-box">
            <i class="fa-solid fa-chalkboard-teacher feature-icon"></i>
            <div class="feature-title">Supervisor</div>
            <a href="cyber_supervisors.php" class="feature-btn">SUPERVISOR</a>
        </div>
        <div class="feature-box">
            <i class="fa-solid fa-money-check-dollar feature-icon"></i>
            <div class="feature-title">Transaction</div>
            <a href="#" class="feature-btn">TRANSACTION</a>
        </div>
        <div class="feature-box">
            <i class="fa-solid fa-envelope-open-text feature-icon"></i>
            <div class="feature-title">Request</div>
            <a href="cyber_requests.php" class="feature-btn">REQUEST</a>
        </div>
    </div>
</body>
</html>
