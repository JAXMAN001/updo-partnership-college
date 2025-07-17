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
$sup_id = $_SESSION['sup_id']; // Supervisor's sup_id (string)


// Include database configuration
include 'config.php';

// Initialize output for table rows
$output = "<tr><td colspan='5'>No students are currently assigned to you.</td></tr>"; // Default message

// Use prepared statements for security
$sql = "SELECT matric, fullname, email, phone_number, department, institution, reason
        FROM student
        WHERE sup_id = ?";

$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("s", $sup_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        // Build the HTML output for table rows
        $output = "";
        while ($student = $result->fetch_assoc()) {
            $output .= "<tr>";
            $output .= "<td>" . htmlspecialchars($student['matric']) . "</td>";
            $output .= "<td>" . htmlspecialchars($student['fullname']) . "</td>";
            $output .= "<td>" . htmlspecialchars($student['email']) . "</td>";
            $output .= "<td>" . htmlspecialchars($student['phone_number']) . "</td>";
            $output .= "<td>" . htmlspecialchars($student['department']) . "</td>";
            $output .= "<td>" . htmlspecialchars($student['institution']) . "</td>";
            $output .= "<td>" . htmlspecialchars($student['reason']) . "</td>";
            
        }
    } else {
        $output = "<tr><td colspan='7'>No students are currently assigned to you.</td></tr>";
    }
    $stmt->close();
} else {
    // Handle statement preparation error
    error_log("Error preparing statement in supervisor_student_list.php: " . $conn->error);
    $output = "<tr><td colspan='7' style='color: red;'>Error retrieving student details. Please try again later.</td></tr>";
}

$conn->close();
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supervisor Dashboard - Student List</title> <!-- More specific title -->
    <link rel="stylesheet" href="../css/list.css">
    <style>
        .student-table {
            width: 98%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: antiquewhite;
        }
        .student-table th, .student-table td {
            border: 5px solid white;
            padding: 10px;
            text-align: center;
        
        }
        .student-table th {
            background-color: blue;
            color: white;
        }
        /* Style for the profile modal */
        .profile-modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1001; /* Ensure it's above other content */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgba(0,0,0,0.6); /* Darker overlay */
            display: flex; /* Use flexbox for centering */
            justify-content: center; /* Center horizontally */
            align-items: center; /* Center vertically */
        }

        /* Modal Content/Box */
        .profile-modal-content {
            background-color: wheat;
            padding: 30px;
            border: 1px solid #888;
            border-radius: 10px; /* Consistent radius */
            width: 90%; /* Responsive width */
            max-width: 450px; /* Limit max width */
            box-shadow: 0 4px 8px rgba(0,0,0,0.2); /* Add shadow */
            position: relative; /* Needed for close button positioning */
        }

        /* The Close Button */
        .close {
            position: absolute; /* Position relative to modal content */
            top: 10px;
            right: 15px;
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer; /* Add pointer cursor */
            border: none; /* Remove default border */
            background: none; /* Remove default background */
        }

        .close:hover,
        .close:focus {
            color: #333; /* Darker color on hover/focus */
            text-decoration: none;
        }
        #profile-content img { /* Style profile image in modal */
            display: block;
            margin: 15px auto;
            border-radius: 50%; /* Circular image */
            border: 3px solid #ddd;
        }
    </style>
</head>
<body>

<!-- Button to toggle the sidebar -->
<button class="toggle-btn" onclick="toggleSidebar()">☰</button>

<!-- Sidebar containing navigation menu -->
<div class="sidebar" id="sidebar">
    <h2>MENU</h2>
    <ul>
        <li><a href="supervisor_dashboard.php">DASHBOARD </a></li><br><br>
        <li>LIST OF STUDENT </li><br><br> <!-- Current page, no link needed -->
        <li><a href="supervisor_student_documents.php">STUDENT DOCUMENTS</a></li><br><br>
    </ul>
    <footer>
        <p>&copy; 2025 Upload Document (UP-DO)</p>
    </footer>
</div>

