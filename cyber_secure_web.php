<?php
session_start();

// Security check: Ensure staff is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'staff') {
    header("Location: cyber_secure_login.php");
    exit();
}

$staff_id = htmlspecialchars($_SESSION['user_id']);
$staff_name = isset($_SESSION['VC_name']) ? htmlspecialchars($_SESSION['VC_name']) : '';
$staff_email = isset($_SESSION['contact_email']) ? htmlspecialchars($_SESSION['contact_email']) : '';
$staff_phone = isset($_SESSION['contact_phone']) ? htmlspecialchars($_SESSION['contact_phone']) : '';
$staff_institution = isset($_SESSION['institution']) ? htmlspecialchars($_SESSION['institution']) : null;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cyber Secure Web</title>
    <link rel="stylesheet" href="../css/cyber.css">
    <link rel="stylesheet" href="../css/cyber_side.css">
    <style>
        .cyber-container {
            margin-left: 220px;
            transition: margin-top 0.7s;
        }
        .cyber-container.pushed-up {
            margin-top: -60px;
        }
        .think-input-container {
            display: none;
            margin-left: 220px;
            margin-top: 20px;
        }
        .think-input-container.active {
            display: block;
            animation: fadeIn 0.7s;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
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
    <div class="cyber-container" id="mainContainer">
        <h1>Welcome to the UP-DO School Management Dashboard</h1>
        <p>Hello, <strong><?php echo $staff_name; ?></strong>
            <?php if ($staff_institution): ?> the Vice-chancellor of <strong><?php echo $staff_institution; ?></strong><?php endif; ?>.
        </p>
        <p> Your school Email: <strong><?php echo $staff_email; ?></strong></p>
        <p>Your school Phone Number: <strong><?php echo $staff_phone; ?></strong></p>
        <p>
            Your UPDO-Staff ID is: <strong><?php echo $staff_id; ?></strong>
        </p>
        <a href="cyber_secure_logout.php" class="logout-btn">Logout</a>
    </div>
    <div class="think-input-container" id="thinkInputContainer">
        <input type="text" placeholder="What do you think?" style="width: 60%; padding: 12px; font-size: 1.1em; border-radius: 8px; border: 1px solid #ccc;">
    </div>
    <script>
        setTimeout(function() {
            document.getElementById('mainContainer').classList.add('pushed-up');
            document.getElementById('thinkInputContainer').classList.add('active');
        }, 3000);
    </script>
</body>
</html>