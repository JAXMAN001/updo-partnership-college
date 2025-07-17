<?php
session_start(); // Start the session

// Security check: Ensure admin (HOD) is logged in
if (!isset($_SESSION['hod_id'])) {
    header("Location: student_login.php");
    exit();
}

$host = "localhost";
$username = "root"; // Replace with your actual database username
$password = ""; // Replace with your actual database password
$dbname = "documents";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['fullname']) && isset($_POST['hod_id']) && isset($_POST['email']) && isset($_POST['password'])) {
    $fullname = $_POST['fullname'];
    $hod_id = $_POST['hod_id'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $department = $_POST['department'];
    $faculty = $_POST['faculty'];
    $date_of_birth = $_POST['date_of_birth'];
    $institution = $_POST['institution'];
    $account_number = $_POST['account_number'];
    $account_name = $_POST['account_name'];
    $bank_name = $_POST['bank_name'];
    // $hashed_password = password_hash($new_password, PASSWORD_DEFAULT); // Hash the password

    // Check if sup_id already exists
    $check_sup_id_sql = "SELECT hod_id FROM admin WHERE hod_id = '$hod_id'";
    $sup_id_result = $conn->query($check_sup_id_sql);

    if ($sup_id_result->num_rows > 0) {
        echo "<script>alert('Supervisor ID already exists. Please use a different ID.'); 
        window.location.href = 'admin_add_admin.php';</script>";
        exit();
    }

    // Check if email already exists
    $check_email_sql = "SELECT email FROM admin WHERE email = '$email'";
    $email_result = $conn->query($check_email_sql);

    if ($email_result->num_rows > 0) {
        echo "<script>alert('Email already exists. Please use a different email.'); 
        window.location.href = 'admin_add_admin.php';</script>";
        exit();
    }

    // Check if phone number already exists
    $check_phone_sql = "SELECT phone FROM admin WHERE phone = '$phone'";
    $phone_result = $conn->query($check_phone_sql);

    if ($phone_result->num_rows > 0) {
        echo "<script>alert('Phone number already exists. Please use a different phone number.'); 
        window.location.href = 'admin_add_admin.php';</script>";
        exit();
    }


    $sql = "INSERT INTO admin (fullname, hod_id, phone, email, department, faculty, date_of_birth, institution, account_number, account_name, bank_name, password) 
    VALUES ('$fullname', '$hod_id', '$phone', '$email', '$department', '$faculty', '$date_of_birth', '$institution', '$account_number', '$account_name', '$bank_name', '$password')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('New admin added successfully.'); 
        window.location.href = 'cyber_features.php';</script>";
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$institutions = [
    "Abia State University, Uturu (ABSU)",
    "Abubakar Tafawa Balewa University, Bauchi (ATBU)",
    "Adekunle Ajasin University, Akungba (AAUA)",
    "Adeyemi College of Education, Ondo (Affiliated to OAU)",
    "Adamawa State University, Mubi (ADSU)",
    "Ahmadu Bello University, Zaria (ABU)",
    "Akwa Ibom State University (AKSU)",
    "Alvan Ikoku College of Education, Owerri",
    "Bauchi State University, Gadau (BASUG)",
    "Bayero University, Kano (BUK)",
    "Benue State University, Makurdi (BSU)",
    "Chukwuemeka Odumegwu Ojukwu University, Uli (COOU)",
    "College of Education, Ikere-Ekiti",
    "Cross River University of Technology (CRUTECH)",
    "Delta State University, Abraka (DELSU)",
    "Ebonyi State University, Abakaliki (EBSU)",
    "Edo State University, Uzairue (EDSU)",
    "Ekiti State University, Ado-Ekiti (EKSU)",
    "Enugu State University of Science and Technology (ESUT)",
    "Federal College of Education (Technical), Akoka",
    "Federal College of Education, Eha-Amufu",
    "Federal College of Education, Kano",
    "Federal College of Education, Zaria",
    "Federal University Gashua, Yobe (FUGASHUA)",
    "Federal University of Petroleum Resources, Effurun (FUPRE)",
    "Federal University of Technology, Akure (FUTA)",
    "Federal University of Technology, Minna (FUTMINNA)",
    "Federal University of Technology, Owerri (FUTO)",
    "Federal University, Dutse, Jigawa (FUD)",
    "Federal University, Dutsin-Ma, Katsina (FUDMA)",
    "Federal University, Gusau, Zamfara (FUGUS)",
    "Federal University, Kashere, Gombe (FUKASHERE)",
    "Federal University, Lafia, Nasarawa (FULAFIA)",
    "Federal University, Lokoja, Kogi (FULOKOJA)",
    "Federal University, Otuoke, Bayelsa (FUOTUOKE)",
    "Federal University, Oye-Ekiti, Ekiti (FUOYE)",
    "Federal University, Wukari, Taraba (FUWUKARI)",
    "Gombe State University (GSU)",
    "Ibrahim Badamasi Babangida University, Lapai (IBBUL)",
    "Imo State University, Owerri (IMSU)",
    "Kebbi State University of Science and Technology, Aliero (KSUSTA)",
    "Kogi State University, Anyigba (KSU)",
    "Kwara State College of Education, Ilorin",
    "Lagos State University (LASU)",
    "Michael Okpara University of Agriculture, Umudike (MOUAU)",
    "Modibbo Adama University of Technology, Yola (MAUTECH)",
    "National Open University of Nigeria (NOUN)",
    "Nasarawa State University, Keffi (NSUK)",
    "Nigerian Defence Academy, Kaduna (NDA)",
    "Nnamdi Azikiwe University, Awka (UNIZIK)",
    "Obafemi Awolowo University, Ile-Ife (OAU)",
    "Olabisi Onabanjo University, Ago-Iwoye (OOU)",
    "Osun State University, Osogbo (UNIOSUN)",
    "Plateau State University, Bokkos (PLASU)",
    "Rivers State University (RSU)",
    "Sacred Heart School of Nursing, Abeokuta",
    "School of Nursing, Ahmadu Bello University Teaching Hospital, Zaria",
    "School of Nursing, Lagos University Teaching Hospital (LUTH)",
    "School of Nursing, National Orthopaedic Hospital, Enugu",
    "School of Nursing, Obafemi Awolowo University Teaching Hospital (OAUTH), Ile-Ife",
    "School of Nursing, University College Hospital (UCH), Ibadan",
    "School of Nursing, University of Nigeria Teaching Hospital (UNTH), Enugu",
    "St. Gerard’s Catholic School of Nursing, Kaduna",
    "Sokoto State University (SSU)",
    "Tai Solarin College of Education, Omu-Ijebu",
    "Tai Solarin University of Education, Ijagun (TASUED)",
    "Taraba State University, Jalingo (TSU)",
    "University of Abuja, Gwagwalada (UNIABUJA)",
    "University of Agriculture, Abeokuta (FUNAAB)",
    "University of Agriculture, Makurdi (UAM)",
    "University of Benin (UNIBEN)",
    "University of Calabar (UNICAL)",
    "University of Ibadan (UI)",
    "University of Ilorin (UNILORIN)",
    "University of Jos (UNIJOS)",
    "University of Lagos (UNILAG)",
    "University of Maiduguri (UNIMAID)",
    "University of Nigeria, Nsukka (UNN)",
    "University of Port Harcourt (UNIPORT)",
    "University of Uyo (UNIUYO)",
    "Usmanu Danfodiyo University, Sokoto (UDUSOK)",
    "Yobe State University, Damaturu (YSU)",
    "Zamfara State University, Talata Mafara"
];

$departments = [
    "Accounting",
    "Agricultural Economics",
    "Animal Science",
    "Architecture",
    "Banking and Finance",
    "Biochemistry",
    "Biological Sciences",
    "Botany",
    "Building",
    "Business Administration",
    "Chemical Engineering",
    "Chemistry",
    "Civil Engineering",
    "Computer Science",
    "Crop Science",
    "Economics",
    "Education and Chemistry",
    "Education and Mathematics",
    "Education and Physics",
    "Electrical Engineering",
    "English Language",
    "Estate Management",
    "Fine and Applied Arts",
    "Fisheries and Aquaculture",
    "Forestry and Wildlife Management",
    "French",
    "Geography",
    "Geology",
    "Hausa",
    "History and International Studies",
    "Industrial Chemistry",
    "Information Technology",
    "Islamic Studies",
    "Law",
    "Library and Information Science",
    "Linguistics",
    "Mass Communication",
    "Mathematics",
    "Mechanical Engineering",
    "Mechatronics Engineering",
    "Medical Laboratory Science",
    "Medicine and Surgery",
    "Microbiology",
    "Nursing Science",
    "Pharmacy",
    "Physics with Electronics",
    "Political Science",
    "Public Administration",
    "Quantity Surveying",
    "Sociology",
    "Soil Science",
    "Statistics",
    "Surveying and Geoinformatics",
    "Theatre Arts",
    "Urban and Regional Planning",
    "Veterinary Medicine",
    "Zoology"
];
$faculties = [
    "Faculty of Agriculture",
    "Faculty of Arts",
    "Faculty of Education",
    "Faculty of Engineering",
    "Faculty of Law",
    "Faculty of Management Sciences",
    "Faculty of Pharmacy",
    "Faculty of Science",
    "Faculty of Social Sciences"
];

// List of Nigerian banks
$banks = [
    "Access Bank",
    "Citibank Nigeria",
    "Ecobank Nigeria",
    "Fidelity Bank",
    "First Bank of Nigeria",
    "First City Monument Bank (FCMB)",
    "Globus Bank",
    "Guaranty Trust Bank (GTBank)",
    "Heritage Bank",
    "Jaiz Bank",
    "Keystone Bank",
    "Polaris Bank",
    "Providus Bank",
    "Stanbic IBTC Bank",
    "Standard Chartered Bank",
    "Sterling Bank",
    "SunTrust Bank",
    "Titan Trust Bank",
    "Union Bank of Nigeria",
    "United Bank for Africa (UBA)",
    "Unity Bank",
    "Wema Bank",
    "Zenith Bank"
];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ADD NEW ADMIN</title>
    <link rel="stylesheet" href="../css/signup.css">

     <style>
        .spinner {
            display: inline-block;
            width: 1em;
            height: 1em;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
            -webkit-animation: spin 1s ease-in-out infinite;
            margin-right: 5px;
            vertical-align: middle;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        @-webkit-keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>
<div class="login-form">

        <form action="#" method="post">

            <div class="content">

                <h2> New HOD </h2>

                <div class="inputbox">
                    <input type="text" name="fullname" required>
                    <i>FULLNAME</i>
                </div>

                <div class="inputbox">
                    <input type="text" id="hod_id" name="hod_id" required readonly >
                    <i>USER ID</i>
                    <button type="button" id="generateHodIdBtn" 
                            style="position: absolute; 
                                   right: -15px; 
                                   top: 50%; 
                                   transform: translateY(-50%); 
                                   padding: 8px 12px; 
                                   border: none; 
                                   background: #555; 
                                   color: white; 
                                   cursor: pointer;
                                   border-radius: 4px;
                                   white-space: nowrap; font-size: 14px; display: inline-flex; align-items: center; min-width: 180px; justify-content: center;">
                        Generate HOD_ID
                    </button>
                </div>


                <div class="inputbox">
                    <input type="number" name="phone" required maxlength="11">
                    <i>PHONE NUMBER</i>
                </div>

                <div class="inputbox">
                    <input type="email" name="email" required>
                    <i>EMAIL ADDRESS</i>
                </div>

                <div class="inputbox">
                   <label for="institution" 
                   style="  position: absolute;
                   top: 0px;
                   left: 0px;
                   pointer-events: none;">
                   INSTITUTION NAME:</label><br>
                    <div style="display:flex;align-items:center;gap:4px;">
                 <select name="institution" id="institution" required 
                    style="flex:0 0 330px; height:42px; width: 300%; border:1px solid #ccc; border-radius:4px; padding:4px 8px; font-size:16px; background:rgba(255, 255, 255, 0.8);">
                    <option value="" disable selection >SELECT</option>
                    <?php foreach ($institutions as $institution): ?>
                       <option value="<?php echo htmlspecialchars($institution); ?>"><?php echo htmlspecialchars($institution); ?></option>
                    <?php endforeach; ?>
                    </select>
                </div>
                 </div>


               <div class="inputbox">
                   <label for="department" 
                   style="  position: absolute;
                   top: 0px;
                   left: 0px;
                   pointer-events: none;">
                   DEPARTMENT:</label><br>
                    <div style="display:flex;align-items:center;gap:4px;">
                 <select name="department" required 
                    style="flex:0 0 330px; height:42px; width: 300%; border:1px solid #ccc; border-radius:4px; padding:4px 8px; font-size:16px; background:rgba(255, 255, 255, 0.8);">
                    <option value="" disable selection >SELECT</option>
                    <?php foreach ($departments as $department): ?>
                       <option value="<?php echo htmlspecialchars($department); ?>"><?php echo htmlspecialchars($department); ?></option>
                    <?php endforeach; ?>
                    </select>
                </div>
                 </div>

                 <div class="inputbox">
                   <label for="faculty " 
                   style="  position: absolute;
                   top: 0px;
                   left: 0px;
                   pointer-events: none;">
                   FACULTY:</label><br>
                    <div style="display:flex;align-items:center;gap:4px;">
                 <select name="faculty" required 
                    style="flex:0 0 330px; height:42px; width: 300%; border:1px solid #ccc; border-radius:4px; padding:4px 8px; font-size:16px; background:rgba(255, 255, 255, 0.8);">
                    <option value="" disable selection >SELECT</option>
                    <?php foreach ($faculties as $faculty): ?>
                       <option value="<?php echo htmlspecialchars($faculty); ?>"><?php echo htmlspecialchars($faculty); ?></option>
                    <?php endforeach; ?>
                    </select>
                </div>
                 </div>

                 <div class="inputbox">
                    <input type="date" name="date_of_birth" required>
                    <i>DATE OF BIRTH</i>
                </div>
                
                <div class="inputbox">
                    <input type="password" name="password" maxlength="8" required>
                    <i>PASSWORD</i>
                </div>

                <div class="inputbox">
                    <input type="text" name="account_number" required maxlength="20">
                    <i>ACCOUNT NUMBER</i>
                </div>
                <div class="inputbox">
                    <input type="text" name="account_name" required>
                    <i>ACCOUNT NAME</i>
                </div>
                <div class="inputbox">
                   <label for="bank_name" 
                   style="  position: absolute;
                   top: 0px;
                   left: 0px;
                   pointer-events: none;">
                   BANK NAME:</label><br>
                    <div style="display:flex;align-items:center;gap:4px;">
                 <select name="bank_name" id="bank_name" required 
                    style="flex:0 0 330px; height:42px; width: 300%; border:1px solid #ccc; border-radius:4px; padding:4px 8px; font-size:16px; background:rgba(255, 255, 255, 0.8);">
                    <option value="" disable selection >SELECT</option>
                    <?php foreach ($banks as $bank): ?>
                       <option value="<?php echo htmlspecialchars($bank); ?>"><?php echo htmlspecialchars($bank); ?></option>
                    <?php endforeach; ?>
                    </select>
                </div>
                 </div>

                <div class="inputbox">
                    <input type="submit" value="REGISTER">
                </div>

                <div class="links">
                    <a href="cyber_features.php" 
                    id="back" 
                    onclick="confirmLogout()">GO BACK</a>
                </div>



            </div>
        </form>
    </div>
</body>
</html>



    <script>
    function confirmLogout() {
        if (confirm("GO BACK TO DASHBOARD PAGE")) {
            document.getElementById('back').submit();
        }
    }
    </script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const hodIdInput = document.getElementById('hod_id');
    const generateBtn = document.getElementById('generateHodIdBtn');

    // Function to check if a hod_id exists in the database
    async function checkHodIdExists(hodId) {
        try {
            const formData = new FormData();
            formData.append('hod_id', hodId);

            const response = await fetch('check_hod_id.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            return data.status === 'exists';
        } catch (error) {
            console.error('Error checking hod_id:', error);
            // Fail safely - assume it exists to prevent duplicates if the check fails
            return true; 
        }
    }

    // Function to generate a unique hod_id, retrying if a duplicate is found
    async function generateUniqueHodId() {
        let generatedId;
        const maxAttempts = 10; // Prevent infinite loops

        for (let i = 0; i < maxAttempts; i++) {
            const minDigits = 3;
            const maxDigits = 5;
            const numDigits = Math.floor(Math.random() * (maxDigits - minDigits + 1)) + minDigits;
            const min = Math.pow(10, numDigits - 1);
            const max = Math.pow(10, numDigits) - 1;
            const randomNumber = Math.floor(Math.random() * (max - min + 1)) + min;
            generatedId = 'HOD-' + randomNumber;

            const exists = await checkHodIdExists(generatedId);
            if (!exists) return generatedId; // Found a unique ID
        }
        return null; // Could not find a unique ID after several attempts
    }

    if (generateBtn) {
        generateBtn.addEventListener('click', async function() {
            // 1. Show loading state with a spinner
            this.disabled = true;
            this.innerHTML = '<span class="spinner"></span> Generating...';
            hodIdInput.value = 'Generating ID...';

            // 2. Generate a unique ID and update the UI
            const uniqueId = await generateUniqueHodId();
            hodIdInput.value = uniqueId || 'Error: Generation failed.';
            this.innerHTML = uniqueId ? 'Generated Successfully!' : 'Generation Failed';
            this.style.background = uniqueId ? '#27ae60' : '#e74c3c';
            if (!uniqueId) this.disabled = false; // Allow retry on failure
        });
    }

    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(event) {
            const hodIdValue = hodIdInput.value;
            // Check if the ID has been successfully generated.
            if (!hodIdValue || !hodIdValue.startsWith('HOD-')) {
                event.preventDefault(); // Stop the form from submitting
                alert('You must generate a User ID before registering.');
                generateBtn.focus(); // Bring attention to the generate button
            }
        });
    }
});
</script>


</body>
</html>
