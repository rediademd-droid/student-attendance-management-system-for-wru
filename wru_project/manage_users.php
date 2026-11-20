<?php
session_start();
require_once 'db_connect.php';

// 1. Security Check: Ensure only Admin can access
if (!isset($_SESSION['role']) || strtolower(trim($_SESSION['role'])) !== 'admin') { 
    header("Location: index.php"); 
    exit(); 
}

$message = "";

// 2. BACKEND LOGIC: Delete User Logic
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    try {
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->execute([$_GET['id']]);
        header("Location: manage_users.php?msg=deleted");
        exit();
    } catch (PDOException $e) {
        $message = "<script>alert('Error deleting user: " . addslashes($e->getMessage()) . "');</script>";
    }
}

// 3. BACKEND LOGIC: Register New User Logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register_user'])) {
    $univ_id = trim($_POST['univ_id']);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $raw_password = $_POST['password'];
    
    // --- START PASSWORD VALIDATION (Min 4, Max 8) ---
    $pw_length = strlen($raw_password);
    if ($pw_length < 4) {
        echo "<script>alert('Password too short! Use 4-8 characters.'); window.history.back();</script>";
        exit();
    } 
    if ($pw_length > 8) {
        echo "<script>alert('Password too long! Maximum 8 characters allowed.'); window.history.back();</script>";
        exit();
    }
    // --- END PASSWORD VALIDATION ---

    // Set department and batch to null if user is an instructor
    $dept = ($role === 'student') ? $_POST['department'] : null;
    $batch = ($role === 'student') ? $_POST['batch'] : null;
    
    // Securely hash the password
    $password_hashed = password_hash($raw_password, PASSWORD_DEFAULT);

    // Standardize ID Prefixes
    if ($role === 'instructor') {
        if (stripos($univ_id, 'INST/') === false) { $univ_id = "INST/" . $univ_id; }
    } else {
        if (stripos($univ_id, 'WRU/') === false) { $univ_id = "WRU/" . $univ_id; }
    }

    try {
        $sql = "INSERT INTO users (university_id, full_name, email, department, role, batch, password) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$univ_id, $full_name, $email, $dept, $role, $batch, $password_hashed]);
        echo "<script>alert('User $univ_id registered successfully!'); window.location='manage_users.php';</script>";
    } catch (PDOException $e) {
        $err = ($e->getCode() == 23000) ? "ID or Email already exists!" : $e->getMessage();
        $message = "<script>alert('Error: " . addslashes($err) . "');</script>";
    }
}

// 4. Fetch all users from database
$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
$instructors = [];
$students = [];

