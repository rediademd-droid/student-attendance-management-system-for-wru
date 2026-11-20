<?php
session_start();
require_once 'db_connect.php';

// Checks if you are an Admin
if (!isset($_SESSION['role']) || strtolower(trim($_SESSION['role'])) !== 'admin') {
    exit("Access Denied");
}

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    try {
        // Deletes the user from the database
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);

        // Redirects back to manage_users.php after successful deletion
        header("Location: manage_users.php?msg=deleted");
        exit();
    } catch (PDOException $e) {
        die("Error deleting user: " . $e->getMessage());
    }
} else {
    header("Location: manage_users.php");
    exit();
}