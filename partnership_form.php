<?php
session_start();

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

$message = '';

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // In production, log this error and show a generic message.
    error_log("Connection failed: " . $e->getMessage());
    die("Our apologies, but we're experiencing a database connection issue. Please try again later.");
}

// --- Form Submission Handling ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // --- 1. Sanitize and retrieve form data ---
        $vc_name = trim($_POST['vc_name'] ?? '');
        $matric_format = trim($_POST['matric_format'] ?? '');
        $institution = trim($_POST['institution'] ?? '');
        $school_location = trim($_POST['school_location'] ?? '');
        $contact_email = trim($_POST['contact_email'] ?? '');
        $contact_phone = trim($_POST['contact_phone'] ?? '');
        $account_name = trim($_POST['account_name'] ?? '');
        $account_number = trim($_POST['account_number'] ?? '');
        $bank_name = trim($_POST['bank_name'] ?? '');
        $profile_pic_name = '';

        // --- 2. Validate All Inputs ---
        $errors = [];
        if (empty($vc_name)) $errors[] = "VC's Name is required.";
        if (empty($institution)) $errors[] = "Institution is required.";
        if (empty($school_location)) $errors[] = "School Location is required.";
        if (empty($contact_email) || !filter_var($contact_email, FILTER_VALIDATE_EMAIL)) $errors[] = "A valid Contact Email is required.";
        if (empty($contact_phone)) $errors[] = "Contact Phone is required.";
        if (empty($account_name)) $errors[] = "Account Name is required.";
        if (empty($account_number)) $errors[] = "Account Number is required.";
        if (empty($bank_name)) $errors[] = "Bank Name is required.";

        // --- 3. Check for Duplicate Partnership (only if basic validation passes) ---
        if (empty($errors)) {
            // Check if email already exists
            $check_email_sql = "SELECT id FROM partnership_form WHERE contact_email = ?";
            $check_email_stmt = $pdo->prepare($check_email_sql);
            $check_email_stmt->execute([$contact_email]);
            if ($check_email_stmt->fetch()) {
                $errors[] = "This contact email is already registered. Please use a different one.";
            }

            // Check if phone already exists
            $check_phone_sql = "SELECT id FROM partnership_form WHERE contact_phone = ?";
            $check_phone_stmt = $pdo->prepare($check_phone_sql);
            $check_phone_stmt->execute([$contact_phone]);
            if ($check_phone_stmt->fetch()) {
                $errors[] = "This contact phone number is already registered. Please use a different one.";
            }

            $check_sql = "SELECT id FROM partnership_form WHERE vc_name = ? AND institution = ?";
            $check_stmt = $pdo->prepare($check_sql);
            $check_stmt->execute([$vc_name, $institution]);
            if ($check_stmt->fetch()) {
                $errors[] = "A partnership for '" . htmlspecialchars($vc_name) . "' at '" . htmlspecialchars($institution) . "' already exists. Please verify the information.";
            }
        }

        // --- 4. Handle File Upload (only if no other errors exist) ---
        if (empty($errors)) {
            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
                $upload_dir = '../uploads/partnership_pics/';
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
                    // Secure filename
                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $profile_pic_name = 'partner_' . uniqid() . '.' . $ext;
                    $filepath = $upload_dir . $profile_pic_name;

                    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                        $errors[] = "Failed to upload profile picture.";
                        $profile_pic_name = ''; // Reset on failure
                    }
                }
            } else {
                $errors[] = "Profile picture is required.";
            }
        }

        // --- 5. If no errors after all checks, insert into database ---
        if (empty($errors)) {
            /*
             NOTE: Ensure your `partnership_form` table has these columns.
             If not, run this SQL command in phpMyAdmin:
             ALTER TABLE partnership_form
             ADD COLUMN contact_email VARCHAR(255) NULL,
             ADD COLUMN contact_phone VARCHAR(50) NULL,
             ADD COLUMN account_name VARCHAR(255) NULL,
             ADD COLUMN account_number VARCHAR(50) NULL,
             ADD COLUMN bank_name VARCHAR(100) NULL;
            */
            $sql = "INSERT INTO partnership_form (
                vc_name, matric_format, institution, school_location, profile_picture,
                contact_email, contact_phone, account_name, account_number, bank_name
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $vc_name, $matric_format, $institution, $school_location, $profile_pic_name,
                $contact_email, $contact_phone, $account_name, $account_number, $bank_name
            ]);

            // Set message and redirect
            $message = "<div class='success-message'>Partnership application submitted successfully! You will be redirected to the payment page shortly to generate your UP-DO staff ID.</div>";
            header("refresh:3;url=payment_gateway.php?id=" . $pdo->lastInsertId());

        } else {
            // Display validation errors
            $message = "<div class='error-message'>" . implode("<br>", $errors) . "</div>";
        }

    } catch (PDOException $e) {
        // --- 5. Log Errors to a File (Instead of Showing Them) ---
        error_log("Partnership form error: " . $e->getMessage());
        $message = "<div class='error-message'>We encountered a problem with your submission. Please try again later.</div>";
    }
}

