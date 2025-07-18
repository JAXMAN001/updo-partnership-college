<?php
include 'config.php';
session_start();

$user_id = isset($_GET['user_id']) ? trim($_GET['user_id']) : '';
$password = '';
$user_type = '';

// Fix: handle POST and SESSION user_id as well
if (isset($_POST['user_id']) && !empty($_POST['user_id'])) {
    $user_id = htmlspecialchars($_POST['user_id']);
    $_SESSION['reset_user'] = $user_id; // Store for subsequent POSTs
} elseif (isset($_SESSION['reset_user'])) {
    $user_id = htmlspecialchars($_SESSION['reset_user']);
}

if ($user_id) {
    // Check student table
    $stmt = $conn->prepare("SELECT password FROM student WHERE matric = ?");
    if ($stmt) {
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $stmt->bind_result($password);
        if ($stmt->fetch()) {
            $user_type = 'Student';
        }
        $stmt->close();
    }
    // Check supervisors table
    if (!$password) {
        $stmt = $conn->prepare("SELECT password FROM supervisors WHERE sup_id = ?");
        if ($stmt) {
            $stmt->bind_param("s", $user_id);
            $stmt->execute();
            $stmt->bind_result($password);
            if ($stmt->fetch()) {
                $user_type = 'Supervisor';
            }
            $stmt->close();
        }
    }
    // Check admin table
    if (!$password) {
        $stmt = $conn->prepare("SELECT password FROM admin WHERE hod_id = ?");
        if ($stmt) {
            $stmt->bind_param("s", $user_id);
            $stmt->execute();
            $stmt->bind_result($password);
            if ($stmt->fetch()) {
                $user_type = 'Admin';
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Password Display</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            text-align: center;
            padding-top: 80px;
        }
        .box {
            background: #fff;
            display: inline-block;
            padding: 40px 60px;
            border-radius: 10px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.09);
        }
        .password {
            font-size: 2em;
            color: #e74c3c;
            margin: 20px 0;
            font-weight: bold;
            letter-spacing: 2px;
        }
        .label {
            font-size: 1.1em;
            color: #555;
        }
        .back {
            margin-top: 24px;
            display: inline-block;
            padding: 10px 22px;
            background: #3498db;
            color: #fff;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
        }
        .back:hover {
            background: #217dbb;
        }
    </style>
</head>
<body>
    <div class="box">
        <h2>Here is the password that you forgot</h2>
        <?php if ($password): ?>
            <div class="label"><?php echo htmlspecialchars($user_type); ?> Password:</div>
            <div class="password"><?php echo htmlspecialchars($password); ?></div>
        <?php else: ?>
            <div class="label" style="color:#e74c3c;">User not found or password unavailable.</div>
        <?php endif; ?>
        <a href="student_login.php" class="back">Back to Login</a>
        <div id="countdown" style="margin-top:20px; color:#e67e22; font-weight:bold;"></div>
    </div>
    <script>
        // 1 minute countdown
        let seconds = 60;
        function updateCountdown() {
            let min = Math.floor(seconds / 60);
            let sec = seconds % 60;
            document.getElementById('countdown').textContent = 
                "Redirecting to login in: " + min.toString().padStart(2, '0') + ":" + sec.toString().padStart(2, '0');
            if (seconds > 0) {
                seconds--;
                setTimeout(updateCountdown, 1000);
            } else {
                window.location.href = "student_login.php";
            }
        }
        updateCountdown();
    </script>
</body>
</html>
