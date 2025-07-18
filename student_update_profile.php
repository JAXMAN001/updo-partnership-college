<?php
include 'config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['matric'])) {
    header("Location: student_login.php");
    exit();
}

$matric = $_SESSION['matric'];

// Fetch user data
$user_sql = "SELECT * FROM student WHERE matric='$matric'";
$user_result = $conn->query($user_sql);

if ($user_result->num_rows > 0) {
    $user_data = $user_result->fetch_assoc();
} else {
    echo "User data not found.";
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone_number = $_POST['phone_number'];
    $department = $_POST['department'];
    $faculty = $_POST['faculty'];
    $email = $_POST['email'];

    // Check if the phone number already exists for another user
    $check_sql = "SELECT matric FROM student WHERE phone_number = '$phone_number' AND matric != '$matric'";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        // Phone number already exists for another user
        echo "<script>alert('This phone number is already in use. Please enter a different phone number.');</script>";
    } else {

    // Update user data in the database
    $update_sql = "UPDATE student SET phone_number='$phone_number', department='$department', faculty='$faculty', email='$email'  WHERE matric='$matric'";

    if ($conn->query($update_sql) === TRUE) {
        echo "Profile updated successfully";
        // Refresh user data
        $user_result = $conn->query($user_sql);
        $user_data = $user_result->fetch_assoc();

        // Redirect to profile page
        header("Location: student_profile.php");
        exit();
    } else {
        echo "Error updating profile: " . $conn->error;
    }
}
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STUDENT DASHOARD</title>
    <link rel="stylesheet" href="../css/home.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/update_profile.css">
    
    
</head>
<body>

<div class="sidebar">
        <img src="../uploads/<?php echo $user_data['profile_pic']; ?>" alt="Profile Picture" class="img">
            <div class="sidebar-header">
            <h2><?php echo $user_data['fullname']; ?></h2>      
            </div>
            <ul class="sidebar-menu">
                <li><a href="student_home.php"><i class="fas fa-home"></i>  HOME</a></li>
                <li><i class="fas fa-user"></i> Profile</li>
                <li><a href="student_upload.php"><i class="fas fa-upload"></i>  UPLOAD</a></li>
            </ul>
            <div class="sidebar-settings">


            <form id="logoutForm" action="student_logout.php" method="post">
                    <button type="button" onclick="confirmLogout()">Logout</button>
                </form>
            </div>
        </div>
        


        <script>
    function confirmLogout() {
        if (confirm("Are you sure you want to logout NIGGA?")) {
            document.getElementById('logoutForm').submit();
        }
    }
    </script>

    <div class="container">
        <h2>Update Profile</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            
        <label for="phone_number">PHONE NUMBER:</label>
            <input type="text" name="phone_number" id="phone_number" value="<?php echo htmlspecialchars($user_data['phone_number']); ?>"><br><br>

            <label for="department">DEPARTMENT:</label>
            <input type="text" name="department" id="department" value="<?php echo htmlspecialchars($user_data['department']); ?>"><br><br>

            <label for="faculty">FACULTY:</label>
            <input type="text" name="faculty" id="faculty" value="<?php echo htmlspecialchars($user_data['faculty']); ?>"><br><br>

            <label for="email">EMAIL ADDRESS:</label>
            <input type="text" name="email" id="email" value="<?php echo htmlspecialchars($user_data['email']); ?>"><br><br>

            <input type="submit" value="Update Profile">
        </form>
    </div>
</body>
</html>

<?php
$conn->close();
?>
