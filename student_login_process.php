<?php
include 'config.php';

// Start session
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $matric = $_POST['matric'];
    $password = $_POST['password'];

    // Sanitize input to prevent SQL injection
    $matric = mysqli_real_escape_string($conn, $matric);

    // Check if matric number exists in students table
    $check_sql = "SELECT * FROM student WHERE matric = '$matric'";
    $result = $conn->query($check_sql);

    if ($result) {
        if ($result->num_rows > 0) {
            $student = $result->fetch_assoc();
            // Check for suspension
            if (isset($student['reason']) && strtolower($student['reason']) === 'suspended') {
                // Set session for suspended user to handle receipt upload
                $_SESSION['matric'] = $student['matric'];
                echo "<script>
                        alert('Your account is suspended.');
                        window.location.href = 'suspension_dashboard.php';
                      </script>";
                exit();
            }
            // Verify password
            if ($password == $student['password']) {

                // Set session variables to remember the student's login
               
                $_SESSION['password'] = $student['password'];
                // Store matric number in session
                $_SESSION['matric'] = $matric;
                $_SESSION['user_type'] = 'student'; // Store user type

                // Redirect to student dashboard
                echo "<script>
                        alert('Student login successful!');
                        window.location.href = 'student_home.php';
                      </script>";
            } else {
                echo "<script>
                        alert('Invalid matric number or password.');
                        window.location.href = 'student_login.php';
                      </script>";
            }
        } 
        // SUPERVISOR  TABLE PANEL FOR LOGIN                // SUPERVISOR  TABLE PANEL FOR LOGIN
        else {
            // If student not found, check supervisors table
            $check_sql = "SELECT * FROM supervisors WHERE sup_id = '$matric'";
            $result = $conn->query($check_sql);

            if ($result) {
                if ($result->num_rows > 0) {
                    $supervisor = $result->fetch_assoc();
                    // Check for suspension
                    if (isset($supervisor['reason']) && strtolower($supervisor['reason']) === 'suspended') {
                        // Set session for suspended user to handle receipt upload
                        $_SESSION['sup_id'] = $supervisor['sup_id'];
                        echo "<script>
                                alert('Your account is suspended.');
                                window.location.href = 'suspension_dashboard.php';
                              </script>";
                        exit();
                    }
                    // Verify password
                    if ($password == $supervisor['password']) {

                        // Set session variables to remember the supervisor's login
                        
                       
                        $_SESSION['password'] = $supervisor['password'];
                         // Store the string ID (e.g., 'SUP-0001')
                        $_SESSION['sup_id'] = $supervisor['sup_id'];
                        $_SESSION['user_type'] = 'supervisor'; // Store user type

                        // Redirect to supervisor dashboard                   
                        echo "<script>
                                alert(' Supervisor login successful!');
                                window.location.href = 'supervisor_dashboard.php';
                              </script>";
                  
                    } else {
                        echo "<script>
                                alert('Supervisor not found, check admin.');
                                window.location.href = 'student_login.php';
                              </script>";
                    }
                    // ADMIN TABLE PANEL FOR LOGIN              // ADMIN TABLE PANEL FOR LOGIN
                } else {
                    // If student and supervisor not found, check admin table
                    $check_sql = "SELECT * FROM admin WHERE hod_id = '$matric'";
                    $result = $conn->query($check_sql);

                    if ($result) {
                        if ($result->num_rows > 0) {
                            $admin = $result->fetch_assoc();
                            // Check for suspension
                            if (isset($admin['reason']) && strtolower($admin['reason']) === 'suspended') {
                                // Set session for suspended user to handle receipt upload
                                $_SESSION['hod_id'] = $admin['hod_id'];
                                echo "<script>
                                        alert('Your account is suspended.');
                                        window.location.href = 'admin_suspension_dashboard.php';
                                      </script>";
                                exit();
                            }
                            // Verify password
                            if ($password == $admin['password']) {

                                // Set session variables to remember the admin's login
                                
                                $_SESSION['password'] = $admin['password'];
                                $_SESSION['hod_id'] = $admin['hod_id'];
                                $_SESSION['user_type'] = 'admin'; // Store user type

                                // Check again for suspension after password match
                                if (isset($admin['reason']) && strtolower($admin['reason']) === 'suspended') {
                                    echo "<script>
                                            alert('Your account is suspended.');
                                            window.location.href = 'admin_suspension_dashboard.php';
                                          </script>";
                                    exit();
                                }

                                // Redirect to admin dashboard
                                echo "<script>
                                        alert('Admin login successful!');
                                        window.location.href = 'admin_dashboard.php';
                                      </script>";
                            } else {
                                echo "<script>
                                        alert('Invalid username or password.');
                                        window.location.href = 'student_login.php';
                                      </script>";
                            }
                        } else {
                            echo "<script>
                                    alert('User not found.');
                                    window.location.href = 'student_login.php';
                                  </script>";
                        }
                    } else {
                        echo "Error: " . $check_sql . "<br>" . $conn->error;
                    }
                }
            } else {
                echo "Error: " . $check_sql . "<br>" . $conn->error;
            }
        } 
        
    } 
    $conn->close();
}
?>