// Re-using the institutions list from other files
$institutions = [
    "Abia State University, Uturu (ABSU)", "Abubakar Tafawa Balewa University, Bauchi (ATBU)", "Adekunle Ajasin University, Akungba (AAUA)",
    "Adeyemi College of Education, Ondo (Affiliated to OAU)", "Adamawa State University, Mubi (ADSU)", "Ahmadu Bello University, Zaria (ABU)",
    "Akwa Ibom State University (AKSU)", "Alvan Ikoku College of Education, Owerri", "Bauchi State University, Gadau (BASUG)",
    "Bayero University, Kano (BUK)", "Benue State University, Makurdi (BSU)", "Chukwuemeka Odumegwu Ojukwu University, Uli (COOU)",
    "College of Education, Ikere-Ekiti", "Cross River University of Technology (CRUTECH)", "Delta State University, Abraka (DELSU)",
    "Ebonyi State University, Abakaliki (EBSU)", "Edo State University, Uzairue (EDSU)", "Ekiti State University, Ado-Ekiti (EKSU)",
    "Enugu State University of Science and Technology (ESUT)", "Federal College of Education (Technical), Akoka", "Federal College of Education, Eha-Amufu",
    "Federal College of Education, Kano", "Federal College of Education, Zaria", "Federal University Gashua, Yobe (FUGASHUA)",
    "Federal University of Petroleum Resources, Effurun (FUPRE)", "Federal University of Technology, Akure (FUTA)", "Federal University of Technology, Minna (FUTMINNA)",
    "Federal University of Technology, Owerri (FUTO)", "Federal University, Dutse, Jigawa (FUD)", "Federal University, Dutsin-Ma, Katsina (FUDMA)",
    "Federal University, Gusau, Zamfara (FUGUS)", "Federal University, Kashere, Gombe (FUKASHERE)", "Federal University, Lafia, Nasarawa (FULAFIA)",
    "Federal University, Lokoja, Kogi (FULOKOJA)", "Federal University, Otuoke, Bayelsa (FUOTUOKE)", "Federal University, Oye-Ekiti, Ekiti (FUOYE)",
    "Federal University, Wukari, Taraba (FUWUKARI)", "Gombe State University (GSU)", "Ibrahim Badamasi Babangida University, Lapai (IBBUL)",
    "Imo State University, Owerri (IMSU)", "Kebbi State University of Science and Technology, Aliero (KSUSTA)", "Kogi State University, Anyigba (KSU)",
    "Kwara State College of Education, Ilorin", "Lagos State University (LASU)", "Michael Okpara University of Agriculture, Umudike (MOUAU)",
    "Modibbo Adama University of Technology, Yola (MAUTECH)", "National Open University of Nigeria (NOUN)", "Nasarawa State University, Keffi (NSUK)",
    "Nigerian Defence Academy, Kaduna (NDA)", "Nnamdi Azikiwe University, Awka (UNIZIK)", "Obafemi Awolowo University, Ile-Ife (OAU)",
    "Olabisi Onabanjo University, Ago-Iwoye (OOU)", "Osun State University, Osogbo (UNIOSUN)", "Plateau State University, Bokkos (PLASU)",
    "Rivers State University (RSU)", "Sacred Heart School of Nursing, Abeokuta", "School of Nursing, Ahmadu Bello University Teaching Hospital, Zaria",
    "School of Nursing, Lagos University Teaching Hospital (LUTH)", "School of Nursing, National Orthopaedic Hospital, Enugu",
    "School of Nursing, Obafemi Awolowo University Teaching Hospital (OAUTH), Ile-Ife", "School of Nursing, University College Hospital (UCH), Ibadan",
    "School of Nursing, University of Nigeria Teaching Hospital (UNTH), Enugu", "St. Gerard’s Catholic School of Nursing, Kaduna", "Sokoto State University (SSU)",
    "Tai Solarin College of Education, Omu-Ijebu", "Tai Solarin University of Education, Ijagun (TASUED)", "Taraba State University, Jalingo (TSU)",
    "University of Abuja, Gwagwalada (UNIABUJA)", "University of Agriculture, Abeokuta (FUNAAB)", "University of Agriculture, Makurdi (UAM)",
    "University of Benin (UNIBEN)", "University of Calabar (UNICAL)", "University of Ibadan (UI)", "University of Ilorin (UNILORIN)",
    "University of Jos (UNIJOS)", "University of Lagos (UNILAG)", "University of Maiduguri (UNIMAID)", "University of Nigeria, Nsukka (UNN)",
    "University of Port Harcourt (UNIPORT)", "University of Uyo (UNIUYO)", "Usmanu Danfodiyo University, Sokoto (UDUSOK)",
    "Yobe State University, Damaturu (YSU)", "Zamfara State University, Talata Mafara"
];

