<?php
session_start();
require_once 'db_connect.php';

// 1. Security Check: Ensure the user is a student
if (!isset($_SESSION['role']) || strtolower(trim($_SESSION['role'])) !== 'student') {
    header("Location: index.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$student_univ_id = $_SESSION['university_id'] ?? 'Unknown ID';

// 2. Fetch ONLY the courses and their specific instructors
try {
    $course_query = $conn->prepare("SELECT course_id, course_name, course_code, instructor_id FROM courses");
    $course_query->execute();
    $courses = $course_query->fetchAll();
} catch (PDOException $e) {
    $courses = [];
}

// 3. Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $reason = trim($_POST['reason']);
    $selected_course = $_POST['course_id']; 
    
    // FETCH the specific instructor for the selected course to ensure privacy
    $inst_stmt = $conn->prepare("SELECT instructor_id FROM courses WHERE course_id = ?");
    $inst_stmt->execute([$selected_course]);
    $course_data = $inst_stmt->fetch();
    $target_instructor_id = $course_data['instructor_id'] ?? null;

    if (!$target_instructor_id) {
        echo "<script>alert('Error: This course has no assigned instructor.'); window.history.back();</script>";
        exit();
    }

    $target_dir = "uploads/documents/";
    if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
    
    $doc_path = "";
    if (!empty($_FILES["doc"]["name"])) {
        $file_ext = pathinfo($_FILES["doc"]["name"], PATHINFO_EXTENSION);
        $file_name = time() . "_" . $student_id . "." . $file_ext;
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES["doc"]["tmp_name"], $target_file)) {
            $doc_path = $target_file;
        }
    }

    try {
        // Insert with instructor_id to lock visibility to that specific person
        $stmt = $conn->prepare("INSERT INTO leave_requests (student_id, course_id, instructor_id, reason, document_path, status, request_date) VALUES (?, ?, ?, ?, ?, 'pending', NOW())");
        if ($stmt->execute([$student_id, $selected_course, $target_instructor_id, $reason, $doc_path])) {
            echo "<script>alert('Leave request submitted privately to the course instructor!'); window.location='request_leave.php';</script>";
        }
    } catch (PDOException $e) {
        echo "<script>alert('Database Error. Please try again.');</script>";
    }
}

// 4. Student View: History of their own requests
try {
    $history_query = $conn->prepare("
        SELECT lr.*, c.course_name 
        FROM leave_requests lr 
        JOIN courses c ON lr.course_id = c.course_id 
        WHERE lr.student_id = ? 
        ORDER BY lr.leave_id DESC
    ");
    $history_query->execute([$student_id]);
    $my_requests = $history_query->fetchAll();
} catch (PDOException $e) {
    $my_requests = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Private Leave Request | WRU-IoT</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --primary: #2c3e50; --accent: #e67e22; --bg: #f4f7f6; }
        body { background: var(--bg); margin: 0; font-family: 'Segoe UI', sans-serif; display: flex; }
        .sidebar { width: 260px; background: var(--primary); color: white; height: 100vh; position: fixed; }
        .sidebar h2 { text-align: center; color: var(--accent); padding: 20px 0; border-bottom: 1px solid #34495e; margin: 0; }
        .sidebar a { display: block; padding: 15px 25px; color: #ecf0f1; text-decoration: none; border-left: 4px solid transparent; }
        .sidebar a:hover, .active-nav { background: #34495e; color: var(--accent); border-left: 4px solid var(--accent); }
        .main-content { margin-left: 260px; width: calc(100% - 260px); padding: 30px; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; background: #fff; padding: 15px 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 25px; }
        .student-id-box { background: var(--accent); color: white; padding: 6px 15px; border-radius: 20px; font-weight: bold; }
        .form-card, .history-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 25px; }
        h3 { margin-top: 0; color: var(--primary); border-bottom: 2px solid #eee; padding-bottom: 10px; }
        select, textarea, input[type="file"] { width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; }
        .btn-submit { background: var(--accent); color: white; padding: 14px; width: 100%; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        .status { padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: bold; }
        .pending { background: #fff3e0; color: #e67e22; }
        .approved { background: #e8f5e9; color: #2ecc71; }
        .rejected { background: #ffebee; color: #e74c3c; }
    </style>
</head>
<body>

<aside class="sidebar">
    <h2>WRU-IoT</h2>
    <a href="student_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
    <a href="request_leave.php" class="active-nav"><i class="fas fa-calendar-plus"></i> Leave Request</a>
    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</aside>

<main class="main-content">
    <div class="top-bar">
        <span>Student Portal</span>
        <div class="student-id-box">ID: <?php echo htmlspecialchars($student_univ_id); ?></div>
    </div>

    <div class="form-card">
        <h3><i class="fas fa-paper-plane"></i> Private Leave Request</h3>
        <p style="font-size: 13px; color: #777;">* Your request will only be visible to the specific instructor of the chosen course.</p>
        <form action="" method="POST" enctype="multipart/form-data">
            <label>Course</label>
            <select name="course_id" required>
                <option value="">-- Choose Course --</option>
                <?php foreach($courses as $course): ?>
                    <option value="<?php echo $course['course_id']; ?>">
                        <?php echo htmlspecialchars($course['course_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Reason</label>
            <textarea name="reason" rows="3" required placeholder="State your reason..."></textarea>
            
            <label>Evidence Document</label>
            <input type="file" name="doc" accept=".pdf,image/*">
            
            <button type="submit" class="btn-submit">Submit Privately</button>
        </form>
    </div>

    <div class="history-card">
        <h3><i class="fas fa-history"></i> Your Requests</h3>
        <table>
            <thead>
                <tr>
                    <th>Course</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($my_requests as $req): ?>
                <tr>
                    <td><?php echo htmlspecialchars($req['course_name']); ?></td>
                    <td>
                        <span class="status <?php echo htmlspecialchars($req['status']); ?>">
                            <?php echo ucfirst($req['status']); ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

</body>
</html>