<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST['fullname'];
    $matric = $_POST['matric'];
    $institution = $_POST['institution'];
    $faculty = $_POST['faculty'];
    $department = $_POST['department'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $date_of_birth = $_POST['date_of_birth'];
    $source = isset($_POST['source']) ? $_POST['source'] : 'student'; // Check for the source

    // Determine the redirect page for errors
    $error_redirect_page = ($source === 'admin') ? 'admin_student_signup.php' : 'student_signup.php';

    // Email validation
    $allowedDomains = ['@gmail.com', '@yahoo.com', '@icloud.com'];
    $isValidDomain = false;
    foreach ($allowedDomains as $domain) {
        if (strpos($email, $domain) !== false && strpos($email, $domain) === strlen($email) - strlen($domain)) {
            $isValidDomain = true;
            break;
        }
    }

    if (!$isValidDomain) {
        echo "<script>
                alert('Invalid email domain. Only gmail, yahoo and icloud  are allowed.');
                window.location.href = '$error_redirect_page';
              </script>";
        exit;
    }

    // Check if email or matric number already exists
    // Use prepared statement to prevent SQL injection
    $check_sql = "SELECT * FROM student WHERE email=? OR matric=?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("ss", $email, $matric);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>
                alert('EMAIL OR MATRIC NUMBER ALREADY EXISTS');
                window.location.href = '$error_redirect_page';
              </script>";
    } else {
        // Use prepared statement to prevent SQL injection
        $sql = "INSERT INTO student (fullname, matric, institution, department, faculty, email, date_of_birth, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssss", $fullname, $matric, $institution, $department, $faculty, $email, $date_of_birth, $password);

        if ($stmt->execute() === TRUE) {
            // Determine success redirect page
            $success_redirect_page = ($source === 'admin') ? 'admin_student_signup.php' : 'student_login.php';
            // Remove email verification process
            echo "<script>
                    alert('REGISTER SUCCESSFUL.');
                    window.location.href = '$success_redirect_page';
                  </script>";
        } else {
            echo "Error: " . $stmt->error;
        }
    }

    $conn->close();
}
?>
