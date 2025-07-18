<?php
include 'config.php';
session_start();

// Check session for admin login
if (!isset($_SESSION['hod_id'])) {
    header("Location: student_login.php");
    exit();
}

// Get admin institution, department, faculty for filtering
$admin_institution = $admin_department = $admin_faculty = '';
if (isset($_SESSION['hod_id'])) {
    $admin_username = $_SESSION['hod_id'];
    $stmt = $conn->prepare("SELECT institution, department, faculty FROM admin WHERE hod_id = ?");
    if ($stmt) {
        $stmt->bind_param("s", $admin_username);
        $stmt->execute();
        $stmt->bind_result($admin_institution, $admin_department, $admin_faculty);
        $stmt->fetch();
        $stmt->close();
    }
}

// Fetch only students where reason='suspended' and institution/department/faculty match admin
$students = [];
$stmt = $conn->prepare("SELECT fullname, matric, email, phone_number, reason, online_payment, institution, department, faculty FROM student WHERE LOWER(reason) = 'suspended' AND LOWER(TRIM(institution)) = ? AND LOWER(TRIM(department)) = ? AND LOWER(TRIM(faculty)) = ?");
if ($stmt) {
    $inst = strtolower(trim($admin_institution));
    $dept = strtolower(trim($admin_department));
    $fac = strtolower(trim($admin_faculty));
    $stmt->bind_param("sss", $inst, $dept, $fac);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    $stmt->close();
}

