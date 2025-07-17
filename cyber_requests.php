<?php
session_start();

// Only allow access for logged-in updo_staff_id users
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'staff') {
    header("Location: cyber_secure_login.php");
    exit();
}

require_once 'config.php';

// Get the institution of the current updo_staff_id from partnership_form
$institution = '';
$stmt = $conn->prepare("SELECT institution FROM partnership_form WHERE updo_staff_id = ?");
$stmt->bind_param("s", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($institution);
$stmt->fetch();
$stmt->close();

// Fetch only suspended HODs from admin table with the same institution, include online_payment
$sql = "SELECT hod_id, institution, department, faculty, reason, online_payment FROM admin WHERE LOWER(reason) = 'suspended' AND institution = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $institution);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Suspended HOD Requests</title>
    <link rel="stylesheet" href="../css/cyber.css">
    <link rel="stylesheet" href="../css/cyber_side.css">
    <style>
        .requests-container {
            margin-right: 200px;
            margin-bottom: 600px;
            padding: 40px;
            max-width: 900px;
        }
        .request-box {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(44,62,80,0.09);
            padding: 28px 24px;
            margin-bottom: 28px;
            border-left: 6px solid #e74c3c;
        }
        .request-box h3 {
            color: #e74c3c;
            margin-bottom: 10px;
        }
        .request-details {
            font-size: 1.08em;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        .no-requests {
            color: #e67e22;
            font-weight: bold;
            margin: 40px 0;
        }
        /* Modal styles for receipt */
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0; top: 0;
            width: 100vw; height: 100vh;
            background: rgba(0,0,0,0.7);
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            background: #222;
            color: wheat;
            padding: 18px 24px;
            border-radius: 10px;
            max-width: 90vw;
            max-height: 90vh;
            text-align: center;
            position: relative;
        }
        .modal-content img, .modal-content embed {
            max-width: 600px;
            max-height: 80vh;
            border-radius: 10px;
            margin-bottom: 10px;
            background: #fff;
        }
        .close-modal {
            position: absolute;
            top: 8px;
            right: 18px;
            font-size: 2em;
            color: #fff;
            background: none;
            border: none;
            cursor: pointer;
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
            <li><a href="cyber_requests.php">REQUESTS</a></li>
            <li><a href="cyber_secure_logout.php">Logout</a></li>
        </ul>
    </div>
    <div class="requests-container" style="margin-top:10px;">
        <h1 style="color:white;">Suspended HOD Requests</h1>
        <?php if ($result && $result->num_rows > 0): ?>
            <table style="width:150%; border-collapse:collapse; background:#111; border-radius:10px; box-shadow:0 4px 16px rgba(44,62,80,0.09); color:white;">
                <thead>
                    <tr style="background:#222; color:white;">
                        <th style="padding:12px; border-bottom:1px solid #444;">HOD ID</th>
                        <th style="padding:12px; border-bottom:1px solid #444;">Institution</th>
                        <th style="padding:12px; border-bottom:1px solid #444;">Department</th>
                        <th style="padding:12px; border-bottom:1px solid #444;">Faculty</th>
                        <th style="padding:12px; border-bottom:1px solid #444;">Status</th>
                        <th style="padding:12px; border-bottom:1px solid #444;">Receipt</th>
                        <th style="padding:12px; border-bottom:1px solid #444;">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td style="padding:10px;"><?php echo htmlspecialchars($row['hod_id']); ?></td>
                        <td style="padding:10px;"><?php echo htmlspecialchars($row['institution']); ?></td>
                        <td style="padding:10px;"><?php echo htmlspecialchars($row['department']); ?></td>
                        <td style="padding:10px;"><?php echo htmlspecialchars($row['faculty']); ?></td>
                        <td style="padding:10px; color:#e74c3c; font-weight:bold;"><?php echo htmlspecialchars($row['reason']); ?></td>
                        <td style="padding:10px;">
                            <?php if (!empty($row['online_payment'])): 
                                $file = '../uploads/receipts/' . htmlspecialchars($row['online_payment']);
                                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                            ?>
                                <a href="#" class="view-receipt-link" data-file="<?php echo $file; ?>" data-ext="<?php echo $ext; ?>" style="color:#fff; background:#27ae60; padding:6px 18px; border-radius:6px; text-decoration:none; font-weight:bold; box-shadow:0 2px 8px rgba(39,174,96,0.15); display:inline-block;">View Receipt</a>
                            <?php else: ?>
                                <span style="color:#e67e22;">No Receipt</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding:10px;">
                            <form method="post" class="remove-suspend-form" style="display:inline;">
                                <input type="hidden" name="remove_hod_id" value="<?php echo htmlspecialchars($row['hod_id']); ?>">
                                <button type="button" class="logout-btn remove-suspend-btn" style="background:#e74c3c; color:#fff; border:none; border-radius:6px; padding:6px 18px; font-weight:bold; cursor:pointer;">Remove from suspension</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-requests" style="color:white;">No suspended HOD requests found for your institution.</div>
        <?php endif; ?>
    </div>
    <!-- Modal for viewing receipt -->
    <div class="modal" id="receiptModal">
        <div class="modal-content">
            <button class="close-modal" id="closeModalBtn">&times;</button>
            <div id="modalReceiptContent"></div>
        </div>
    </div>
    <!-- Modal for remove confirmation -->
    <div class="modal" id="removeModal">
        <div class="modal-content">
            <button class="close-modal" id="closeRemoveModalBtn">&times;</button>
            <div id="removeModalContent" style="font-size:1.1em;"></div>
        </div>
    </div>
    <script>
    // Modal logic for viewing receipt
    document.querySelectorAll('.view-receipt-link').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            var file = this.getAttribute('data-file');
            var ext = this.getAttribute('data-ext');
            var content = '';
            if (ext === 'pdf') {
                content = '<embed src="' + file + '" type="application/pdf" width="100%" height="500px" />';
            } else {
                content = '<img src="' + file + '" alt="Receipt" />';
            }
            document.getElementById('modalReceiptContent').innerHTML = content;
            document.getElementById('receiptModal').style.display = 'flex';
        });
    });
    document.getElementById('closeModalBtn').onclick = function() {
        document.getElementById('receiptModal').style.display = 'none';
        document.getElementById('modalReceiptContent').innerHTML = '';
    };
    document.getElementById('receiptModal').onclick = function(e) {
        if (e.target === this) {
            this.style.display = 'none';
            document.getElementById('modalReceiptContent').innerHTML = '';
        }
    };

    // Modal logic for remove from suspension
    document.querySelectorAll('.remove-suspend-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var form = this.closest('form');
            var hodId = form.querySelector('input[name="remove_hod_id"]').value;
            var modal = document.getElementById('removeModal');
            var content = document.getElementById('removeModalContent');
            content.innerHTML = `
                <div style="margin-bottom:18px;">Did the user pay <strong>₦1,000</strong>?</div>
                <div style="display:flex; gap:18px; justify-content:center;">
                    <button id="confirmRemoveBtn" style="background:#27ae60; color:#fff; border:none; border-radius:6px; padding:8px 22px; font-weight:bold; cursor:pointer;">Yes</button>
                    <button id="cancelRemoveBtn" style="background:#e74c3c; color:#fff; border:none; border-radius:6px; padding:8px 22px; font-weight:bold; cursor:pointer;">No</button>
                </div>
            `;
            modal.style.display = 'flex';
            modal.style.alignItems = 'center';
            modal.style.justifyContent = 'center';

            document.getElementById('confirmRemoveBtn').onclick = function() {
                // AJAX to remove suspension
                var xhr = new XMLHttpRequest();
                xhr.open('POST', '', true);
                xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (xhr.status === 200 && xhr.responseText.trim() === 'success') {
                        content.innerHTML = '<div style="font-size:2.5em; color:#27ae60; margin-bottom:10px;">&#10004;</div><div style="font-size:1.2em; color:#27ae60;">Successfully removed from suspension!</div>';
                        setTimeout(function() {
                            window.location.reload(); // Reload the page to update recent activity
                        }, 2000);
                    } else {
                        content.innerHTML = '<div style="color:#e74c3c;">Failed to remove suspension.</div>';
                    }
                };
                xhr.send('remove_hod_id=' + encodeURIComponent(hodId) + '&confirm_remove=1');
            };
            document.getElementById('cancelRemoveBtn').onclick = function() {
                modal.style.display = 'none';
                // Redirect back to the page
                window.location.href = 'cyber_requests.php';
            };
        });
    });
    document.getElementById('closeRemoveModalBtn').onclick = function() {
        document.getElementById('removeModal').style.display = 'none';
    };
    document.getElementById('removeModal').onclick = function(e) {
        if (e.target === this) {
            this.style.display = 'none';
        }
    };
    </script>
