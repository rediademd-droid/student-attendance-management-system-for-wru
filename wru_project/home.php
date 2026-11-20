<?php
session_start();
// Check if user is logged in to toggle button text
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home | WRU-IoT Attendance</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* --- 1. Basic Styling --- */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f8fafc; color: #2c3e50; line-height: 1.6; overflow-x: hidden; }

        /* --- 2. Navbar --- */
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
            width: 100%;
        }

        .logo { 
            display: flex !important; 
            flex-direction: column !important; 
            align-items: center !important; 
            text-decoration: none;
        }
        .logo img { height: 50px; width: auto; object-fit: contain; }
        
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
        .nav-links li a:hover { color: #e67e22; }

        .btn-login {
            background: #e67e22;
            color: white !important;
            padding: 8px 22px;
            border-radius: 8px;
            font-weight: 600;
            transition: 0.3s;
        }
        .btn-login:hover { background: #d35400; transform: translateY(-2px); }

        .nav-icons { 
            display: flex; 
            align-items: center; 
            gap: 18px; 
            border-left: 1.5px solid #eee; 
            padding-left: 18px; 
        }
        .nav-icons a { color: #2c3e50; text-decoration: none; font-size: 19px; transition: 0.3s; cursor: pointer; }
        .nav-icons a:hover { color: #e67e22; }

        .menu-toggle { display: none; font-size: 24px; cursor: pointer; color: #2c3e50; }

        /* --- 3. Search Overlay --- */
        .search-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(44, 62, 80, 0.95);
            display: none; 
            justify-content: center;
            align-items: center;
            z-index: 3000;
        }
        .search-container-box { width: 80%; max-width: 600px; text-align: center; }
        .search-container-box h2 { color: white; margin-bottom: 25px; font-weight: 300; }
        .search-container-box input {
            width: 100%; padding: 18px 30px; border-radius: 50px; border: 2px solid transparent;
            font-size: 18px; outline: none; box-shadow: 0 10px 25px rgba(0,0,0,0.3); transition: 0.3s;
        }
        .search-container-box input:focus { border-color: #e67e22; }
        .close-search { position: absolute; top: 30px; right: 40px; color: white; font-size: 45px; cursor: pointer; transition: 0.3s; }
        .close-search:hover { color: #e67e22; transform: rotate(90deg); }

        /* --- 4. Hero Section Updated --- */
        .hero {
            padding: 80px 10%;
            display: flex;
            align-items: center; /* Vertically centers the text and image */
            justify-content: space-between;
            min-height: calc(100vh - 80px);
            gap: 60px; /* Increased gap for better whitespace */
            position: relative;
            background: linear-gradient(rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.9)), 
                        url('image3.png'); 
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        
        .hero-text { flex: 1.2; z-index: 1; }

        .marquee-container {
            width: 100%;
            overflow: hidden;
            margin-bottom: 20px;
            border-bottom: 2px solid #e67e22; /* Adds a nice underline to the scrolling text */
        }

        .hero-text h1 { 
            font-size: 42px; 
            font-weight: 800; 
            color: #2c3e50; 
            white-space: nowrap;
            padding-bottom: 10px;
        }

        .hero-text p { 
            font-size: 18px; 
            color: #475569; 
            margin-bottom: 35px; 
            max-width: 580px;
            font-weight: 500;
        }
        
        .hero-image { flex: 0.8; text-align: right; z-index: 1; }
        .hero-image img { 
            width: 100%; 
            max-width: 480px; 
            border-radius: 30px; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.15); /* Softened shadow */
            border: 8px solid white;
            transition: transform 0.4s ease; /* Smooth hover effect */
        }

        .hero-image img:hover {
            transform: scale(1.02); /* Slight zoom on hover */
        }

        /* --- 5. Responsive Design --- */
        @media (max-width: 992px) {
            nav { padding: 10px 5%; }
            .menu-toggle { display: block; }
            .nav-links {
                display: none; flex-direction: column; position: absolute;
                top: 100%; left: 0; width: 100%; background: white;
                padding: 30px; gap: 20px; box-shadow: 0 10px 15px rgba(0,0,0,0.1);
            }
            .nav-links.active { display: flex; }
            .hero { padding: 40px 5%; text-align: center; flex-direction: column; justify-content: center; }
            .hero-text h1 { font-size: 28px; }
            .hero-image { order: -1; text-align: center; }
            .hero-image img { max-width: 320px; }
        }
    </style>
</head>
<body>

    <div id="searchOverlay" class="search-overlay">
        <span class="close-search" onclick="closeSearch()">&times;</span>
        <div class="search-container-box">
            <h2>Find Students or Faculty</h2>
            <form action="search_results.php" method="GET">
                <input type="text" name="query" id="searchInput" placeholder="Search by name or ID..." required>
                <p style="color: #bdc3c7; margin-top: 15px; font-size: 14px;">Press Enter to Search</p>
            </form>
        </div>
    </div>

    <nav>
        <a href="home.php" class="logo">
            <img src="wu_logo.png" alt="WRU Logo">
            <div class="logo-text">WRU-<span>IoT</span></div>
        </a>
        
        <div class="nav-container">
            <ul class="nav-links" id="navLinks">
                <li><a href="home.php" style="color: #e67e22;">Home</a></li>
                <li><a href="about.php">About Us</a></li>
                <li><a href="help.php">Help</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li>
                    <?php if($isLoggedIn): ?>
                        <a href="profile.php" class="btn-login">Profile</a>
                    <?php else: ?>
                        <a href="index.php" class="btn-login">Login</a>
                    <?php endif; ?>
                </li>
            </ul>

            <div class="nav-icons">
                <a href="javascript:void(0)" onclick="openSearch()"><i class="fas fa-search"></i></a>
                <a href="<?php echo $isLoggedIn ? 'profile.php' : 'index.php'; ?>">
                    <i class="fas fa-user-circle"></i>
                </a>
            </div>

            <div class="menu-toggle" onclick="toggleMenu()">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </nav>

    <section class="hero">
        <div class="hero-text">
            <div class="marquee-container">
                <marquee behavior="scroll" direction="left" scrollamount="7">
                    <h1>Welcome to Worabe University College of Technology and Digital Convergence</h1>
                </marquee>
            </div>

            <p>Experience the modern solution for classroom presence. Our Smart Attendance System uses QR and GPS verification to ensure secure, fast, and paperless tracking for all students and faculty.</p>
            
            <a href="<?php echo $isLoggedIn ? 'profile.php' : 'index.php'; ?>" 
               style="display: inline-block; background: #e67e22; color: white; padding: 16px 40px; border-radius: 12px; text-decoration: none; font-weight: bold; transition: 0.3s; box-shadow: 0 4px 15px rgba(230,126,34,0.3);">
               <?php echo $isLoggedIn ? 'View Dashboard' : 'Get Started Now'; ?>
            </a>
        </div>
        <div class="hero-image">
            <img src="image.png" alt="WRU Campus">
        </div>
    </section>

    <script>
        function openSearch() {
            document.getElementById('searchOverlay').style.display = 'flex';
            setTimeout(() => { document.getElementById('searchInput').focus(); }, 100);
        }

        function closeSearch() { document.getElementById('searchOverlay').style.display = 'none'; }

        window.onclick = function(event) {
            let overlay = document.getElementById('searchOverlay');
            if (event.target == overlay) { closeSearch(); }
        }

        function toggleMenu() {
            const navLinks = document.getElementById('navLinks');
            navLinks.classList.toggle('active');
        }
    </script>

</body>
</html>