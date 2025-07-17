<?php
session_start();
include 'config.php';

// Get the user_id from POST (from student_forget_process.php) or SESSION (after first submit)
$user_id = '';
if (isset($_POST['user_id']) && !empty($_POST['user_id'])) {
    $user_id = htmlspecialchars($_POST['user_id']);
    $_SESSION['reset_user'] = $user_id; // Store for subsequent POSTs
} elseif (isset($_SESSION['reset_user'])) {
    $user_id = htmlspecialchars($_SESSION['reset_user']);
}

// Determine user type (matric, sup_id, hod_id)
$user_type = '';
$user_details = '';
if (!empty($user_id)) {
    // Check student
    $stmt = $conn->prepare("SELECT matric, email, phone FROM student WHERE matric = ?");
    if ($stmt) {
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $row = $result->fetch_assoc()) {
            $user_type = 'MATRIC';
            $user_details = "Matric: {$row['matric']}";
        }
        $stmt->close();
    }

    // Check supervisor if not found as student
    if (!$user_type) {
        $stmt = $conn->prepare("SELECT sup_id, email, phone FROM supervisors WHERE sup_id = ?");
        if ($stmt) {
            $stmt->bind_param("s", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result && $row = $result->fetch_assoc()) {
                $user_type = 'SUPERVISOR ID';
                
            }
            $stmt->close();
        }
    }

    // Check admin if not found as student or supervisor
    if (!$user_type) {
        $stmt = $conn->prepare("SELECT hod_id, email, phone FROM admin WHERE hod_id = ?");
        if ($stmt) {
            $stmt->bind_param("s", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result && $row = $result->fetch_assoc()) {
                $user_type = 'HOD ID';
                
            }
            $stmt->close();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dob = isset($_POST['date_of_birth']) ? trim($_POST['date_of_birth']) : '';
    $user_id = isset($_SESSION['reset_user']) ? $_SESSION['reset_user'] : '';
    $email = isset($_SESSION['reset_email']) ? $_SESSION['reset_email'] : '';
    
    

    // Check in student table
    $stmt = $conn->prepare("SELECT * FROM student WHERE (matric = ? OR email = ?) AND date_of_birth = ?");
    $stmt->bind_param("sss", $user_id, $email, $dob);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        unset($_SESSION['reset_attempts']);
        header("Location: verification_code.php?user_id=" . urlencode($user_id));
        exit();
    }
    $stmt->close();

    // Check in supervisors table
    $stmt = $conn->prepare("SELECT * FROM supervisors WHERE (sup_id = ? OR email = ?) AND date_of_birth = ?");
    $stmt->bind_param("sss", $user_id, $email, $dob);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        unset($_SESSION['reset_attempts']);
        header("Location: verification_code.php?user_id=" . urlencode($user_id));
        exit();
    }
    $stmt->close();

    // Check in admin table (for HOD)
    $stmt = $conn->prepare("SELECT * FROM admin WHERE (hod_id = ? OR email = ?) AND date_of_birth = ?");
    $stmt->bind_param("sss", $user_id, $email, $dob);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        unset($_SESSION['reset_attempts']);
        header("Location: verification_code.php?user_id=" . urlencode($user_id));
        exit();
    }
    $stmt->close();

    // If not matched, increment attempts
    if (!isset($_SESSION['reset_attempts'])) {
        $_SESSION['reset_attempts'] = 1;
    } else {
        $_SESSION['reset_attempts']++;
    }

    if ($_SESSION['reset_attempts'] >= 3) {
        // Insert suspended user info into suspended table
        $suspend_id = isset($_SESSION['reset_user']) ? $_SESSION['reset_user'] : '';
        $suspend_email = isset($_SESSION['reset_email']) ? $_SESSION['reset_email'] : '';
        $suspend_phone = isset($_SESSION['reset_phone']) ? $_SESSION['reset_phone'] : '';

        // Check which table the user belongs to using the user_id echoed on the page
        $matric = $sup_id = $hod_id = null;

        // Student table
        $stmt = $conn->prepare("SELECT matric FROM student WHERE matric = ?");
        if ($stmt) {
            $stmt->bind_param("s", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result && $row = $result->fetch_assoc()) {
                $matric = $row['matric'];
            }
            $stmt->close();
        }
        // Supervisors table
        if (!$matric) {
            $stmt = $conn->prepare("SELECT sup_id FROM supervisors WHERE sup_id = ?");
            if ($stmt) {
                $stmt->bind_param("s", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result && $row = $result->fetch_assoc()) {
                    $sup_id = $row['sup_id'];
                }
                $stmt->close();
            }
        }
        // Admin table
        if (!$matric && !$sup_id) {
            $stmt = $conn->prepare("SELECT hod_id FROM admin WHERE hod_id = ?");
            if ($stmt) {
                $stmt->bind_param("s", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result && $row = $result->fetch_assoc()) {
                    $hod_id = $row['hod_id'];
                }
                $stmt->close();
            }
        }

        // Write "suspended" in the reason column of the user's table
        if ($matric) {
            $stmt = $conn->prepare("UPDATE student SET reason = 'suspended' WHERE matric = ?");
            if ($stmt) {
                $stmt->bind_param("s", $matric);
                $stmt->execute();
                $stmt->close();
            }
        } elseif ($sup_id) {
            $stmt = $conn->prepare("UPDATE supervisors SET reason = 'suspended' WHERE sup_id = ?");
            if ($stmt) {
                $stmt->bind_param("s", $sup_id);
                $stmt->execute();
                $stmt->close();
            }
        } elseif ($hod_id) {
            $stmt = $conn->prepare("UPDATE admin SET reason = 'suspended' WHERE hod_id = ?");
            if ($stmt) {
                $stmt->bind_param("s", $hod_id);
                $stmt->execute();
                $stmt->close();
            }
        }

        // Use session email/phone if not found in DB
        $final_email = !empty($db_email) ? $db_email : $suspend_email;
        $final_phone = !empty($db_phone) ? $db_phone : $suspend_phone;

        // Insert into suspended table with all columns, any available info
        $sql = "INSERT INTO suspended (matric, sup_id, hod_id, email, phone, suspended_at) VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param(
                "sssss",
                $matric,
                $sup_id,
                $hod_id,
                $final_email,
                $final_phone
            );
            $stmt->execute();
            $stmt->close();
        }

        // Mark the user as suspended in session
        $_SESSION['suspended_user'] = $suspend_id;
        unset($_SESSION['reset_attempts']);
        unset($_SESSION['reset_user']);
        unset($_SESSION['reset_email']);
        unset($_SESSION['reset_phone']);

        // Redirect to display_password.php with user_id
        header("Location: display_password.php?user_id=" . urlencode($user_id));
        exit();
    } else {
        $remaining = 3 - $_SESSION['reset_attempts'];
        echo "<script>alert('Invalid date of birth. You have {$remaining} attempt(s) left.'); window.location.href='date_of_birth_validation.php';</script>";
        exit();
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
        .center-user-id {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 30vh;
            font-size: 1.5em;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="login-form">
    <form action="date_of_birth_validation.php" method="post">
        <?php if ($user_id): ?>
            <div class="center-user-id">
                <?php
                if ($user_type && $user_details) {
                    echo "<strong>$user_type:</strong> $user_id<br>$user_details";
                } else {
                    echo "USER ID: $user_id";
                }
                ?>
            </div>
        <?php endif; ?>
        <div class="content">
            <h2>AUTHENTICATION </h2>
            <div class="inputbox">
                <input type="date" name="date_of_birth" required>
                <i>DATE OF BIRTH</i>
            </div>
            <div class="inputbox">
                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
                <input type="submit" value="VALIDATE TO GET CODE">
            </div>
            <div class="links">
                <a href="forget_password.php">BACK</a>
            </div>
        </div>
    </form>
</div>
</body>
</html>
