<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cyber Secure Login</title>
    <link rel="stylesheet" href="../css/cyber.css">
    <style>
        .error {
            background: #ff4d4d;
            color: #fff;
            padding: 12px 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
            animation: fadeIn 0.5s;
        }
        .success {
            background: #4CAF50;
            color: #fff;
            padding: 12px 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
            animation: fadeIn 0.5s;
        }
    </style>
</head>
<body>
<div class="login-form">

    <?php if (isset($_SESSION['error'])): ?>
        <div class="error" id="error-msg"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <script>
            setTimeout(function() {
                var err = document.getElementById('error-msg');
                if (err) err.style.display = 'none';
            }, 2000);
        </script>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="success" id="success-msg"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php if (isset($_GET['redirect'])): ?>
        <script>
            setTimeout(function() {
                window.location.href = "<?php echo htmlspecialchars($_GET['redirect']); ?>";
            }, 3000);
        </script>
        <?php endif; ?>
    <?php endif; ?>

    <form action="cyber_login_process.php" method="post">
        <div class="cyber-container">
            <h2>General Login</h2>
            <div class="inputbox">
                <input type="text" id="user_id" name="user_id" required placeholder="Enter your ID">
                <i>Updo Staff ID / Cyber ID</i>
            </div>
            <div class="inputbox">
                <input type="submit" value="LOGIN">
            </div>
            <a href="partnership_home.php" class="logout-btn">Back to Dashboard</a>
        </div>
    </form>
</div>
</body>
</html>