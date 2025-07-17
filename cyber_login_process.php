<?php
session_start();
include 'config.php'; // Database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';

    if (!empty($user_id)) {
        // Check partnership_form for updo_staff_id and fetch details
        $stmt = $conn->prepare("SELECT updo_staff_id, VC_name, contact_email, contact_phone, institution FROM partnership_form WHERE updo_staff_id = ?");
        if (!$stmt) {
            $_SESSION['error'] = "Database error: " . $conn->error;
            header("Location: cyber_secure_login.php");
            exit();
        }
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $row = $result->fetch_assoc();
            $_SESSION['user_id'] = $row['updo_staff_id'];
            $_SESSION['user_type'] = 'staff';
            $_SESSION['VC_name'] = $row['VC_name'];
            $_SESSION['contact_email'] = $row['contact_email'];
            $_SESSION['contact_phone'] = $row['contact_phone'];
            $_SESSION['institution'] = $row['institution'];
            $_SESSION['success'] = "Login successful! Redirecting to dashboard...";
            header("Location: cyber_secure_login.php?redirect=cyber_secure_web.php");
            exit();
        }

        // Check cyber_security for cyber_id
        $stmt = $conn->prepare("SELECT cyber_id FROM cyber_security WHERE cyber_id = ?");
        if (!$stmt) {
            $_SESSION['error'] = "Database error: " . $conn->error;
            header("Location: cyber_secure_login.php");
            exit();
        }
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_type'] = 'cyber';
            $_SESSION['success'] = "Login successful! Redirecting to dashboard...";
            header("Location: cyber_secure_login.php?redirect=cyber_security_dashboard.php");
            exit();
        }
    }

    $_SESSION['error'] = "Invalid credentials. Please login again.";
    header("Location: cyber_secure_login.php");
    exit();
} else {
    header("Location: cyber_secure_login.php");
    exit();
}