<?php
// Handle Remove Admin action (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_hod_id']) && isset($_POST['confirm_remove'])) {
    $remove_hod_id = $_POST['remove_hod_id'];
    // Log the activity before updating
    $log_stmt = $conn->prepare("INSERT INTO admin_activity_log (hod_id, action, performed_by, activity_time) VALUES (?, 'Removed from suspension', ?, NOW())");
    if ($log_stmt) {
        $log_stmt->bind_param("ss", $remove_hod_id, $_SESSION['user_id']);
        $log_stmt->execute();
        $log_stmt->close();
    }
    $update_stmt = $conn->prepare("UPDATE admin SET reason = '' WHERE hod_id = ?");
    if ($update_stmt) {
        $update_stmt->bind_param("s", $remove_hod_id);
        if ($update_stmt->execute()) {
            echo "success";
        } else {
            echo "fail";
        }
        $update_stmt->close();
    } else {
        echo "fail";
    }
    exit;
}
?>
</body>
</html>
                xhr.send('remove_hod_id=' + encodeURIComponent(hodId) + '&confirm_remove=1');
            };
            document.getElementById('cancelRemoveBtn').onclick = function() {
                modal.style.display = 'none';
                // Redirect back to the page
                window.location.href = 'cyber_requests.php';
            };
        });
    });
    document.getElementById('closeRemoveModalBtn').onclick = function() {
        document.getElementById('removeModal').style.display = 'none';
    };
    document.getElementById('removeModal').onclick = function(e) {
        if (e.target === this) {
            this.style.display = 'none';
        }
    };
    </script>
<?php
// Handle Remove Admin action (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_hod_id']) && isset($_POST['confirm_remove'])) {
    $remove_hod_id = $_POST['remove_hod_id'];
    // Log the activity before updating
    $log_stmt = $conn->prepare("INSERT INTO admin_activity_log (hod_id, action, performed_by, activity_time) VALUES (?, 'Removed from suspension', ?, NOW())");
    if ($log_stmt) {
        $log_stmt->bind_param("ss", $remove_hod_id, $_SESSION['user_id']);
        $log_stmt->execute();
        $log_stmt->close();
    }
    $update_stmt = $conn->prepare("UPDATE admin SET reason = '' WHERE hod_id = ?");
    if ($update_stmt) {
        $update_stmt->bind_param("s", $remove_hod_id);
        if ($update_stmt->execute()) {
            echo "success";
        } else {
            echo "fail";
        }
        $update_stmt->close();
    } else {
        echo "fail";
    }
    exit;
}
?>
</body>
</html>
</html>
