<?php
session_start();

// 1. Security Check (Ensure user is an Instructor)
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'instructor') {
    header("Location: index.php");
    exit();
}

require_once 'db_connect.php';

// 2. Ensure Course is Selected
if (!isset($_GET['course_id'])) {
    die("Error: No course selected. Please return to the dashboard and select a course.");
}

$course_id = $_GET['course_id'];
$date = date('Y-m-d');

try {
    // 3. Prevent duplicate session increments on page refresh
    $check_stmt = $conn->prepare("SELECT session_id FROM sessions WHERE course_id = ? AND start_time > DATE_SUB(NOW(), INTERVAL 10 MINUTE) LIMIT 1");
    $check_stmt->execute([$course_id]);
    $existing_session = $check_stmt->fetch();

    if (!$existing_session) {
        $conn->beginTransaction();

        $stmt = $conn->prepare("INSERT INTO sessions (course_id, session_date, start_time) VALUES (?, ?, NOW())");
        $stmt->execute([$course_id, $date]);
        $session_id = $conn->lastInsertId();

        $update_course = $conn->prepare("UPDATE courses SET total_sessions = total_sessions + 1 WHERE course_id = ?");
        $update_course->execute([$course_id]);

        $conn->commit();
    } else {
        $session_id = $existing_session['session_id'];
    }

} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance QR Code | WRU-IoT</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        :root {
            --sidebar-bg: #2c3e50;
            --accent-color: #e67e22; /* Dashboard Orange */
            --bg-light: #f4f7f6;
            --text-dark: #2c3e50;
        }

        body { 
            background: var(--sidebar-bg); /* Matches Sidebar color */
            font-family: 'Segoe UI', Tahoma, sans-serif; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            margin: 0; 
        }

        .qr-container { 
            background: white; 
            padding: 40px; 
            border-radius: 20px; 
            text-align: center; 
            color: var(--text-dark); 
            box-shadow: 0 15px 40px rgba(0,0,0,0.4); 
            max-width: 450px; 
            width: 90%;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        .session-badge { 
            background: rgba(230, 126, 34, 0.1); 
            padding: 8px 20px; 
            border-radius: 50px; 
            font-weight: 700; 
            color: var(--accent-color); 
            font-size: 13px;
            display: inline-block;
            margin-bottom: 15px;
            border: 1px solid rgba(230, 126, 34, 0.2);
        }

        .info h2 { 
            color: var(--text-dark); 
            margin: 10px 0; 
            font-size: 22px; 
        }

        .info p { 
            color: #7f8c8d; 
            font-size: 0.95rem; 
            line-height: 1.5; 
            margin-bottom: 20px; 
        }

        #qrcode { 
            margin: 15px auto; 
            padding: 15px; 
            background: #fff;
            border: 2px solid #f1f2f6; 
            display: inline-block; 
            border-radius: 15px;
        }

        .warning-text { 
            background: #fff3e0; /* Match dashboard alert */
            color: #d35400; 
            font-size: 0.85rem; 
            margin-top: 25px; 
            padding: 15px;
            border-radius: 10px;
            border-left: 4px solid var(--accent-color);
            text-align: left;
        }

        .back-btn { 
            background: var(--sidebar-bg); 
            color: white; 
            padding: 14px 25px; 
            border-radius: 10px; 
            text-decoration: none; 
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 25px; 
            font-weight: 600; 
            transition: 0.3s;
        }

        .back-btn:hover { 
            background: #34495e;
            transform: translateY(-2px);
        }

        i.orange-icon {
            color: var(--accent-color);
        }
    </style>
</head>
<body>

<div class="qr-container">
    <div class="info">
        <span class="session-badge">Active Session ID: #<?php echo $session_id; ?></span>
        <h2><i class="fas fa-qrcode orange-icon"></i> Attendance QR Code</h2>
        <p>Ask students to scan this code with their mobile devices to mark attendance.</p>
    </div>

    <div id="qrcode"></div>

    <div class="warning-text">
        <i class="fas fa-info-circle"></i> <b>System Note:</b> <br>
        The total session count has been updated. Ensure students scan before the class ends.
    </div>

    <a href="instructor_dashboard.php" class="back-btn">
        <i class="fas fa-arrow-left"></i> Done - Return to Dashboard
    </a>
</div>

<script>
    var qrcode = new QRCode(document.getElementById("qrcode"), {
        text: "<?php echo $session_id; ?>", 
        width: 220,
        height: 220,
        colorDark : "#2c3e50",
        colorLight : "#ffffff",
        correctLevel : QRCode.CorrectLevel.H
    });

    if ('wakeLock' in navigator) {
        navigator.wakeLock.request('screen').catch(err => console.log(err));
    }
</script>

</body>
</html>