$banks = [
    "Access Bank", "Citibank", "Ecobank Nigeria", "Fidelity Bank", "First Bank of Nigeria", "First City Monument Bank (FCMB)",
    "Guaranty Trust Bank (GTBank)", "Heritage Bank", "Keystone Bank", "Polaris Bank", "Providus Bank", "Stanbic IBTC Bank",
    "Standard Chartered Bank", "Sterling Bank", "SunTrust Bank", "Union Bank of Nigeria", "United Bank for Africa (UBA)",
    "Unity Bank", "Wema Bank", "Zenith Bank", "Jaiz Bank", "Titan Trust Bank", "Globus Bank", "Parallex Bank", "PremiumTrust Bank"
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UP-DO Partnership Application</title>
    <link rel="stylesheet" href="../css/signup.css">
    <style>
        .login-form { max-width: 400px; }
        .inputbox label { position: absolute; top: -10px; left: 0; font-size: 0.9em; color: black; pointer-events: none; }
        .inputbox select, .inputbox input[type="file"] { width: 100%; height: 42px; border: 1px solid #ccc; border-radius: 4px; padding: 4px 8px; font-size: 16px; background: rgba(255, 255, 255, 0.9); color: #333; }
        .inputbox input[type="file"] { padding: 5px 8px; margin-top: 20px;}
        .error-message, .success-message { padding: 15px; margin-bottom: 20px; border-radius: 5px; color: #fff; text-align: center; }
        .error-message { background-color: #e74c3c; }
        .success-message { background-color: #2ecc71; }
    </style>
</head>
<body>
    <section class="login-form">
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="content">
                <h2>Partnership Application Form</h2>
                <?php if ($message) echo $message; ?>

                <div class="inputbox">
                    <input type="text" name="vc_name" required>
                    <i>VC's Name</i>
                </div>

                <div class="inputbox">
                    <input type="text" name="matric_format" placeholder="e.g., FUDMA/1234/56789" required>
                    <i>Matric Number Format Compulsory</i>
                </div>

                <div class="inputbox">
                    <input type="text" name="account_name" required>
                    <i>Account Name</i>
                </div>
                <div class="inputbox">
                    <input type="text" name="account_number" required>
                    <i>Account Number</i>
                </div>
                <div class="inputbox">
                    <select name="bank_name" required>
                        <option value="">-- Select Bank Name --</option>
                        <?php foreach ($banks as $bank): ?>
                            <option value="<?php echo htmlspecialchars($bank); ?>"><?php echo htmlspecialchars($bank); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="inputbox">
                    <select id="institution" name="institution" required>
                        <option value="">-- Select Institution your VC is affiliated with --</option>
                        <?php foreach ($institutions as $inst): ?>
                            <option value="<?php echo htmlspecialchars($inst); ?>"><?php echo htmlspecialchars($inst); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="inputbox">
                    <input type="text" name="school_location" required>
                    <i>School Location (e.g., Zaria, Kaduna State)</i>
                </div>

                <div class="inputbox">
                    <input type="email" name="contact_email" required>
                    <i>SCHOOL EMAIL</i>
                </div>

                <div class="inputbox">
                    <input type="number" name="contact_phone" required>
                    <i>SCHOOL PHONE</i>
                </div>

                <div class="inputbox">
                    <label for="profile_picture">Upload VC's Profile Picture</label>
                    <input type="file" id="profile_picture" name="profile_picture" accept="image/*" required>
                </div>

                <div class="inputbox">
                    <input type="submit" value="Submit Application & Proceed to Payment">
                </div>

                <div class="links">
                    <a href="partnership_home.php">Back to Partnership Home</a>
                </div>
            </div>
        </form>
    </section>
</body>
</html>
