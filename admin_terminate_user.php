<?php
session_start();

// Security check: Ensure admin (HOD) is logged in
if (!isset($_SESSION['hod_id'])) {
    header("Location: student_login.php");
    exit();
}

$host = "localhost";
$username = "root";
$password = "";
$dbname = "documents";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch admin details
$admin_institution = $admin_department = $admin_faculty = '';
if (isset($_SESSION['hod_id'])) {
    $admin_username = $_SESSION['hod_id'];
    $admin_sql = "SELECT institution, department, faculty FROM admin WHERE hod_id = '$admin_username'";
    $admin_result = $conn->query($admin_sql);
    $admin = $admin_result->fetch_assoc();
    $admin_institution = $admin['institution'];
    $admin_department = $admin['department'];
    $admin_faculty = $admin['faculty'];
}

$user_data = null;
$user_type = null;
$matric_or_sup_id_or_hod_id = '';

if (isset($_POST['matric_or_sup_id_or_hod_id'])) {
    $matric_or_sup_id_or_hod_id = $_POST['matric_or_sup_id_or_hod_id'];

    // Check student
    $sql_updo = "SELECT * FROM student WHERE matric = '$matric_or_sup_id_or_hod_id'";
    $result_updo = $conn->query($sql_updo);

    if ($result_updo && $result_updo->num_rows > 0) {
        $user_data = $result_updo->fetch_assoc();
        $user_type = 'matric';
        // Check alignment
        if (
            $user_data['institution'] !== $admin_institution ||
            $user_data['department'] !== $admin_department ||
            $user_data['faculty'] !== $admin_faculty
        ) {
            echo "<script>alert('You can only terminate students in your institution, department, and faculty.');</script>";
            $user_data = null;
            $user_type = null;
        }
    } else {
        // Check supervisor
        $sql_supervisor = "SELECT * FROM supervisors WHERE sup_id = '$matric_or_sup_id_or_hod_id'";
        $result_supervisor = $conn->query($sql_supervisor);

        if ($result_supervisor && $result_supervisor->num_rows > 0) {
            $user_data = $result_supervisor->fetch_assoc();
            $user_type = 'sup_id';
            // Check alignment
            if (
                $user_data['institution'] !== $admin_institution ||
                $user_data['department'] !== $admin_department ||
                $user_data['faculty'] !== $admin_faculty
            ) {
                echo "<script>alert('You can only terminate supervisors in your institution, department, and faculty.');</script>";
                $user_data = null;
                $user_type = null;
            }
        } else {
            echo "<script>alert('No user found with that input.');</script>";
        }
    }
}

if (isset($_POST['confirm_terminate']) && isset($_POST['user_type'])) {
    $identifier = $_POST['confirm_terminate'];
    $user_type = $_POST['user_type'];

    if ($user_type === 'matric') {
        $sql = "DELETE FROM student WHERE matric = '$identifier'";
    } elseif ($user_type === 'sup_id') {
        $sql = "DELETE FROM supervisors WHERE sup_id = '$identifier'";
    } else {
        echo "<script>alert('Invalid user type.');</script>";
        exit;
    }

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('User terminated successfully.');</script>";
        $user_data = null;
    } else {
        echo "<script>alert('Error terminating user: " . $conn->error . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terminate User</title>
    <link rel="stylesheet" href="../css/Admin_dashboard.css">
    <link rel="stylesheet" href="../css/terminate_user.css">
</head>
<body>
<div class="menu">
        <a href="admin_dashboard.php">DASHBOARD</a>
        <a href="supervisor_signup.php">REGISTER NEW SUPERVISOR</a>
        <a href="admin_student_signup.php">REGISTER NEW STUDENT</a>
        <a href="admin_view_student_supervisor.php">VIEW STUDENT SUPERVISOR</a> <!-- Link to view_student_supervisor.php -->
        <a href="admin_terminate_user.php">TERMINATE USER</a>
        <a href="admin_view_student.php">REGISTERD STUDENT LIST</a>
        <a href="admin_assigned_and_unassigned.php">VIEW ASSIGNED STUDENTS</a> 

    </div>
    <div class="container4">
    <title>SUPER ADMIN DASHBOARD</title>
     <button onclick="location.reload();">REFRESH</button>
        <form method="post">
            <label for="matric_or_sup_id_or_hod_id">ENTER MATRIC NUMBER or SUP ID:</label><br>
            <input type="text" id="matric_or_sup_id_or_hod_id" name="matric_or_sup_id_or_hod_id" required><br><br>
            <input type="submit" value="View User">
        </form>

        <?php if ($user_data !== null): ?>
            <h3>User Information:</h3>
            <?php if ($user_type === 'matric'): ?>
                <p><strong>Full Name:</strong> <?php echo htmlspecialchars($user_data['fullname']); ?></p>
                <p><strong>Matric Number:</strong> <?php echo htmlspecialchars($user_data['matric']); ?></p>
                <p><strong>Department:</strong> <?php echo htmlspecialchars($user_data['department']); ?></p>
            <?php elseif ($user_type === 'sup_id'): ?>
                <p><strong>USERNAME:</strong> <?php echo htmlspecialchars($user_data['fullname']); ?></p>
                <p><strong>PHONE NUMBER:</strong> <?php echo htmlspecialchars($user_data['phone']); ?></p>
                <p><strong>EMAIL:</strong> <?php echo htmlspecialchars($user_data['email']); ?></p>
                <p><strong>USER ID:</strong> <?php echo htmlspecialchars($user_data['sup_id']); ?></p>
                <p><strong>DEPARTMENT:</strong> <?php echo htmlspecialchars($user_data['department']); ?></p>
                <p><strong>FACULTY:</strong> <?php echo htmlspecialchars($user_data['faculty']); ?></p>
            <?php endif; ?>

            <form method="post">
                <input type="hidden" name="confirm_terminate" value="<?php echo htmlspecialchars($matric_or_sup_id_or_hod_id); ?>">
                <input type="hidden" name="user_type" value="<?php echo htmlspecialchars($user_type); ?>">
                <input type="submit" value="Confirm Terminate">
            </form>
        <?php endif; ?>
    </div>
    
</body>
</html>

<?php
$conn->close();
?>
