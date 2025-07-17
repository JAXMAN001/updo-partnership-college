<?php
session_start();
include 'config.php';

// Get user info from session or GET param
$user_id = '';
if (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
    $_SESSION['reset_user'] = $user_id;
} elseif (isset($_SESSION['reset_user'])) {
    $user_id = $_SESSION['reset_user'];
}
$email = isset($_SESSION['reset_email']) ? $_SESSION['reset_email'] : '';

// Generate and store a 6-digit random code if not already generated for this session
if (!isset($_SESSION['verification_code'])) {
    $_SESSION['verification_code'] = str_pad(strval(rand(0, 999999)), 6, '0', STR_PAD_LEFT);
}

// Store the code in the correct table if not already stored
if (!isset($_SESSION['code_stored']) && !empty($user_id)) {
    $code = $_SESSION['verification_code'];
    // Try to update student table
    $stmt = $conn->prepare("UPDATE student SET user_code = ? WHERE (matric = ? OR email = ?)");
    $stmt->bind_param("sss", $code, $user_id, $user_id);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        $_SESSION['code_stored'] = true;
    }
    $stmt->close();

    // Try to update supervisors table
    if (!isset($_SESSION['code_stored'])) {
        $stmt = $conn->prepare("UPDATE supervisors SET user_code = ? WHERE (sup_id = ? OR email = ?)");
        $stmt->bind_param("sss", $code, $user_id, $user_id);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            $_SESSION['code_stored'] = true;
        }
        $stmt->close();
    }

    // Try to update admin table
    if (!isset($_SESSION['code_stored'])) {
        $stmt = $conn->prepare("UPDATE admin SET user_code = ? WHERE (hod_id = ? OR email = ?)");
        $stmt->bind_param("sss", $code, $user_id, $user_id);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            $_SESSION['code_stored'] = true;
        }
        $stmt->close();
    }
}

// Handle form submission
$danger_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_code = isset($_POST['user_code']) ? trim($_POST['user_code']) : '';
    $session_code = isset($_SESSION['verification_code']) ? trim(strval($_SESSION['verification_code'])) : '';
    // Compare as strings and ignore leading zeros issues
    if ($user_code !== '' && $session_code !== '' && $user_code == $session_code) {
        // Store the code in the correct table for the user
        if (!empty($user_id)) {
            // Update student table
            $stmt = $conn->prepare("UPDATE student SET user_code = ? WHERE (matric = ? OR email = ?)");
            $stmt->bind_param("sss", $user_code, $user_id, $user_id);
            $stmt->execute();
            $affected = $stmt->affected_rows;
            $stmt->close();

            // Update supervisors table if not found in student
            if ($affected <= 0) {
                $stmt = $conn->prepare("UPDATE supervisors SET user_code = ? WHERE (sup_id = ? OR email = ?)");
                $stmt->bind_param("sss", $user_code, $user_id, $user_id);
                $stmt->execute();
                $affected = $stmt->affected_rows;
                $stmt->close();
            }

            // Update admin table if not found in previous tables
            if ($affected <= 0) {
                $stmt = $conn->prepare("UPDATE admin SET user_code = ? WHERE (hod_id = ? OR email = ?)");
                $stmt->bind_param("sss", $user_code, $user_id, $user_id);
                $stmt->execute();
                $stmt->close();
            }
        }
        $_SESSION['verified_code'] = $user_code; // Save for use in reset_password.php
        unset($_SESSION['verification_code']);
        unset($_SESSION['code_stored']);
        // Redirect to reset_password.php immediately
        echo '<script>window.location.href="reset_password.php";</script>';
        exit();
    } else {
        $danger_message = "Invalid code. Please enter the correct code.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PASSWORD RESET</title>
    <link rel="stylesheet" href="../css/signup.css">
    <style>
        .danger-message {
            color: #fff;
            background: #dc3545;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="login-form">
    <form action="verification_code.php" method="post">
        <div class="content">
            <h2>VERIFICATION CODE</h2>
            <?php if (!empty($danger_message)): ?>
                <div class="danger-message"><?php echo $danger_message; ?></div>
            <?php endif; ?>
            <div class="inputbox">
                <input type="text" value="<?php echo htmlspecialchars($_SESSION['verification_code']); ?>" readonly style="font-weight:bold; color:blue;">
            </div>
            <div class="inputbox">
                <input type="text" name="user_code" maxlength="6" required>
                <i>ENTER THE CODE ABOVE</i>
            </div>
            <div class="inputbox">
                <input type="submit" value="PROCEED">
            </div>
        </div>
    </form>
</div>
</body>
</html>
