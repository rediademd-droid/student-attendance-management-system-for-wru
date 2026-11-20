<?php
require_once 'db_connect.php';

if (isset($_GET['session_id'])) {
    $session_id = $_GET['session_id'];
    
    $stmt = $conn->prepare("SELECT COUNT(*) FROM attendance_records WHERE session_id = ?");
    $stmt->execute([$session_id]);
    echo $stmt->fetchColumn();
}
?>