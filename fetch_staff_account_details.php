<?php
include 'config.php';
session_start();

$admin_hod_id = $_POST['admin_hod_id'] ?? '';
if (!$admin_hod_id) {
    echo '<div style="color:#e74c3c;">Invalid request.</div>';
    exit;
}

// Get admin institution
$stmt = $conn->prepare("SELECT institution FROM admin WHERE hod_id = ?");
$stmt->bind_param("s", $admin_hod_id);
$stmt->execute();
$stmt->bind_result($admin_institution);
$stmt->fetch();
$stmt->close();

if (!$admin_institution) {
    echo '<div style="color:#e74c3c;">Admin institution not found.</div>';
    exit;
}

// Find staff with matching institution
$stmt = $conn->prepare("SELECT updo_staff_id, vc_name, account_name, account_number, bank_name, contact_email, contact_phone FROM partnership_form WHERE institution = ?");
$stmt->bind_param("s", $admin_institution);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo '<div style="color:#e74c3c;">No UPDO staff found for this institution.</div>';
    exit;
}

while ($row = $result->fetch_assoc()) {
    echo '<div style="margin-bottom:18px; padding:12px; background:#f7f7f7; border-radius:8px;">';
    echo '<strong>Account Name:</strong> ' . htmlspecialchars($row['account_name']) . '<br>';
    echo '<strong>Account Number:</strong> ' . htmlspecialchars($row['account_number']) . '<br>';
    echo '<strong>Bank Name:</strong> ' . htmlspecialchars($row['bank_name']) . '<br>';
    echo '<strong>Amount to Pay:</strong> <span style="color:#27ae60; font-weight:bold;">₦1,000</span><br>';
    echo '</div>';
}
$stmt->close();
?>
?>
