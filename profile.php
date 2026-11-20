<?php
// 1. Initialize the session at the very top
session_start();

// 2. Include database connection
require_once 'db_connect.php';

/**
 * AUTHENTICATION CHECK
 * If the session 'user_id' is not set, the user is not logged in.
 * Redirect them to index.php (login page).
 */
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // 3. Fetch user data from the database based on the session ID
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    // 4. Safety check: if the user exists in the session but not in the DB
    if (!$user) {
        session_destroy();
        header("Location: index.php?error=usernotfound");
        exit();
    }
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile | WRU-IoT</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #e67e22;
            --dark: #2c3e50;
            --light: #f4f7f6;
            --white: #ffffff;
            --text: #333333;
            --gray: #7f8c8d;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: var(--light); color: var(--text); }

        /* Navigation */
        nav {
            background: var(--white);
            padding: 15px 10%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .logo { font-size: 24px; font-weight: 800; color: var(--primary); text-decoration: none; }
        .logo span { color: var(--dark); }
        nav ul { display: flex; list-style: none; gap: 20px; }
        nav ul li a { text-decoration: none; color: var(--gray); font-weight: 500; transition: 0.3s; }
        nav ul li a:hover { color: var(--primary); }

        /* Profile Layout */
        .profile-container {
            max-width: 1100px;
            margin: 50px auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
        }

        /* Sidebar Card */
        .profile-card {
            background: var(--white);
            padding: 30px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            height: fit-content;
        }

        .profile-image {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            background: #fef5ed;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 4px solid var(--primary);
        }
        .profile-image i { font-size: 60px; color: var(--primary); }

        .profile-card h3 { color: var(--dark); margin-bottom: 5px; text-transform: capitalize; }
        .role-tag { color: var(--primary); font-weight: bold; text-transform: uppercase; font-size: 14px; margin-bottom: 20px; }

        .btn-edit {
            background: var(--primary);
            color: var(--white);
            padding: 12px 20px;
            border-radius: 8px;
            text-decoration: none;
            display: block;
            font-weight: 600;
            transition: 0.3s;
            margin-bottom: 10px;
        }
        .btn-edit:hover { background: #d35400; transform: translateY(-2px); }

        /* Main Details */
        .profile-details {
            background: var(--white);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .detail-group {
            margin-bottom: 25px;
            border-bottom: 1px solid #f0f0f0;
            padding-bottom: 15px;
        }
        .detail-group h4 { color: var(--gray); font-size: 13px; text-transform: uppercase; margin-bottom: 8px; letter-spacing: 1px; }
        .detail-group p { color: var(--dark); font-size: 17px; font-weight: 500; }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 30px;
        }
        .stat-box { background: #fef5ed; padding: 20px; border-radius: 15px; text-align: center; }
        .stat-box h2 { color: var(--primary); margin-bottom: 5px; }
        .stat-box span { font-size: 12px; color: var(--gray); font-weight: 600; }

        footer { padding: 30px; background: var(--dark); color: var(--white); text-align: center; margin-top: 50px; }
        
        @media (max-width: 768px) { .profile-container { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

    <nav>
        <a href="home.php" class="logo">WRU-<span>IoT</span></a>
        <ul>
            <li><a href="home.php">Home</a></li>
            <li><a href="about.php">About</a></li>
            <li><a href="help.php">Help</a></li>
            <li><a href="logout.php" style="color: #c0392b;"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>

    <div class="profile-container">
        <div class="profile-card">
            <div class="profile-image">
                <i class="fas fa-user"></i>
            </div>
            <h3><?php echo htmlspecialchars($user['full_name']); ?></h3>
            <p class="role-tag"><?php echo htmlspecialchars($user['role']); ?> Institute</p>
            
            <a href="edit_profile.php" class="btn-edit"><i class="fas fa-edit"></i> Edit Profile</a>
            
            <div style="margin-top: 25px; text-align: left; background: #f9f9f9; padding: 15px; border-radius: 10px;">
                <p style="color: var(--gray); font-size: 13px; margin-bottom: 5px;">
                    <i class="fas fa-id-card" style="margin-right: 8px;"></i> University ID
                </p>
                <p style="color: var(--dark); font-weight: bold;"><?php echo htmlspecialchars($user['university_id']); ?></p>
            </div>
        </div>

        <div class="profile-details">
            <h2 style="margin-bottom: 30px; color: var(--dark); font-size: 22px;">Personal Information</h2>
            
            <div class="detail-group">
                <h4>Full Name</h4>
                <p><?php echo htmlspecialchars($user['full_name']); ?></p>
            </div>

            <div class="detail-group">
                <h4>Email Address</h4>
                <p><?php echo htmlspecialchars($user['email']); ?></p>
            </div>

            <div class="detail-group">
                <h4>Department</h4>
                <p>Internet of Things (IoT)</p>
            </div>

            <h2 style="margin: 40px 0 20px; color: var(--dark); font-size: 22px;">Attendance Summary</h2>
            <div class="stats-grid">
                <div class="stat-box">
                    <h2>0%</h2>
                    <span>Attendance Rate</span>
                </div>
                <div class="stat-box">
                    <h2>0</h2>
                    <span>Sessions Present</span>
                </div>
                <div class="stat-box">
                    <h2>0</h2>
                    <span>Sessions Absent</span>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2026 Worabe University - IoT Institute. All rights reserved.</p>
    </footer>

</body>
</html>