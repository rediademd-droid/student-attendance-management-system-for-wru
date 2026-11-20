<?php
session_start();

// 1. Security Check: Ensure user is logged in and has the 'student' role
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'student') {
    header("Location: index.php");
    exit();
}

require_once 'db_connect.php';

$student_id = $_SESSION['user_id'];

// 2. Fetch Course Statistics
try {
    /**
     * Note: If attendance shows 0%, ensure the 'status' values in your 
     * attendance_records table match the case used here (e.g., 'present').
     */
    $stmt = $conn->prepare("
        SELECT 
            c.course_id,
            c.course_name, 
            c.course_code,
            c.total_sessions, 
            c.min_attendance_req,
            (SELECT COUNT(DISTINCT session_id) FROM attendance_records a 
             WHERE a.course_id = c.course_id AND a.student_id = :sid AND LOWER(a.status) = 'present') as attended_days
        FROM courses c
    ");
    $stmt->execute(['sid' => $student_id]);
    $courses_stats = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

// 3. Calculate Overall Average
$total_conducted = 0;
$total_attended = 0;

foreach ($courses_stats as $row) {
    $total_conducted += $row['total_sessions'];
    $total_attended += $row['attended_days'];
}

$overall_percentage = ($total_conducted > 0) ? round(($total_attended / $total_conducted) * 100, 1) : 0;

// Cap at 100% to prevent logical errors from extra sessions
if ($overall_percentage > 100) $overall_percentage = 100; 

// Determine status color (Red for < 80%, Green for >= 80%)
$overall_color = ($overall_percentage < 80) ? '#e74c3c' : '#2ecc71';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | WRU-IoT</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --primary: #2c3e50; --accent: #e67e22; --danger: #e74c3c; --success: #2ecc71; --warning: #f1c40f; }
        body { background: #f4f7f6; margin: 0; font-family: 'Segoe UI', sans-serif; display: flex; }
        
        .sidebar { width: 260px; background: var(--primary); color: white; height: 100vh; position: fixed; padding-top: 20px; transition: 0.3s; }
        .sidebar h2 { text-align: center; color: var(--accent); margin-bottom: 30px; font-size: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px; }
        .sidebar a { display: block; padding: 15px 25px; color: #ecf0f1; text-decoration: none; border-left: 4px solid transparent; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { background: #34495e; color: var(--accent); border-left-color: var(--accent); }
        .sidebar i { margin-right: 10px; width: 20px; }

        .main-content { margin-left: 260px; width: calc(100% - 260px); padding: 40px; box-sizing: border-box; min-height: 100vh; }
        
        .header-bar { display: flex; justify-content: space-between; align-items: center; background: white; padding: 20px 30px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .id-badge { background: #f8f9fa; border: 1px solid #ddd; padding: 8px 20px; border-radius: 30px; font-size: 14px; font-weight: bold; color: var(--primary); display: flex; align-items: center; gap: 8px; }

        .dashboard-grid { display: grid; grid-template-columns: 1fr 2fr; gap: 30px; }
        .stats-box { background: white; padding: 30px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); text-align: center; }
        
        .percentage-circle {
            width: 150px; height: 150px; border-radius: 50%;
            border: 12px solid #eee;
            border-top-color: <?php echo $overall_color; ?>; 
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            margin: 20px auto; font-size: 32px; font-weight: bold; color: var(--primary);
        }

        .course-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .course-table th { text-align: left; padding: 15px; color: #7f8c8d; border-bottom: 2px solid #eee; font-size: 13px; text-transform: uppercase; }
        .course-table td { padding: 18px 15px; border-bottom: 1px solid #f9f9f9; vertical-align: middle; }
        
        .progress-container { width: 100%; background: #eee; border-radius: 10px; height: 8px; margin-top: 8px; overflow: hidden; }
        .progress-bar { height: 100%; border-radius: 10px; transition: 1s cubic-bezier(0.17, 0.67, 0.83, 0.67); }

        .warning-tag { color: var(--danger); font-size: 11px; font-weight: bold; display: flex; align-items: center; gap: 4px; margin-top: 6px; }

        .btn-view { color: var(--accent); text-decoration: none; font-weight: bold; font-size: 13px; transition: 0.2s; }
        .btn-view:hover { color: #d35400; transform: translateX(3px); }

        .action-btns { display: flex; gap: 15px; justify-content: center; margin-top: 25px; }
        .btn { padding: 12px 20px; border-radius: 10px; text-decoration: none; font-weight: bold; color: white; transition: 0.3s; display: flex; align-items: center; gap: 8px; flex: 1; justify-content: center; }
        .btn-scan { background: var(--accent); }
        .btn-scan:hover { background: #d35400; box-shadow: 0 4px 12px rgba(230, 126, 34, 0.3); }
        .btn-leave { background: #3498db; }
        .btn-leave:hover { background: #2980b9; box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3); }

        @media (max-width: 1100px) { .dashboard-grid { grid-template-columns: 1fr; } }
        @media (max-width: 768px) { .sidebar { width: 0; overflow: hidden; } .main-content { margin-left: 0; width: 100%; padding: 20px; } }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>WRU-IoT</h2>
    <a href="student_dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a>
    <a href="student_scan.php"><i class="fas fa-qrcode"></i> Scan Attendance</a>
    <a href="request_leave.php"><i class="fas fa-paper-plane"></i> Leave Request</a>
     <a href="settings.php"><i class="fas fa-cog"></i> <span>Settings</span></a>
    <a href="logout.php" style="color: #e74c3c; margin-top: 30px;"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="main-content">
    <div class="header-bar">
        <h2 style="margin:0;">Welcome, <span style="color: var(--accent);"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Student'); ?></span>!</h2>
        <div class="id-badge">
            <i class="fas fa-id-badge"></i> 
            ID: <?php echo htmlspecialchars($_SESSION['university_id'] ?? $_SESSION['univ_id'] ?? 'N/A'); ?>
        </div>
    </div>

    <div class="dashboard-grid">
        <!-- Summary Box -->
        <div class="stats-box">
            <h3 style="margin:0; color: var(--primary);">Overall Attendance</h3>
            <div class="percentage-circle">
                <?php echo $overall_percentage; ?>%
                <span style="font-size: 11px; color: #999; font-weight: normal;">AVG</span>
            </div>
            <p style="color: #7f8c8d; font-size: 14px;">Total Sessions: <strong><?php echo $total_conducted; ?></strong></p>
            
            <?php if($overall_percentage < 80): ?>
                <div style="color: var(--danger); font-size: 12px; background: #feebee; padding: 15px; border-radius: 12px; margin: 20px 0; border: 1px solid rgba(231, 76, 60, 0.1);">
                    <i class="fas fa-exclamation-triangle"></i> <strong>Critical Warning:</strong><br> Attendance is below the 80% requirement!
                </div>
            <?php endif; ?>

            <div class="action-btns">
                <a href="student_scan.php" class="btn btn-scan"><i class="fas fa-camera"></i> Scan QR</a>
                <a href="request_leave.php" class="btn btn-leave"><i class="fas fa-envelope"></i> Leave</a>
            </div>
        </div>

        <!-- Course Progress Table -->
        <div class="stats-box" style="text-align: left;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="margin:0; color: var(--primary);">Course-wise Progress</h3>
                <span style="font-size: 12px; color: #999; background: #f8f9fa; padding: 4px 10px; border-radius: 5px;">Min Requirement: 80%</span>
            </div>
            
            <div style="overflow-x: auto;">
                <table class="course-table">
                    <thead>
                        <tr>
                            <th>Course Details</th>
                            <th style="text-align: center;">Sessions</th>
                            <th style="width: 35%;">Attendance Level</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($courses_stats)): ?>
                            <tr><td colspan="4" style="text-align:center; color:#999; padding:30px;">No courses assigned yet.</td></tr>
                        <?php endif; ?>

                        <?php foreach($courses_stats as $course): 
                            $raw_percent = ($course['total_sessions'] > 0) ? ($course['attended_days'] / $course['total_sessions']) * 100 : 0;
                            $percent = round($raw_percent, 1);
                            if ($percent > 100) $percent = 100;

                            $is_low = ($percent < 80);
                            $bar_color = $is_low ? 'var(--danger)' : 'var(--success)';
                        ?>
                        <tr>
                            <td>
                                <strong style="color: var(--primary); font-size: 15px;"><?php echo htmlspecialchars($course['course_code']); ?></strong><br>
                                <small style="color: #7f8c8d;"><?php echo htmlspecialchars($course['course_name']); ?></small>
                            </td>
                            <td style="text-align: center; font-weight: bold; font-size: 14px;">
                                <?php echo $course['attended_days']; ?><span style="color: #ccc; font-weight: normal;"> / <?php echo $course['total_sessions']; ?></span>
                            </td>
                            <td>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                    <span style="font-weight: bold; color: <?php echo $bar_color; ?>; font-size: 13px;">
                                        <?php echo $percent; ?>%
                                    </span>
                                    <?php if($is_low): ?>
                                        <i class="fas fa-arrow-down" style="color: var(--danger); font-size: 10px;"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="progress-container">
                                    <div class="progress-bar" style="width: <?php echo $percent; ?>%; background: <?php echo $bar_color; ?>;"></div>
                                </div>
                                <?php if($is_low): ?>
                                    <span class="warning-tag"><i class="fas fa-exclamation-circle"></i> Below Requirement</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right;">
                                <a href="view_report.php?course_id=<?php echo $course['course_id']; ?>" class="btn-view">
                                    Details <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</body>
</html>