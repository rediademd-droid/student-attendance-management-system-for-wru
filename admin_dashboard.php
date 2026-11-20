<?php
session_start();
require_once 'db_connect.php';

// 1. Security Check: Verify if the user is an admin
if (!isset($_SESSION['role']) || strtolower(trim($_SESSION['role'])) !== 'admin') {
    header("Location: index.php");
    exit();
}

// Fetch statistical information from the database
try {
    // Total registered users
    $userCount = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
    
    // Total active courses
    $courseCount = $conn->query("SELECT COUNT(*) FROM courses")->fetchColumn();

    // Calculate today's attendance percentage
    $today = date('Y-m-d');
    $stmtPresent = $conn->prepare("SELECT COUNT(DISTINCT student_id) FROM attendance_records WHERE DATE(session_date) = :today AND status = 'present'");
    $stmtPresent->execute(['today' => $today]);
    $presentCount = $stmtPresent->fetchColumn();

    $totalStudents = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
    $attendanceRate = ($totalStudents > 0) ? round(($presentCount / $totalStudents) * 100) : 0;

} catch (PDOException $e) {
    $userCount = 0; $courseCount = 0; $attendanceRate = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | WRU-IoT</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
        body { background-color: #f4f7f6; display: flex; flex-direction: column; min-height: 100vh; }
        header { background: #fff; padding: 15px 5%; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); position: sticky; top: 0; z-index: 1000; }
        .logo h2 { color: #e67e22; font-size: 24px; }
        .user-profile { display: flex; align-items: center; gap: 15px; font-size: 15px; }
        .logout-link { color: #e74c3c; text-decoration: none; font-weight: bold; padding: 5px 10px; border: 1px solid #e74c3c; border-radius: 5px; transition: 0.3s; }
        .logout-link:hover { background: #e74c3c; color: white; }
        
        nav { background: #2c3e50; color: white; padding: 10px 5%; display: flex; gap: 20px; }
        nav a { color: white; text-decoration: none; padding: 8px 15px; border-radius: 5px; transition: 0.3s; }
        nav a:hover, nav a.active { background: #34495e; color: #e67e22; }
        
        .wrapper { display: flex; flex: 1; padding: 20px 5%; gap: 20px; }
        .sidebar { width: 250px; background: white; padding: 20px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); height: fit-content; }
        .sidebar-menu { list-style: none; margin-top: 15px; }
        .sidebar-menu li a { display: block; padding: 10px; color: #2c3e50; text-decoration: none; border-radius: 8px; margin-bottom: 5px; }
        .sidebar-menu li a:hover { background: #f9f9f9; color: #e67e22; }
        
        .main-content { flex: 1; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 15px; }
        .stat-card i { font-size: 28px; color: #e67e22; background: #fff4e5; padding: 15px; border-radius: 12px; }
        
        .action-box { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .btn-group { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin-top: 20px; }
        .btn { padding: 12px; border-radius: 8px; text-decoration: none; color: white; text-align: center; font-weight: bold; transition: 0.3s; display: flex; align-items: center; justify-content: center; gap: 8px; border: none; cursor: pointer; }
        .btn-blue { background: #3498db; }
        .btn-green { background: #2ecc71; }
        .btn-orange { background: #e67e22; }
        .btn-dark { background: #34495e; } 
        
        .btn:hover { opacity: 0.9; transform: translateY(-2px); }

        footer { background: #2c3e50; color: #bdc3c7; text-align: center; padding: 20px; margin-top: auto; }
    </style>
</head>
<body>

    <header>
        <div class="logo">
            <h2>WRU-IoT <span style="color: #2c3e50;">Admin</span></h2>
        </div>
        <div class="user-profile">
            <span>Welcome, <strong><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></strong></span>
            <a href="logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </header>

    <nav>
        <a href="admin_dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a>
        <a href="manage_users.php"><i class="fas fa-users"></i> Users</a>
        <a href="manage_courses.php"><i class="fas fa-book"></i> Courses</a>
        <a href="admin_view_courses.php"><i class="fas fa-file-alt"></i> Attendance Reports</a>
    </nav>

    <div class="wrapper">
        <aside class="sidebar">
            <h3>Quick Links</h3>
            <ul class="sidebar-menu">
                <li><a href="manage_users.php"><i class="fas fa-user-plus"></i> Register Users</a></li>
                <li><a href="manage_courses.php"><i class="fas fa-plus-circle"></i> Create Course</a></li>
                <li><a href="admin_view_courses.php"><i class="fas fa-chart-line"></i> View All Reports</a></li>
                <hr style="margin: 10px 0; border: 0.5px solid #eee;">
                <li><a href="index.php" style="color: #7f8c8d;"><i class="fas fa-arrow-left"></i> Back to Login</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-users"></i>
                    <div class="stat-info">
                        <h4><?php echo $userCount; ?></h4>
                        <p>Registered Users</p>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-book-open"></i>
                    <div class="stat-info">
                        <h4><?php echo $courseCount; ?></h4>
                        <p>Active Courses</p>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-calendar-check"></i>
                    <div class="stat-info">
                        <h4><?php echo $attendanceRate; ?>%</h4>
                        <p>Today's Rate</p>
                    </div>
                </div>
            </div>

            <div class="action-box">
                <h3>Admin Management Tools</h3>
                <div class="btn-group">
                    <a href="manage_users.php" class="btn btn-blue"><i class="fas fa-user-plus"></i> Add User</a>
                    <a href="manage_courses.php" class="btn btn-green"><i class="fas fa-plus-circle"></i> New Course</a>
                    <a href="admin_view_courses.php" class="btn btn-orange"><i class="fas fa-chart-bar"></i> View Reports</a>
                    <a href="index.php" class="btn btn-dark"><i class="fas fa-sign-in-alt"></i> Back to Login Page</a>
                </div>
            </div>
        </main>
    </div>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Werabe University - IoT Attendance System.</p>
    </footer>

</body>
</html>