// Fetch only supervisors where reason='suspended' and institution/department/faculty match admin
$supervisors = [];
$stmt = $conn->prepare("SELECT fullname,sup_id, email, phone, reason, online_payment, institution, department, faculty FROM supervisors WHERE LOWER(reason) = 'suspended' AND LOWER(TRIM(institution)) = ? AND LOWER(TRIM(department)) = ? AND LOWER(TRIM(faculty)) = ?");
if ($stmt) {
    $stmt->bind_param("sss", $inst, $dept, $fac);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $supervisors[] = $row;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Suspended Users Log</title>
    <link rel="stylesheet" href="../css/Admin_dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Table styling similar to admin_view_student */
        .main-table {
            width: 95%;
            margin: 40px auto 30px auto;
            
            background: wheat;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(44,62,80,0.09);
        }
        .main-table th, .main-table td {
            padding: 13px 18px;
            border-bottom: 6px solid #eaeaea;
            text-align: left;
        }
        .main-table th {
            background: blue;
            color: #fff;
            font-size: 1.07em;
            font-weight: 600;
            text-transform: uppercase;
        }
        .main-table tr:nth-child(even) { background: #f7f7f7; }
        .main-table tr:last-child td { border-bottom: none; }
        h2 {
            margin-top: 38px;
            color: #2c3e50;
            text-align: center;
            font-size: 1.7em;
            letter-spacing: 1px;
        }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            
            margin: 0;
            padding: 0;
        }
        .highlight-row {
            background: #d4edda !important;
            color: #155724 !important;
            font-weight: bold;
        }
        .modal-message {
            position: fixed;
            top: 30px;
            left: 50%;
            transform: translateX(-50%);
            background: #e74c3c;
            color: #fff;
            padding: 18px 38px;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: 600;
            z-index: 9999;
            box-shadow: 0 4px 18px rgba(44,62,80,0.13);
            display: none;
        }
        .payment-method-option.blurred {
            filter: blur(2px);
            opacity: 0.6;
            box-shadow: none;
        }
        .payment-method-option.selected {
            filter: none !important;
            opacity: 1 !important;
            background: #27ae60 !important;
            color: #fff !important;
            box-shadow: 0 2px 12px rgba(39,174,96,0.13);
        }
    </style>
</head>
<body>
    <div id="modalMsg" class="modal-message"></div>
    <div style="width:95%; margin: 30px auto 0 auto; display:flex; align-items:center;">
        <a href="admin_dashboard.php" style="text-decoration:none; background:none; border:none; color:inherit; box-shadow:none; font-size:1.5em; margin-right:18px;">
            <i class="fas fa-arrow-left"></i>
        </a>
        <span style="font-size:1.15em; font-weight:600;">Back to Dashboard</span>
    </div>
    <form method="get" id="refreshForm">
        <button type="submit" style="margin-left:95%; margin-top:10px; margin-bottom:10px; padding:8px 22px; border-radius:5px; border:none; background:#2c3e50; color:#fff; font-weight:600; font-size:1em; cursor:pointer;">
            REFRESH
        </button>
    </form>
    <div style="display: flex; align-items: center; justify-content: space-between; width: 95%; margin: 38px auto 0 auto;">
        <h2 style="margin-top: 0;">SUSPENDED STUDENTS</h2>
        <form method="get" style="margin: 0;">
            <input type="text" name="student_search" placeholder="Search by Matric..." value="<?php echo isset($_GET['student_search']) ? htmlspecialchars($_GET['student_search']) : ''; ?>" style="padding: 8px 14px; border-radius: 5px; border: 1px solid #bbb; font-size: 1em;">
            <button type="submit" style="padding: 8px 18px; border-radius: 5px; border: none; background: #2c3e50; color: #fff; font-weight: 600; margin-left: 6px;">Search</button>
        </form>
    </div>
    <table class="main-table">
        <tr>
            <th>Full Name</th>
            <th>Matric</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Institution</th>
            <th>Department</th>
            <th>Faculty</th>
            <th>Reason</th>
            <th style="text-align:center;">Action</th>
            <th style="text-align:center;">Online Payment</th>
        </tr>
        <?php
        // Student search filter (matric only)
        $student_search = isset($_GET['student_search']) ? strtolower(trim($_GET['student_search'])) : '';
        $filtered_students = [];
        $student_found = false;
        if ($student_search !== '') {
            foreach ($students as $stu) {
                if (strpos(strtolower($stu['matric']), $student_search) !== false) {
                    $filtered_students[] = $stu;
                    $student_found = true;
                }
            }
        } else {
            $filtered_students = $students;
        }
        ?>
        <?php if ($student_search !== '' && !$student_found): ?>
            <script>
                window.addEventListener('DOMContentLoaded', function() {
                    var modal = document.getElementById('modalMsg');
                    modal.textContent = "Student is not available in the record.";
                    modal.style.display = "block";
                    setTimeout(function() { modal.style.display = "none"; }, 2000);
                });
            </script>
        <?php endif; ?>
        <?php if (count($filtered_students) > 0): ?>
            <?php foreach ($filtered_students as $stu): ?>
                <tr class="<?php echo ($student_search !== '' && strpos(strtolower($stu['matric']), $student_search) !== false) ? 'highlight-row' : ''; ?>">
                    <td><?php echo htmlspecialchars($stu['fullname']); ?></td>
                    <td><?php echo htmlspecialchars($stu['matric']); ?></td>
                    <td><?php echo htmlspecialchars($stu['email']); ?></td>
                    <td><?php echo htmlspecialchars($stu['phone_number']); ?></td>
                    <td><?php echo htmlspecialchars($stu['institution']); ?></td>
                    <td><?php echo htmlspecialchars($stu['department']); ?></td>
                    <td><?php echo htmlspecialchars($stu['faculty']); ?></td>
                    <td><?php echo htmlspecialchars($stu['reason']); ?></td>
                    <td style="text-align:center;">
                        <button type="button" class="remove-suspension-btn" data-matric="<?php echo htmlspecialchars($stu['matric']); ?>"
                            style="padding:10px 28px; border-radius:7px; border:none; background:#1abc9c; color:#fff; font-weight:600; cursor:pointer; min-width:170px; font-size:1em;">
                            Remove from Suspension
                        </button>
                    </td>
                    <td style="text-align:center;">
                        <?php if (!empty($stu['online_payment'])): ?>
                            <button type="button" class="view-receipt-btn"
                               data-receipt-url="../uploads/receipts/<?php echo htmlspecialchars($stu['online_payment']); ?>"
                               style="padding:8px 22px; border-radius:5px; background:#3498db; color:#fff; text-decoration:none; font-weight:600; border:none; cursor:pointer;">
                                View Receipt
                            </button>
                        <?php else: ?>
                            <span style="color:#7f8c8d; font-style:italic;">Not Submitted</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="10">No suspended students found.</td></tr>
        <?php endif; ?>
    </table>

    <div style="display: flex; align-items: center; justify-content: space-between; width: 95%; margin: 38px auto 0 auto;">
        <h2 style="margin-top: 0;">SUSPENDED SUPERVISORS</h2>
        <form method="get" style="margin: 0;">
            <input type="text" name="supervisor_search" placeholder="Search by Supervisor ID..." value="<?php echo isset($_GET['supervisor_search']) ? htmlspecialchars($_GET['supervisor_search']) : ''; ?>" style="padding: 8px 14px; border-radius: 5px; border: 1px solid #bbb; font-size: 1em;">
            <button type="submit" style="padding: 8px 18px; border-radius: 5px; border: none; background: #2c3e50; color: #fff; font-weight: 600; margin-left: 6px;">Search</button>
        </form>
    </div>
    <table class="main-table">
        <tr>
            <th>Full Name</th>
            <th>Supervisor ID</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Institution</th>
            <th>Department</th>
            <th>Faculty</th>
            <th>Reason</th>
            <th style="text-align:center;">Action</th>
            <th style="text-align:center;">Online Payment</th>
        </tr>
        <?php
        // Supervisor search filter (sup_id only)
        $supervisor_search = isset($_GET['supervisor_search']) ? strtolower(trim($_GET['supervisor_search'])) : '';
        $filtered_supervisors = [];
        $supervisor_found = false;
        if ($supervisor_search !== '') {
            foreach ($supervisors as $sup) {
                if (strpos(strtolower($sup['sup_id']), $supervisor_search) !== false) {
                    $filtered_supervisors[] = $sup;
                    $supervisor_found = true;
                }
            }
        } else {
            $filtered_supervisors = $supervisors;
        }
        ?>
        <?php if ($supervisor_search !== '' && !$supervisor_found): ?>
            <script>
                window.addEventListener('DOMContentLoaded', function() {
                    var modal = document.getElementById('modalMsg');
                    modal.textContent = "Supervisor is not available in the record.";
                    modal.style.display = "block";
                    setTimeout(function() { modal.style.display = "none"; }, 2000);
                });
            </script>
        <?php endif; ?>
        <?php if (count($filtered_supervisors) > 0): ?>
            <?php foreach ($filtered_supervisors as $sup): ?>
                <tr class="<?php echo ($supervisor_search !== '' && strpos(strtolower($sup['sup_id']), $supervisor_search) !== false) ? 'highlight-row' : ''; ?>">
                    <td><?php echo htmlspecialchars($sup['fullname']); ?></td>
                    <td><?php echo htmlspecialchars($sup['sup_id']); ?></td>
                    <td><?php echo htmlspecialchars($sup['email']); ?></td>
                    <td><?php echo htmlspecialchars($sup['phone']); ?></td>
                    <td><?php echo htmlspecialchars($sup['institution']); ?></td>
                    <td><?php echo htmlspecialchars($sup['department']); ?></td>
                    <td><?php echo htmlspecialchars($sup['faculty']); ?></td>
                    <td><?php echo htmlspecialchars($sup['reason']); ?></td>
                    <td style="text-align:center;">
                        <button type="button" class="remove-suspension-btn" data-sup_id="<?php echo htmlspecialchars($sup['sup_id']); ?>"
                            style="padding:10px 28px; border-radius:7px; border:none; background:#1abc9c; color:#fff; font-weight:600; cursor:pointer; min-width:170px; font-size:1em;">
                            Remove from Suspension
                        </button>
                    </td>
                    <td style="text-align:center;">
                        <?php if (!empty($sup['online_payment'])): ?>
                            <button type="button" class="view-receipt-btn"
                               data-receipt-url="../uploads/receipts/<?php echo htmlspecialchars($sup['online_payment']); ?>"
                               style="padding:8px 22px; border-radius:5px; background:#3498db; color:#fff; text-decoration:none; font-weight:600; border:none; cursor:pointer;">
                                View Receipt
                            </button>
                        <?php else: ?>
                            <span style="color:#7f8c8d; font-style:italic;">Not Submitted</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="10">No suspended supervisors found.</td></tr>
        <?php endif; ?>
    </table>

    <!-- Modal for confirmation -->
    <div id="confirmModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.35); z-index:9999; align-items:center; justify-content:center;">
        <div style="background:#fff; padding:38px 32px 28px 32px; border-radius:12px; min-width:320px; max-width:90vw; box-shadow:0 8px 32px rgba(0,0,0,0.18); position:relative; text-align:center;">
            <div style="font-size:1.2em; font-weight:600; margin-bottom:22px;">Are you sure that user has paid N500?</div>
            <div style="display:flex; gap:22px; justify-content:center; margin-top:18px;">
                <button id="modalYes" style="padding:10px 32px; background:#27ae60; color:#fff; border:none; border-radius:6px; font-size:1em; font-weight:bold; cursor:pointer;">Yes</button>
                <button id="modalNo" style="padding:10px 32px; background:#e74c3c; color:#fff; border:none; border-radius:6px; font-size:1em; font-weight:bold; cursor:pointer;">No</button>
            </div>
        </div>
    </div>
    <!-- Modal for payment selection -->
    <div id="paymentModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.35); z-index:10000; align-items:center; justify-content:center;">
        <div style="background:#fff; padding:38px 32px 28px 32px; border-radius:12px; min-width:320px; max-width:90vw; box-shadow:0 8px 32px rgba(0,0,0,0.18); position:relative; text-align:center;">
            <div style="font-size:1.1em; font-weight:600; margin-bottom:18px;">Select payment amount:</div>
            <form id="paymentForm" style="margin-bottom:18px;">
                <label><input type="radio" name="amount" value="100" required> N100</label><br>
                <label><input type="radio" name="amount" value="200"> N200</label><br>
                <label><input type="radio" name="amount" value="300"> N300</label><br>
                <label><input type="radio" name="amount" value="400"> N400</label><br>
                <label><input type="radio" name="amount" value="500"> N500</label><br>
                <button type="submit" id="submitPayment" style="display:none; margin-top:16px; padding:10px 32px; background:#2c3e50; color:#fff; border:none; border-radius:6px; font-size:1em; font-weight:bold; cursor:pointer;">NEXT</button>
            </form>
        </div>
    </div>
    <!-- Modal for payment method selection -->
    <div id="paymentMethodModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.35); z-index:10001; align-items:center; justify-content:center;">
        <div style="background:#fff; padding:38px 32px 28px 32px; border-radius:12px; min-width:320px; max-width:90vw; box-shadow:0 8px 32px rgba(0,0,0,0.18); position:relative; text-align:center;">
            <div style="font-size:1.2em; font-weight:600; margin-bottom:22px;">Select Payment Method</div>
            <div style="display:flex; gap:30px; justify-content:center; margin-bottom:18px;">
                <div class="payment-method-option blurred" data-method="cash" style="padding:18px 32px; border-radius:8px; background:#f1f1f1; cursor:pointer; font-size:1.1em; filter:blur(2px); transition:filter 0.2s, box-shadow 0.2s;">
                    Cash
                </div>
                <div class="payment-method-option blurred" data-method="bank" style="padding:18px 32px; border-radius:8px; background:#f1f1f1; cursor:pointer; font-size:1.1em; filter:blur(2px); transition:filter 0.2s, box-shadow 0.2s;">
                    Bank
                </div>
            </div>
            <button id="submitPaymentMethod" style="display:none; margin-top:10px; padding:10px 32px; background:#2c3e50; color:#fff; border:none; border-radius:6px; font-size:1em; font-weight:bold; cursor:pointer;">Submit</button>
        </div>
    </div>
    <!-- Modal for success -->
    <div id="successModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.18); z-index:10001; align-items:center; justify-content:center;">
        <div style="background:#fff; padding:38px 32px 28px 32px; border-radius:12px; min-width:320px; max-width:90vw; box-shadow:0 8px 32px rgba(0,0,0,0.18); position:relative; text-align:center;">
            <div style="font-size:2.5em; color:#27ae60; margin-bottom:12px;">
                <i class="fas fa-check-circle"></i>
            </div>
            <div style="font-size:1.2em; font-weight:600; color:#27ae60;">Remove successful</div>
        </div>
    </div>
    <!-- Modal for viewing receipt -->
    <div id="receiptModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.65); z-index:10002; align-items:center; justify-content:center; padding:20px;">
        <div style="background:#fff; padding:15px; border-radius:12px; max-width:90vw; max-height:90vh; box-shadow:0 8px 32px rgba(0,0,0,0.2); position:relative; text-align:center;">
            <button id="closeReceiptModal" style="position:absolute; top:-15px; right:-15px; width:36px; height:36px; background:#e74c3c; border:2px solid #fff; border-radius:50%; font-size:1.5em; color:#fff; cursor:pointer; line-height:1; padding:0;">&times;</button>
            <img id="receiptImage" src="" alt="Payment Receipt" style="max-width:100%; max-height:85vh; border-radius:8px;">
        </div>
    </div>

    <form id="removeSuspensionForm" method="post" action="remove_suspension.php" style="display:none;">
        <input type="hidden" name="matric" id="modalMatric">
        <input type="hidden" name="sup_id" id="modalSupId">
        <input type="hidden" name="amount" id="modalAmount">
    </form>
    <script>
    let pendingMatric = '';
    let pendingSupId = '';

    document.querySelectorAll('.remove-suspension-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            pendingMatric = btn.getAttribute('data-matric') || '';
            pendingSupId = btn.getAttribute('data-sup_id') || '';
            document.getElementById('modalMatric').value = pendingMatric;
            document.getElementById('modalSupId').value = pendingSupId;
            document.getElementById('confirmModal').style.display = 'flex';
        });
    });

    document.getElementById('modalYes').onclick = function() {
        document.getElementById('confirmModal').style.display = 'none';
        // Show payment method modal directly for Yes (fixed amount 500)
        showPaymentMethodModal(500);
    };

    document.getElementById('modalNo').onclick = function() {
        document.getElementById('confirmModal').style.display = 'none';
        document.getElementById('paymentModal').style.display = 'flex';
    };

    // Show submit button when radio is selected in payment amount modal
    document.querySelectorAll('#paymentForm input[type=radio]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            document.getElementById('submitPayment').style.display = 'inline-block';
        });
    });

    // Payment amount form submit (for No)
    document.getElementById('paymentForm').onsubmit = function(e) {
        e.preventDefault();
        const amount = document.querySelector('#paymentForm input[name=amount]:checked').value;
        document.getElementById('paymentModal').style.display = 'none';
        // Show payment method modal for selected amount
        showPaymentMethodModal(amount);
    };

    // Payment method modal logic
    let selectedPaymentMethod = '';
    function showPaymentMethodModal(amount) {
        document.getElementById('paymentMethodModal').style.display = 'flex';
        document.getElementById('paymentMethodModal').setAttribute('data-amount', amount);
        // Reset payment method selection
        document.querySelectorAll('.payment-method-option').forEach(function(opt) {
            opt.classList.remove('selected');
            opt.classList.add('blurred');
        });
        document.getElementById('submitPaymentMethod').style.display = 'none';
        selectedPaymentMethod = '';
    }

    document.querySelectorAll('.payment-method-option').forEach(function(opt) {
        opt.addEventListener('click', function() {
            document.querySelectorAll('.payment-method-option').forEach(function(o) {
                o.classList.remove('selected');
                o.classList.add('blurred');
            });
            opt.classList.add('selected');
            opt.classList.remove('blurred');
            selectedPaymentMethod = opt.getAttribute('data-method');
            document.getElementById('submitPaymentMethod').style.display = 'inline-block';
        });
    });

    document.getElementById('submitPaymentMethod').onclick = function() {
        document.getElementById('paymentMethodModal').style.display = 'none';
        // Get amount from modal attribute
        const amount = document.getElementById('paymentMethodModal').getAttribute('data-amount');
        // Get fullname from table row
        let fullname = '';
        if (pendingMatric) {
            const row = document.querySelector('button[data-matric="' + pendingMatric + '"]')?.closest('tr');
            if (row) fullname = row.children[0].textContent.trim();
        } else if (pendingSupId) {
            const row = document.querySelector('button[data-sup_id="' + pendingSupId + '"]')?.closest('tr');
            if (row) fullname = row.children[0].textContent.trim();
        }
        // Record transaction with payment method, then remove suspension only if transaction is successful
        recordTransaction(pendingMatric, pendingSupId, fullname, amount, selectedPaymentMethod, function(success) {
            if (success) {
                removeSuspension(pendingMatric, pendingSupId, amount);
            } else {
                console.log('Transaction response:', xhr.responseText); // Debug: See what is returned
                alert("Failed to record transaction. Please try again.");
            }
        });
    };

    function recordTransaction(matric, sup_id, fullname, amount, payment_method, callback) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'transaction_report.php', true);
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        var params = '';
        params += 'matric=' + encodeURIComponent(matric ? matric : '') + '&';
        params += 'sup_id=' + encodeURIComponent(sup_id ? sup_id : '') + '&';
        params += 'fullname=' + encodeURIComponent(fullname ? fullname : '-') + '&';
        params += 'amount=' + encodeURIComponent(amount ? amount : '0') + '&';
        params += 'payment=' + encodeURIComponent(payment_method ? payment_method : 'cash');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                if (xhr.status === 200 && xhr.responseText.trim().toLowerCase() === "success") {
                    if (typeof callback === 'function') callback(true);
                } else {
                    if (typeof callback === 'function') callback(false);
                }
            }
        };
        xhr.send(params);
    }

    function removeSuspension(matric, sup_id, amount) {
        // AJAX to remove_suspension.php
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'remove_suspension.php', true);
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        var params = '';
        if (matric) params += 'matric=' + encodeURIComponent(matric) + '&';
        if (sup_id) params += 'sup_id=' + encodeURIComponent(sup_id) + '&';
        params += 'amount=' + encodeURIComponent(amount);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                showSuccessModalAndReload();
            }
        };
        xhr.send(params);
    }

    function showSuccessModalAndReload() {
        document.getElementById('successModal').style.display = 'flex';
        setTimeout(function() {
            document.getElementById('successModal').style.display = 'none';
            window.location.reload();
        }, 2000);
    }

    // --- Receipt Modal Logic ---
    const receiptModal = document.getElementById('receiptModal');
    const receiptImage = document.getElementById('receiptImage');
    const closeReceiptBtn = document.getElementById('closeReceiptModal');

    document.querySelectorAll('.view-receipt-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const receiptUrl = btn.getAttribute('data-receipt-url');
            if (receiptUrl) {
                receiptImage.src = receiptUrl;
                receiptModal.style.display = 'flex';
            }
        });
    });

    function closeReceiptModal() {
        receiptModal.style.display = 'none';
        receiptImage.src = ''; // Clear image src to avoid showing old image briefly
    }

    closeReceiptBtn.onclick = closeReceiptModal;

    // Close modal when clicking on the background overlay
    receiptModal.onclick = function(e) {
        if (e.target === receiptModal) {
            closeReceiptModal();
        }
    };
    </script>
</body>
</html>
