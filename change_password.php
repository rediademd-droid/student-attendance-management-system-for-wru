<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$message = "";

if (isset($_POST['update_btn'])) {
    $uid = $_SESSION['user_id'];
    $old = $_POST['old_pass'];
    $new = $_POST['new_pass'];
    $confirm = $_POST['confirm_pass'];
    $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->execute([$uid]);
    $user = $stmt->fetch();

    if ($new !== $confirm) {
        $message = "<p style='color:red;'>New password and confirmation do not match!</p>";
    } elseif (!password_verify($old, $user['password']) && $old !== $user['password']) {
        $message = "<p style='color:red;'>The current password you entered is incorrect!</p>";
    } else {
   
        $hashed_pass = password_hash($new, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        
        if ($update->execute([$hashed_pass, $uid])) {
            $message = "<p style='color:green;'>Password updated successfully!</p>";
        } else {
            $message = "<p style='color:red;'>An error occurred. Please try again.</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password | WRU-IoT</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: #f4f7f6; font-family: 'Segoe UI', sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .pw-container { width: 100%; max-width: 400px; padding: 30px; background: white; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        h2 { color: #2c3e50; text-align: center; margin-bottom: 25px; }
        label { display: block; font-size: 14px; color: #7f8c8d; margin-bottom: 5px; }
        input { width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; outline: none; transition: 0.3s; }
        input:focus { border-color: #e67e22; }
        button { width: 100%; padding: 12px; background: #e67e22; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; transition: 0.3s; }
        button:hover { background: #d35400; transform: translateY(-1px); }
        .back-link { display: block; text-align: center; margin-top: 20px; text-decoration: none; color: #95a5a6; font-size: 14px; }
        .back-link:hover { color: #2c3e50; }
    </style>
</head>
<body>
    <div class="pw-container">
        <h2>Change Password</h2>
        
        <?php echo $message; ?>
        
        <form action="change_password.php" method="POST">
            <label>Current Password:</label>
            <input type="password" name="old_pass" required>
            
            <label>New Password:</label>
            <input type="password" name="new_pass" required>
            
            <label>Confirm New Password:</label>
            <input type="password" name="confirm_pass" required>
            
            <button type="submit" name="update_btn">Update Password</button>
        </form>
        
        <a href="instructor_dashboard.php" class="back-link">← Back to Dashboard</a>
    </div>
</body>
</html>