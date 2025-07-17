<?php
session_start(); // Start the session to manage user login state

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

// Check if the admin is logged in and the username is set in the session
if (isset($_SESSION['sup_id'])) 
{$admin_user_id = $_SESSION['sup_id']; // Retrieve admin USER_ID from session
} else {
    // Redirect to login page if not logged in
    header("Location: student_login.php"); // Redirect to login page
    exit(); // Terminate script
}
// Retrieve supervisor information from session variables
$supervisor_sup_id = $_SESSION['sup_id']; // Supervisor's sup_id (string)

// Count students assigned to this supervisor using sup_id (string)
$sql_mine = "SELECT COUNT(*) AS total_mine FROM student WHERE sup_id = ?";
$stmt_mine = $conn->prepare($sql_mine);

$stmt_mine->bind_param("s", $supervisor_sup_id);
$stmt_mine->execute();
$result_mine = $stmt_mine->get_result();
if ($result_mine && $result_mine->num_rows > 0) {
    $row_mine = $result_mine->fetch_assoc();
    $total_mine = $row_mine['total_mine'];
    
} else {
    $total_mine = 0;
}

 // Display the total number of students for the current supervisor ID
 ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supervisor Dashboard</title>
    <link rel="stylesheet" href="../css/list.css">
</head>
<body>

<button class="toggle-btn" onclick="toggleSidebar()">☰</button>

<div class="sidebar" id="sidebar">
    <h2>MENU</h2>
    <ul>
        <li>DASHBOARD   </li><br><br>
        <li><a href="supervisor_student_list.php">LIST OF STUDENT</a></li><br><br>
        <li><a href="supervisor_student_documents.php">STUDENT DOCUMENTS</a></li><br><br>
    </ul>
    <footer>
        <p>&copy; 2025 Upload Document (UP-DO)</p>
    </footer>
</div>

<div class="container" id="container">
    <header>
        <h1>SUPERVISOR: <?php echo htmlspecialchars($admin_user_id); ?></h1>
        <button class="login-btn" onclick="loginAlert()">LOG-OUT</button>
        <button onclick="location.reload();">REFRESH</button>
    </header>

    <div class="dashboard">

    

        <div class="card">
            <h3>TOTAL STUDENT ASSIGN TO ME</h3>
            <p><?php echo htmlspecialchars($total_mine); ?></p>
        </div>
        
    </div>
</div>

<script>
    // Function to toggle the sidebar's visibility and shift the container
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const container = document.getElementById('container');
        sidebar.classList.toggle('open');
        container.classList.toggle('shift');
    }

    // Function to display an alert message and redirect to the student dashboard
    function loginAlert() {
        alert("YOU ARE LOGGING IN TO STUDENT DASHBOARD SIR!");
        window.location.href = "student_login.php";
    }
</script>

</body>
</html>
<?php 
// Close the database connection to free up resources
$conn->close(); 
?>