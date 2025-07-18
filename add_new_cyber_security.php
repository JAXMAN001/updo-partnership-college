<?php
session_start();
$message = '';

// --- Form Submission Handling ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Database Configuration ---
    $host = 'localhost';
    $db   = 'documents';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);

        // 1. Sanitize and retrieve form data
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $staff_id = trim($_POST['cyber_id'] ?? '');
        $profile_pic_name = '';

        // 2. Validate Inputs
        $errors = [];
        if (empty($name)) $errors[] = "Name is required.";
        if (empty($staff_id)) $errors[] = "Cyber ID is required. Please generate one.";
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "A valid email is required.";
        if (empty($phone)) $errors[] = "Phone number is required.";

        // 3. Check for unique email and phone in the new table
        if (empty($errors)) {
            $stmt = $pdo->prepare("SELECT id FROM cyber_security WHERE email = ? OR phone = ?");
            $stmt->execute([$email, $phone]);
            if ($stmt->fetch()) {
                $errors[] = "The provided email or phone number is already registered.";
            }
        }

        // 4. Handle File Upload
        if (empty($errors)) {
            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
                $upload_dir = '../uploads/cyber_pics/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                $file = $_FILES['profile_picture'];
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                $max_size = 5 * 1024 * 1024; // 5MB

                if (!in_array($file['type'], $allowed_types)) {
                    $errors[] = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
                } elseif ($file['size'] > $max_size) {
                    $errors[] = "File size exceeds the 5MB limit.";
                } else {
                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $profile_pic_name = 'cyber_' . uniqid() . '.' . $ext;
                    if (!move_uploaded_file($file['tmp_name'], $upload_dir . $profile_pic_name)) {
                        $errors[] = "Failed to upload profile picture.";
                    }
                }
            } else {
                $errors[] = "Profile picture is required.";
            }
        }

        // 5. If no errors, insert into database
        if (empty($errors)) {
            /*
             NOTE: You need to create the `cyber_security` table in your 'documents' database.
             Run this SQL command in phpMyAdmin:

             CREATE TABLE cyber_security (
                 id INT AUTO_INCREMENT PRIMARY KEY,
                 name VARCHAR(255) NOT NULL,
                 email VARCHAR(255) NOT NULL UNIQUE,
                 phone VARCHAR(50) NOT NULL UNIQUE,
                 staff_id VARCHAR(50) NOT NULL UNIQUE,
                 profile_picture VARCHAR(255) NOT NULL,
                 registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
             );
            */
            $sql = "INSERT INTO cyber_security (name, email, phone, cyber_id, profile_picture) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $email, $phone, $staff_id, $profile_pic_name]);
            $message = "<div class='success-message'>Cyber security staff registered successfully!</div>";
        } else {
            $message = "<div class='error-message'>" . implode("<br>", $errors) . "</div>";
        }

    } catch (PDOException $e) {
        $message = "<div class='error-message'>Database error. Please try again later.</div>";
        error_log("Cyber security registration error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Cyber Security Staff</title>
    <link rel="stylesheet" href="../css/cyber.css">
    <style>
        .success-message { padding: 15px; margin-bottom: 20px; border-radius: 5px; color: #fff; text-align: center; background-color: rgba(39, 174, 96, 0.7); border: 1px solid yellow; }
        .spinner { display: inline-block; width: 1em; height: 1em; border: 2px solid rgba(255,255,255,0.3); border-radius: 50%; border-top-color: #fff; animation: spin 1s ease-in-out infinite; -webkit-animation: spin 1s ease-in-out infinite; margin-right: 5px; vertical-align: middle; }
        @keyframes spin { to { transform: rotate(360deg); } }
        @-webkit-keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>
<div class="login-form">
    <form action="" method="post" enctype="multipart/form-data">
        <div class="cyber-container">
            <h2>Register Cyber Security Staff</h2>
            <?php if ($message) echo $message; ?>

            <div class="inputbox">
                <input type="text" name="name" required>
                <i>Full Name</i>
            </div>
            <div class="inputbox">
                <input type="email" name="email" required>
                <i>Email Address</i>
            </div>
            <div class="inputbox">
                <input type="number" name="phone" required>
                <i>Phone Number</i>
            </div>
            <div class="inputbox" style="display: flex; align-items: center; gap: 10px;">
                <input type="text" id="cyber_id" name="cyber_id" required readonly style="flex-grow: 1;">
                <i>Cyber ID</i>
                <button type="button" id="generateIdBtn" class="logout-btn" style="margin-top: 0; padding: 8px 12px; white-space: nowrap;">Generate ID</button>
            </div>
            <div class="inputbox">
                <label for="profile_picture" style="color: yellow; display: block; text-align: left; margin-bottom: 5px;">Profile Picture</label>
                <input type="file" id="profile_picture" name="profile_picture" accept="image/*" required style="color: yellow; width: 100%;">
            </div>

            <div class="inputbox">
                <input type="submit" value="REGISTER STAFF">
            </div>
            <a href="cyber_secure_login.php" class="logout-btn">Back to Login</a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const staffIdInput = document.getElementById('cyber_id');
    const generateBtn = document.getElementById('generateIdBtn');

    generateBtn.addEventListener('click', function() {
        this.disabled = true;
        this.innerHTML = '<span class="spinner"></span> Generating...';
        staffIdInput.value = 'Generating...';

        // Simulate a 3-second delay
        setTimeout(() => {
            // Generate a random 3 or 4 digit number
            const randomNumber = Math.floor(100 + Math.random() * 9900); // Generates a number between 100 and 9999
            const generatedId = 'CY-' + randomNumber;

            staffIdInput.value = generatedId;
            this.innerHTML = 'Generate ID';
            this.disabled = false;
        }, 3000);
    });
});
</script>
</body>
</html>