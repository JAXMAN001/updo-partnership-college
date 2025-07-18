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

$supervisors = [];
if ($institution) {
    // Fetch supervisors with the same institution, including picture column
    $stmt = $conn->prepare("SELECT sup_id, fullname, email, phone, department, faculty, profile_pic FROM supervisors WHERE institution = ?");
    if ($stmt) {
        $stmt->bind_param("s", $institution);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $supervisors[] = $row;
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
    <title>Registered Supervisors</title>
    <link rel="stylesheet" href="../css/cyber.css">
    <link rel="stylesheet" href="../css/cyber_side.css">
    <style>
        .supervisors-table-container {
            margin-left: 260px;
            margin-bottom: 700px;
            padding: 30px;
            
            border-radius: 18px;
            
            max-width: 1800px;
            width: 95%;
            margin-top: 30px; /* Raise table up */
            color: white;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px; /* Less margin to raise table */
            background: #111;
            color: white;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            
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
        .no-supervisors {
            color: #ffc107;
            font-weight: bold;
            margin-top: 30px;
        }
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
            <li><a href="cyber_supervisors.php">SUPERVISOR</a></li>
            <li><a href="cyber_secure_logout.php">Logout</a></li>
        </ul>
    </div>
    <div class="supervisors-table-container">
        <h2>Registered Supervisors for Institution: <span style="color:#007bff;"><?php echo htmlspecialchars($institution); ?></span></h2>
        <?php if (count($supervisors) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>SUPERVISOR ID</th>
                    <th>FULL NAME</th>
                    <th>EMAIL</th>
                    <th>PHONE</th>
                    <th>DEPARTMENT</th>
                    <th>FACULTY</th>
                    <th>PICTURE</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($supervisors as $supervisor): ?>
                <tr>
                    <td><?php echo htmlspecialchars($supervisor['sup_id']); ?></td>
                    <td><?php echo htmlspecialchars($supervisor['fullname']); ?></td>
                    <td><?php echo htmlspecialchars($supervisor['email']); ?></td>
                    <td><?php echo htmlspecialchars($supervisor['phone']); ?></td>
                    <td><?php echo htmlspecialchars($supervisor['department']); ?></td>
                    <td><?php echo htmlspecialchars($supervisor['faculty']); ?></td>
                    <td>
                        <?php if (!empty($supervisor['profile_pic'])): ?>
                            <a href="#" class="view-picture-link" data-img="<?php echo htmlspecialchars('../uploads/' . $supervisor['profile_pic']); ?>">View Picture</a>
                        <?php else: ?>
                            No Image
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <div class="no-supervisors">No supervisors registered for this institution.</div>
        <?php endif; ?>
    </div>
    <!-- Modal for viewing supervisor picture -->
    <div class="modal" id="pictureModal">
        <div class="modal-content">
            <button class="close-modal" id="closeModalBtn">&times;</button>
            <img id="modalImage" src="" alt="Supervisor Picture">
        </div>
    </div>
    <script>
    // Modal logic for viewing supervisor picture
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
