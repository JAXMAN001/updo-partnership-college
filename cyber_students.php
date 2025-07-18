<?php
session_start();

// Only allow access for logged-in updo_staff_id users
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'staff') {
    header("Location: cyber_secure_login.php");
    exit();
}

require_once 'config.php';

// Get the institution for the logged-in staff
$staff_id = $_SESSION['user_id'];
$institution = '';
$stmt = $conn->prepare("SELECT institution FROM partnership_form WHERE updo_staff_id = ?");
$stmt->bind_param("s", $staff_id);
$stmt->execute();
$stmt->bind_result($institution);
$stmt->fetch();
$stmt->close();

$students = [];
if ($institution) {
    // Fetch students with the same institution, including picture column
    $stmt = $conn->prepare("SELECT matric, fullname, email, phone_number, department, faculty, profile_pic FROM student WHERE institution = ?");
    if ($stmt) {
        $stmt->bind_param("s", $institution);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
        $stmt->close();
    } else {
        echo "<div style='color:red;margin-left:260px;'>Database error: " . htmlspecialchars($conn->error) . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registered Students</title>
    <link rel="stylesheet" href="../css/cyber.css">
    <link rel="stylesheet" href="../css/cyber_side.css">
    <style>
        .students-table-container {
            margin-left: 260px;
            margin-bottom: 700px;
            padding: 30px;
            
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            max-width: 1800px;
            width: 95%;
            margin-top: 30px; /* Raise table up */
            color: wheat;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px; /* Less margin to raise table */
            background: #111;
            color: wheat;
        }
        th, td {
            border: 1px solid #444;
            padding: 12px 10px;
            text-align: left;
            color: wheat;
        }
        th {
            background: #222;
            color: wheat;
        }
        tr:nth-child(even) {
            background: #222;
        }
        tr:hover {
            background: #333;
        }
        .no-students {
            color: #ffc107;
            font-weight: bold;
            margin-top: 30px;
        }
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0; top: 0;
            width: 100vw; height: 100vh;
            background: rgba(0,0,0,0.7);
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            background: #222;
            color: wheat;
            padding: 18px 24px;
            border-radius: 10px;
            max-width: 90vw;
            max-height: 90vh;
            text-align: center;
            position: relative;
        }
        .modal-content img {
            max-width: 350px;
            max-height: 70vh;
            border-radius: 10px;
            margin-bottom: 10px;
        }
        .close-modal {
            position: absolute;
            top: 8px;
            right: 18px;
            font-size: 2em;
            color: #333;
            background: none;
            border: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Menu</h2>
        <ul>
            <li><a href="cyber_secure_web.php">DASHBOARD</a></li>
            <li><a href="cyber_secure_settings.php">SETTINGS</a></li>
            <li><a href="cyber_features.php">FEATURES</a></li>
            <li><a href="cyber_students.php">STUDENT</a></li>
            <li><a href="cyber_secure_logout.php">Logout</a></li>
        </ul>
    </div>
    <div class="students-table-container">
        <h2>Registered Students for Institution: <span style="color:#007bff;"><?php echo htmlspecialchars($institution); ?></span></h2>
        <?php if (count($students) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>MATRIC NUMBER</th>
                    <th>FULL NAME</th>
                    <th>EMAIL</th>
                    <th>PHONE</th>
                    <th>DEPARTMENT</th>
                    <th>FACULTY</th>
                    <th>PICTURE</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $student): ?>
                <tr>
                    <td><?php echo htmlspecialchars($student['matric']); ?></td>
                    <td><?php echo htmlspecialchars($student['fullname']); ?></td>
                    <td><?php echo htmlspecialchars($student['email']); ?></td>
                    <td><?php echo htmlspecialchars($student['phone_number']); ?></td>
                    <td><?php echo htmlspecialchars($student['department']); ?></td>
                    <td><?php echo htmlspecialchars($student['faculty']); ?></td>
                    <td>
                        <?php if (!empty($student['profile_pic'])): ?>
                            <a href="#" class="view-picture-link" data-img="<?php echo htmlspecialchars('../uploads/' . $student['profile_pic']); ?>" style="color:#fff; background:#007bff; padding:6px 18px; border-radius:6px; text-decoration:none; font-weight:bold; box-shadow:0 2px 8px rgba(0,123,255,0.15); display:inline-block;">View Picture</a>
                        <?php else: ?>
                            No Image
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <div class="no-students">No students registered for this institution.</div>
        <?php endif; ?>
    </div>
    <!-- Modal for viewing student picture -->
    <div class="modal" id="pictureModal">
        <div class="modal-content">
            <button class="close-modal" id="closeModalBtn">&times;</button>
            <img id="modalImage" src="" alt="Student Picture">
        </div>
    </div>
    <script>
    // Modal logic for viewing student picture
    document.querySelectorAll('.view-picture-link').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            var imgSrc = this.getAttribute('data-img');
            document.getElementById('modalImage').src = imgSrc;
            document.getElementById('pictureModal').style.display = 'flex';
        });
    });
    document.getElementById('closeModalBtn').onclick = function() {
        document.getElementById('pictureModal').style.display = 'none';
        document.getElementById('modalImage').src = '';
    };
    document.getElementById('pictureModal').onclick = function(e) {
        if (e.target === this) {
            this.style.display = 'none';
            document.getElementById('modalImage').src = '';
        }
    };
    </script>
</body>
</html>
