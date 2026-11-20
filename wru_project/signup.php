<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WRU-IoT | Sign Up</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #e67e22;
            --dark: #1a2a3a;
            --bg-color: #0f172a; 
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: var(--bg-color);
            padding: 20px;
        }

        .main-wrapper {
            display: flex;
            width: 98%;
            max-width: 1300px;
            height: 850px; /* Slightly increased height to avoid scrolling */
            background: white;
            border-radius: 30px;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(0,0,0,0.5);
        }

        .side-image {
            flex: 0.8; 
            overflow: hidden;
            position: relative;
        }

        /* Backdrop Blur Layer */
        .side-image::after {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(26, 42, 58, 0.3); /* Dark overlay */
            backdrop-filter: blur(2px); /* Added the 5px blur effect */
            -webkit-backdrop-filter: blur(2px); /* Support for Safari */
        }

        .side-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .signup-box {
            flex: 1.2;
            padding: 30px 50px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: white;
            z-index: 2;
            overflow-y: auto;
        }

        .signup-box h2 { color: var(--dark); margin-bottom: 5px; font-size: 28px; font-weight: 800; }
        .signup-box p { color: #7f8c8d; margin-bottom: 25px; font-size: 15px; }

        .input-group { width: 100%; margin-bottom: 15px; position: relative; text-align: left; }
        
        .input-group i {
            position: absolute; left: 18px; top: 50%;
            transform: translateY(-50%); color: var(--primary); font-size: 18px;
            z-index: 1;
        }

        .input-group input, .input-group select {
            width: 100%; padding: 14px 15px 14px 50px;
            border: 1.5px solid #f1f5f9; border-radius: 12px;
            outline: none; font-size: 15px; background-color: #f8fafc;
            color: #2c3e50; transition: 0.3s;
        }

        .input-group input:focus, .input-group select:focus { 
            border-color: var(--primary); 
            background: #fff;
            box-shadow: 0 0 8px rgba(230, 126, 34, 0.1);
        }

        #student-only-fields {
            display: none; 
            width: 100%;
        }

        .input-row {
            display: flex;
            gap: 12px;
            width: 100%;
            margin-bottom: 15px;
        }
        .input-row .input-group { margin-bottom: 0; flex: 1; }

        .signup-btn {
            background: var(--primary); color: white; border: none;
            width: 100%; padding: 16px; border-radius: 12px;
            font-size: 17px; font-weight: bold; cursor: pointer;
            transition: 0.3s; margin-top: 10px;
            box-shadow: 0 5px 15px rgba(230, 126, 34, 0.2);
        }

        .signup-btn:hover { 
            background: #d35400; 
            transform: translateY(-1px); 
        }

        .login-link { margin-top: 20px; font-size: 14px; color: #7f8c8d; text-align: center; }
        .login-link a { color: var(--primary); text-decoration: none; font-weight: bold; }

        @media (max-width: 1000px) {
            .side-image { display: none; }
            .main-wrapper { max-width: 500px; height: auto; border-radius: 20px; }
            .signup-box { padding: 40px 30px; }
        }
    </style>
</head>
<body>

    <div class="main-wrapper">
        <div class="side-image">
            <img src="image3.png" alt="University Campus">
        </div>

        <div class="signup-box">
            <i class="fas fa-user-plus" style="font-size: 45px; color: var(--primary); margin-bottom: 10px;"></i>
            <h2>Create Account</h2>
            <p>Join WRU-IoT Attendance System</p>

            <form action="signup_process.php" method="POST" style="width: 100%;" onsubmit="return validatePassword()">
                
                <div class="input-group">
                    <i class="fas fa-users-cog"></i>
                    <select name="role" id="role-select" onchange="toggleFields()" required>
                        <option value="" disabled selected>Select Your Role</option>
                        <option value="student">Student</option>
                        <option value="instructor">Instructor</option>
                    </select>
                </div>

                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" name="full_name" placeholder="Full Name" required>
                </div>

                <div class="input-group">
                    <i class="fas fa-id-card"></i>
                    <input type="text" name="university_id" placeholder="University ID (e.g. 1234/15)" required>
                </div>

                <div id="student-only-fields">
                    <div class="input-row">
                        <div class="input-group">
                            <i class="fas fa-graduation-cap"></i>
                            <select name="department" id="dept-input">
                                <option value="" disabled selected>Select Dept</option>
                                <option value="CS">Computer Science</option>
                                <option value="IT">Info. Tech</option>
                                <option value="IS">Info. Systems</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <i class="fas fa-calendar-alt"></i>
                            <input type="number" name="batch" id="batch-input" placeholder="Batch (E.C)" min="2010" max="2020">
                        </div>
                    </div>
                </div>

                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" placeholder="Email Address" required>
                </div>

                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" id="password" placeholder="Password (4-8 characters)" required minlength="4" maxlength="8">
                </div>

                <button type="submit" class="signup-btn">Register Now</button>
            </form>

            <div class="login-link">
                Already have an account? <br><br>
                <a href="index.php"><i class="fas fa-arrow-left"></i> Back to Login</a>
            </div>
        </div>

        <div class="side-image">
            <img src="image3.png" alt="University Campus">
        </div>
    </div>

    <script>
        /**
         * Toggles Department and Batch fields based on the selected role.
         */
        function toggleFields() {
            const roleSelect = document.getElementById('role-select');
            const studentFields = document.getElementById('student-only-fields');
            const deptInput = document.getElementById('dept-input');
            const batchInput = document.getElementById('batch-input');

            if (roleSelect.value === 'student') {
                studentFields.style.display = 'block';
                deptInput.setAttribute('required', '');
                batchInput.setAttribute('required', '');
            } else {
                studentFields.style.display = 'none';
                deptInput.removeAttribute('required');
                batchInput.removeAttribute('required');
                deptInput.value = "";
                batchInput.value = "";
            }
        }

        /**
         * Validates password length before form submission (4-8 chars).
         */
        function validatePassword() {
            const password = document.getElementById('password').value;
            if (password.length < 4) {
                alert("Password must be at least 4 characters long.");
                return false;
            }
            if (password.length > 8) {
                alert("Password cannot exceed 8 characters.");
                return false;
            }
            return true;
        }
    </script>

</body>
</html>