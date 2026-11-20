<?php
session_start();
require_once 'db_connect.php';

// 1. Authentication Check: Verify user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = strtolower(trim($_SESSION['role']));
$msg = "";
$msg_class = "";

// 2. Password Change Logic (Universal for both roles)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_pw = $_POST['current_password'];
    $new_pw = $_POST['new_password'];
    $confirm_pw = $_POST['confirm_password'];

    try {
        $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if ($user && password_verify($current_pw, $user['password'])) {
            if ($new_pw === $confirm_pw) {
                if (strlen($new_pw) >= 6) {
                    $hashed_pw = password_hash($new_pw, PASSWORD_DEFAULT);
                    $update = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                    $update->execute([$hashed_pw, $user_id]);
                    
                    $msg = "Success! Password updated successfully.";
                    $msg_class = "success-msg";
                } else {
                    $msg = "New password must be at least 6 characters.";
                    $msg_class = "error-msg";
                }
            } else {
                $msg = "New passwords do not match!";
                $msg_class = "error-msg";
            }
        } else {
            $msg = "Current password is incorrect.";
            $msg_class = "error-msg";
        }
    } catch (PDOException $e) {
        $msg = "Error: " . $e->getMessage();
        $msg_class = "error-msg";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | WRU-IoT</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --sidebar-bg: #2c3e50; --accent: #e67e22; --bg: #f4f7f6; }
        body { margin: 0; font-family: 'Segoe UI', sans-serif; background: var(--bg); display: flex; height: 100vh; }
        
        /* Sidebar Styling */
        .sidebar { width: 260px; background: var(--sidebar-bg); color: white; display: flex; flex-direction: column; flex-shrink: 0; }
        .sidebar-header { padding: 30px 20px; font-size: 22px; font-weight: bold; color: var(--accent); text-align: center; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .sidebar-menu { list-style: none; padding: 0; margin: 20px 0; flex-grow: 1; display: flex; flex-direction: column; }
        .sidebar-menu li a { display: flex; align-items: center; padding: 15px 25px; color: #bdc3c7; text-decoration: none; transition: 0.3s; }
        .sidebar-menu li a:hover, .sidebar-menu li.active a { background: rgba(255,255,255,0.1); color: var(--accent); border-left: 4px solid var(--accent); }
        .sidebar-menu li a i { margin-right: 15px; width: 20px; }

        /* Content Styling */
        .main-content { flex-grow: 1; display: flex; justify-content: center; align-items: center; padding: 20px; }
        .settings-card { background: white; padding: 40px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); width: 100%; max-width: 450px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; color: #2c3e50; font-weight: 600; }
        .form-group input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; }
        .save-btn { background: var(--accent); color: white; border: none; padding: 12px; border-radius: 8px; cursor: pointer; font-weight: bold; width: 100%; transition: 0.3s; font-size: 16px; }
        .save-btn:hover { background: #d35400; }
        .success-msg { background: #d4edda; color: #155724; padding: 10px; border-radius: 8px; margin-bottom: 15px; text-align: center; }
        .error-msg { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 8px; margin-bottom: 15px; text-align: center; }
    </style>
</head>
<body>

    <!-- Dynamic Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">WRU-IoT <?php echo ucfirst($role); ?></div>
        <ul class="sidebar-menu">
            <!-- Navigation links change based on User Role -->
            <?php if($role === 'instructor'): ?>
                <li><a href="instructor_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="view_leave_requests.php"><i class="fas fa-envelope-open-text"></i> Leave Requests</a></li>
            <?php else: ?>
                <li><a href="student_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="scan_qr.php"><i class="fas fa-qrcode"></i> Scan Attendance</a></li>
                <li><a href="leave_request.php"><i class="fas fa-paper-plane"></i> Leave Request</a></li>
            <?php endif; ?>

            <li class="active"><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
            
            <li style="margin-top: auto;">
                <a href="logout.php" style="color: #e74c3c;"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </li>
        </ul>
    </div>

    <!-- Main Content Area -->
    <div class="main-content">
        <div class="settings-card">
            <h2 style="margin-top:0; color:#2c3e50;"><i class="fas fa-shield-alt"></i> Security Settings</h2>
            
            <?php if($msg): ?>
                <div class="<?php echo $msg_class; ?>"><?php echo $msg; ?></div>
            <?php endif; ?>

            <form action="settings.php" method="POST">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" required>
                </div>
                <button type="submit" name="change_password" class="save-btn">Update Password</button>
            </form>
        </div>
    </div>

</body>
</html>