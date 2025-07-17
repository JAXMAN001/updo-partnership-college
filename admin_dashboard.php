<?php
session_start(); // Start the session to manage user login state

$host = "localhost"; // Database host
$username = "root"; // Database username
$password = ""; // Database password
$dbname = "documents"; // Database name

// Create a database connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if admin is logged in
if (!isset($_SESSION['hod_id'])) {
    header("Location: student_login.php");
    exit();
}
$admin_user_id = $_SESSION['hod_id'];

// Fetch data for the chart (example: counts of students, supervisors, etc.)
// Add completed projects: count unique students (matric) with at least one completed document
$sql = "SELECT 
    (SELECT COUNT(*) FROM student) AS total_students,
    (SELECT COUNT(*) FROM supervisors) AS total_supervisors,
    (SELECT COUNT(*) FROM student WHERE sup_id IS NOT NULL AND sup_id != '') AS assigned_students,
    (SELECT COUNT(*) FROM student WHERE sup_id IS NULL OR sup_id = '') AS unassigned_students,
    (SELECT COUNT(DISTINCT matric) FROM document WHERE status = 'completed') AS completed_projects
";
$result = $conn->query($sql);

if (!$result) {
    die("Query error: " . $conn->error);
}
$data = $result->fetch_assoc();

// Fetch suspended students (reason = 'suspended')
$suspended_students = [];
$student_sql = "SELECT matric, name, reason FROM student WHERE reason = 'suspended'";
$student_result = $conn->query($student_sql);
if ($student_result && $student_result->num_rows > 0) {
    while ($row = $student_result->fetch_assoc()) {
        $suspended_students[] = $row;
    }
}
$total_suspended_students = count($suspended_students);

// Fetch suspended supervisors (reason = 'suspended')
$suspended_supervisors = [];
$supervisor_sql = "SELECT staff_id, name, reason FROM supervisors WHERE reason = 'suspended'";
$supervisor_result = $conn->query($supervisor_sql);
if ($supervisor_result && $supervisor_result->num_rows > 0) {
    while ($row = $supervisor_result->fetch_assoc()) {
        $suspended_supervisors[] = $row;
    }
}
$total_suspended_supervisors = count($suspended_supervisors);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUPER ADMIN DASHBOARD</title>
    <link rel="stylesheet" href="../css/Admin_dashboard.css">
    <!-- Load Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Load Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <div class="menu">
        <a href="admin_dashboard.php">DASHBOARD</a>
        <!-- <a href="admin_add_admin.php">REGISTER NEW HOD</a> -->
        <a href="supervisor_signup.php">REGISTER NEW SUPERVISOR</a>
        <a href="admin_student_signup.php">REGISTER NEW STUDENT</a>
        <a href="admin_view_student_supervisor.php">VIEW STUDENT SUPERVISOR</a>
        <a href="admin_terminate_user.php">TERMINATE USER</a>
        <a href="admin_view_student.php">REGISTERED STUDENT LIST</a>
        <a href="admin_assigned_and_unassigned.php">VIEW ASSIGNED STUDENTS</a>

        <form id="logoutForm" action="student_login.php" method="post">
            <button type="button" onclick="confirmLogout()">LOG-OUT</button>
        </form>
    </div>

    <div class="container">
        <h1>SUPER ADMIN ACTIVE: <?php echo htmlspecialchars($admin_user_id); ?>!</h1>
         <button onclick="location.reload();">REFRESH</button>
        <!-- Add a canvas for the chart -->
        <div style="width: 80%; margin: 20px auto;">
            <canvas id="adminChart" height="100"></canvas>
        </div>
      

    </div>

    <!-- OTHER FEATURES button at the bottom -->
    <div style="width:100%; text-align:center; margin: 40px 0 20px 0;">
        <button id="otherFeaturesBtn" style="padding:14px 38px; background:#2c3e50; color:#fff; border:none; border-radius:7px; font-size:1.1em; font-weight:bold; cursor:pointer;">
            OTHER FEATURES
        </button>
    </div>

    <!-- Modal for Other Features -->
    <style>
