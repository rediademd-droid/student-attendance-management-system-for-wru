<?php
session_start();
require_once 'db_connect.php';

// --- 1. Verify User Session ---
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$current_user_id = $_SESSION['user_id'];
$role = strtolower(trim($_SESSION['role'])); // roles: admin, instructor, student
$course_id = isset($_GET['course_id']) ? $_GET['course_id'] : null;
$dept_filter = isset($_GET['dept']) ? $_GET['dept'] : ''; 

if (!$course_id) {
    die("Error: Course ID is missing. Please go back and select a course.");
}

// --- 2. Data Retrieval Logic ---
try {
    $c_stmt = $conn->prepare("SELECT course_name, course_code, total_sessions FROM courses WHERE course_id = ?");
    $c_stmt->execute([$course_id]);
    $course = $c_stmt->fetch();
    $total_planned = (int)($course['total_sessions'] ?? 0);

    $query = "SELECT 
                u.user_id, u.full_name, u.university_id, u.department,
                COUNT(DISTINCT ar.record_id) as attended_days,
                MAX(CASE WHEN DATE(ar.scan_time) = CURDATE() THEN 1 ELSE 0 END) as scanned_today,
                MAX(CASE WHEN lr.status = 'approved' AND DATE(lr.request_date) = CURDATE() THEN 1 ELSE 0 END) as on_leave_today
              FROM users u
              LEFT JOIN attendance_records ar ON u.user_id = ar.student_id AND ar.course_id = :cid
              LEFT JOIN leave_requests lr ON u.user_id = lr.student_id AND lr.course_id = :cid
              WHERE u.role = 'student'";
    
    if (!empty($dept_filter)) { $query .= " AND u.department = :dept"; }
    if ($role === 'student') { $query .= " AND u.user_id = :sid"; }

    $query .= " GROUP BY u.user_id ORDER BY u.full_name ASC";
    $stmt = $conn->prepare($query);
    $params = ['cid' => $course_id];
    
    if (!empty($dept_filter)) { $params['dept'] = $dept_filter; }
    if ($role === 'student') { $params['sid'] = $current_user_id; }
    
    $stmt->execute($params); 
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

// --- 3. Excel Export Logic (Updated: ONLY Instructor can export) ---
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    // Admin and Student are denied export access
    if ($role !== 'instructor') { 
        die("Access Denied: Only instructors are permitted to export attendance data."); 
    }
    
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="Attendance_Report.xls"');
    
    echo "Full Name\tDept\tUniversity ID\tAttended\tTotal\tPercentage\n";
    
    foreach ($reports as $r) {
        $attended = (int)$r['attended_days'];
        $percent = ($total_planned > 0) ? round(($attended / $total_planned) * 100, 2) : 0;
        
        echo htmlspecialchars($r['full_name']) . "\t" . 
             htmlspecialchars($r['department']) . "\t" . 
             htmlspecialchars($r['university_id']) . "\t" . 
             $attended . "\t" . 
             $total_planned . "\t" . 
             $percent . "%\n";
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attendance Summary | WRU-IoT</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { 
            --sidebar-bg: #2c3e50; 
            --sidebar-hover: #34495e;
            --accent: #e67e22; 
            --success: #27ae60; 
            --danger: #e74c3c; 
        }
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; margin: 0; display: flex; }

        .sidebar { width: 260px; background: var(--sidebar-bg); height: 100vh; color: white; position: fixed; display: flex; flex-direction: column; }
        .sidebar-header { padding: 30px 20px; font-size: 20px; font-weight: bold; color: var(--accent); text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .menu-list { list-style: none; padding: 0; margin: 20px 0; }
        .menu-list li a { display: flex; align-items: center; padding: 15px 25px; color: #ecf0f1; text-decoration: none; transition: 0.3s; }
        .menu-list li a i { margin-right: 15px; width: 20px; font-size: 18px; }
        .menu-list li a:hover, .menu-list li.active a { background: var(--sidebar-hover); color: var(--accent); }
        .logout-item { margin-top: auto; border-top: 1px solid rgba(255,255,255,0.1); }

        .main-content { margin-left: 260px; padding: 40px; width: 100%; }
        .card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .header-flex { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
        
        .filter-box { background: #f8f9fa; padding: 15px; border-radius: 10px; margin-bottom: 25px; display: flex; gap: 15px; align-items: center; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 15px; background: #f8f9fa; color: #7f8c8d; border-bottom: 2px solid #eee; }
        td { padding: 15px; border-bottom: 1px solid #eee; }
        .badge { padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .status-good { background: #e8f5e9; color: var(--success); }
        .status-bad { background: #ffebee; color: var(--danger); }
        .btn-excel { background: var(--success); color: white; padding: 10px 15px; text-decoration: none; border-radius: 8px; font-size: 14px; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">
        <?php 
            if($role == 'admin') echo "WRU-IoT Admin";
            elseif($role == 'instructor') echo "WRU-IoT Instructor";
            else echo "WRU-IoT Student";
        ?>
    </div>
    <ul class="menu-list">
        <li><a href="<?php echo $role; ?>_dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a></li>
        <?php if($role == 'admin'): ?>
            <li><a href="manage_users.php"><i class="fas fa-users"></i> Manage Users</a></li>
            <li><a href="manage_courses.php"><i class="fas fa-book"></i> Courses</a></li>
            <li class="active"><a href="#"><i class="fas fa-file-alt"></i> Attendance Reports</a></li>
        <?php elseif($role == 'instructor'): ?>
            <li><a href="view_leave_requests.php"><i class="fas fa-envelope-open-text"></i>view_leave_requests.php</a></li>
            <li class="active"><a href="#"><i class="fas fa-chart-bar"></i> Attendance Reports</a></li>
        <?php else: ?>
            <li><a href="student_scan.php"><i class="fas fa-qrcode"></i> Scan Attendance</a></li>
            <li><a href="view_leave_requests.php"><i class="fas fa-paper-plane"></i> view_leave_requests.php</a></li>
        <?php endif; ?>
        <li class="logout-item"><a href="logout.php" style="color: #e74c3c;"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="card">
        <div class="header-flex">
            <div>
                <h1 style="margin:0; color:var(--sidebar-bg);"><?php echo htmlspecialchars($course['course_name'] ?? 'Attendance Report'); ?></h1>
                <p style="color:#7f8c8d;">Code: <?php echo htmlspecialchars($course['course_code'] ?? 'N/A'); ?> | Planned Sessions: <?php echo $total_planned; ?></p>
            </div>
            
            <?php 
            // CHANGED: Only show export button to Instructors
            if ($role === 'instructor'): ?>
                <a href="view_report.php?course_id=<?php echo $course_id; ?>&dept=<?php echo $dept_filter; ?>&export=excel" class="btn-excel">
                    <i class="fas fa-file-excel"></i> Export to Excel
                </a>
            <?php endif; ?>
        </div>

        <?php if ($role !== 'student'): ?>
        <form class="filter-box" method="GET">
            <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
            <label>Filter by Department:</label>
            <select name="dept" style="padding:8px; border-radius:5px;">
                <option value="">All Departments</option>
                 <option value="Computer Science" <?php if($dept_filter == 'Computer Science') echo 'selected'; ?>>Computer Science</option>
                <option value="Information Technology" <?php if($dept_filter == 'Information Technology') echo 'selected'; ?>>Information Technology</option>
                 <option value="Information Sience" <?php if($dept_filter == 'Information Sience') echo 'selected'; ?>>Information Sience</option>
                 <option value="Electrical Engineering" <?php if($dept_filter == 'Electrical Engineering') echo 'selected'; ?>>Electrical Engineering</option>
                 <option value="Mechanical Engineering" <?php if($dept_filter == 'Mechanical Engineering') echo 'selected'; ?>>Mechanical Engineering</option>
                  <option value="Water Suplly $ Environmental Engineering" <?php if($dept_filter == 'Water Suplly $ Environmental Engineering') echo 'selected'; ?>>Water Suplly $ Environmental Engineering</option>
            </select>
            <button type="submit" style="background:var(--sidebar-bg); color:white; border:none; padding:8px 15px; border-radius:5px; cursor:pointer;">Apply Filter</button>
        </form>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>University ID</th>
                    <th>Today's Status</th>
                    <th>Total Attended</th>
                    <th>Attendance %</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($reports as $r): 
                    $attended = (int)$r['attended_days'];
                    $percent = ($total_planned > 0) ? round(($attended / $total_planned) * 100, 1) : 0;
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($r['full_name']); ?></td>
                    <td><strong><?php echo htmlspecialchars($r['university_id']); ?></strong></td>
                    <td>
                        <?php if ($r['on_leave_today']): ?>
                            <span class="badge" style="background:#fff3e0; color:#ef6c00;">On Leave</span>
                        <?php elseif ($r['scanned_today']): ?>
                            <span class="badge status-good">Present</span>
                        <?php else: ?>
                            <span class="badge status-bad">Absent</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $attended; ?> / <?php echo $total_planned; ?></td>
                    <td>
                        <span class="badge <?php echo ($percent >= 75) ? 'status-good' : 'status-bad'; ?>">
                            <?php echo $percent; ?>%
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>