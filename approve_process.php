<?php
session_start();
require_once 'db_connect.php';

// Check instructor permission
if ($_SESSION['role'] !== 'instructor') {
    die("Permission denied.");
}

$request_id = $_GET['id']; 

try {
    $conn->beginTransaction();
    
    // Fetch leave request details
    $stmt = $conn->prepare("SELECT student_id, course_id, leave_date FROM leave_requests WHERE id = ?");
    $stmt->execute([$request_id]);
    $leave = $stmt->fetch();

    if ($leave) {
        // Update request status to Approved
        $updateStatus = $conn->prepare("UPDATE leave_requests SET status = 'Approved' WHERE id = ?");
        $updateStatus->execute([$request_id]);
        
        // Insert record into attendance_records marked as 'Present'
        $insertAttendance = $conn->prepare("INSERT INTO attendance_records (student_id, course_id, status, timestamp) VALUES (?, ?, 'Present', ?)");
        $attendance_time = $leave['leave_date'] . " " . date("H:i:s"); 
        $insertAttendance->execute([$leave['student_id'], $leave['course_id'], $attendance_time]);

        $conn->commit();
        echo "<script>alert('Leave request approved! The student has been marked as Present in the report.'); window.location.href='view_requests.php';</script>";
    } else {
        throw new Exception("Request not found.");
    }

} catch (Exception $e) {
    $conn->rollBack();
    die("Error: " . $e->getMessage());
}
?>