<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help Center | WRU-IoT Attendance</title>
    <!-- Font Awesome ለ icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* --- basic styling --- */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f8fafc; color: #2c3e50; line-height: 1.6; overflow-x: hidden; }

        /* --- 1. Navbar  --- */
        nav {
            background: white;
            padding: 5px 10%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 2000;
        }

        /* ሎጎ ቁልቁል እንዲሆን */
        .logo { 
            display: flex !important; 
            flex-direction: column !important; 
            align-items: center !important; 
            text-decoration: none;
            gap: 0px;
        }
        .logo img { height: 45px; width: auto; object-fit: contain; }
        
        .logo-text {
            font-size: 16px; 
            font-weight: 800; 
            color: #e67e22;
            margin-top: -2px;
        }
        .logo-text span { color: #2c3e50; }

        .nav-container { display: flex; align-items: center; gap: 20px; }

        /* ሊንኮች */
        .nav-links { display: flex; list-style: none; gap: 20px; align-items: center; }
        .nav-links li a { text-decoration: none; color: #7f8c8d; font-weight: 500; transition: 0.3s; }
        .nav-links li a:hover, .nav-links li a.active { color: #e67e22; }

        .btn-login {
            background: #e67e22;
            color: white !important;
            padding: 8px 22px;
            border-radius: 8px;
            font-weight: 600;
        }

        .menu-toggle { display: none; font-size: 24px; cursor: pointer; color: #2c3e50; }

        /* --- 2. Help Header (ከጀርባ ፎቶ ጋር) --- */
        .help-header-section {
            padding: 80px 10%;
            text-align: center;
            /* እዚህ ጋር የጀርባ ፎቶህን ስም ተካ */
            background: linear-gradient(rgba(44, 62, 80, 0.8), rgba(44, 62, 80, 0.8)), 
                        url('help_bg.jpg'); 
            background-size: cover;
            background-position: center;
            color: white;
        }
        .help-header-section h1 { font-size: 40px; margin-bottom: 15px; }
        .help-header-section p { font-size: 18px; opacity: 0.9; }

        /* --- 3. Help Content --- */
        .help-container { padding: 60px 10%; max-width: 1200px; margin: auto; }

        .help-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; }
        .help-card { 
            background: white; 
            padding: 35px; 
            border-radius: 20px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.03); 
            border-top: 5px solid #e67e22;
            transition: 0.3s;
        }
        .help-card:hover { transform: translateY(-5px); }
        .help-card h3 { color: #2c3e50; margin-bottom: 25px; display: flex; align-items: center; gap: 12px; font-size: 22px; }
        .help-card i { color: #e67e22; }

        .steps { list-style: none; }
        .steps li { margin-bottom: 15px; padding-left: 35px; position: relative; font-size: 15px; color: #555; }
        .steps li::before { 
            content: "\f058"; 
            font-family: "Font Awesome 6 Free"; 
            font-weight: 900; 
            position: absolute; 
            left: 0; 
            color: #27ae60; 
            font-size: 18px;
        }

        /* FAQ */
        .faq-section { margin-top: 80px; }
        .faq-section h2 { text-align: center; margin-bottom: 40px; color: #2c3e50; font-size: 28px; }
        .faq-item { 
            background: #fff; 
            margin-bottom: 15px; 
            padding: 25px; 
            border-radius: 12px; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            transition: 0.3s;
        }
        .faq-item:hover { background: #fef5ed; border-left: 4px solid #e67e22; }
        .faq-item h4 { color: #2c3e50; margin-bottom: 12px; display: flex; align-items: center; gap: 10px; }

        footer { padding: 40px 10%; background: #2c3e50; color: white; text-align: center; margin-top: 60px; }

        /* --- 4. Responsive --- */
        @media (max-width: 992px) {
            nav { padding: 10px 5%; }
            .menu-toggle { display: block; }
            .nav-links {
                display: none;
                flex-direction: column;
                position: absolute;
                top: 100%;
                left: 0;
                width: 100%;
                background: white;
                padding: 25px;
                box-shadow: 0 10px 20px rgba(0,0,0,0.1);
                gap: 20px;
            }
            .nav-links.active { display: flex; }
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav>
        <a href="home.php" class="logo">
            <img src="wu_logo.png" alt="WRU Logo">
            <div class="logo-text">WRU-<span>IoT</span></div>
        </a>
        
        <div class="nav-container">
            <ul class="nav-links" id="navLinks">
                <li><a href="home.php">Home</a></li>
                <li><a href="about.php">About Us</a></li>
                <li><a href="help.php" class="active">Help</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="index.php" class="btn-login">Login</a></li>
            </ul>

            <div class="menu-toggle" onclick="toggleMenu()">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </nav>

    <!-- Help Header with Background -->
    <section class="help-header-section">
        <h1>How can we help?</h1>
        <p>Step-by-step guides and answers for the WRU-IoT Attendance System.</p>
    </section>

    <div class="help-container">
        <div class="help-grid">
            <!-- Student Guide -->
            <div class="help-card">
                <h3><i class="fas fa-user-graduate"></i> For Students</h3>
                <ul class="steps">
                    <li><b>Login:</b> Use your University ID and password to sign in.</li>
                    <li><b>Navigate:</b> Click on the <b>"Scan QR"</b> button.</li>
                    <li><b>GPS Check:</b> Ensure your phone's <b>Location</b> is turned on.</li>
                    <li><b>Permissions:</b> Click "Allow" for Camera and Location access.</li>
                    <li><b>Scan:</b> Point your camera at the instructor's QR code.</li>
                </ul>
            </div>

            <!-- Instructor Guide -->
            <div class="help-card">
                <h3><i class="fas fa-chalkboard-teacher"></i> For Instructors</h3>
                <ul class="steps">
                    <li><b>Login:</b> Access your account and select your course.</li>
                    <li><b>Generate:</b> Click <b>"Generate QR"</b> for a new session.</li>
                    <li><b>Display:</b> Show the QR code on the screen for students.</li>
                    <li><b>Verify:</b> The system automatically checks student GPS.</li>
                    <li><b>Records:</b> Go to <b>"Reports"</b> to export attendance lists.</li>
                </ul>
            </div>
        </div>

        <div class="faq-section">
            <h2>Frequently Asked Questions</h2>
            
            <div class="faq-item">
                <h4><i class="fas fa-map-marker-alt"></i> Why do I see a "Location Error"?</h4>
                <p>You must be inside the classroom. If you are, try refreshing your GPS or moving away from thick walls.</p>
            </div>

            <div class="faq-item">
                <h4><i class="fas fa-camera"></i> The camera isn't opening?</h4>
                <p>Check your browser settings. Look for the "lock" icon in the address bar to reset camera permissions.</p>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2026 Worabe University - IoT Institute. All rights reserved.</p>
    </footer>

    <script>
        function toggleMenu() {
            const navLinks = document.getElementById('navLinks');
            navLinks.classList.toggle('active');
        }
    </script>

</body>
</html>