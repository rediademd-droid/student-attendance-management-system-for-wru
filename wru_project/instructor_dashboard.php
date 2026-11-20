<?php
session_start();

// 1. Security Check: Ensure the user is an instructor
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'instructor') {
    header("Location: index.php");
    exit();
}

require_once 'db_connect.php';

// 2. Ensure User ID exists in the session
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$instructor_id = $_SESSION['user_id'];

// 3. Database Query: Fetch courses + requests
try {
    $stmt = $conn->prepare("
        SELECT courses.*, 
        (SELECT COUNT(DISTINCT DATE(session_date)) 
         FROM attendance_records 
         WHERE attendance_records.course_id = courses.course_id) as sessions_taken
        FROM courses 
        WHERE instructor_id = :my_inst_id 
        ORDER BY course_id DESC
    ");
    $stmt->execute(['my_inst_id' => $instructor_id]);
    $courses = $stmt->fetchAll();

    $query = $conn->prepare("
        SELECT lr.*, u.full_name, c.course_name 
        FROM leave_requests lr
        JOIN users u ON lr.student_id = u.user_id
        JOIN courses c ON lr.course_id = c.course_id
        WHERE lr.instructor_id = ? 
        AND lr.status = 'pending'
    ");
    $query->execute([$instructor_id]);
    $request_count = count($query->fetchAll());

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Dashboard | WRU-IoT</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #f4f7f6; font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; }
        
        /* Sidebar Styling - Simple Vertical List */
        .sidebar { 
            width: 260px; background: #2c3e50; color: white; height: 100vh; 
            position: fixed; left: 0; top: 0;
        }
        .sidebar-header { padding: 30px 10px; text-align: center; }
        .sidebar-header h2 { font-size: 22px; margin: 0; color: #e67e22; letter-spacing: 1px; }
        
        .sidebar-menu { padding-top: 10px; }

        .sidebar a { 
            display: flex; align-items: center; justify-content: space-between; 
            padding: 18px 25px; color: #ecf0f1; text-decoration: none; 
            transition: 0.3s; border-left: 4px solid transparent; 
        }
        .sidebar a:hover { background: #34495e; color: #e67e22; }
        .sidebar a.active { background: #34495e; color: #e67e22; border-left: 4px solid #e67e22; }
        
        .sidebar i { margin-right: 15px; width: 20px; font-size: 18px; text-align: center; }

        .badge { background: #e74c3c; color: white; padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: bold; }

        /* Main Content */
        .main-container { margin-left: 260px; width: calc(100% - 260px); padding: 40px; }
        .top-nav { display: flex; justify-content: space-between; align-items: center; background: white; padding: 20px 30px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        
        .request-alert { background: #fff3e0; border-left: 5px solid #e67e22; padding: 15px 20px; border-radius: 10px; margin-top: 25px; display: flex; justify-content: space-between; align-items: center; }
        .course-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 25px; margin-top: 20px; }
        .course-card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); border-left: 6px solid #e67e22; }
        
        .session-info { background: #f8f9fa; padding: 12px; border-radius: 8px; margin: 15px 0 10px 0; display: flex; justify-content: space-between; font-size: 0.9rem; }
        .progress-container { background: #eee; border-radius: 10px; height: 8px; margin-bottom: 20px; overflow: hidden; }
        .progress-bar { height: 100%; transition: 0.5s ease; }

        .action-btns { display: flex; gap: 10px; }
        .mark-btn, .report-btn { padding: 10px; border-radius: 8px; text-decoration: none; flex: 1; text-align: center; font-size: 13px; font-weight: 600; color: white; }
        .mark-btn { background: #e67e22; }
        .report-btn { background: #3498db; }

        @media (max-width: 768px) { .sidebar { width: 70px; } .sidebar span, .sidebar-header h2, .badge { display: none; } .main-container { margin-left: 70px; width: calc(100% - 70px); } }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">
        <h2>WRU-IoT</h2>
    </div>
    
    <div class="sidebar-menu">
        <a href="instructor_dashboard.php" class="active">
            <span><i class="fas fa-th-large"></i> <span>Dashboard</span></span>
        </a>
        
        <a href="view_leave_requests.php">
            <span><i class="fas fa-envelope-open-text"></i> <span>Leave Requests</span></span>
            <?php if($request_count > 0): ?>
                <span class="badge"><?php echo $request_count; ?></span>
            <?php endif; ?>
        </a>

        <a href="settings.php">
            <span><i class="fas fa-user-cog"></i> <span>Settings</span></span>
        </a>

        <a href="logout.php" style="margin-top: 10px;">
            <span><i class="fas fa-sign-out-alt" style="color: #e74c3c;"></i> <span style="color: #e74c3c;">Logout</span></span>
        </a>
    </div>
</div>

<div class="main-container">
    <div class="top-nav">
        <h2 style="margin: 0; color: #2c3e50; font-size: 20px;">
            <i class="fas fa-user-circle" style="color: #e67e22; margin-right: 8px;"></i> 
            Welcome, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Instructor'); ?>
        </h2>
        <span style="color: #7f8c8d; font-size: 14px; font-weight: 500;">Instructor Portal</span>
    </div>

    <?php if($request_count > 0): ?>
    <div class="request-alert">
        <div>
            <i class="fas fa-bell" style="color: #e67e22; margin-right: 10px;"></i>
            <strong>Action Required:</strong> You have <?php echo $request_count; ?> new leave requests.
        </div>
        <a href="view_leave_requests.php" class="report-btn" style="flex: none; padding: 6px 15px; font-size: 12px;">View All</a>
    </div>
    <?php endif; ?>

    <h3 style="margin-top: 35px; color: #2c3e50;">My Assigned Courses</h3>

    <div class="course-grid">
    <?php if(!empty($courses)): ?>
        <?php foreach($courses as $course): 
            $total = (int)($course['total_sessions'] ?? 0);
            $taken = (int)$course['sessions_taken'];
            $percent = ($total > 0) ? min(($taken / $total) * 100, 100) : 0;
        ?>
            <div class="course-card">
                <h3 style="margin: 0 0 10px 0;"><?php echo htmlspecialchars($course['course_name']); ?></h3>
                <div style="font-size: 12px; color: #95a5a6; margin-bottom: 15px;">
                    <code><?php echo htmlspecialchars($course['course_code']); ?></code> | Batch: <?php echo htmlspecialchars($course['batch'] ?? 'N/A'); ?>
                </div>

                <div class="session-info">
                    <span>Sessions: <strong><?php echo $taken; ?> / <?php echo $total; ?></strong></span>
                    <span style="color: #e67e22; font-weight: bold;"><?php echo round($percent); ?>%</span>
                </div>

                <div class="progress-container">
                    <div class="progress-bar" style="width: <?php echo $percent; ?>%; background: #e67e22;"></div>
                </div>
                
                <div class="action-btns">
                    <a href="generate_qr.php?course_id=<?php echo $course['course_id']; ?>" class="mark-btn"><i class="fas fa-qrcode"></i> QR</a>
                    <a href="view_report.php?course_id=<?php echo $course['course_id']; ?>" class="report-btn"><i class="fas fa-file-alt"></i> Report</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    </div>
</div>

</body>
</html>