<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | WRU-IoT Attendance</title>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* --- Basic Styles --- */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f8fafc; color: #2c3e50; line-height: 1.6; overflow-x: hidden; }

        /* --- 1. Navbar --- */
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

        /* Vertical Logo Alignment */
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

        /* --- 2. Contact Header --- */
        .contact-header {
            background: linear-gradient(rgba(44, 62, 80, 0.9), rgba(44, 62, 80, 0.9)), 
                        url('campus_bg.jpg'); 
            background-size: cover;
            background-position: center;
            color: white;
            padding: 80px 10%;
            text-align: center;
        }
        .contact-header h1 { font-size: 40px; margin-bottom: 15px; }

        /* --- 3. Contact Content --- */
        .contact-container { 
            padding: 60px 10%; 
            display: grid; 
            grid-template-columns: 1fr 1.5fr; 
            gap: 50px; 
            max-width: 1200px; 
            margin: auto; 
        }
        
        .contact-info h2 { color: #2c3e50; font-size: 32px; margin-bottom: 20px; }
        .contact-info p { margin-bottom: 30px; color: #7f8c8d; }
        
        .info-box { display: flex; align-items: center; gap: 20px; margin-bottom: 25px; }
        .info-box i { 
            background: #e67e22; 
            color: white; 
            padding: 15px; 
            border-radius: 50%; 
            font-size: 20px; 
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .info-box h4 { margin-bottom: 2px; color: #2c3e50; }
        
        .contact-form { 
            background: white; 
            padding: 40px; 
            border-radius: 20px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.05); 
        }
        .contact-form input, .contact-form textarea {
            width: 100%; padding: 15px; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 10px; outline: none; font-size: 15px; transition: 0.3s;
        }
        .contact-form input:focus, .contact-form textarea:focus { border-color: #e67e22; box-shadow: 0 0 8px rgba(230, 126, 34, 0.2); }
        
        .send-btn { 
            background: #e67e22; 
            color: white; 
            border: none; 
            padding: 15px 40px; 
            border-radius: 10px; 
            font-weight: bold; 
            cursor: pointer; 
            transition: 0.3s; 
            width: 100%; 
            font-size: 16px;
        }
        .send-btn:hover { background: #d35400; transform: translateY(-2px); }

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
                text-align: center;
            }
            .nav-links.active { display: flex; }
            
            .contact-container { grid-template-columns: 1fr; padding: 40px 5%; }
            .contact-info { text-align: center; }
            .info-box { flex-direction: column; text-align: center; }
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
                <li><a href="help.php">Help</a></li>
                <li><a href="contact.php" class="active">Contact</a></li>
                <li><a href="index.php" class="btn-login">Login</a></li>
            </ul>

            <div class="menu-toggle" onclick="toggleMenu()">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </nav>

    <!-- Contact Header -->
    <header class="contact-header">
        <h1>Get in Touch</h1>
        <p>We're here to help with any technical issues or inquiries.</p>
    </header>

    <div class="contact-container">
        <!-- Info Side -->
        <div class="contact-info">
            <h2>Contact Details</h2>
            <p>Have any questions or technical issues with the WRU-IoT Attendance system? Reach out to our team.</p>
            
            <div class="info-box">
                <i class="fas fa-map-marker-alt"></i>
                <div><h4>Location</h4><p>Worabe University, seminar building</p></div>
            </div>
            <div class="info-box">
                <i class="fas fa-envelope"></i>
                <div><h4>Email</h4><p>support@wru.edu.et</p></div>
            </div>
            <div class="info-box">
                <i class="fas fa-phone"></i>
                <div><h4>Phone</h4><p>+251 973 394 429</p></div>
            </div>
        </div>

        <!-- Form Side -->
        <div class="contact-form">
            <form action="#">
                <input type="text" placeholder="Full Name" required>
                <input type="email" placeholder="Email Address" required>
                <textarea rows="5" placeholder="Your Message..." required></textarea>
                <button type="submit" class="send-btn">Send Message</button>
            </form>
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