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
{$supervisor_user_id = $_SESSION['sup_id']; // Retrieve admin USER_ID from session
} else {
    // Redirect to login page if not logged in
    header("Location: student_login.php"); // Redirect to login page
    exit(); // Terminate script
}

// Retrieve supervisor information from session
$sup_id = $_SESSION['sup_id'];



// Include database configuration
include 'config.php'; // Include the database configuration file

// Fetch students assigned to the supervisor from the database
$sql = "SELECT * FROM student WHERE sup_id = '$sup_id'"; // SQL query to select students assigned to the supervisor
$result = $conn->query($sql); // Execute the query

$students = []; // Initialize an empty array to store the students
if ($result && $result->num_rows > 0) { // If students are found
    while ($row = $result->fetch_assoc()) { // Loop through each student
        $students[] = $row; // Add the student to the array
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supervisor - Student Documents</title>
    <!-- Title of the page -->
    <link rel="stylesheet" href="../css/list.css">
    <!-- Link to the CSS stylesheet -->
    <style>
        .document-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: wheat;
        }
        .document-table th, .document-table td {
            border: 5px solid white;
            padding: 10px;
            text-align: center;
        }
        .document-table th {
            background-color: blue;
            color: white;
        }
        /* Style for the iframe */
        #statusFrame {
            width: 100%;
            height: 200px; /* Adjust height as needed */
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>

<button class="toggle-btn" onclick="toggleSidebar()">☰</button>
<!-- Button to toggle the sidebar -->

<div class="sidebar" id="sidebar">
    <!-- Sidebar content -->
    <h2>MENU</h2>
    <!-- Sidebar menu -->
    <ul>
        <li><a href="supervisor_dashboard.php">DASHBOARD</a></li><br><br>
        <li><a href="supervisor_student_list.php">LIST OF STUDENT</a></li><br><br>
        <li>STUDENT DOCUMENTS</li><br><br>
    </ul>
    <footer>
        <p>&copy; 2025 Upload Document (UP-DO)</p>
    </footer>
</div>

<div class="container" id="container">
    <!-- Main content container -->
    <header>
    <!-- Header section -->
    <h1>SUPERVISOR: <?php echo htmlspecialchars($supervisor_user_id); ?></h1>
        <!-- Supervisor's username -->
        <button class="login-btn" onclick="loginAlert()">LOG-OUT</button>
        <!-- Button to log out to the student dashboard -->
         <button onclick="location.reload();">REFRESH</button>
        <!-- Button to reload the page -->
    </header>

    <h1 class="list">STUDENT DOCUMENTS</h1>
    <!-- Page title -->

    <table class="document-table">
        <!-- Table to display student documents -->
        <thead>
            <tr>
                <th>MATRIC NUMBER</th>
                <!-- Table header for matric number -->
                <th>FULL NAME</th>
                <!-- Table header for full name -->
                <th>DOCUMENT</th>
                <!-- Table header for document -->
                <th>STATUS</th>
                <!-- Table header for status -->
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $student): ?>
                <!-- Loop through each student -->
                <?php
                // Fetch the student's uploaded documents
                $student_matric = $student['matric']; // Get the student's matric number
                $document_sql = "SELECT * FROM document WHERE matric = '$student_matric'"; // SQL query to select documents based on the matric number
                $document_result = $conn->query($document_sql); // Execute the query

                if ($document_result && $document_result->num_rows > 0) { // If documents are found
                    while ($document_data = $document_result->fetch_assoc()) { // Loop through each document
                        $document_id = $document_data['id']; // Get the document ID
                        $document_path = $document_data['path']; // Get the document path
                        $document_name = $document_data['name']; // Get the document name
                        // Check if the 'status' key exists before accessing it
                        $document_status = isset($document_data['status']) ? $document_data['status'] : 'pending'; // Get the document status, default to 'pending'
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['matric']); ?></td>
                            <!-- Display the student's matric number -->
                            <td><?php echo htmlspecialchars($student['fullname']); ?></td>
                            <!-- Display the student's full name -->
                            <td><a href="<?php echo htmlspecialchars($document_path); ?>" target="_blank">
                                        <?php echo htmlspecialchars($document_name); ?></a></td>
                            <!-- Display the document name as a link -->
                            <td>
                                <iframe id="statusFrame" src="supervisor_student_status.php?document_id=<?php echo $document_id; ?>"></iframe>
                                <!-- Iframe to display the document status -->
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($student['matric']); ?></td>
                        <!-- Display the student's matric number -->
                        <td><?php echo htmlspecialchars($student['fullname']); ?></td>
                        <!-- Display the student's full name -->
                        <td>No documents uploaded</td>
                        <!-- Display a message if no documents are uploaded -->
                        <td></td>
                        <!-- Empty cell for status -->
                    </tr>
                    <?php
                }
            endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    function toggleSidebar() { // Function to toggle the sidebar
        const sidebar = document.getElementById('sidebar'); // Get the sidebar element
        const container = document.getElementById('container'); // Get the container element
        sidebar.classList.toggle('open'); // Toggle the 'open' class on the sidebar
        container.classList.toggle('shift'); // Toggle the 'shift' class on the container
    }

    function loginAlert() { // Function to display a login alert
        alert("YOU ARE LOGGING IN TO STUDENT DASHBOARD SIR!"); // Display an alert message
        window.location.href = "student_login.php"; // Redirect to the student dashboard
    }
</script>

</body>
</html>
<?php $conn->close(); ?>