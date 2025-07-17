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
    error_log("Connection failed: " . $e->getMessage());
    die("Our apologies, but we're experiencing a database connection issue. Please try again later.");
}

// --- Form Submission Handling ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 1. Get form data
        $contact_email = trim($_POST['contact_email'] ?? '');
        $contact_phone = trim($_POST['contact_phone'] ?? '');
        $institution = trim($_POST['institution'] ?? '');

        // 2. Basic validation
        if (empty($contact_email) || empty($contact_phone) || empty($institution)) {
            $message = "<div class='error-message'>All fields are required.</div>";
        } else {
            // 3. Check database for a matching record in the partnership_form table
            $sql = "SELECT id FROM partnership_form WHERE contact_email = ? AND contact_phone = ? AND institution = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$contact_email, $contact_phone, $institution]);
            $partnership = $stmt->fetch();

            if ($partnership) {
                // 4. Match found, redirect to payment gateway
                header("Location: payment_gateway.php?id=" . $partnership['id']);
                exit();
            } else {
                // 5. No match found, show error
                $message = "<div class='error-message'>The information you provided is wrong. No such record in the database.</div>";
            }
        }
    } catch (PDOException $e) {
        error_log("Continue payment error: " . $e->getMessage());
        $message = "<div class='error-message'>A database error occurred. Please try again.</div>";
    }
}

// Re-using the institutions list from other files for consistency
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Continue Partnership Application</title>
    <link rel="stylesheet" href="../css/signup.css">
    <style>
        .login-form { max-width: 400px; }
        .inputbox select { width: 100%; height: 42px; border: 1px solid #ccc; border-radius: 4px; padding: 4px 8px; font-size: 16px; background: rgba(255, 255, 255, 0.9); color: #333; margin-top: 20px; }
        .error-message, .success-message { padding: 15px; margin-bottom: 20px; border-radius: 5px; color: #fff; text-align: center; }
        .error-message { background-color: #e74c3c; }
        .success-message { background-color: #2ecc71; }
    </style>
</head>
<body>
    <section class="login-form">
        <form action="" method="POST">
            <div class="content">
                <h2>Continue to Payment</h2>
                <p style="color: #555; margin-bottom: 20px; font-size: 0.9em;">Please enter the details you used during registration to find your application.</p>
                <?php if ($message) echo $message; ?>

                <div class="inputbox">
                    <input type="email" name="contact_email" required>
                    <i>Your Contact Email</i>
                </div>

                <div class="inputbox">
                    <input type="number" name="contact_phone" required>
                    <i>Your Contact Phone</i>
                </div>

                <div class="inputbox">
                    <select id="institution" name="institution" required>
                        <option value="">-- Select Your Institution --</option>
                        <?php foreach ($institutions as $inst): ?>
                            <option value="<?php echo htmlspecialchars($inst); ?>"><?php echo htmlspecialchars($inst); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="inputbox" style="margin-top: 30px;">
                    <input type="submit" value="Proceed to Payment">
                </div>

                <div class="links">
                    <a href="partnership_home.php">Back to Home</a>
                </div>
            </div>
        </form>
    </section>
</body>
</html>