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

$student_data = null;
$supervisor_data = null;

if (isset($_POST['matric'])) {
    $matric = $_POST['matric'];

    // Fetch student data
    $student_sql = "SELECT * FROM student WHERE matric = ?";
    $stmt = $conn->prepare($student_sql);
    
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("s", $matric);
    $stmt->execute();
    $student_result = $stmt->get_result();

    if ($student_result->num_rows > 0) {
        $student_data = $student_result->fetch_assoc();
        $supervisor_id = $student_data['sup_id'];

        // Fetch supervisor data
        $supervisor_sql = "SELECT * FROM supervisors WHERE sup_id = ?";
        $stmt = $conn->prepare($supervisor_sql);

        if ($stmt === false) {
            die("Error preparing statement: " . $conn->error);
        }

        $stmt->bind_param("s", $supervisor_id);
        $stmt->execute();
        $supervisor_result = $stmt->get_result();

        if ($supervisor_result->num_rows > 0) {
            $supervisor_data = $supervisor_result->fetch_assoc();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Student Supervisor</title>
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

    <div class="container">
        <h2>View Student Supervisor</h2>
        <form method="post">
            <div class="input-group">
                <label>Enter Matric Number:</label>
                <input type="text" name="matric" required>
            </div>
            <button type="submit">View Supervisor</button>
        </form>

        <?php if ($student_data): ?>
            <h3>Student Information:</h3>
            <p><strong>Full Name:</strong> <?php echo htmlspecialchars($student_data['fullname']); ?></p>
            <p><strong>Matric Number:</strong> <?php echo htmlspecialchars($student_data['matric']); ?></p>
            <!-- Display other student information as needed -->
        <?php endif; ?>

        <?php if ($supervisor_data): ?>
            <h3>Supervisor Information:</h3>
            <p><strong>USER ID:</strong> <?php echo htmlspecialchars($supervisor_data['sup_id']); ?></p>
            <p><strong>NMAE:</strong> <?php echo htmlspecialchars($supervisor_data['fullname']); ?></p>
            <p><strong>PHONE NUMBER:</strong> <?php echo htmlspecialchars($supervisor_data['phone']); ?></p>
            <p><strong>EMAIL:</strong> <?php echo htmlspecialchars($supervisor_data['email']); ?></p>
            <p><strong>DEPARTMET:</strong> <?php echo htmlspecialchars($supervisor_data['department']); ?></p>
            <p><strong>FACULTY:</strong> <?php echo htmlspecialchars($supervisor_data['faculty']); ?></p>
            <!-- Display other supervisor information as needed -->
        <?php elseif ($student_data): ?>
            <p>No supervisor assigned to this student.</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php $conn->close(); ?>
