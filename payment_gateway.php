<?php
session_start();

// --- Flutterwave Configuration (Demo Credentials for Testing) ---
define('FLUTTERWAVE_PUBLIC_KEY', 'FLWPUBK_TEST-43193d02189b6f9875e4faacbc37166c-X');
define('FLUTTERWAVE_SECRET_KEY', 'FLWSECK_TEST-cbcef72dab8b693480c6d6bcda775dbf-X');
define('FLUTTERWAVE_VERIFY_URL', 'https://api.flutterwave.com/v3/transactions/');


// --- Database Configuration ---
$host = 'localhost';
$db   = 'documents';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host={$host};dbname={$db};charset={$charset}";
$options = [ // PDO options for robust connection
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

$message = '';
$partnership_id = null;

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    error_log("Connection failed: " . $e->getMessage());
    die("Our apologies, but we're experiencing a database connection issue. Please try again later.");
}

// --- 3. Handle Flutterwave Callback & Verify Transaction (Real-time Check) ---
if (isset($_GET['status']) && isset($_GET['tx_ref']) && isset($_GET['transaction_id'])) {
    $status = $_GET['status'];
    $tx_ref = $_GET['tx_ref'];
    $transaction_id = $_GET['transaction_id'];

    // Extract partnership ID from our tx_ref format (e.g., 'UPDO-PARTNER-123-1678886400')
    $parts = explode('-', $tx_ref);
    $partnership_id = $parts[2] ?? null;

    if ($status === 'successful' && filter_var($partnership_id, FILTER_VALIDATE_INT)) {
        // Transaction was successful, now verify it on the server
        $url = FLUTTERWAVE_VERIFY_URL . $transaction_id . '/verify';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . FLUTTERWAVE_SECRET_KEY
        ]);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code == 200) {
            $res = json_decode($response);
            if ($res && $res->status == 'success' && $res->data->status == 'successful') {
                // --- Payment is successful and verified, update database ---
                $payment_amount = $res->data->amount;
                $updo_staff_id = 'UPDO-STAFF-' . str_pad($partnership_id, 4, '0', STR_PAD_LEFT) . '-' . date('Y');

                // Only update if it's still pending to prevent re-processing
                $sql = "UPDATE partnership_form SET payment_status = 'paid', updo_staff_id = ?, payment_amount = ? WHERE id = ? AND payment_status = 'pending'";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$updo_staff_id, $payment_amount, $partnership_id]);

                $message = "<div class='success-message'>Payment verified successfully! Your partnership is now active.</div>";
            } else {
                $message = "<div class='error-message'>Payment verification failed. Please contact support.</div>";
            }
        } else {
            $message = "<div class='error-message'>Could not connect to payment gateway to verify transaction. Please contact support.</div>";
        }
    } elseif ($status === 'cancelled') {
        $message = "<div class='error-message'>Payment was cancelled. Please try again.</div>";
        // Extract partnership ID to reload the page correctly
        $parts = explode('-', $tx_ref);
        $partnership_id = $parts[2] ?? null;
    } else {
        // Handle other failed statuses
        $message = "<div class='error-message'>Payment failed. Please try again.</div>";
        $parts = explode('-', $tx_ref);
        $partnership_id = $parts[2] ?? null;
    }
} else {
    // --- 1. Get Partnership ID from initial redirect ---
    if (!isset($_GET['id'])) {
        die("Invalid request. No partnership ID provided.");
    }
    $partnership_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if (!$partnership_id) {
        die("Invalid partnership ID.");
    }
}

// --- 2. Fetch Current Partnership Data (runs on initial load and after callback) ---
try {
    $stmt = $pdo->prepare("SELECT * FROM partnership_form WHERE id = ?");
    $stmt->execute([$partnership_id]);
    $partnership_data = $stmt->fetch();

    if (!$partnership_data) {
        die("Partnership application not found.");
    }
} catch (PDOException $e) {
    error_log("Error fetching partnership data: " . $e->getMessage());
    die("Could not retrieve partnership details.");
}

// --- Prepare data for Flutterwave form ---
$payment_amount = 10000; // The fee for the partnership
$tx_ref = 'UPDO-PARTNER-' . $partnership_id . '-' . time(); // Unique transaction reference
$payerName = $partnership_data['vc_name'];
// Use the contact details from the form submission
$payerEmail = $partnership_data['contact_email'] ?? 'not-provided@updo.com';
$payerPhone = $partnership_data['contact_phone'] ?? '00000000000';

