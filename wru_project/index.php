<?php
session_start();

// If the user is already logged in, redirect them directly to their respective dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    $role = strtolower(trim($_SESSION['role']));
    if ($role === 'admin') {
        header("Location: admin_dashboard.php");
    } elseif ($role === 'instructor' || $role === 'teacher') {
        header("Location: instructor_dashboard.php");
    } elseif ($role === 'student') {
        header("Location: student_dashboard.php");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WRU-IoT Attendance | Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0f172a;
            overflow: hidden;
        }

        /* Page layout structure */
        .page-wrapper {
            display: flex;
            width: 100%;
            height: 100vh;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        /* Background image containers */
        .side-image-container {
            position: fixed;
            top: 0;
            width: 50%;
            height: 100vh;
            z-index: 1;
            overflow: hidden;
        }
        
        .left-bg { left: 0; }
        .right-bg { right: 0; }

        .side-image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: brightness(0.6);
        }

        /* Subtle Blur Overlay */
        .side-image-container::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            backdrop-filter: blur(2px);
            -webkit-backdrop-filter: blur(2px);
            background: rgba(15, 23, 42, 0.2);
        }

        /* Login Card Section */
        .login-section {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.98); 
            padding: 40px;
            border-radius: 25px;
            /* Stronger shadow for the card to give depth */
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4), 0 10px 10px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        .university-logo { 
            width: 80px; 
            height: auto; 
            margin-bottom: 10px;
            /* Logo shadow */
            filter: drop-shadow(0 5px 5px rgba(0,0,0,0.1));
        }

        .login-card h2 { 
            color: #1e293b; 
            margin: 0; 
            font-size: 24px; 
            font-weight: 800;
            /* Subtle text shadow */
            text-shadow: 1px 1px 2px rgba(0,0,0,0.05);
        }

        .login-card p.title-sub { 
            font-size: 26px; 
            font-weight: bold; 
            color:black; 
            margin: 10px 0; 
            /* Subtle text shadow for the title */
            text-shadow: 1px 2px 4px rgba(230, 126, 34, 0.2);
        }
        
        .input-group { margin-bottom: 18px; position: relative; text-align: left; }
        .input-group i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #e67e22; }
        
        .input-group input { 
            width: 100%; padding: 14px 12px 14px 45px; border: 1px solid #e2e8f0; 
            border-radius: 12px; outline: none; box-sizing: border-box; font-size: 15px; background: #f8fafc;
            transition: 0.3s;
            /* Input inner shadow */
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
        }
        
        .input-group input:focus { 
            border-color: #e67e22; 
            background: #fff; 
            /* Focus shadow */
            box-shadow: 0 0 15px rgba(230, 126, 34, 0.15); 
        }

        .login-btn {
            background: #e67e22; color: white; border: none; width: 100%; padding: 15px;
            border-radius: 12px; font-size: 17px; font-weight: bold; cursor: pointer;
            transition: 0.3s; margin-top: 10px;
            /* Button shadow */
            box-shadow: 0 8px 20px rgba(230, 126, 34, 0.3);
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }

        .login-btn:hover { 
            background: #d35400; 
            transform: translateY(-2px); 
            /* Hover shadow */
            box-shadow: 0 12px 25px rgba(211, 84, 0, 0.4);
        }

        .signup-link { margin-top: 25px; font-size: 14px; color: #475569; }
        .signup-link a { color: #e67e22; text-decoration: none; font-weight: bold; }

        .footer-note { margin-top: 25px; font-size: 12px; color: #94a3b8; }
        .footer-note a { color: #475569; text-decoration: none; font-weight: 600; display: inline-block; margin-bottom: 10px; }
        
        .error-msg { 
            background: #fef2f2; color: #dc2626; padding: 12px; border-radius: 10px; 
            margin-bottom: 20px; font-size: 13px; border: 1px solid #fee2e2;
            /* Error alert shadow */
            box-shadow: 0 4px 6px rgba(220, 38, 38, 0.05);
        }

        /* Responsive */
        @media (max-width: 600px) {
            .side-image-container { display: none; }
            body { background: #0f172a; }
            .login-card { padding: 30px 20px; }
        }
    </style>
</head>
<body>

<div class="page-wrapper">
    <div class="side-image-container left-bg">
        <img src="image3.png" alt="Background">
    </div>
    <div class="side-image-container right-bg">
        <img src="image3.png" alt="Background">
    </div>

    <div class="login-section">
        <div class="login-card">
            <img src="wu_logo.png" alt="Werabe University Logo" class="university-logo">
            <h2>WRU-IoT</h2>
            <p class="title-sub">Smart Attendance</p>

            <?php if(isset($_GET['error'])): ?>
                <div class="error-msg">
                    <i class="fas fa-exclamation-circle"></i> 
                    <?php 
                        if($_GET['error'] == "invalid_role") echo "Access Denied: Invalid User Role!";
                        elseif($_GET['error'] == "user_not_found") echo "User ID not found!";
                        elseif($_GET['error'] == "wrong_password") echo "Incorrect Password!";
                        else echo "Invalid ID or Password!";
                    ?>
                </div>
            <?php endif; ?>

            <form action="login_process.php" method="POST" autocomplete="off">
                <div class="input-group">
                    <i class="fas fa-id-card"></i>
                    <input type="text" name="university_id" placeholder="University ID" required autofocus>
                </div>
                
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="Password" required>
                </div>

                <button type="submit" class="login-btn">
                    Login <i class="fas fa-sign-in-alt" style="margin-left: 8px;"></i>
                </button>
            </form>

            <div class="signup-link">
                Don't have an account? <a href="signup.php">Sign Up Now</a>
            </div>

            <div class="footer-note">
                <a href="home.php"><i class="fas fa-arrow-left"></i> Back to home </a><br>
                &copy; <?php echo date("Y"); ?> Werabe University - IoT Dept.
            </div>
        </div>
    </div>
</div>

</body>
</html>