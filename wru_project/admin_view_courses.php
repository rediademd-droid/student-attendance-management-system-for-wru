<?php
session_start();
require_once 'db_connect.php';

// Admin permission check
if (!isset($_SESSION['role']) || strtolower(trim($_SESSION['role'])) !== 'admin') {
    header("Location: index.php");
    exit();
}

$courses = $conn->query("SELECT * FROM courses")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Reports | WRU-IoT Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --sidebar-bg: #2c3e50;
            --sidebar-active: rgba(255, 255, 255, 0.1);
            --accent-orange: #e67e22;
            --bg-light: #f4f7f6;
            --text-dark: #2c3e50;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--bg-light);
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* Sidebar Styling - Based on the design */
        .sidebar {
            width: 260px;
            background: var(--sidebar-bg);
            color: white;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
        }

        .sidebar-header {
            padding: 30px 20px;
            font-size: 22px;
            font-weight: bold;
            color: var(--accent-orange);
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 20px 0;
        }

        .sidebar-menu li a {
            display: flex;
            align-items: center;
            padding: 15px 25px;
            color: #bdc3c7;
            text-decoration: none;
            transition: 0.3s;
        }

        .sidebar-menu li a i {
            margin-right: 15px;
            width: 20px;
            font-size: 18px;
        }

        .sidebar-menu li a:hover, .sidebar-menu li.active a {
            background: var(--sidebar-active);
            color: var(--accent-orange);
            border-left: 4px solid var(--accent-orange);
        }

        /* Main Content Styling */
        .main-content {
            flex-grow: 1;
            overflow-y: auto;
            padding: 40px;
        }

        .top-header {
            background: white;
            padding: 20px 30px;
            border-radius: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }

        .top-header h1 {
            margin: 0;
            font-size: 24px;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .top-header h1 i { color: var(--accent-orange); }

        .report-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .course-list {
            border: 1px solid #edf2f7;
            border-radius: 10px;
            overflow: hidden;
        }

        .course-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 25px;
            border-bottom: 1px solid #edf2f7;
            transition: 0.2s;
        }

        .course-item:last-child { border-bottom: none; }
        .course-item:hover { background: #f8fafc; }

        .course-info strong { color: var(--text-dark); font-size: 16px; }
        .course-code { 
            background: #edf2f7; 
            padding: 3px 8px; 
            border-radius: 5px; 
            font-size: 12px; 
            color: #7f8c8d;
            margin-left: 10px;
        }

        .btn-report {
            background: #2ecc71; /* Green color as seen in design */
            color: white;
            padding: 8px 18px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
        }

        .btn-report:hover { background: #27ae60; transform: translateY(-2px); }

        .empty-state {
            text-align: center;
            padding: 50px;
            color: #95a5a6;
        }
    </style>
</head>
<body>

    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <div class="sidebar-header">WRU-IoT Admin</div>
        <ul class="sidebar-menu">
            <li><a href="admin_dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a></li>
            <li><a href="manage_users.php"><i class="fas fa-users"></i> Manage Users</a></li>
            <li><a href="manage_courses.php"><i class="fas fa-book"></i> Courses</a></li>
            <li class="active"><a href="attendance_report.php"><i class="fas fa-file-alt"></i> Attendance Reports</a></li>
            <li style="margin-top: auto;"><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        
        <!-- Top Welcome Bar -->
        <div class="top-header">
            <h1><i class="fas fa-university"></i> Attendance Reports</h1>
            <div class="admin-profile">
                Welcome, <strong>System Admin</strong>
            </div>
        </div>

        <div class="report-card">
            <div class="section-title">
                <i class="fas fa-list-ul"></i> Select a Course to Generate Report
            </div>

            <?php if(!empty($courses)): ?>
                <div class="course-list">
                    <?php foreach($courses as $c): ?>
                        <div class="course-item">
                            <div class="course-info">
                                <strong><?php echo htmlspecialchars($c['course_name']); ?></strong> 
                                <span class="course-code"><?php echo htmlspecialchars($c['course_code']); ?></span>
                            </div>
                            <a href="view_report.php?course_id=<?php echo $c['course_id']; ?>" class="btn-report">
                                <i class="fas fa-file-medical"></i> Report
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-folder-open fa-3x" style="margin-bottom: 15px;"></i>
                    <p>No courses registered yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>