#otherFeaturesModal {
    display: none;
    position: fixed;
    top: 0; left: 0; width: 100vw; height: 100vh;
    background: rgba(0,0,0,0.45);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    transition: background 0.3s;
}
#otherFeaturesModal.show {
    display: flex;
    animation: fadeInBg 0.3s;
}
@keyframes fadeInBg {
    from { background: rgba(0,0,0,0); }
    to   { background: rgba(0,0,0,0.45); }
}
#otherFeaturesModal .modal-content-anim {
    opacity: 0;
    transform: translateY(-40px) scale(0.95);
    animation: modalIn 0.35s cubic-bezier(.68,-0.55,.27,1.55) forwards;
}
@keyframes modalIn {
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}
</style>
<div id="otherFeaturesModal">
    <div class="modal-content-anim" style="background:#fff; padding:36px 32px 28px 32px; border-radius:10px; min-width:320px; max-width:90vw; box-shadow:0 8px 32px rgba(0,0,0,0.18); position:relative;">
        <button onclick="closeOtherFeatures()" style="position:absolute; top:10px; right:16px; background:none; border:none; font-size:1.5em; color:#e74c3c; cursor:pointer;">&times;</button>
        <h2 style="margin-bottom:18px; color:#2c3e50;">Other Features</h2>
        <div style="display:flex; flex-direction:column; gap:16px;">
            <button onclick="window.location.href='admin_suspended_log.php'" style="padding:10px 0; background:red; color:#fff; border:none; border-radius:5px; font-size:1em; font-weight:bold; cursor:pointer;">
                <i class="fas fa-user-slash" style="margin-right:8px;"></i>SUSPENDED LOG
            </button>
            <button onclick="window.location.href='transaction_report.php'" style="padding:10px 0; background:blue; color:#fff; border:none; border-radius:5px; font-size:1em; font-weight:bold; cursor:pointer;">
                <i class="fas fa-receipt" style="margin-right:8px;"></i>TRANSACTION REPORT
            </button>
        </div>
    </div>
</div>
    <script>
    // Function to confirm logout action
    function confirmLogout() {
        if (confirm("ARE YOU SURE YOU WANT TO LOG-OUT SIR?")) {
            document.getElementById('logoutForm').submit();
        }
    }

    // Initialize the chart with PHP data
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('adminChart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [
                    'Total Students',
                    'Total Supervisors',
                    'Assigned Students',
                    'Unassigned Students',
                    'Student Completed Project'
                ],
                datasets: [{
                    label: 'System Statistics',
                    data: [
                        <?php echo $data['total_students']; ?>,
                        <?php echo $data['total_supervisors']; ?>,
                        <?php echo $data['assigned_students']; ?>,
                        <?php echo $data['unassigned_students']; ?>,
                        <?php echo $data['completed_projects']; ?>
                    ],
                    backgroundColor: [
                        'rgb(8, 141, 42)',
                        'rgb(189, 9, 48)',
                        'rgb(8, 32, 241)',
                        'rgb(2, 2, 2)',
                        'rgb(0, 123, 255)'
                    ],
                    borderColor: [
                        'rgb(8, 141, 42)',
                        'rgb(189, 9, 48)',
                        'rgb(8, 32, 241)',
                        'rgb(2, 2, 2)',
                        'rgb(0, 123, 255)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        suggestedMax: Math.max(
                            <?php echo $data['total_students']; ?>,
                            <?php echo $data['total_supervisors']; ?>,
                            <?php echo $data['assigned_students']; ?>,
                            <?php echo $data['unassigned_students']; ?>,
                            <?php echo $data['completed_projects']; ?>,
                            2
                        ) > 2 ? Math.max(
                            <?php echo $data['total_students']; ?>,
                            <?php echo $data['total_supervisors']; ?>,
                            <?php echo $data['assigned_students']; ?>,
                            <?php echo $data['unassigned_students']; ?>,
                            <?php echo $data['completed_projects']; ?>
                        ) + 1 : 2
                    }
                }
            }
        });

        // Optional: Simulate real-time updates (replace with actual AJAX calls)
        //  setInterval(() => {
        //      fetch('get_live_data.php')
        //          .then(response => response.json())
        //          .then(newData => {
        //              chart.data.datasets[0].data = [
        //                  newData.total_students,
        //                  newData.total_supervisors,
        //                  newData.assigned_students,
        //                  newData.unassigned_students,
        //                  newData.completed_projects
        //              ];
        //              let maxVal = Math.max(
        //                  newData.total_students,
        //                  newData.total_supervisors,
        //                  newData.assigned_students,
        //                  newData.unassigned_students,
        //                  newData.completed_projects,
        //                  2
        //              );
        //              chart.options.scales.y.suggestedMax = maxVal > 2 ? maxVal + 1 : 2;
        //              chart.update();
        //          });
        //  }, 5000);
    });

    // Modal logic for Other Features
    document.getElementById('otherFeaturesBtn').onclick = function() {
        var modal = document.getElementById('otherFeaturesModal');
        modal.classList.add('show');
        // Restart animation for modal content
        var content = modal.querySelector('.modal-content-anim');
        content.style.animation = 'none';
        // Force reflow
        void content.offsetWidth;
        content.style.animation = '';
        content.classList.remove('modalIn');
        // Add animation class
        setTimeout(function() {
            content.classList.add('modalIn');
        }, 10);
    };
    function closeOtherFeatures() {
        var modal = document.getElementById('otherFeaturesModal');
        modal.classList.remove('show');
    }
    document.getElementById('otherFeaturesModal').onclick = function(e) {
        if (e.target === this) closeOtherFeatures();
    };
    </script>
</body>
</html>