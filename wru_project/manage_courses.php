<?php
session_start();
// Security: Redirect non-admin users to login page
if (!isset($_SESSION['role']) || strtolower(trim($_SESSION['role'])) !== 'admin') {
    header("Location: index.php");
    exit();
}
require_once 'db_connect.php';

// Fetch instructors for the dropdown menu
$instructors = $conn->query("SELECT user_id, full_name FROM users WHERE role = 'instructor'")->fetchAll();

// Fetch courses with their assigned instructors
$courses = $conn->query("
    SELECT c.*, u.full_name as instructor_name 
    FROM courses c 
    LEFT JOIN users u ON c.instructor_id = u.user_id
    ORDER BY c.batch DESC
")->fetchAll();

// Handle new course registration
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code = trim($_POST['course_code']);
    $name = trim($_POST['course_name']);
    $batch = $_POST['batch']; 
    $inst_id = $_POST['instructor_id'];
    $total_sessions = $_POST['total_sessions']; // The new numerical value received from form

    try {
        // SQL query - Including total_sessions in the insertion
        $stmt = $conn->prepare("INSERT INTO courses (course_code, course_name, batch, instructor_id, total_sessions) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$code, $name, $batch, $inst_id, $total_sessions]);
        echo "<script>alert('Course registered successfully with $total_sessions sessions!'); window.location='manage_courses.php';</script>";
    } catch (PDOException $e) {
        echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses | WRU-IoT</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { display: block; height: auto; background: #f4f7f6; margin: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { width: 250px; height: 100vh; background: #2c3e50; color: white; position: fixed; padding: 20px; z-index: 100; }
        .sidebar h2 { color: #e67e22; margin-bottom: 30px; font-size: 22px; border-bottom: 1px solid #34495e; padding-bottom: 10px; text-align: center; }
        .sidebar-menu { list-style: none; padding: 0; }
        .sidebar-menu a { color: white; text-decoration: none; display: block; padding: 12px 15px; border-radius: 8px; transition: 0.3s; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background: #34495e; color: #e67e22; }
        .sidebar-menu i { margin-right: 10px; width: 20px; }

        .main-content { margin-left: 250px; padding: 30px; }
        .header-bar { display: flex; justify-content: space-between; align-items: center; background: white; padding: 15px 30px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 30px; }
        
        .form-box { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; align-items: end; }
        
        .input-group { display: flex; flex-direction: column; gap: 5px; }
        .input-group label { font-size: 13px; color: #2c3e50; font-weight: bold; }
        .input-group input, .input-group select { padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; }

        .table-container { background: white; border-radius: 15px; padding: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #e67e22; color: white; padding: 15px; text-align: left; }
        td { padding: 12px 15px; border-bottom: 1px solid #eee; }
        
        .action-btns { display: flex; gap: 10px; }
        .btn-report { background: #2ecc71; color: white; padding: 6px 12px; border-radius: 5px; text-decoration: none; font-size: 13px; font-weight: bold; }
        .btn-delete { background: #e74c3c; color: white; padding: 6px 12px; border-radius: 5px; text-decoration: none; font-size: 13px; font-weight: bold; }
        
        .batch-badge { background: #3498db; color: white; padding: 4px 10px; border-radius: 15px; font-size: 11px; }
        .session-badge { background: #9b59b6; color: white; padding: 4px 10px; border-radius: 15px; font-size: 11px; font-weight: bold; }
        .submit-btn { height: 42px; background: #e67e22; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; transition: 0.3s; }
        .submit-btn:hover { background: #d35400; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>WRU-IoT Admin</h2>
        <ul class="sidebar-menu">
            <li><a href="admin_dashboard.php"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
            <li><a href="manage_users.php"><i class="fas fa-users"></i> <span>Manage Users</span></a></li>
            <li><a href="manage_courses.php" class="active"><i class="fas fa-book"></i> <span>Courses</span></a></li>
            <li><a href="admin_view_courses.php"><i class="fas fa-file-alt"></i> <span>Attendance Reports</span></a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header-bar">
            <h1><i class="fas fa-university" style="color: #e67e22;"></i> Course Management</h1>
            <span>Welcome, <strong><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></strong></span>
        </div>

        <!-- New Course Registration Form -->
        <div class="form-box">
            <h3><i class="fas fa-plus-circle" style="color: #e67e22;"></i> Register New Course</h3>
            <form method="POST" style="margin-top: 15px;">
                <div class="form-grid">
                    <div class="input-group">
                        <label>Course Code</label>
                        <input type="text" name="course_code" placeholder="e.g. IoT101" required>
                    </div>
                    <div class="input-group">
                        <label>Course Name</label>
                        <input type="text" name="course_name" placeholder="Name of Course" required>
                    </div>
                    <div class="input-group">
                        <label>Batch Year</label>
                        <select name="batch" required>
                            <option value="">Select Batch</option>
                            <?php for($y=2014; $y<=2018; $y++) echo "<option value='$y'>$y E.C</option>"; ?>
                        </select>
                    </div>
                    
                    <!-- New Session Count Input Field -->
                    <div class="input-group">
                        <label>Total Sessions</label>
                        <input type="number" name="total_sessions" min="1" placeholder="e.g. 32" required>
                    </div>

                    <div class="input-group">
                        <label>Instructor</label>
                        <select name="instructor_id" required>
                            <option value="">Select Instructor</option>
                            <?php foreach($instructors as $ins): ?>
                                <option value="<?php echo $ins['user_id']; ?>"><?php echo htmlspecialchars($ins['full_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="submit-btn">Add Course</button>
                </div>
            </form>
        </div>

        <div class="table-container">
            <h3>Active Courses List</h3>
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Course Name</th>
                        <th>Batch</th>
                        <th>Instructor</th>
                        <th>Planned Sessions</th> 
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($courses as $c): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($c['course_code']); ?></strong></td>
                        <td><?php echo htmlspecialchars($c['course_name']); ?></td>
                        <td><span class="batch-badge"><?php echo htmlspecialchars($c['batch']); ?> E.C</span></td>
                        <td><?php echo htmlspecialchars($c['instructor_name'] ?? 'Not Assigned'); ?></td>
                        
                        <!-- Column showing the number of planned sessions -->
                        <td><span class="session-badge"><?php echo htmlspecialchars($c['total_sessions'] ?? '0'); ?> Sessions</span></td>
                        
                        <td class="action-btns">
                            <a href="admin_view_courses.php" class="btn-report">
                                <i class="fas fa-chart-bar"></i> Report
                            </a>
                            <a href="delete_course.php?id=<?php echo $c['course_id']; ?>" 
                               class="btn-delete" onclick="return confirm('Are you sure?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>