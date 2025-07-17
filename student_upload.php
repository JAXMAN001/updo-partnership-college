<?php
include 'config.php';

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['matric'])) {
    header("Location: student_login.php");
    exit();
}

// Fetch user data
$matric = $_SESSION['matric'];
$user_sql = "SELECT * FROM student WHERE matric='$matric'";
$user_result = $conn->query($user_sql) or die(mysqli_error($conn));

if ($user_result->num_rows > 0) {
    $user_data = $user_result->fetch_assoc();
} else {
    echo "User data not found.";
    exit();
}

// Fetch profile picture
$profile_pic = !empty($user_data['profile_pic']) ? $user_data['profile_pic'] : 'default.jpg';

// Initialize session array for documents if it doesn't exist
if (!isset($_SESSION['uploaded_documents'])) {
    $_SESSION['uploaded_documents'] = [];
}

// Initialize JavaScript alert variables
$upload_success = false;
$file_exists_error = false;
$upload_error = false;
$file_size_error = false;
$file_type_error = false;

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['document'])) {
    $target_dir = "../uploads/documents/";

    // Check if the target directory exists, if not, create it
    if (!is_dir($target_dir)) {
        if (!mkdir($target_dir, 0777, true)) {
            echo "<div class='error'>Failed to create directory: " . $target_dir . "</div>";
            exit();
        }
    }

    $uploadOk = 1;
    $allowedFileTypes = ["pdf", "doc", "docx"];

    // Generate a unique filename
    $safe_filename = $_FILES["document"]["name"]; // Use the original filename
    $target_file = $target_dir . $safe_filename;
    $documentFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if file already exists
    if (file_exists($target_file)) {
        $file_exists_error = true;
        $uploadOk = 0;
    }

    // Check file size
    if ($_FILES["document"]["size"] > 10000000) { // Increased file size to 10MB
        $file_size_error = true;
        $uploadOk = 0;
    }

    // Allow certain file formats
    if (!in_array($documentFileType, $allowedFileTypes)) {
        $file_type_error = true;
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        // Set appropriate error message
    // if everything is ok, try to upload file
    } else {
        if (move_uploaded_file($_FILES["document"]["tmp_name"], $target_file)) {
            $upload_success = true;

            // Store the uploaded file path and name in the database
            $document_name = $_FILES["document"]["name"];
            $sql = "INSERT INTO document (matric, name, path, status) 
            VALUES ('$matric', '$document_name', '$target_file', 'pending')";
            if ($conn->query($sql) === TRUE) {
                // Clear the session variable after successful database insertion
                unset($_SESSION['uploaded_documents']);
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        } else {
            $upload_error = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STUDENT DASHOARD</title>
    <link rel="stylesheet" href="../css/home.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/upload.css">
    <link rel="stylesheet" href="../css/uploaded_documents.css">
    <link rel="stylesheet" href="../css/supervisor_info.css">
    
    <script>
        var uploadSuccess = <?php echo json_encode($upload_success); ?>;
        var fileExistsError = <?php echo json_encode($file_exists_error); ?>;
        var uploadError = <?php echo json_encode($upload_error); ?>;
        var fileSizeError = <?php echo json_encode($file_size_error); ?>;
        var fileTypeError = <?php echo json_encode($file_type_error); ?>;

        window.onload = function() {
            if (uploadSuccess) {
                alert('Document successfully uploaded!');
            }
            if (fileExistsError) {
                alert('Sorry, file already exists. Please upload another file.');
            }
            if (uploadError) {
                alert('Sorry, there was an error uploading your file.');
            }
            if (fileSizeError) {
                alert('Sorry, your file is too large.');
            }
            if (fileTypeError) {
                alert('Sorry, only PDF, DOC & DOCX files are allowed.');
            }
        }

        function showSupervisorInfo() {
            // Fetch supervisor info using AJAX
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    document.getElementById('supervisorDetails').innerHTML = xhr.responseText;
                    document.getElementById('supervisorInfo').style.display = 'block';
                }
            };
            xhr.open('GET', 'student_get_supervisor.php', true);
            xhr.send();
        }
    </script>

    <!-- this style is for the document uploaded successfull and show red pending before approving-->
    <style>
        
        .pending-note {
            color: white;
            font-size: 15px;
            font-style: italic;
            margin-left: 100px;
            border: 2px solid white;
            border-radius: 10px;
            width: 80px;
            text-align: center;

        }
        .uploaded-documents {
            margin-top: 10px;
            border-radius: 10px;
            
        }
        .uploaded-files-list {
            list-style: none;
            padding: 0;
            border: 3px solid  rgb(6, 169, 245);;
            border-radius: 20px;
            color: white;
        }
        .uploaded-files-list li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border: 1px solid red;
            margin-top: 5px;
            margin-bottom: 5px;
            color: white;
            background: black;
            border-radius: 20px;
        }
    </style>
    <!-- the style ends here-->

</head>
<body>
    <!-- Include the sidebar -->
    <div class="sidebar">
        <img src="../uploads/<?php echo $profile_pic; ?>" alt="Profile Picture" class="img">
        <div class="sidebar-header">
            <h2><?php echo $user_data['fullname']; ?></h2>      
        </div>
        <ul class="sidebar-menu">
            <li><a href="student_home.php"><i class="fas fa-home"></i>  HOME</a></li>
            <li><a href="student_profile.php"><i class="fas fa-user"></i>  PROFILE</a></li>
            <li><i class="fas fa-upload"></i> Upload</li>
            <li><button type="button" onclick="showSupervisorInfo()">
            <i class="fas fa-eye"></i> VIEW SUPERVISOR</button></li>
            <button onclick="location.reload();">REFRESH</button>
        </ul>
        <div class="sidebar-settings">
        <form id="logoutForm" action="student_logout.php" method="post">
                <button type="button" onclick="confirmLogout()">Logout</button>
                
            </form>
        </div>
    </div>

    <!-- Supervisor Information Display -->
    <div id="supervisorInfo" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); background-color:white; padding:20px; border:1px solid black; z-index:1000;">
        <h2>YOUR PROJECT SUPERVISOR </h2>
        <div id="supervisorDetails">
            <p>Loading supervisor information...</p>
        </div>
        <button onclick="hideSupervisorInfo()">Close</button>
    </div>

    <div class="upload-form">
        <h2>Upload Document (Document <?php echo count($_SESSION) + 1; ?>)</h2>
        <form action="student_upload.php" method="post" enctype="multipart/form-data">
            <label for="document">Select document to upload:</label>
            <input type="file" name="document" id="document" onchange="previewFile()">
            <div id="file-preview"></div>
            <input type="submit" value="Upload Document" name="submit">
        </form>
    </div>

    <div class="uploaded-documents">
        <h2>Uploaded Documents:</h2>
        <?php
        // Fetch uploaded documents from the database
        $document_sql = "SELECT * FROM document WHERE matric = '$matric'";
        $document_result = $conn->query($document_sql);

        if ($document_result && $document_result->num_rows > 0) {
            echo "<ul class='uploaded-files-list'>";
            while ($document_data = $document_result->fetch_assoc()) {
                $document_id = $document_data['id'];
                $document_path = $document_data['path'];
                $document_name = $document_data['name'];
                // Check if the 'status' key exists before accessing it
                $document_status = isset($document_data['status']) ? $document_data['status'] : 'pending';
                echo "<li><a href='" . htmlspecialchars($document_path) . "' target='_blank'>" 
                . htmlspecialchars($document_name) . "</a>";
                echo "<span class='pending-note'>" . htmlspecialchars($document_status) . "</span>";
                echo "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>No documents have been uploaded yet.</p>";
        }
        ?>
    </div>

    <script>
        function confirmLogout() {
            if (confirm("Are you sure you want to logout NIGGA?")) {
                document.getElementById('logoutForm').submit();
            }
        }

        function previewFile() {
            var preview = document.querySelector('#file-preview');
            preview.innerHTML = ''; // Clear previous previews
            var file = document.querySelector('input[type=file]').files[0];

            if (file) {
                var reader = new FileReader();

                reader.addEventListener("load", function () {
                    var fileType = file.type;
                    var fileName = file.name;
                    var fileSize = (file.size / 1024).toFixed(2); // Size in KB

                    var previewItem = document.createElement('div');
                    previewItem.innerHTML = `
                        <p><strong>Name:</strong> ${fileName}</p>
                        <p><strong>Size:</strong> ${fileSize} KB</p>
                        <p><strong>Type:</strong> ${fileType}</p>
                        <hr>
                    `;
                    preview.appendChild(previewItem);
                }, false);

                reader.readAsDataURL(file);
            }
        }

        function hideSupervisorInfo() {
            document.getElementById('supervisorInfo').style.display = 'none';
        }
    </script>
    <?php
        // Move the connection close to the end of the file
        $conn->close();
    ?>
</body>
</html>
