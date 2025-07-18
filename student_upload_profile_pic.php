<?php
session_start();
include 'config.php';

if (!isset($_SESSION['matric'])) {
    header("Location: student_login.php");
    exit();
}

$matric = $_SESSION['matric'];
$target_dir = "../uploads/";
$target_file = $target_dir . basename($_FILES["profile_pic"]["name"]);
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
$errorMessage = "";

// Check if file already exists
if (file_exists($target_file)) {
    //Option to overwrite
    if (isset($_POST['overwrite']) && $_POST['overwrite'] == 'yes') {
        //Proceed with upload, knowing we will overwrite
    } else {
        $errorMessage = "Sorry, file already exists.";
        $uploadOk = 0;
    }
}

// Check if image file is an actual image or fake image
if (isset($_POST["submit"])) {
    $check = getimagesize($_FILES["profile_pic"]["tmp_name"]);
    if ($check !== false) {
        $uploadOk = 1;
    } else {
        $errorMessage .= "File is not an image.";
        $uploadOk = 0;
    }
}

// Check file size
if ($_FILES["profile_pic"]["size"] > 50000000) {
    $errorMessage .= "Sorry, your file is too large.";
    $uploadOk = 0;
}

// Allow certain file formats
if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
    $errorMessage .= "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
    $uploadOk = 0;
}

// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    echo $errorMessage;
    echo "Sorry, your file was not uploaded.";
    // if everything is ok, try to upload file
} else {
    if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
        // update the database
        $image_name = basename($_FILES["profile_pic"]["name"]);
        $sql = "UPDATE student SET profile_pic='$image_name' WHERE matric='$matric'";

        if ($conn->query($sql) === TRUE) {
            // Redirect to profile page after successful upload and database update
            header("Location: student_profile.php");
            exit();
        } else {
            echo "Error updating record: " . $conn->error;
        }
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}

$conn->close();
?>
