<?php
session_start();

if (!isset($_SESSION['hod_id'])) {
    header("Location:student_login.php");
    exit();
}

include 'config.php';
$admin_username = $_SESSION['hod_id'];
$admin_sql = "SELECT reason FROM admin WHERE hod_id = ?";
$stmt = $conn->prepare($admin_sql);
$is_suspended = false;
if ($stmt) {
    $stmt->bind_param("s", $admin_username);
    $stmt->execute();
    $stmt->bind_result($admin_reason);
    $stmt->fetch();
    $is_suspended = (strtolower(trim($admin_reason)) === 'suspended');
    $stmt->close();
}
if (!$is_suspended) {
    header("Location: admin_dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Suspended</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-image: url(white.png);
            background-size: cover;
            background-attachment: fixed;
            background-repeat: no-repeat;
            background-position: center center;
            color: #333;
            text-align: center;
            padding: 60px 20px;
            line-height: 1.6;
        }
        .container {
            max-width: 500px;
            margin: 0 auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 16px rgba(44,62,80,0.09);
            padding: 40px;
            border: 5px solid #e74c3c;
            animation: blink-border 3s linear infinite;
        }
        @keyframes blink-border {
            0% { border-color: green; }
            50% { border-color: #fff; }
            100% { border-color: #e74c3c; }
        }
        h2 { color: #e74c3c; margin-bottom: 18px; }
        .logout-btn { margin-top: 30px; padding: 12px 40px; background: #e74c3c; color: #fff; border: none; border-radius: 6px; font-size: 1.2em; font-weight: bold; cursor: pointer; }
        .logout-btn:hover { background: #c0392b; }
        .logo { max-width: 150px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <img src="updo.png" alt="Company Logo" class="logo">
        <h2>🚧 Mr. HOD your account is suspended 🚧</h2>
        <p>Your account has been suspended due to too many incorrect login attempts.<br>
        Please contact the system administrator to resolve your suspension.</p>
        <?php
        // Check if receipt has already been uploaded for this HOD
        $receipt_uploaded = false;
        $receipt_filename = '';
        $receipt_upload_time = null;
        $receipt_sql = "SELECT online_payment, online_payment FROM admin WHERE hod_id = ?";
        $stmt = $conn->prepare($receipt_sql);
        if ($stmt) {
            $stmt->bind_param("s", $_SESSION['hod_id']);
            $stmt->execute();
            $stmt->bind_result($receipt_filename, $receipt_upload_time);
            $stmt->fetch();
            $stmt->close();
            if (!empty($receipt_filename)) {
                $receipt_uploaded = true;
            }
        } else {
            // Handle DB error gracefully
            echo '<div style="color:#e74c3c;">Database error: ' . htmlspecialchars($conn->error) . '</div>';
        }
        // Set upload time if just uploaded
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['receipt']) && !$receipt_uploaded) {
            $receipt_upload_time = time();
        }
        // Calculate if 24hrs has passed
        $show_request_btn = true;
        $countdown_active = false;
        $expire_time = null;
        if ($receipt_uploaded && $receipt_upload_time) {
            $expire_time = (int)$receipt_upload_time + 24 * 60 * 60;
            if (time() < $expire_time) {
                $show_request_btn = false;
                $countdown_active = true;
            } else {
                $show_request_btn = true;
                $countdown_active = false;
            }
        }
        ?>
        <?php if ($show_request_btn): ?>
        <form id="requestForm" action="#" method="post" style="margin-top:18px;">
            <button type="button" class="logout-btn" style="background:#27ae60;" id="requestBtn">Send Request to School Management</button>
        </form>
        <?php endif; ?>

        <form action="student_login.php" method="post">
            <button type="submit" class="logout-btn">LOG OUT</button>
        </form>
    </div>
    <div id="accountDetailsModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.35); z-index:9999; align-items:center; justify-content:center;">
        <div style="background:#fff; padding:38px 32px 28px 32px; border-radius:12px; min-width:320px; max-width:95vw; box-shadow:0 8px 32px rgba(0,0,0,0.18); position:relative;">
            <button id="closeAccountDetailsModal" style="position:absolute; top:10px; right:16px; background:none; border:none; font-size:1.5em; color:#e74c3c; cursor:pointer;">&times;</button>
            <h2 style="margin-bottom:18px;">School Management Account Details</h2>
            <div id="accountDetailsContent" style="font-size:1.1em;"></div>
            <?php if (!$receipt_uploaded || $countdown_active): ?>
            <form id="uploadReceiptForm" method="post" enctype="multipart/form-data" style="margin-top:28px;">
                <label style="font-weight:bold;">Upload Payment Receipt:</label><br>
                <input type="file" name="receipt" accept="image/*,.pdf" required style="margin:12px 0;">
                <button type="submit" class="logout-btn" style="background:#2980b9; margin-top:8px;">Upload Receipt</button>
            </form>
            <?php endif; ?>
            <?php
            // Handle receipt upload and save to admin table
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['receipt']) && !$receipt_uploaded) {
                $upload_dir = __DIR__ . '/../uploads/receipts/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                $file = $_FILES['receipt'];
                $allowed_types = ['image/png', 'image/jpeg', 'image/jpg', 'application/pdf'];
                $max_size = 20 * 1024 * 1024; // 20MB

                if ($file['error'] !== UPLOAD_ERR_OK) {
                    echo '<div style="color:#e74c3c; margin-top:10px;">File upload error.</div>';
                } elseif (!in_array($file['type'], $allowed_types)) {
                    echo '<div style="color:#e74c3c; margin-top:10px;">Invalid file type. Only PNG, JPG, JPEG, and PDF allowed.</div>';
                } elseif ($file['size'] > $max_size) {
                    echo '<div style="color:#e74c3c; margin-top:10px;">File size exceeds 20MB.</div>';
                } else {
                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $filename = 'receipt_hod_' . $_SESSION['hod_id'] . '_' . uniqid() . '.' . $ext;
                    $filepath = $upload_dir . $filename;
                    if (move_uploaded_file($file['tmp_name'], $filepath)) {
                        // Save filename and upload time to admin table under online_payment and online_payment_uploaded_at
                        $now = time();
                        $stmt = $conn->prepare("UPDATE admin SET online_payment = ?, online_payment = ? WHERE hod_id = ?");
                        $stmt->bind_param("sis", $filename, $now, $_SESSION['hod_id']);
                        if ($stmt->execute()) {
                            $receipt_uploaded = true;
                            $receipt_filename = $filename;
                            $receipt_upload_time = $now;
                            header("Location: " . $_SERVER['REQUEST_URI']);
                            exit();
                        } else {
                            echo '<div style="color:#e74c3c; margin-top:10px;">Failed to save receipt to database.</div>';
                        }
                        $stmt->close();
                    } else {
                        echo '<div style="color:#e74c3c; margin-top:10px;">Failed to save uploaded file.</div>';
                    }
                }
            }
            // Show persistent success message and countdown if available
            if ($receipt_uploaded && !empty($receipt_filename)) {
                echo '<div style="color:green; margin-top:10px;">Receipt uploaded successfully!<br>File stored at: <span style="color:#2980b9;">uploads/receipts/' . htmlspecialchars($receipt_filename) . '</span></div>';
                // Countdown timer for 24 hours
                if ($countdown_active && $expire_time) {
                    echo '<div id="countdown24hr" style="margin-top:14px; font-weight:bold; color:#e67e22;"></div>';
                    echo "<script>
                        function updateCountdown24hr() {
                            var expire = $expire_time * 1000;
                            var now = Date.now();
                            var diff = expire - now;
                            if (diff <= 0) {
                                document.getElementById('countdown24hr').textContent = '24 hours has expired.';
                                // Show the send request button again
                                var reqForm = document.getElementById('requestForm');
                                if (reqForm) reqForm.style.display = 'block';
                            } else {
                                var hours = Math.floor(diff / (1000 * 60 * 60));
                                var minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                                var seconds = Math.floor((diff % (1000 * 60)) / 1000);
                                document.getElementById('countdown24hr').textContent =
                                    'Time left: ' + hours.toString().padStart(2, '0') + ':' +
                                    minutes.toString().padStart(2, '0') + ':' +
                                    seconds.toString().padStart(2, '0');
                                // Hide the send request button while countdown is active
                                var reqForm = document.getElementById('requestForm');
                                if (reqForm) reqForm.style.display = 'none';
                            }
                        }
                        setInterval(updateCountdown24hr, 1000);
                        updateCountdown24hr();
                    </script>";
                }
            }
            ?>
        </div>
    </div>
    <script>
        <?php if ($show_request_btn): ?>
        document.getElementById('requestBtn').onclick = function(e) {
            e.preventDefault();
            var modal = document.getElementById('accountDetailsModal');
            var content = document.getElementById('accountDetailsContent');
            content.innerHTML = '<div style="color:#2980b9;">Loading...</div>';
            modal.style.display = 'flex';
            modal.style.alignItems = 'center';
            modal.style.justifyContent = 'center';

            // AJAX request to fetch staff account details
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'fetch_staff_account_details.php', true);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    content.innerHTML = xhr.responseText;
                } else {
                    content.innerHTML = '<div style="color:#e74c3c;">Failed to fetch account details.</div>';
                }
            };
            xhr.send('admin_hod_id=<?php echo urlencode($_SESSION['hod_id']); ?>');
        };
        <?php endif; ?>
        document.getElementById('closeAccountDetailsModal').onclick = function() {
            document.getElementById('accountDetailsModal').style.display = 'none';
        };
    </script>
</body>
</html>
