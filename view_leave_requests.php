<?php
session_start();
require_once 'db_connect.php';

// 1. Security Check: Ensure the user is an instructor or admin
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'instructor' && $_SESSION['role'] !== 'admin')) {
    header("Location: index.php");
    exit();
}

// 2. Process Approval or Rejection Actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $leave_id = $_GET['id'];
    $new_status = ($_GET['action'] == 'approve') ? 'approved' : 'rejected';

    try {
        $stmt = $conn->prepare("UPDATE leave_requests SET status = ? WHERE leave_id = ?");
        $stmt->execute([$new_status, $leave_id]);
        header("Location: view_leave_requests.php?msg=updated");
        exit();
    } catch (PDOException $e) {
        $error_msg = "Database Error: " . $e->getMessage();
    }
}

// 3. Fetch requests - university_id is included here for student identification
try {
    $requests = $conn->query("
        SELECT lr.*, u.full_name, u.university_id 
        FROM leave_requests lr 
        JOIN users u ON lr.student_id = u.user_id 
        ORDER BY lr.request_date DESC
    ")->fetchAll();
} catch (PDOException $e) {
    die("Error fetching requests: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Leave Requests | WRU-IoT</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #f4f7f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; display: flex; margin: 0; }
        .sidebar { width: 260px; background: #2c3e50; color: white; height: 100vh; position: fixed; padding-top: 20px; }
        .sidebar h2 { text-align: center; color: #e67e22; font-size: 22px; margin-bottom: 30px; border-bottom: 1px solid #34495e; padding-bottom: 10px; }
        .sidebar a { display: block; padding: 15px 25px; color: #ecf0f1; text-decoration: none; transition: 0.3s; }
        .sidebar a:hover { background: #34495e; color: #e67e22; }
        .active-nav { background: #34495e; color: #e67e22 !important; border-left: 4px solid #e67e22; }
        .main-content { margin-left: 260px; width: calc(100% - 260px); padding: 40px; box-sizing: border-box; }
        .title { color: #2c3e50; margin-bottom: 25px; font-weight: bold; display: flex; align-items: center; gap: 12px; }
        .table-container { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; }
        th { background: #e67e22; color: white; padding: 18px; text-align: left; text-transform: uppercase; font-size: 13px; letter-spacing: 0.5px; }
        td { padding: 15px; border-bottom: 1px solid #eee; color: #34495e; font-size: 14px; }
        
        .student-id { display: block; font-size: 11px; color: #e67e22; font-weight: bold; margin-top: 3px; }
        
        .badge { padding: 6px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .pending { background: #fff9c4; color: #f57f17; }
        .approved { background: #c8e6c9; color: #2e7d32; }
        .rejected { background: #ffcdd2; color: #c62828; }
        .btn { padding: 8px 14px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: bold; display: inline-flex; align-items: center; gap: 6px; transition: 0.2s; border: none; cursor: pointer; }
        .btn-approve { background: #2ecc71; color: white; margin-right: 5px; }
        .btn-reject { background: #e74c3c; color: white; }
        .view-link { color: #3498db; text-decoration: none; font-weight: 600; }
        .no-data { text-align: center; padding: 60px; color: #95a5a6; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>WRU-IoT</h2>
    <a href="instructor_dashboard.php"><i class="fas fa-home"></i> <span>Dashboard</span></a>
    <a href="view_leave_requests.php" class="active-nav"><i class="fas fa-envelope-open-text"></i> <span>Leave Requests</span></a>
    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
</div>

<div class="main-content">
    <h2 class="title"><i class="fas fa-user-clock" style="color: #e67e22;"></i> Student Leave Requests</h2>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Student Info</th>
                    <th>Reason</th>
                    <th>Evidence</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($requests) > 0): ?>
                    <?php foreach($requests as $r): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($r['full_name']); ?></strong>
                            <span class="student-id"><?php echo htmlspecialchars($r['university_id']); ?></span>
                        </td>
                        <td>
                            <?php 
                                $reason = htmlspecialchars($r['reason']);
                                echo (strlen($reason) > 50) ? substr($reason, 0, 50) . "..." : $reason; 
                            ?>
                        </td>
                        <td>
                            <?php if(!empty($r['document_path'])): ?>
                                <a href="<?php echo $r['document_path']; ?>" target="_blank" class="view-link">
                                    <i class="fas fa-file-download"></i> View File
                                </a>
                            <?php else: ?>
                                <span style="color:#bdc3c7;">No Evidence</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge <?php echo htmlspecialchars($r['status']); ?>">
                                <?php echo htmlspecialchars($r['status']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if($r['status'] == 'pending'): ?>
                                <a href="?action=approve&id=<?php echo $r['leave_id']; ?>" class="btn btn-approve" onclick="return confirm('Are you sure you want to approve this request?')">
                                    <i class="fas fa-check"></i>
                                </a>
                                <a href="?action=reject&id=<?php echo $r['leave_id']; ?>" class="btn btn-reject" onclick="return confirm('Are you sure you want to reject this request?')">
                                    <i class="fas fa-times"></i>
                                </a>
                            <?php else: ?>
                                <span style="color:#95a5a6; font-size: 12px;">Processed</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="no-data">No leave requests found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>