<?php
session_start();
include 'config.php'; // Include configuration

// Check if the user is an admin
if (!isset($_SESSION['hod_id'])) {
    header("Location: student_login.php");
    exit();
}

// Check if student_id and sup_id (the string ID like 'SUP-0001') are set
if (isset($_POST['student_id']) && isset($_POST['sup_id'])) {
    $student_id = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT); // Validate student ID
    $supervisor_sup_id = trim($_POST['sup_id']); // The string ID (e.g., 'SUP-0001')

    if ($student_id === false || empty($supervisor_sup_id)) {
        // Handle invalid input
        // Redirect back with an error message is often better UX
        $_SESSION['error_message'] = "Invalid student ID or supervisor ID provided.";
        header("Location: admin_view_student.php");
        exit();
    }

    // --- Step 1: Find the supervisor's integer primary key (id) based on sup_id ---
    $find_sup_sql = "SELECT id FROM supervisors WHERE sup_id = ?";
    $find_stmt = $conn->prepare($find_sup_sql);
    $supervisor_int_id = null; // Initialize

    if ($find_stmt) {
        $find_stmt->bind_param("s", $supervisor_sup_id);
        $find_stmt->execute();
        $find_result = $find_stmt->get_result();
        if ($find_row = $find_result->fetch_assoc()) {
            $supervisor_int_id = $find_row['id']; // Get the integer ID
        }
        $find_stmt->close();
    } else {
        // Handle error preparing statement
        error_log("Error preparing supervisor lookup: " . $conn->error);
        $_SESSION['error_message'] = "Database error finding supervisor.";
        header("Location: admin_view_student.php");
        exit();
    }

    // Check if a valid supervisor integer ID was found
    if ($supervisor_int_id === null) {
        $_SESSION['error_message'] = "Supervisor with ID '" . htmlspecialchars($supervisor_sup_id) . "' not found.";
        header("Location: admin_view_student.php");
        exit();
    }

    // --- Additional Check: Verify supervisor's department and faculty ---
    $verify_sql = "SELECT COUNT(*) FROM supervisors 
                   WHERE sup_id = ? 
                   AND institution = (SELECT institution FROM student WHERE id = ?)
                   AND department = (SELECT department FROM student WHERE id = ?)
                   AND faculty = (SELECT faculty FROM student WHERE id = ?)";
    $verify_stmt = $conn->prepare($verify_sql);
    $supervisor_valid = 0;

    if ($verify_stmt) {
        $verify_stmt->bind_param("siii", $supervisor_sup_id, $student_id, $student_id, $student_id);
        $verify_stmt->execute();
        $verify_result = $verify_stmt->get_result();
        if ($verify_row = $verify_result->fetch_row()) {
            $supervisor_valid = $verify_row[0];
        }
        $verify_stmt->close();
    } else {
        error_log("Error preparing supervisor verification: " . $conn->error);
        $_SESSION['error_message'] = "Database error verifying supervisor's affiliation.";
        header("Location: admin_view_student.php");
        exit();
    }

    // --- Step 2: Update the student's record with the supervisor's sup_id (string) if valid ---
    $update_sql = "UPDATE student SET sup_id = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);

    if ($update_stmt) {
        // Bind parameters: sup_id (string), student_id (int)
        $update_stmt->bind_param("si", $supervisor_sup_id, $student_id);

        // Execute the statement
        if ($update_stmt->execute()) {
            // --- Step 3: Add the student ID to the supervisor's record in the supervisors table ---
            $add_student_sql = "UPDATE supervisors SET student_id = ? WHERE id = ?";
            $add_student_stmt = $conn->prepare($add_student_sql);

            if ($add_student_stmt) {
                $add_student_stmt->bind_param("ii", $student_id, $supervisor_int_id);
                $add_student_stmt->execute();
                $add_student_stmt->close();
            } else {
                error_log("Error preparing supervisor update: " . $conn->error);
                $_SESSION['error_message'] = "Updating assigned stydent successful .";
                header("Location: admin_view_student.php");
                exit();
            }

            // --- Step 3: Remove the student ID from the supervisor's record in the supervisors table ---
            $remove_student_sql = "UPDATE supervisors SET student_id = NULL WHERE id = ?";
            $remove_student_stmt = $conn->prepare($remove_student_sql);

            if ($remove_student_stmt) {
                $remove_student_stmt->bind_param("i", $supervisor_int_id);
                $remove_student_stmt->execute();
                $remove_student_stmt->close();
            } else {
                error_log("Error preparing supervisor student_id removal: " . $conn->error);
                $_SESSION['error_message'] = "Error removing student ID from supervisor.";
                header("Location: admin_view_student.php");
                exit();
            }

            // Supervisor assigned successfully
            $_SESSION['success_message'] = "Supervisor assigned successfully to student ID " . $student_id . ".";
            // Redirect back to student view (or assigned list)
            header("Location: admin_view_student.php");
            exit();
        } else {
            // Error assigning supervisor
            error_log("Error executing student update: " . $update_stmt->error);
            $_SESSION['error_message'] = "Error assigning supervisor: Database update failed.";
            header("Location: admin_view_student.php");
            exit();
        }
        if (!$supervisor_valid) {
            $_SESSION['error_message'] = "The selected supervisor does not belong to the same institution, department, and faculty as the student.";
            header("Location: admin_view_student.php");
            exit();
        }
        // Close the statement
        $update_stmt->close();
    } else {
        // Error preparing statement
        error_log("Error preparing student update statement: " . $conn->error);
        $_SESSION['error_message'] = "Error preparing assignment update.";
        header("Location: admin_view_student.php");
        exit();
    }

    $conn->close();
} else {
    // Student ID and/or Supervisor ID not provided in POST request
    $_SESSION['error_message'] = "Required information (Student ID and Supervisor ID) not provided.";
    header("Location: admin_view_student.php"); // Redirect back
    exit();
}
?>
