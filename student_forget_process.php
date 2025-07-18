<?php
session_start();
include 'config.php';

// Get POST data
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$user_id = isset($_POST['matric']) ? trim($_POST['matric']) : '';



// Check in student table (email or phone)
if (!empty($email)) {
    $stmt = $conn->prepare("SELECT * FROM student WHERE email = ? AND matric = ?");
    $stmt->bind_param("ss", $email, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $_SESSION['reset_user'] = $user_id;
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Confirm Details</title>
            <link rel="stylesheet" href="../css/signup.css">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
        </head>
        <body>
        <div class="login-form">
            <form action="date_of_birth_validation.php" method="post">
                <div class="content">
                    <h2>CONFIRM YOUR DETAILS</h2>
                    <div class="inputbox">
                        <input type="text" value="<?php echo htmlspecialchars($email); ?>" readonly>
                        <i>EMAIL ADDRESS</i>
                    </div>
                    <div class="inputbox">
                        <input type="text" value="<?php echo htmlspecialchars($user_id); ?>" readonly>
                        <i>USER ID</i>
                    </div>
                    <input type="hidden" name="contact" value="<?php echo htmlspecialchars($email); ?>">
                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
                    <div class="inputbox">
                        <input type="submit" value="PROCEED">
                    </div>
                </div>
            </form>
        </div>
        </body>
        </html>
        <?php
        exit();
    }
    $stmt->close();
}
if (!empty($phone)) {
    $stmt = $conn->prepare("SELECT * FROM student WHERE phone_number = ? AND matric = ?");
    if ($stmt) {
        $stmt->bind_param("ss", $phone, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $_SESSION['reset_user'] = $user_id;
            ?>
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <title>Confirm Details</title>
                <link rel="stylesheet" href="../css/signup.css">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
            </head>
            <body>
            <div class="login-form">
                <form action="date_of_birth_validation.php" method="post">
                    <div class="content">
                        <h2>CONFIRM YOUR DETAILS</h2>
                        <div class="inputbox">
                            <input type="text" value="<?php echo htmlspecialchars($phone); ?>" readonly>
                            <i>PHONE NUMBER</i>
                        </div>
                        <div class="inputbox">
                            <input type="text" value="<?php echo htmlspecialchars($user_id); ?>" readonly>
                            <i>USER ID</i>
                        </div>
                        <input type="hidden" name="contact" value="<?php echo htmlspecialchars($phone); ?>">
                        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
                        <div class="inputbox">
                            <input type="submit" value="PROCEED">
                        </div>
                    </div>
                </form>
            </div>
            </body>
            </html>
            <?php
            exit();
        }
        $stmt->close();
    }
}

// Check in supervisors table (email or phone)
if (!empty($email)) {
    $stmt = $conn->prepare("SELECT * FROM supervisors WHERE email = ? AND sup_id = ?");
    $stmt->bind_param("ss", $email, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $_SESSION['reset_user'] = $user_id;
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Confirm Details</title>
            <link rel="stylesheet" href="../css/signup.css">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
        </head>
        <body>
        <div class="login-form">
            <form action="date_of_birth_validation.php" method="post">
                <div class="content">
                    <h2>CONFIRM YOUR DETAILS</h2>
                    <div class="inputbox">
                        <input type="text" value="<?php echo htmlspecialchars($email); ?>" readonly>
                        <i>EMAIL ADDRESS</i>
                    </div>
                    <div class="inputbox">
                        <input type="text" value="<?php echo htmlspecialchars($user_id); ?>" readonly>
                        <i>USER ID</i>
                    </div>
                    <input type="hidden" name="contact" value="<?php echo htmlspecialchars($email); ?>">
                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
                    <div class="inputbox">
                        <input type="submit" value="PROCEED">
                    </div>
                </div>
            </form>
        </div>
        </body>
        </html>
        <?php
        exit();
    }
    $stmt->close();
}
if (!empty($phone)) {
    $stmt = $conn->prepare("SELECT * FROM supervisors WHERE phone = ? AND sup_id = ?");
    if ($stmt) {
        $stmt->bind_param("ss", $phone, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $_SESSION['reset_user'] = $user_id;
            ?>
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <title>Confirm Details</title>
                <link rel="stylesheet" href="../css/signup.css">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
            </head>
            <body>
            <div class="login-form">
                <form action="date_of_birth_validation.php" method="post">
                    <div class="content">
                        <h2>CONFIRM YOUR DETAILS</h2>
                        <div class="inputbox">
                            <input type="text" value="<?php echo htmlspecialchars($phone); ?>" readonly>
                            <i>PHONE NUMBER</i>
                        </div>
                        <div class="inputbox">
                            <input type="text" value="<?php echo htmlspecialchars($user_id); ?>" readonly>
                            <i>USER ID</i>
                        </div>
                        <input type="hidden" name="contact" value="<?php echo htmlspecialchars($phone); ?>">
                        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
                        <div class="inputbox">
                            <input type="submit" value="PROCEED">
                        </div>
                    </div>
                </form>
            </div>
            </body>
            </html>
            <?php
            exit();
        }
        $stmt->close();
    }
}

