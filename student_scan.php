<?php
session_start();
require_once 'db_connect.php';

// ተማሪ መሆኑን ማረጋገጥ
if (!isset($_SESSION['role']) || strtolower(trim($_SESSION['role'])) !== 'student') {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan QR | WRU-IoT</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>
        :root {
            --sidebar-bg: #2c3e50;
            --sidebar-hover: #1a252f;
            --accent-orange: #e67e22;
            --bg-light: #f4f7f6;
        }

        body { 
            margin: 0; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: var(--bg-light);
            display: flex;
            height: 100vh;
        }

        /* Sidebar Style - ልክ በምስሉ ላይ እንዳለው */
        .sidebar {
            width: 260px;
            background: var(--sidebar-bg);
            color: white;
            display: flex;
            flex-direction: column;
            padding-top: 20px;
            flex-shrink: 0;
        }

        .sidebar-header {
            text-align: center;
            padding: 20px;
            font-size: 24px;
            font-weight: bold;
            color: var(--accent-orange);
            letter-spacing: 1px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu li a {
            display: flex;
            align-items: center;
            padding: 15px 25px;
            color: #bdc3c7;
            text-decoration: none;
            transition: 0.3s;
            font-size: 16px;
        }

        .sidebar-menu li a i {
            margin-right: 15px;
            width: 20px;
            text-align: center;
        }

        .sidebar-menu li a:hover, .sidebar-menu li a.active {
            background: var(--sidebar-hover);
            color: var(--accent-orange);
            border-left: 4px solid var(--accent-orange);
        }

        .logout-link {
            margin-top: auto;
            margin-bottom: 20px;
        }

        /* Main Content Area */
        .main-content {
            flex-grow: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px;
            overflow-y: auto;
        }

        .scanner-card { 
            width: 100%; 
            max-width: 500px; 
            background: white; 
            padding: 40px; 
            border-radius: 25px; 
            box-shadow: 0 15px 45px rgba(0,0,0,0.1); 
            text-align: center; 
        }

        #reader { 
            width: 100%; 
            border-radius: 20px; 
            overflow: hidden; 
            border: 2px solid #edf2f7; 
            background: #fafafa;
            margin-top: 10px;
        }

        .status-success { 
            display: none; 
            color: #2ecc71; 
            margin-top: 20px; 
            animation: fadeIn 0.5s;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Html5Qrcode Scanner Button Styling */
        #reader button {
            background: var(--accent-orange);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            margin: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <div class="sidebar-header">WRU-IoT</div>
        <ul class="sidebar-menu">
            <li><a href="student_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="scan_qr.php" class="active"><i class="fas fa-qrcode"></i> Scan Attendance</a></li>
            <li><a href="request_leave.php"><i class="fas fa-paper-plane"></i> Leave Request</a></li>
            <li class="logout-link"><a href="logout.php" style="color: #e74c3c;"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="scanner-card">
            <h2 style="color: #2c3e50; margin-bottom: 10px;">
                <i class="fas fa-camera" style="color: var(--accent-orange);"></i> Scan QR Code
            </h2>
            <p style="color: #7f8c8d; font-size: 15px; margin-bottom: 25px;">
                Align the instructor's QR code within the camera frame to mark your attendance.
            </p>

            <div id="reader"></div>

            <div id="result" class="status-success">
                <i class="fas fa-check-circle fa-4x"></i>
                <h3 style="margin-top: 15px;">Success!</h3>
                <p>Attendance has been marked successfully.</p>
                <small style="color: #7f8c8d;">Redirecting back to dashboard...</small>
            </div>
        </div>
    </div>

<script>
let isScanning = true;

function onScanSuccess(decodedText) {
    if (!isScanning) return;
    isScanning = false; 

    // ዳታውን ወደ process_qr.php መላክ
    fetch('process_qr.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'session_data=' + encodeURIComponent(decodedText)
    })
    .then(response => response.text())
    .then(data => {
        if (data.trim() === "success") {
            html5QrcodeScanner.clear();
            document.getElementById('reader').style.display = 'none';
            document.getElementById('result').style.display = 'block';
            
            // ከ 2.5 ሰከንድ በኋላ ወደ ዳሽቦርድ መመለስ
            setTimeout(() => { window.location.href = 'student_dashboard.php'; }, 2500);
        } else {
            alert("Attendance Error: " + data);
            isScanning = true; 
        }
    })
    .catch(err => {
        alert("Server connection failed!");
        isScanning = true;
    });
}

// ስካነሩን የማስጀመርያ ኮድ
let html5QrcodeScanner = new Html5QrcodeScanner("reader", { 
    fps: 15, 
    qrbox: { width: 250, height: 250 } 
}, /* verbose= */ false);
html5QrcodeScanner.render(onScanSuccess);
</script>

</body>
</html>