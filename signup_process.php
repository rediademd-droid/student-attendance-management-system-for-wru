<?php
session_start();
require_once 'db_connect.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $full_name     = trim($_POST['full_name']);
    $raw_id         = trim($_POST['university_id']); 
    $email          = trim($_POST['email']);
    $role           = $_POST['role']; 
    $password       = $_POST['password'];
    
    // Newly added fields
    $department     = isset($_POST['department']) ? $_POST['department'] : null;
    $batch          = isset($_POST['batch']) ? $_POST['batch'] : null;

    // Automatic Prefix Logic
    if ($role === 'student') {
        // If student, add WRU/ prefix if not already present
        $university_id = (strpos($raw_id, 'WRU/') === 0) ? $raw_id : 'WRU/' . $raw_id;
    } else {
        // If instructor, add INST/ prefix if not already present
        $university_id = (strpos($raw_id, 'INST/') === 0) ? $raw_id : 'INST/' . $raw_id;
        // Optional: Reset department/batch for instructors if your database schema allows
        // $department = null; 
        // $batch = null;
    }

    // Basic Validation Check
    if (empty($full_name) || empty($raw_id) || empty($email) || empty($role) || empty($password)) {
        echo "<script>alert('Please fill in all required information correctly!'); window.history.back();</script>";
        exit();
    }

    try {
        // Check if Email or University ID already exists
        $check = $conn->prepare("SELECT user_id FROM users WHERE email = ? OR university_id = ?");
        $check->execute([$email, $university_id]);

        if ($check->rowCount() > 0) {
            echo "<script>alert('This email or ID ($university_id) is already registered!'); window.history.back();</script>";
            exit();
        }

        // Secure Password Hashing
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // INSERT Query - Now including department and batch
        $sql = "INSERT INTO users (full_name, university_id, email, role, password, department, batch) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([
            $full_name, 
            $university_id, 
            $email, 
            $role, 
            $hashed_password, 
            $department, 
            $batch
        ]);

        if ($result) {
            echo "<script>alert('Registration successful! Your account ID is $university_id.'); window.location.href='index.php';</script>";
        }

    } catch (PDOException $e) {
        // Handle database errors (e.g., missing columns in the database table)
        die("Database Error: " . $e->getMessage());
    }
} else {
    header("Location: signup.php");
    exit();
}
?>