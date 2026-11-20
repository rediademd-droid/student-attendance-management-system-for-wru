<?php
session_start();
require_once 'db_connect.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $univ_id = trim($_POST['university_id']);
    $pass = $_POST['password'];

    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE university_id = :uid LIMIT 1");
        $stmt->execute(['uid' => $univ_id]);
        $user = $stmt->fetch();

        if ($user) {
            /**
             * Password Verification
             * Note: '123456' is included for testing purposes. 
             * In production, only password_verify() should be used.
             */
            if ($pass === '123456' || password_verify($pass, $user['password'])) {
                
                // Registering Sessions - Note the specific key names used here
                $_SESSION['user_id']       = $user['user_id'];
                $_SESSION['university_id'] = $user['university_id']; 
                $_SESSION['user_name']     = $user['full_name'];
                $_SESSION['role']          = $user['role'];
                $_SESSION['dept']          = $user['department'] ?? 'ICT';

                // Redirect based on User Role
                switch (strtolower($user['role'])) {
                    case 'admin':
                        header("Location: admin_dashboard.php");
                        break;
                    case 'instructor':
                        header("Location: instructor_dashboard.php");
                        break;
                    case 'student':
                        header("Location: student_dashboard.php");
                        break;
                    default:
                        header("Location: index.php?error=unknown_role");
                        break;
                }
                exit();
            } else {
                header("Location: index.php?error=wrong_password");
                exit();
            }
        } else {
            header("Location: index.php?error=user_not_found");
            exit();
        }
    } catch (PDOException $e) {
        // Log the error message in a real production environment instead of using die()
        die("A system error occurred: " . $e->getMessage());
    }
}
?>