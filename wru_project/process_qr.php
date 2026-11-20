<?php
session_start();
require_once 'db_connect.php';

// 1. Security Verification: Ensure the user is logged in
if (!isset($_SESSION['user_id'])) { 
    die("Error: Please login first."); 
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['session_data'])) {
    // Sanitize the data received from the QR scanner
    $session_id = trim($_POST['session_data']); 
    $student_id = $_SESSION['user_id'];

    try {
        // 2. Retrieve course information associated with this session_id
        $stmt = $conn->prepare("SELECT course_id FROM sessions WHERE session_id = :sid");
        $stmt->execute(['sid' => $session_id]);
        $session = $stmt->fetch();

        if (!$session) {
            die("Invalid QR Code. This session was not found in the system.");
        }

        $course_id = $session['course_id']; 

        // 3. Prevent duplicate entries: Check if the student already scanned for this session
        $check = $conn->prepare("SELECT record_id FROM attendance_records WHERE session_id = :sid AND student_id = :stid");
        $check->execute(['sid' => $session_id, 'stid' => $student_id]);
        
        if ($check->rowCount() > 0) {
            die("Already marked! You have already registered for this session.");
        }

        // 4. Record the attendance with current date and scan time
        $insert = $conn->prepare("
            INSERT INTO attendance_records (session_id, student_id, course_id, status, session_date, scan_time) 
            VALUES (:sid, :stid, :cid, 'present', NOW(), NOW())
        ");
        
        $result = $insert->execute([
            'sid'  => $session_id, 
            'stid' => $student_id, 
            'cid'  => $course_id
        ]);

        if ($result) {
            echo "success";
        } else {
            echo "Error: Could not save the attendance record.";
        }

    } catch (PDOException $e) {
        // Provide detailed database error information for debugging
        echo "Database Error: " . $e->getMessage();
    }
} else {
    echo "No data received.";
}
?>