foreach($users as $u) {
    if (strtolower($u['role']) === 'instructor') { $instructors[] = $u; } 
    else if (strtolower($u['role']) === 'student') { $students[] = $u; }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | WRU-IoT</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
        body { background: #f4f7f6; display: flex; flex-direction: column; min-height: 100vh; }
        .sidebar { width: 250px; height: 100vh; background: #2c3e50; color: white; position: fixed; padding: 20px; }
        .sidebar h2 { color: #e67e22; margin-bottom: 30px; font-size: 22px; text-align: center; }
        .sidebar-menu { list-style: none; }
        .sidebar-menu a { color: white; text-decoration: none; display: block; padding: 12px; border-radius: 8px; margin-bottom: 5px; }
        .sidebar-menu a.active { background: #34495e; color: #e67e22; }
        .main-content { margin-left: 250px; padding: 30px; flex: 1; }
        .user-form { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
        .form-grid input, .form-grid select { padding: 12px; border: 1px solid #ddd; border-radius: 8px; width: 100%; }
        .table-container { background: white; border-radius: 15px; padding: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); overflow-x: auto; margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #e67e22; color: white; padding: 15px; text-align: left; }
        td { padding: 15px; border-bottom: 1px solid #eee; }
        .submit-btn { margin-top: 15px; background: #e67e22; color: white; border: none; padding: 12px 30px; border-radius: 8px; cursor: pointer; font-weight: bold; }
        .hidden-field { display: none; }
    </style>
</head>
<body>

    <?php echo $message; ?>

    <div class="sidebar">
        <h2>WRU-IoT Admin</h2>
        <ul class="sidebar-menu">
            <li><a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="manage_users.php" class="active"><i class="fas fa-users"></i> Manage Users</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="user-form">
            <h3><i class="fas fa-user-plus" style="color: #e67e22;"></i> Register New User</h3>
            <hr style="margin: 15px 0; opacity: 0.1;">
            <form action="manage_users.php" method="POST">
                <div class="form-grid">
                    <select name="role" id="roleSelect" onchange="toggleFields()" required>
                        <option value="student">Student</option>
                        <option value="instructor">Instructor</option>
                    </select>

                    <input type="text" name="univ_id" id="univ_id" placeholder="ID (WRU/...)" required>
                    <input type="text" name="full_name" placeholder="Full Name" required>
                    <input type="email" name="email" placeholder="Email Address" required>
                    
                    <div id="dept_container">
                        <select name="department" id="dept_input">
                            <option value="">Select Department</option>
                            <option value="Computer Science">Computer Science</option>
                            <option value="Information Technology">Information Technology</option>
                            <option value="Information Science">Information Science</option>
                            <option value="Electrical Engineering">Electrical Engineering</option>
                             <option value="Mechanical Engineering">Mechanical Engineering</option>
                              <option value="Water Suplly $ Environmental Engineering">Water Suplly $ Environmental Engineering</option>
                        </select>
                    </div>

                    <div id="batch_container">
                        <input type="number" name="batch" id="batch_input" placeholder="Batch Year (E.C)" min="2010">
                    </div>
                    
                    <input type="password" name="password" placeholder="Password (4-8 chars)" required minlength="4" maxlength="8">
                </div>
                <button type="submit" name="register_user" class="submit-btn">Register User</button>
            </form>
        </div>

        <div class="table-container" style="border-top: 5px solid #3498db;">
            <h3 style="color: #2980b9;">Instructors List</h3>
            <table>
                <thead>
                    <tr><th>ID</th><th>Full Name</th><th>Email</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php foreach($instructors as $inst): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($inst['university_id']); ?></strong></td>
                        <td><?php echo htmlspecialchars($inst['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($inst['email']); ?></td>
                        <td><a href="?action=delete&id=<?php echo $inst['user_id']; ?>" style="color:red" onclick="return confirm('Delete user?')"><i class="fas fa-trash"></i></a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="table-container" style="border-top: 5px solid #e67e22;">
            <h3 style="color: #d35400;">Students List</h3>
            <table>
                <thead>
                    <tr><th>University ID</th><th>Full Name</th><th>Dept & Batch</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php foreach($students as $std): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($std['university_id']); ?></strong></td>
                        <td><?php echo htmlspecialchars($std['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($std['department']); ?> (<?php echo htmlspecialchars($std['batch']); ?> E.C)</td>
                        <td><a href="?action=delete&id=<?php echo $std['user_id']; ?>" style="color:red" onclick="return confirm('Delete student?')"><i class="fas fa-trash"></i></a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    // Logic to show/hide fields based on user role selection
    function toggleFields() {
        const role = document.getElementById('roleSelect').value;
        const univId = document.getElementById('univ_id');
        const deptCont = document.getElementById('dept_container');
        const batchCont = document.getElementById('batch_container');
        const deptInput = document.getElementById('dept_input');
        const batchInput = document.getElementById('batch_input');

        if (role === 'instructor') {
            univId.placeholder = "ID (INST/...)";
            deptCont.style.display = 'none'; // Hide Department for Instructor
            batchCont.style.display = 'none'; // Hide Batch for Instructor
            deptInput.removeAttribute('required');
            batchInput.removeAttribute('required');
        } else {
            univId.placeholder = "ID (WRU/...)";
            deptCont.style.display = 'block'; // Show Department for Student
            batchCont.style.display = 'block'; // Show Batch for Student
            deptInput.setAttribute('required', 'required');
            batchInput.setAttribute('required', 'required');
        }
    }
    window.onload = toggleFields;
    </script>
</body>
</html>