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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STUDENT DASHOARD</title>
    <link rel="stylesheet" href="../css/home.css">
    <link rel="stylesheet" href="../css/sidebar.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Include the sidebar -->
        <div class="sidebar">
        <img src="../uploads/<?php echo $profile_pic; ?>" alt="Profile Picture" class="img">
            <div class="sidebar-header">
            <h2><?php echo $user_data['fullname']; ?></h2>      
            </div>
            <ul class="sidebar-menu">
                <li><a href="student_home.php"><i class="fas fa-home"></i>  HOME</a></li>
                <li><i class="fas fa-user"></i> Profile</li>
                <li><a href="student_upload.php"><i class="fas fa-upload"></i>  UPLOAD</a></li>
                <button onclick="location.reload();">REFRESH</button>           
              
            </ul>
            <div class="sidebar-settings">


            <form id="logoutForm" action="student_logout.php" method="post">
                    <button type="button" onclick="confirmLogout()">Logout</button>
                </form>
            </div>
        </div>
        <!-- Main content area -->
        <div class="main-content">
           
            <div class="profile-section">
              
                
                <form action="student_upload_profile_pic.php" method="post" enctype="multipart/form-data">
                    <label for="profile_pic">Change Profile Picture:</label>
                    <input type="file" name="profile_pic" id="profile_pic">
                    <input type="checkbox" id="overwrite" name="overwrite" value="yes">
                    <label for="overwrite"></label>
                    <button type="submit" name="submit">Upload</button>          
                </form>

                <img id="profilePreview" src="#" alt="Profile Picture Preview" style="max-width:100px; max-height:100px; display:none;">
                <div id="profileErrorMessage" style="color: red;"></div>

                <p>INSTITUTION NAME: </p>         <?php echo $user_data['institution']; ?>
                 <p>DEPARTMENT: </p>               <?php echo $user_data['department']; ?>
                  <p>FACULTY: </p>               <?php echo $user_data['faculty']; ?>
                <p>MATRIC NUMBER: </p>            <?php echo $user_data['matric']; ?>
                <p>EMAIL ADDRESS: </p>            <?php echo $user_data['email']; ?>
                <p>PHONE NUMBER: </p>             <?php echo $user_data['phone_number']; ?>
               
                
                    <button type="button">
                        <a href="student_update_profile.php">
                            UPDATE PROFILE
                        </a>
                    </button>
                
                <!-- Add more user-specific information here -->

                
            </div>
        </div>
    </div>
    <script>
    function confirmLogout() {
        if (confirm("Are you sure you want to logout NIGGA?")) {
            document.getElementById('logoutForm').submit();
        }
    }

    const profilePicInput = document.getElementById('profile_pic');
    const profilePreview = document.getElementById('profilePreview');
    const profileErrorMessage = document.getElementById('profileErrorMessage');

    profilePicInput.addEventListener('change', function() {
        const file = this.files[0];

        if (file) {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();

                reader.addEventListener('load', function() {
                    profilePreview.src = this.result;
                    profilePreview.style.display = 'block';
                    profileErrorMessage.textContent = ''; // Clear any previous error messages
                });

                reader.readAsDataURL(file);
            } else {
                profileErrorMessage.textContent = 'Please select an image file.';
                profilePreview.src = '#';
                profilePreview.style.display = 'none';
            }
        } else {
            profilePreview.src = '#';
            profilePreview.style.display = 'none';
            profileErrorMessage.textContent = '';
        }
    });

    const uploadForm = document.querySelector('form[action="student_upload_profile_pic.php"]');
    uploadForm.addEventListener('submit', function(event) {
        if (!profilePicInput.files || profilePicInput.files.length === 0) {
            event.preventDefault(); // Prevent form submission
            alert('Please select an image to upload.');
        }
    });
    </script>
</body>
</html>
