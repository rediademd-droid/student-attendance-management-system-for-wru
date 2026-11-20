<?php
/**
 * Database Connection for WRU-IoT Attendance System
 * This file uses PDO (PHP Data Objects) for secure database interactions.
 */

$host = 'localhost';
$dbname = 'wru_iot_attendance_db'; 
$username = 'root';  
$password = '';      

try {
    // 1. Establish the connection with UTF-8 character encoding
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    // 2. Set Error Mode to Exception
    // This will help you catch and debug SQL errors easily
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 3. Set Default Fetch Mode to Associative Array
    // This allows you to access data like $row['full_name'] instead of $row[0]
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Optional: Uncomment the line below for testing connection
    // echo "Connected successfully!"; 

} catch(PDOException $e) {
    // If connection fails, stop the script and display the error
    die("Database Connection Failed: " . $e->getMessage());
}
?><?php
/**
 * Database Connection for WRU-IoT Attendance System
 * This file uses PDO (PHP Data Objects) for secure database interactions.
 */

$host = 'localhost';
$dbname = 'wru_iot_attendance_db'; 
$username = 'root';  
$password = '';      

try {
    // 1. Establish the connection with UTF-8 character encoding
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    // 2. Set Error Mode to Exception
    // This will help you catch and debug SQL errors easily
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 3. Set Default Fetch Mode to Associative Array
    // This allows you to access data like $row['full_name'] instead of $row[0]
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Optional: Uncomment the line below for testing connection
    // echo "Connected successfully!"; 

} catch(PDOException $e) {
    // If connection fails, stop the script and display the error
    die("Database Connection Failed: " . $e->getMessage());
}
?>