<?php
session_start();
require_once 'db_connect.php';

// Security Check: Ensure the user is logged in and is an instructor
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'instructor') {
    die("Unauthorized access.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_id = $_POST['course_id'];
    $statuses = $_POST['status']; // Array of student_id => status
    $date = date('Y-m-d');

    try {
        // 1. Check if a session already exists for this course today (to prevent double counting)
        $stmt = $conn->prepare("SELECT session_id FROM attendance_sessions WHERE course_id = ? AND session_date = ?");
        $stmt->execute([$course_id, $date]);
        $existing_session = $stmt->fetch();

        if ($existing_session) {
            // If session exists, use the existing ID
            $session_id = $existing_session['session_id'];
            
            // Delete old records for today's session to allow for a clean update
            $del = $conn->prepare("DELETE FROM attendance_records WHERE session_id = ?");
            $del->execute([$session_id]);
        } else {
            // If no session exists, create a new one
            $stmt = $conn->prepare("INSERT INTO attendance_sessions (course_id, session_date, start_time) VALUES (?, ?, NOW())");
            $stmt->execute([$course_id, $date]);
            $session_id = $conn->lastInsertId();
        }

        // 2. Record attendance for each student
        $sql = "INSERT INTO attendance_records (session_id, student_id, status) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);

        foreach ($statuses as $student_id => $status) {
            $stmt->execute([$session_id, $student_id, $status]);
        }

        echo "success";
    } catch (PDOException $e) {
        // Log the error for the developer and show a clean message
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Invalid request method.";
}
?>