<!-- Main content container -->
<div class="container" id="container">
    <!-- Header section displaying supervisor's username and logout options -->
    <header>
        <!-- Use the display ID for the heading -->
        <h1>SUPERVISOR: <?php echo htmlspecialchars($supervisor_user_id); ?></h1>
        <button class="login-btn" onclick="loginAlert()">LOG-OUT</button> <!-- Simplified logout text -->
        <button onclick="location.reload();">REFRESH</button>

    </header>

    <!-- Heading for the list of students -->
    <h1 class="list">LIST OF STUDENTS ASSIGNED TO YOU</h1> <!-- Corrected spelling -->

    <!-- Table to display student information -->
    <table class="student-table">
        <thead>
            <tr>
                <th>MATRIC NUMBER</th>
                <th>FULLNAME</th>
                <th>EMAIL</th>
                <th>PHONE NUMBER</th>
                <th>DEPARTMENT</th>
                <th>INSTITUTION</th>
                <th>REASON</th>
                <th>PROFILE</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Regenerate the table rows to ensure the button appears for each student
            $host = "localhost";
            $username = "root";
            $password = "";
            $dbname = "documents";
            $conn = new mysqli($host, $username, $password, $dbname);
            $sup_id = $_SESSION['sup_id'];
            $sql = "SELECT matric, fullname, email, phone_number, department, institution, reason FROM student WHERE sup_id = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("s", $sup_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result && $result->num_rows > 0) {
                    while ($student = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($student['matric']) . "</td>";
                        echo "<td>" . htmlspecialchars($student['fullname']) . "</td>";
                        echo "<td>" . htmlspecialchars($student['email']) . "</td>";
                        echo "<td>" . htmlspecialchars($student['phone_number']) . "</td>";
                        echo "<td>" . htmlspecialchars($student['department']) . "</td>";
                        echo "<td>" . htmlspecialchars($student['institution']) . "</td>";
                        echo "<td>" . htmlspecialchars($student['reason']) . "</td>";
                        // Place the profile link in the PROFILE column for each student row
                        echo "<td><a class=\"profile-btn\" href=\"#\" onclick=\"openProfile('" . htmlspecialchars($student['matric']) . "'); return false;\">View Profile</a></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='8'>No students are currently assigned to you.</td></tr>";
                }
                $stmt->close();
            } else {
                echo "<tr><td colspan='8' style='color: red;'>Error retrieving student details. Please try again later.</td></tr>";
            }
            $conn->close();
            ?>
        </tbody>
    </table>

    <!-- The Modal -->
    <div id="profileModal" class="profile-modal">
        <!-- Modal content -->
        <div class="profile-modal-content">
            <!-- Use a button for the close symbol for better accessibility -->
            <button class="close" onclick="closeProfile()" aria-label="Close profile modal">&times;</button>
            <div id="profile-content">
                <!-- Student profile content will be loaded here -->
                <p>Loading profile...</p>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const container = document.getElementById('container');
        sidebar.classList.toggle('open');
        container.classList.toggle('shift');
    }

    function loginAlert() {
        // Consider a more standard logout confirmation
        if (confirm("Are you sure you want to log out?")) {
             // Redirect to a dedicated logout script if available
             // window.location.href = "supervisor_logout.php";
             // Or redirect to login page directly
             window.location.href = "student_login.php";
        }
    }

    function openProfile(matric) {
        var modal = document.getElementById("profileModal");
        var profileContent = document.getElementById("profile-content");
        profileContent.innerHTML = '<p>Loading profile...</p>'; // Show loading state
        modal.style.display = "flex"; // Use flex to show and center

        // Fetch student profile data using AJAX
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4) { // Request finished
                 if (this.status == 200) { // Request successful
                    profileContent.innerHTML = this.responseText;
                 } else {
                    // Handle errors (e.g., show an error message)
                    profileContent.innerHTML = '<p>Could not load profile. Please try again later.</p>';
                    console.error("AJAX Error: Status " + this.status);
                 }
            }
        };
        // Ensure the path to the PHP script is correct
        xhttp.open("GET", "supervisor_get_student_profile.php?matric=" + encodeURIComponent(matric), true);
        xhttp.send();
    }

    function closeProfile() {
        var modal = document.getElementById("profileModal");
        modal.style.display = "none";
    }

    // Close the modal if the user clicks outside of the modal content
    window.onclick = function(event) {
        var modal = document.getElementById("profileModal");
        // Check if the click target is the modal background itself
        if (event.target == modal) {
            closeProfile();
        }
    }
</script>

</body>
</html>
