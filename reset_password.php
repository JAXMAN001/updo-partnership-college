<?php
session_start();
include 'config.php';

if (!isset($_SESSION['verified_code'])) {
    header("Location: verification_code.php");
    exit();
}

$code = $_SESSION['verified_code'];
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';

    // Try to update student table
    $stmt = $conn->prepare("UPDATE student SET password = ?, user_code = NULL WHERE user_code = ?");
    $stmt->bind_param("ss", $new_password, $code);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        $success = "Password updated successfully.";
        unset($_SESSION['verified_code']);
    }
    $stmt->close();

    // Try to update supervisors table
    if (!$success) {
        $stmt = $conn->prepare("UPDATE supervisors SET password = ?, user_code = NULL WHERE user_code = ?");
        $stmt->bind_param("ss", $new_password, $code);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            $success = "Password updated successfully.";
            unset($_SESSION['verified_code']);
        }
        $stmt->close();
    }

    // Try to update admin table
    if (!$success) {
        $stmt = $conn->prepare("UPDATE admin SET password = ?, user_code = NULL WHERE user_code = ?");
        $stmt->bind_param("ss", $new_password, $code);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            $success = "Password updated successfully.";
            unset($_SESSION['verified_code']);
        }
        $stmt->close();
    }

    if (!$success) {
        $error = "Failed to update password. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link rel="stylesheet" href="../css/signup.css">
    <?php if ($success): ?>
    <meta http-equiv="refresh" content="2;url=student_login.php">
    <?php endif; ?>
</head>
<body>
<div class="login-form">
    <form action="reset_password.php" method="post">
        <div class="content">
            <h2>RESET PASSWORD</h2>
            <?php if ($success): ?>
                <div style="color:green;"><?php echo $success; ?></div>
            <?php elseif ($error): ?>
                <div style="color:red;"><?php echo $error; ?></div>
            <?php endif; ?>
            <div class="inputbox">
                <input type="password" name="new_password" required placeholder="Enter new password">
            </div>
            <div class="inputbox">
                <input type="submit" value="UPDATE PASSWORD">
            </div>
        </div>
    </form>
</div>
</body>
</html>
