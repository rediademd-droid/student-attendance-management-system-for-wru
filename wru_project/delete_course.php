<?php
session_start();
require_once 'db_connect.php';

// 1. Verify user is logged in and is an admin (Security Check)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Redirect to index page if the user is not an admin
    header("Location: index.php");
    exit();
}

// 2. Ensure the Course ID to be deleted was sent
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $course_id = $_GET['id'];

    try {
        // 3. Delete the course from the database (Using Prepared Statements to prevent SQL Injection)
        $stmt = $conn->prepare("DELETE FROM courses WHERE course_id = ?");
        $result = $stmt->execute([$course_id]);

        if ($result) {
            // Redirect to manage_courses.php after successful deletion
            echo "<script>alert('Course deleted successfully!'); window.location='manage_courses.php';</script>";
        } else {
            echo "<script>alert('Error: Could not delete the course.'); window.location='manage_courses.php';</script>";
        }
    } catch (PDOException $e) {
        // Log the detailed error for debugging, but show a generic message to the user for security
        error_log($e->getMessage()); // Record the error in the log file
        echo "<script>alert('A database error occurred!'); window.location='manage_courses.php';</script>";
    }
} else {
    // Redirect to main page if no ID was provided
    header("Location: manage_courses.php");
    exit();
}
?>