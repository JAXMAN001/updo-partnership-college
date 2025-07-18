<?php
include 'config.php';

// Start session
session_start();

// Check if accessed via localhost
/*if ($_SERVER['REMOTE_ADDR'] == '127.0.0.1' || $_SERVER['REMOTE_ADDR'] == '::1') {
          header("Location: ../form/login.php");
          exit();
      }*/
      
// Check if user is logged in
if (!isset($_SESSION['matric'])) {
    header("Location: student_login.php");
    exit();
}

// Fetch user data
$matric = $_SESSION['matric'];
$user_sql = "SELECT * FROM student WHERE matric='$matric'";
$user_result = $conn->query($user_sql);

if ($user_result->num_rows > 0) {
    $user_data = $user_result->fetch_assoc();
} else {
    echo "User data not found.";
    exit();
}

// Fetch profile picture
$profile_pic = !empty($user_data['profile_pic']) ? $user_data['profile_pic'] : 'default.jpg';

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STUDENT DASHOARD</title>
    <link rel="stylesheet" href="../css/home.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/content.css">
    <link rel="stylesheet" href="../css/idcard.css">
    
</head>
<body>
        <!-- Include the sidebar -->
        <div class="sidebar">
        <img src="../uploads/<?php echo $profile_pic; ?>" alt="Profile Picture" class="img">
            <div class="sidebar-header">
            <h2><?php echo $user_data['fullname']; ?></h2>      
            </div>
            <ul class="sidebar-menu">
                <li><i class="fas fa-home"></i>Home</li>
                <li><a href="student_profile.php"><i class="fas fa-user"></i>PROFILE</a></li>
                <li><a href="student_upload.php"><i class="fas fa-upload"></i> UPLOAD</a></li>
                <button onclick="location.reload();">REFRESH</button>
              
                
                
            </ul>
            <div class="sidebar-settings">


            <form id="logoutForm" action="student_logout.php" method="post">
                    <button type="button" onclick="confirmLogout()">Logout</button>
                </form>
            </div>
        </div>
  


    
    <div class="hero">
        <h1>DOCUMENT STORAGE</h1>
        <p>We will cover important considerations such as choosing the right storage containers,
            determining a suitable storage location, and ensuring proper document protection. By following these steps,
            you can maintain the security and accessibility of your documents while minimizing clutter and confusion.</p>
        <div class="buttons">
            <a href="#">LIKE IT</a>
            <a href="#">APPRECIATE IT</a>
        </div>
        
    </div>

    <p class="paragraph">Created by on of the ACCOUNTING STUDENT in FEDERAL UNIVERSITY DUTSINMA</p>

    

    <div class="card">
        <img src="sadiq.png" alt="Profile Photo">
        <h2>ABUBAKAR SADIQ MUTAKHA</h2>
        <p>SOFTWEAR DEVELOPER</p>
        <h3>A student of Computer department at <br>
        FEDERAL UNIVERSITY DUTSINMA
        </h3>
    </div>

    <div class="card1">
        <img src="sag.png" alt="Profile Photo">
        <h2>SAGIRU GARBA </h2>
        <p>WEB-DEVELOPER</p>
        <h3>A student of Computer department at <br>
        FEDERAL UNIVERSITY DUTSINMA
        </h3>
    </div>

    <div class="card2">
        <img src="suhaib.png" alt="Profile Photo">
        <h2>SUHAIB LAWAL</h2>
        <p>PROGRAMMER</p>
        <h3>A student of Computer department at <br>
        FEDERAL UNIVERSITY DUTSINMA
        </h3>
    </div>

    <div class="card3">
        <img src="maryam.png" alt="Profile Photo">
        <h2>SALMA, HAUWA, MARYAM</h2>
        <p>SOFTWEAR DEVELOPERS</p>
        <h3>A student of Computer department at <br>
        FEDERAL UNIVERSITY DUTSINMA
        </h3>
    </div>

    <div class="card4">
        <img src="abdul.png" alt="Profile Photo">
        <h2>ABDULLAHI ABDULRASHEED </h2>
        <p>PROGRAMMER</p>
    </div>

    <div class="card5">
        <img src="bilal.png" alt="Profile Photo">
        <h2>BILAL MUHAMMED SHUAIBU</h2>
        <p>WEB-DEVELOPER</p>
        <h3>A student of Accounting department at <br>
        FEDERAL UNIVERSITY DUTSINMA
        </h3>
    </div>

    <script>
        var images = [
            'document.jpg',
            'document2.jpg',
            '../css/document3.jpg'
        ];
        var hero = document.querySelector('.hero');
        var currentImageIndex = 0;

        function changeBackground() {
            // Remove any existing animation classes
            hero.classList.remove('slide-down', 'slide-up', 'slide-left');

            // Apply animation class based on the image index
            if (currentImageIndex === 0) {
                hero.classList.add('slide-down');
            } else if (currentImageIndex === 1) {
                hero.classList.add('slide-up');
            } else if (currentImageIndex === 2) {
                hero.classList.add('slide-left');
            }

            hero.style.backgroundImage = 'url(' + images[currentImageIndex] + ')';
            hero.style.backgroundSize = 'cover';
            hero.style.backgroundPosition = 'center';

            currentImageIndex = (currentImageIndex + 1) % images.length;
        }

        setInterval(changeBackground, 3000);
    </script>

    <script>
    function confirmLogout() {
        if (confirm("Are you sure you want to logout NIGGA?")) {
            document.getElementById('logoutForm').submit();
        }
    }
    </script>
   
</body>
</html>
