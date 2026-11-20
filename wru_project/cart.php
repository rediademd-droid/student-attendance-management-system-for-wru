<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cart | WRU-IoT</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { background-color: #f4f7f6; color: #333; }

        /* Navbar */
        nav {
            background: white;
            padding: 20px 10%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .logo { font-size: 24px; font-weight: 800; color: #e67e22; text-decoration: none; }
        nav ul { display: flex; list-style: none; gap: 20px; }
        nav ul li a { text-decoration: none; color: #7f8c8d; font-weight: 500; }

        /* Cart Container */
        .cart-container {
            max-width: 1000px;
            margin: 50px auto;
            padding: 20px;
        }

        .cart-header {
            border-bottom: 2px solid #e67e22;
            padding-bottom: 10px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Empty Cart Style */
        .empty-cart {
            text-align: center;
            padding: 100px 20px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .empty-cart i {
            font-size: 80px;
            color: #bdc3c7;
            margin-bottom: 20px;
        }

        .empty-cart h3 { color: #2c3e50; margin-bottom: 10px; }
        
        .btn-browse {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 30px;
            background: #e67e22;
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: bold;
            transition: 0.3s;
        }

        .btn-browse:hover { background: #d35400; }

        footer { padding: 40px 10%; background: #2c3e50; color: white; text-align: center; margin-top: 50px; }
    </style>
</head>
<body>

    <nav>
        <a href="home.php" class="logo">WRU-IoT</a>
        <ul>
            <li><a href="home.php">Home</a></li>
            <li><a href="about.php">About Us</a></li>
            <li><a href="help.php">Help</a></li>
            <li><a href="contact.php">Contact</a></li>
        </ul>
    </nav>

    <div class="cart-container">
        <div class="cart-header">
            <h2><i class="fas fa-shopping-cart"></i> Your Cart</h2>
            <span>0 Items</span>
        </div>

        <!-- ለጊዜው ካርቱ ባዶ ስለሚሆን የሚታይ መልዕክት -->
        <div class="empty-cart">
            <i class="fas fa-shopping-basket"></i>
            <h3>Your cart is empty!</h3>
            <p>You haven't added any courses or items yet.</p>
            <a href="home.php" class="btn-browse">Go Back to Home</a>
        </div>
    </div>

    <footer>
        <p>&copy; 2024 Worabe University - IoT Institute. All rights reserved.</p>
    </footer>

</body>
</html>