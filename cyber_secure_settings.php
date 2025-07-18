<?php
session_start();

// Security check: Ensure staff is logged in and updo_staff_id is set
if (
    !isset($_SESSION['user_id']) ||
    $_SESSION['user_type'] !== 'staff' ||
    empty($_SESSION['user_id'])
) {
    header("Location: cyber_secure_login.php");
    exit();
}

include 'config.php';

// Fetch only the partnership_form record for the logged in staff using updo_staff_id
$updo_staff_id = $_SESSION['user_id'];
$row = null;
$sql = "SELECT * FROM partnership_form WHERE updo_staff_id = ?";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("s", $updo_staff_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cyber Secure Settings</title>
    <link rel="stylesheet" href="../css/cyber.css">
    <link rel="stylesheet" href="../css/cyber_side.css">
    <style>
        .cyber-container {
            margin-left: 220px;
            transition: margin-top 0.7s;
            padding: 30px 0;
            border: 1px solid #eee;
        }
        .info-table {
            width: 95%;
            margin: 30px auto;
            border-radius: 10px;
            box-shadow: 0 4px 16px rgba(44,62,80,0.09);
            padding: 18px 24px;
        }
        .info-row {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 18px;
            width: 95%;
            margin: 30px auto;

        }

        .info-col {
            flex: 1 1 48%;
            margin-right: 2%;
            margin-bottom: 8px;
            border-radius: 6px;
            padding: 12px 18px;
            font-size: 1.08em;
            color: white;
            box-sizing: border-box;
        }
        .info-col:last-child {
            margin-right: 10;
        }
        .edit-btn {
            margin-top: 100px;
            padding: 12px 38px;
            background: blue;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1.15em;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
        }
        .edit-btn:hover {
            background: yellow;
            color: black;
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
        <div class="cyber-container">
        <div class="info-col">
                        
                        <?php if (!empty($row['profile_picture'])): ?>
                            <img src="../uploads/partnership_pics/<?php echo htmlspecialchars($row['profile_picture']); ?>" alt="Profile" style="max-width:80px; max-height:80px; border-radius:8px;">
                        <?php else: ?>
                            <span style="color:#7f8c8d;">Not uploaded</span>
                        <?php endif; ?>
                    </div>
        <h1>SCHOOL MANAGEMENT INFORMATION</h1>
        <?php if (!$row): ?>
            <div style="color:#e74c3c; font-weight:bold; margin:30px 0;">
                No partnership record found for your account.<br>
                <small>Staff ID: <?php echo htmlspecialchars($updo_staff_id); ?></small>
            </div>
        <?php else: ?>
            <div class="info-table">
                <div class="info-row">
                    <div class="info-col"><strong>VC's Name:</strong> <?php echo htmlspecialchars($row['vc_name']); ?></div>
                    <div class="info-col"><strong>Matric Format:</strong> <?php echo htmlspecialchars($row['matric_format']); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-col"><strong>School Location:</strong> <?php echo htmlspecialchars($row['school_location']); ?></div>
                    <div class="info-col"><strong>Contact Email:</strong> <?php echo htmlspecialchars($row['contact_email']); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-col"><strong>Contact Phone:</strong> <?php echo htmlspecialchars($row['contact_phone']); ?></div>
                    <div class="info-col"><strong>Account Name:</strong> <?php echo htmlspecialchars($row['account_name']); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-col"><strong>Account Number:</strong> <?php echo htmlspecialchars($row['account_number']); ?></div>
                    <div class="info-col"><strong>Bank Name:</strong> <?php echo htmlspecialchars($row['bank_name']); ?></div>
                </div>
                
                <form id="editInfoFormTrigger" action="javascript:void(0);" method="get">
                    <button type="button" class="edit-btn" id="editInfoBtn">Edit Information</button>
                </form>
            </div>
            <!-- Modal for editing info -->
            <div id="editInfoModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.35); z-index:9999; display:none; align-items:center; justify-content:center;">
                <div style="background:#fff; padding:38px 32px 28px 32px; border-radius:12px; min-width:320px; max-width:95vw; box-shadow:0 8px 32px rgba(0,0,0,0.18); position:relative; margin:auto;">
                    <button id="closeEditModal" style="position:absolute; top:0px; right:16px; background:none; border:none; font-size:1.5em; color:#e74c3c; cursor:pointer;">&times;</button>
                    <h2 style="margin-bottom:18px;">Edit Partnership Information</h2>
                    <form id="editInfoForm" method="post" enctype="multipart/form-data">
                        <div style="display:flex; gap:18px; flex-wrap:wrap;">
                            <div style="flex:1;">
                                <label>VC's Name</label>
                                <input type="text" name="vc_name" value="<?php echo htmlspecialchars($row['vc_name']); ?>" required style="width:100%;padding:8px;">
                            </div>
                            <div style="flex:1;">
                                <label>Matric Format</label>
                                <input type="text" name="matric_format" value="<?php echo htmlspecialchars($row['matric_format']); ?>" required style="width:100%;padding:8px;">
                            </div>
                        </div>
                        <div style="display:flex; gap:18px; flex-wrap:wrap; margin-top:12px;">
                            <div style="flex:1;">
                                <label>School Location</label>
                                <input type="text" name="school_location" value="<?php echo htmlspecialchars($row['school_location']); ?>" required style="width:100%;padding:8px;">
                            </div>
                            <div style="flex:1;">
                                <label>Contact Email</label>
                                <input type="email" name="contact_email" value="<?php echo htmlspecialchars($row['contact_email']); ?>" required style="width:100%;padding:8px;">
                            </div>
                        </div>
                        <div style="display:flex; gap:18px; flex-wrap:wrap; margin-top:12px;">
                            <div style="flex:1;">
                                <label>Contact Phone</label>
                                <input type="text" name="contact_phone" value="<?php echo htmlspecialchars($row['contact_phone']); ?>" required style="width:100%;padding:8px;">
                            </div>
                            <div style="flex:1;">
                                <label>Account Name</label>
                                <input type="text" name="account_name" value="<?php echo htmlspecialchars($row['account_name']); ?>" required style="width:100%;padding:8px;">
                            </div>
                        </div>
                        <div style="display:flex; gap:18px; flex-wrap:wrap; margin-top:12px;">
                            <div style="flex:1;">
                                <label>Account Number</label>
                                <input type="text" name="account_number" value="<?php echo htmlspecialchars($row['account_number']); ?>" required style="width:100%;padding:8px;">
                            </div>
                            <div style="flex:1;">
                                <label>Bank Name</label>
                                <input type="text" name="bank_name" value="<?php echo htmlspecialchars($row['bank_name']); ?>" required style="width:100%;padding:8px;">
                            </div>
                        </div>
                        <div style="margin-top:12px;">
                            <label>Profile Picture</label>
                            <input type="file" name="profile_picture" accept="image/*">
                            <?php if (!empty($row['profile_picture'])): ?>
                                <div style="margin-top:8px;">
                                    <img src="../uploads/partnership_pics/<?php echo htmlspecialchars($row['profile_picture']); ?>" alt="Profile" style="max-width:80px; max-height:80px; border-radius:8px;">
                                </div>
                            <?php endif; ?>
                        </div>
                        <div style="margin-top:22px; text-align:center;">
                            <button type="submit" class="edit-btn" style="margin-top:0;">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
            <script>
                // Show modal only when button is clicked
                document.getElementById('editInfoBtn').onclick = function() {
                    var modal = document.getElementById('editInfoModal');
                    modal.style.display = 'flex';
                    modal.style.alignItems = 'center';
                    modal.style.justifyContent = 'center';
                };
                document.getElementById('closeEditModal').onclick = function() {
                    document.getElementById('editInfoModal').style.display = 'none';
                };
                // Handle form submit via AJAX
                document.getElementById('editInfoForm').onsubmit = function(e) {
                    e.preventDefault();
                    var formData = new FormData(this);
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', '', true);
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            alert('Information updated successfully!');
                            window.location.reload();
                        } else {
                            alert('Failed to update information.');
                        }
                    };
                    xhr.send(formData);
                };
            </script>
            <?php
            // Handle POST for update
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Sanitize and update fields
                $vc_name = trim($_POST['vc_name'] ?? '');
                $matric_format = trim($_POST['matric_format'] ?? '');
                $school_location = trim($_POST['school_location'] ?? '');
                $contact_email = trim($_POST['contact_email'] ?? '');
                $contact_phone = trim($_POST['contact_phone'] ?? '');
                $account_name = trim($_POST['account_name'] ?? '');
                $account_number = trim($_POST['account_number'] ?? '');
                $bank_name = trim($_POST['bank_name'] ?? '');
                $profile_pic_name = $row['profile_picture'];

                // Handle profile picture upload if provided
                if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
                    $upload_dir = '../uploads/partnership_pics/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    $file = $_FILES['profile_picture'];
                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $profile_pic_name = 'partner_' . uniqid() . '.' . $ext;
                    $filepath = $upload_dir . $profile_pic_name;
                    move_uploaded_file($file['tmp_name'], $filepath);
                }

                // Update the partnership_form table for this updo_staff_id
                $update_sql = "UPDATE partnership_form SET vc_name=?, matric_format=?, school_location=?, contact_email=?, contact_phone=?, account_name=?, account_number=?, bank_name=?, profile_picture=? WHERE updo_staff_id=?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param(
                    "ssssssssss",
                    $vc_name, $matric_format, $school_location, $contact_email, $contact_phone,
                    $account_name, $account_number, $bank_name, $profile_pic_name, $updo_staff_id
                );
                $update_stmt->execute();
                $update_stmt->close();
                // Redirect to settings page after successful save
                header("Location: cyber_secure_settings.php");
                exit;
            }
            ?>
        <?php endif; ?>
    </div>
</body>
</html>
