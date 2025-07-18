<?php
session_start(); // Start the session

// Database connection
$host = "localhost";
$username = "root";
$password = "";
$dbname = "documents";

$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the admin is logged in
if (!isset($_SESSION['hod_id'])) {
    header("Location: admin_dashboard.php");
    exit();
}

// Fetch students assigned to supervisors (exclude sup_id IS NULL or 0)
$sql = "SELECT s.id, s.matric, s.fullname, s.institution, s.email, s.phone_number, s.department, sp.fullname AS supervisor_name 
        FROM student s
        LEFT JOIN supervisors sp ON s.sup_id = sp.sup_id
        WHERE s.sup_id IS NOT NULL AND s.sup_id != ''";

$result = $conn->query($sql);

// Check for query errors
if (!$result) {
    die("Error fetching students: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List of Assigned Students</title>
    <link rel="stylesheet" href="../css/Admin_dashboard.css">
    <link rel="stylesheet" href="../css/assign_supervisor.css">
    <link rel="stylesheet" href="../css/view_student_supervisor.css">
    <style>
        .supervisor-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .supervisor-table th, .supervisor-table td {
            border: 5px solid royalblue;
            background: wheat;
            padding: 8px;
            text-align: left;
            font-family: Arial, Helvetica, sans-serif;
        }
        .supervisor-table th {
            background-color: rgb(25, 45, 226);
            color: white;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="menu">
        <a href="admin_dashboard.php">DASHBOARD</a>
        <a href="supervisor_signup.php">REGISTER NEW SUPERVISOR</a>
        <a href="admin_student_signup.php">REGISTER NEW STUDENT</a>
        <a href="admin_view_student_supervisor.php">VIEW STUDENT SUPERVISOR</a> <!-- Link to view_student_supervisor.php -->
        <a href="admin_terminate_user.php">TERMINATE USER</a>
        <a href="admin_view_student.php">REGISTERD STUDENT LIST</a>
        <a href="admin_assigned_and_unassigned.php">VIEW ASSIGNED STUDENTS</a> </div>

<?php
// Display success or error messages if set
if (isset($_SESSION['success_message'])) {
    echo '<div id="success-message" style="color: green; font-weight: bold;">' . $_SESSION['success_message'] . '</div>';
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    echo '<div id="error-message" style="color: green; font-weight: bold;">' . $_SESSION['error_message'] . '</div>';
    unset($_SESSION['error_message']);
}
?>
<script>
    // Hide messages after 2 seconds
    setTimeout(function() {
        var successMsg = document.getElementById('success-message');
        var errorMsg = document.getElementById('error-message');
        if (successMsg) successMsg.style.display = 'none';
        if (errorMsg) errorMsg.style.display = 'none';
    }, 1000);
</script>

<div>
    <h1>List of Assigned Students</h1>
    <table class="supervisor-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>MATRIC NUMBER</th>
                <th>FULL NAME</th>
                <th>INSTITUTION</th>
                <th>EMAIL</th>
                <th>PHONE NUMBER</th>
                <th>DEPARTMENT</th>
                <th>SUPERVISOR</th>
                <th>UNASSIGN</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row["id"]); ?></td>
                        <td><?php echo htmlspecialchars($row["matric"]); ?></td>
                        <td><?php echo htmlspecialchars($row["fullname"]); ?></td>
                        <td><?php echo htmlspecialchars($row["institution"]); ?></td>
                        <td><?php echo htmlspecialchars($row["email"]); ?></td>
                        <td><?php echo htmlspecialchars($row["phone_number"]); ?></td>
                        <td><?php echo htmlspecialchars($row["department"]); ?></td>
                        <td><?php echo htmlspecialchars($row["supervisor_name"] ?? 'N/A'); ?></td>
                        <td>
                         <form method="POST" action="admin_unassign_student.php" style="display:inline;">
                            <input type="hidden" name="student_id" value="<?php echo $row['id']; ?>">
                            
                          
                            <button type="submit">UNASSIGN</button>
                        </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9">No students are currently assigned to any supervisor.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
     <button onclick="location.reload();">REFRESH</button>
</div>

<?php $conn->close(); ?>
</body>
</html>
