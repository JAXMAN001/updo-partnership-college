<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "documents";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the profile_pic column exists, if not, add it
$check_column_sql = "SHOW COLUMNS FROM student LIKE 'profile_pic'";
$result = $conn->query($check_column_sql);
if ($result){
if ($result->num_rows == 0) {
    $alter_table_sql = "ALTER TABLE student ADD profile_pic VARCHAR(255)";
    if ($conn->query($alter_table_sql) !== TRUE) {
        echo "Error adding profile_pic column: " . $conn->error;
    }
}
}

// --- Self-healing: Ensure online_payment column exists in user tables ---
$tables_to_check = ['student', 'supervisors', 'admin'];
foreach ($tables_to_check as $table) {
    // Check if table exists first to avoid errors
    $check_table_sql = "SHOW TABLES LIKE '$table'";
    $table_result = $conn->query($check_table_sql);
    if ($table_result && $table_result->num_rows > 0) {
        $check_column_sql = "SHOW COLUMNS FROM `$table` LIKE 'online_payment'";
        $column_result = $conn->query($check_column_sql);
        if ($column_result && $column_result->num_rows == 0) {
            // Column does not exist, so add it
            $alter_sql = "ALTER TABLE `$table` ADD `online_payment` VARCHAR(255) NULL DEFAULT NULL";
            if ($conn->query($alter_sql) !== TRUE) {
                // Log error but don't kill the script
                error_log("Failed to add online_payment column to $table: " . $conn->error);
            }
        }
    }
}

// Update to match the fields in the signup file
$fullname = isset($_POST['fullname']) ? $_POST['fullname'] : '';
$matric = isset($_POST['matric']) ? $_POST['matric'] : '';
$institution = isset($_POST['institution']) ? $_POST['institution'] : '';
$email = isset($_POST['email']) ? $_POST['email'] : '';
$email_verified = 0; // Default value for email verification status
?>
