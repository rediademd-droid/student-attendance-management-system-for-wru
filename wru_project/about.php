<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | WRU-IoT Attendance</title>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* --- basic style --- */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f8fafc; color: #2c3e50; line-height: 1.6; overflow-x: hidden; }

        /* --- 1. Navbar */
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

        /* --- 2. About Header --- */
        .about-header {
            background: linear-gradient(rgba(44, 62, 80, 0.9), rgba(44, 62, 80, 0.9)), 
                        url('campus_bg.jpg'); 
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 10%;
            text-align: center;
        }
        .about-header h1 { font-size: 40px; margin-bottom: 15px; }

        /* --- 3. Main Content --- */
        .container { padding: 80px 10%; }

        .about-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            align-items: center;
        }

        .about-text h2 { color: #e67e22; margin-bottom: 20px; font-size: 32px; }
        .about-text p { margin-bottom: 20px; color: #555; }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 40px;
            text-align: center;
        }

        .stat-item { background: white; padding: 20px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .stat-item i { font-size: 30px; color: #e67e22; margin-bottom: 10px; }
        .stat-item h4 { font-size: 24px; color: #2c3e50; }

        /* Technology Section */
        .tech-section { background: #fff; padding: 80px 10%; text-align: center; }
        .tech-icons {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin-top: 30px;
            font-size: 50px;
            color: #bdc3c7;
        }
        .tech-icons i:hover { color: #e67e22; transition: 0.3s; cursor: pointer; }

        footer { padding: 40px 10%; background: #2c3e50; color: white; text-align: center; }

        /* --- 4. Responsive (Mobile) --- */
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
            
            .about-grid { grid-template-columns: 1fr; text-align: center; }
            .stats-grid { grid-template-columns: 1fr; }
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
                <li><a href="about.php" class="active">About Us</a></li>
                <li><a href="help.php">Help</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="index.php" class="btn-login">Login</a></li>
            </ul>

            <div class="menu-toggle" onclick="toggleMenu()">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </nav>

    <header class="about-header">
        <h1>Transforming Education with IoT</h1>
        <p>Digitizing Worabe University one step at a time.</p>
    </header>

    <section class="container">
        <div class="about-grid">
            <div class="about-image">
                <i class="fas fa-microchip" style="font-size: clamp(150px, 20vw, 250px); color: #e2e8f0;"></i>
            </div>
            <div class="about-text">
                <h2>Our Mission</h2>
                <p>The WRU-IoT Attendance System is a modern solution designed to solve the challenges of traditional paper-based attendance. By using dynamic QR codes and GPS geofencing, we ensure that student attendance is accurate, fast, and fraud-proof.</p>
                <p>Developed specifically for the Worabe University Institute of Technology, this project showcases how IoT principles and web technologies can improve campus efficiency.</p>
                
                <div class="stats-grid">
                    <div class="stat-item">
                        <i class="fas fa-bolt"></i>
                        <h4>100%</h4>
                        <p>Fast</p>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-shield-alt"></i>
                        <h4>Secure</h4>
                        <p>GPS Locked</p>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-leaf"></i>
                        <h4>Paperless</h4>
                        <p>Eco-friendly</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="tech-section">
        <h2>Technology Stack</h2>
        <p>Built with reliable and modern web technologies.</p>
        <div class="tech-icons">
            <i class="fab fa-php" title="PHP"></i>
            <i class="fab fa-js" title="JavaScript"></i>
            <i class="fab fa-html5" title="HTML5"></i>
            <i class="fas fa-database" title="MySQL"></i>
            <i class="fab fa-css" title="CSS"></i>
        </div>
    </section>

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