// Check in admin table (email or phone)
if (!empty($email)) {
    $stmt = $conn->prepare("SELECT * FROM admin WHERE email = ? AND hod_id = ?");
    if ($stmt) {
        $stmt->bind_param("ss", $email, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $_SESSION['reset_user'] = $user_id;
            ?>
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <title>Confirm Details</title>
                <link rel="stylesheet" href="../css/signup.css">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
            </head>
            <body>
            <div class="login-form">
                <form action="date_of_birth_validation.php" method="post">
                    <div class="content">
                        <h2>CONFIRM YOUR DETAILS</h2>
                        <div class="inputbox">
                            <input type="text" value="<?php echo htmlspecialchars($email); ?>" readonly>
                            <i>EMAIL ADDRESS</i>
                        </div>
                        <div class="inputbox">
                            <input type="text" value="<?php echo htmlspecialchars($user_id); ?>" readonly>
                            <i>USER ID</i>
                        </div>
                        <input type="hidden" name="contact" value="<?php echo htmlspecialchars($email); ?>">
                        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
                        <div class="inputbox">
                            <input type="submit" value="PROCEED">
                        </div>
                    </div>
                </form>
            </div>
            </body>
            </html>
            <?php
            exit();
        }
        $stmt->close();
    }
}
if (!empty($phone)) {
    $stmt = $conn->prepare("SELECT * FROM admin WHERE phone = ? AND hod_id = ?");
    if ($stmt) {
        $stmt->bind_param("ss", $phone, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $_SESSION['reset_user'] = $user_id;
            ?>
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <title>Confirm Details</title>
                <link rel="stylesheet" href="../css/signup.css">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
            </head>
            <body>
            <div class="login-form">

                <form action="date_of_birth_validation.php" method="post">

                    <div class="content">

                        <h2>CONFIRM YOUR DETAILS</h2>

                        <div class="inputbox">
                            <input type="text" value="<?php echo htmlspecialchars($phone); ?>" readonly>
                            <i>PHONE NUMBER</i>
                        </div>

                        <div class="inputbox">
                            <input type="text" value="<?php echo htmlspecialchars($user_id); ?>" readonly>
                            <i>USER ID</i>
                        </div>

                        <input type="hidden" name="contact" value="<?php echo htmlspecialchars($phone); ?>">
                        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
                        
                        <div class="inputbox">
                            <input type="submit" value="PROCEED">
                        </div>

                    </div>
                    
                </form>
            </div>
            </body>
            </html>
            <?php
            exit();
        }
        $stmt->close();
    }
}

// If not found in any table
echo "<script>alert('Invalid information. Please try again.'); window.history.back();</script>";
exit();

$conn->close();
?>
