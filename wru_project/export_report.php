<?php
session_start();
require_once 'db_connect.php';

if (!isset($_GET['course_id'])) {
    die("Course ID is missing!");
}

$course_id = $_GET['course_id'];

// 1. Get the course name
$c_stmt = $conn->prepare("SELECT course_name FROM courses WHERE course_id = ?");
$c_stmt->execute([$course_id]);
$course = $c_stmt->fetch();
$filename = ($course ? str_replace(' ', '_', $course['course_name']) : 'Attendance') . "_Report.xls";

try {
    // Note: The word 'marked_time' is not used anywhere here
    $query = "SELECT 
                u.university_id, 
                u.full_name, 
                COUNT(CASE WHEN ar.status = 'Present' THEN 1 END) as total_present,
                COUNT(DISTINCT DATE(ar.session_date)) as total_sessions
              FROM users u
              JOIN attendance_records ar ON u.user_id = ar.student_id
              WHERE ar.course_id = :course_id
              GROUP BY u.user_id, u.university_id, u.full_name
              ORDER BY u.full_name ASC";
              
    $stmt = $conn->prepare($query);
    $stmt->execute(['course_id' => $course_id]);
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // This will show us if there is an error
    die("Database Error: " . $e->getMessage());
}

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=$filename");
header("Pragma: no-cache");
header("Expires: 0");

echo "University ID\tFull Name\tPresent Sessions\tTotal Sessions\n";

if ($reports) {
    foreach($reports as $r) {
        echo $r['university_id'] . "\t";
        echo $r['full_name'] . "\t";
        echo $r['total_present'] . "\t";
        echo $r['total_sessions'] . "\n";
    }
}
exit();