<?php
session_start();
// Security Check: Ensure only instructors can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'instructor') { 
    header("Location: index.php"); 
    exit(); 
}
require_once 'db_connect.php';

// Get course_id from the URL
$course_id = isset($_GET['course_id']) ? $_GET['course_id'] : null;

if (!$course_id) {
    header("Location: instructor_dashboard.php");
    exit();
}

// Fetch Course Name
$cStmt = $conn->prepare("SELECT course_name FROM courses WHERE course_id = ?");
$cStmt->execute([$course_id]);
$course = $cStmt->fetch();

// Fetch all students (Note: In a production system, you would filter by course enrollment)
$students = $conn->query("SELECT user_id, university_id, full_name FROM users WHERE role = 'student'")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Attendance | WRU-IoT</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #f4f7f6; display: block; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .container { padding: 30px; max-width: 900px; margin: auto; }
        .attendance-table { width: 100%; background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.05); border-collapse: collapse; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background-color: #2c3e50; color: white; }
        .status-select { padding: 8px; border-radius: 5px; border: 1px solid #ccc; width: 100%; }
        .save-btn { background: #e67e22; color: white; padding: 15px 30px; border-radius: 10px; border: none; cursor: pointer; margin-top: 20px; float: right; font-weight: bold; transition: 0.3s; }
        .save-btn:hover { background: #d35400; }
        .header-info { margin-bottom: 20px; }
    </style>
</head>
<body>

<div class="container">
    <div class="header-info">
        <h2><i class="fas fa-clipboard-check"></i> Attendance Sheet - <?php echo htmlspecialchars($course['course_name']); ?></h2>
        <p><strong>Date:</strong> <?php echo date('F d, Y'); ?></p>
    </div>

    <form id="attendanceForm">
        <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
        <table class="attendance-table">
            <thead>
                <tr>
                    <th>University ID</th>
                    <th>Full Name</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($students as $s): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($s['university_id']); ?></strong></td>
                    <td><?php echo htmlspecialchars($s['full_name']); ?></td>
                    <td>
                        <select name="status[<?php echo $s['user_id']; ?>]" class="status-select">
                            <option value="present">Present</option>
                            <option value="absent" selected>Absent</option>
                        </select>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button type="submit" class="save-btn">Save Attendance</button>
    </form>
</div>

<script>
document.getElementById('attendanceForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Submit data using AJAX
    let formData = new FormData(this);
    
    fetch('save_attendance.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        alert("Attendance recorded successfully!");
        window.location.href = 'instructor_dashboard.php';
    })
    .catch(error => {
        console.error('Error:', error);
        alert("An error occurred while saving attendance.");
    });
});
</script>

</body>
</html>