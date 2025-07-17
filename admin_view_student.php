<?php
session_start(); // Start the session

ini_set('display_errors', 1); // Display errors
ini_set('display_startup_errors', 1); // Display startup errors
error_reporting(E_ALL); // Report all errors

$host = "localhost"; // Database host
$username = "root"; // Database username
$password = ""; // Database password
$dbname = "documents"; // Database name

// Create a database connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error); // If connection fails, terminate script and display error
}

// Check if the admin is logged in
if (isset($_SESSION['hod_id'])) {
    $admin_username = $_SESSION['hod_id']; // Retrieve admin username from session

    // Fetch admin details
    $admin_sql = "SELECT institution, department, faculty FROM admin WHERE hod_id = '$admin_username'";
    $admin_result = $conn->query($admin_sql);
    $admin = $admin_result->fetch_assoc();

    $admin_institution = $admin['institution'];
    $admin_department = $admin['department'];
    $admin_faculty = $admin['faculty'];

} else {
    // Redirect to login page if not logged in
    header("Location: admin_dashboard.php"); // Redirect to dashboard page
    exit(); // Terminate script
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List of Students</title>
    <link rel="stylesheet" href="../css/Admin_dashboard.css">
    <link rel="stylesheet" href="../css/assign_supervisor.css">
    <link rel="stylesheet" href="../css/view_student_supervisor.css">
    <style>
        .supervisor-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            align-items: center;
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
    <a href="admin_view_student_supervisor.php">VIEW STUDENT SUPERVISOR</a>
    <a href="admin_terminate_user.php">TERMINATE USER</a>
    <a href="admin_view_student.php">REGISTERED STUDENT LIST</a>
    <a href="admin_assigned_and_unassigned.php">VIEW ASSIGNED STUDENTS</a> 
</div>

<div>
    <h1>REGISTERED STUDENT LIST AND ASSIGN SESSION</h1>
    
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
                <th>FACULTY</th>
                <th>ACTION</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Fetch students where institution, department, and faculty match the admin's
            $sql = "SELECT * FROM student 
                    WHERE institution = '$admin_institution' 
                    AND department = '$admin_department' 
                    AND faculty = '$admin_faculty'
                    AND (sup_id IS NULL OR sup_id = '')"; // Only show unassigned students
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $student_id = $row["id"];
                    $matric_number = $row["matric"];
                    $fullname = $row["fullname"];
                    $institution = $row["institution"];
                    $email = $row["email"];
                    $phone = $row["phone_number"];
                    $department = $row["department"];
                    $faculty = $row["faculty"];
                    
                    echo "<tr>";
                    echo "<td>" . $student_id . "</td>";
                    echo "<td>" . $matric_number . "</td>";
                    echo "<td>" . $fullname . "</td>";
                    echo "<td>" . $institution . "</td>";
                    echo "<td>" . $email . "</td>";
                    echo "<td>" . $phone . "</td>";
                    echo "<td>" . $department . "</td>";
                    echo "<td>" . $faculty . "</td>";
                    echo "<td>
                            <button class='assign-button' onclick='openAssignModal(" . $student_id . ")'>ASSIGN SUPERVISOR</button>
                        </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='9'>No registered students found matching your criteria.</td></tr>";
            }
            ?>
        </tbody>
    </table>
    <button onclick="location.reload();">REFRESH</button>
</div>

<!-- Assign Supervisor Modal -->
<div id="assignModal" 
style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background-color: wheat; padding: 20px; border: 2px solid blue; z-index: 1000; border-radius: 20px">
    <h2>ASSIGN SUPERVISOR TO THE STUDENT</h2>
    <form id="assignForm" method="post" action="admin_assign_supervisor.php" onsubmit="return validateSupervisorAssignment();">
        <input type="hidden" id="studentId" name="student_id">
        <label for="supervisorSup_id">SUPERVISOR USER ID:</label>
        <input type="text" id="supervisorSup_id" name="sup_id" required onblur="getSupervisorDetails()">
        <div id="supervisorDetails" style="margin-top: 10px;"></div>
        <div id="supervisorValidationMsg" style="margin-top:10px;color:red;font-weight:bold;"></div>
        <button type="submit">ASSIGN</button>
        <button type="button" onclick="closeAssignModal()">CANCEL</button>
    </form>
</div>

<script>
    // Function to open the assign supervisor modal
    function openAssignModal(studentId) {
        document.getElementById('studentId').value = studentId; // Set the student ID in the hidden input field
        document.getElementById('assignModal').style.display = "block"; // Display the modal
    }

    // Function to close the assign supervisor modal
    function closeAssignModal() {
        document.getElementById('assignModal').style.display = "none"; // Hide the modal
    }

    // Function to get supervisor details and validate department/faculty/institution
    function getSupervisorDetails() {
        var supId = document.getElementById('supervisorSup_id').value;
        var studentId = document.getElementById('studentId').value;
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                var resp = {};
                try { resp = JSON.parse(this.responseText); } catch(e) {}
                var detailsDiv = document.getElementById('supervisorDetails');
                var validationDiv = document.getElementById('supervisorValidationMsg');
                detailsDiv.innerHTML = '';
                validationDiv.innerHTML = '';
                if (resp.status === 'not_found') {
                    validationDiv.innerHTML = "Supervisor not found.";
                } else if (resp.status === 'mismatch') {
                    validationDiv.innerHTML = "This supervisor does not belong to the same institution, department, or faculty as the student.";
                } else if (resp.status === 'ok') {
                    detailsDiv.innerHTML = "<b>Name:</b> " + resp.fullname + "<br><b>Email:</b> " + resp.email;
                }
            }
        };
        xhttp.open("GET", "admin_get_supervisor_details.php?sup_id=" + encodeURIComponent(supId) + "&student_id=" + encodeURIComponent(studentId), true);
        xhttp.send();
    }

    // Prevent form submission if validation fails
    function validateSupervisorAssignment() {
        var validationMsg = document.getElementById('supervisorValidationMsg').innerText;
        if (validationMsg && validationMsg.length > 0) {
            alert(validationMsg);
            return false;
        }
        return true;
    }
</script>
<?php
$conn->close(); // Close the database connection
?>