// The URL to which Flutterwave will redirect the user after payment.
$redirect_url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
$logo_url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/updo.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UP-DO Partnership Payment</title>
    <link rel="stylesheet" href="../css/signup.css">
    <style>
        .login-form { max-width: 500px; }
        .payment-summary, .payment-success { text-align: left; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background: #f9f9f9; color: #333; }
        .payment-summary h3, .payment-success h3 { margin-top: 0; color: blue; border-bottom: 2px solid blue; padding-bottom: 10px; margin-bottom: 15px; }
        .payment-summary p, .payment-success p { margin: 8px 0; font-size: 1.1em; }
        .payment-summary strong, .payment-success strong { color: #000; }
        .payment-amount { font-size: 2em !important; font-weight: bold; color: #27ae60 !important; text-align: center; margin: 20px 0 !important; }
        .error-message, .success-message { padding: 15px; margin-bottom: 20px; border-radius: 5px; color: #fff; text-align: center; }
        .error-message { background-color: #e74c3c; }
        .success-message { background-color: #2ecc71; }
        /* Style for the payment button to match the input submit */
        .pay-button {
            width: 100%; padding: 10px; background: #333; color: #fff; border: none;
            cursor: pointer; transition: 0.3s; font-size: 16px; border-radius: 4px;
            margin-top: 20px; /* Match the margin of the inputbox */
        }
        .pay-button:hover { background: #555; }
        .page-logo {
            max-width: 120px;
            height: auto;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <section class="login-form">
        <div class="content">
            <img src="<?php echo $logo_url; ?>" alt="UP-DO Logo" class="page-logo">
            <h2 style="margin-top: 0;">Partnership Payment Gateway</h2>
            <?php if ($message) echo $message; ?>

            <?php if (isset($partnership_data['payment_status']) && $partnership_data['payment_status'] === 'paid'): ?>
                <div class="payment-success">
                    <h3>Partnership Activated!</h3>
                    <p><strong>VC's Name:</strong> <?php echo htmlspecialchars($partnership_data['vc_name']); ?></p>
                    <p><strong>Institution:</strong> <?php echo htmlspecialchars($partnership_data['institution']); ?></p>
                    <p><strong>Payment Status:</strong> <span style="color: green; font-weight: bold;">PAID</span></p>
                    <?php if (isset($partnership_data['payment_amount']) && $partnership_data['payment_amount'] > 0): ?>
                        <p><strong>Amount Paid:</strong> ₦<?php echo number_format($partnership_data['payment_amount'], 2); ?></p>
                    <?php endif; ?>
                    <p><strong>Your UP-DO Staff ID:</strong> <span style="background: blue; color: white; padding: 5px 10px; border-radius: 5px;"><?php echo htmlspecialchars($partnership_data['updo_staff_id']); ?></span></p>
                    <p>Please save this ID. You will need it for future interactions.</p>
                </div>
                 <div class="links">
                    <a href="partnership_home.php">Return to Home</a>
                </div>
            <?php else: ?>
                <div class="payment-summary">
                    <h3>Payment Summary</h3>
                    <p><strong>VC's Name:</strong> <?php echo htmlspecialchars($partnership_data['vc_name']); ?></p>
                    <p><strong>Institution:</strong> <?php echo htmlspecialchars($partnership_data['institution']); ?></p>
                    <p class="payment-amount">Amount: ₦<?php echo number_format($payment_amount, 2); ?></p>
                    <p style="font-size: 0.9em; text-align: center; color: #777;">This is a one-time fee for partnership setup and UP-DO Staff ID generation.</p>
                </div>

                <!-- NOTE: The ALTER TABLE command below is still required for the database to work correctly. -->
                <!-- ALTER TABLE partnership_form ADD payment_status VARCHAR(50) DEFAULT 'pending', ADD updo_staff_id VARCHAR(100) NULL DEFAULT NULL, ADD payment_amount DECIMAL(10, 2) NULL; -->
                
                <button type="button" onclick="makePayment()" class="pay-button">Pay ₦<?php echo number_format($payment_amount, 2); ?> with Flutterwave</button>

                 <div class="links">
                    <a href="partnership_home.php">Cancel Payment</a>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <script src="https://checkout.flutterwave.com/v3.js"></script>
    <script>
        function makePayment() {
            FlutterwaveCheckout({
                public_key: "<?php echo FLUTTERWAVE_PUBLIC_KEY; ?>",
                tx_ref: "<?php echo $tx_ref; ?>",
                amount: <?php echo $payment_amount; ?>,
                currency: "NGN",
                redirect_url: "<?php echo $redirect_url; ?>",
                customer: {
                    email: "<?php echo htmlspecialchars($payerEmail); ?>",
                    phone_number: "<?php echo htmlspecialchars($payerPhone); ?>",
                    name: "<?php echo htmlspecialchars($payerName); ?>",
                },
                customizations: {
                    title: "UP-DO Partnership Fee",
                    description: "Payment for partnership with UP-DO",
                    logo: "<?php echo $logo_url; ?>",
                },
            });
        }
    </script>
</body>
</html>