<?php
session_start();
require_once 'db_connect.php';

// Check if a search query was actually sent
$query = isset($_GET['query']) ? trim($_GET['query']) : '';
$results = [];

if (!empty($query)) {
    try {
        /**
         * We use LIKE to search for partial matches in Name, ID, or Email.
         * %query% means "contains this text"
         */
        $stmt = $conn->prepare("SELECT full_name, university_id, email, role FROM users 
                                WHERE full_name LIKE ? OR university_id LIKE ? OR email LIKE ?");
        $searchTerm = "%$query%";
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        $results = $stmt->fetchAll();
    } catch (PDOException $e) {
        die("Search Error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results | WRU-IoT</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { background-color: #f4f7f6; color: #333; }

        nav {
            background: white;
            padding: 15px 10%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .logo { font-size: 24px; font-weight: 800; color: #e67e22; text-decoration: none; }
        .logo span { color: #2c3e50; }

        .container {
            max-width: 900px;
            margin: 50px auto;
            padding: 0 20px;
        }

        .search-header { margin-bottom: 30px; }
        .search-header h2 { color: #2c3e50; }
        .search-header span { color: #e67e22; }

        /* Result Card Styling */
        .result-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 3px 10px rgba(0,0,0,0.03);
            transition: 0.3s;
            border-left: 5px solid transparent;
        }
        .result-card:hover { 
            transform: translateX(5px); 
            border-left: 5px solid #e67e22;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .user-info { display: flex; align-items: center; gap: 20px; }
        .user-avatar {
            width: 50px;
            height: 50px;
            background: #fef5ed;
            color: #e67e22;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .user-details h3 { font-size: 18px; color: #2c3e50; }
        .user-details p { font-size: 14px; color: #7f8c8d; }

        .role-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            background: #eee;
        }
        .student { background: #e1f5fe; color: #0288d1; }
        .instructor { background: #fff3e0; color: #e67e22; }

        .no-results {
            text-align: center;
            padding: 50px;
            background: white;
            border-radius: 20px;
        }
        .no-results i { font-size: 50px; color: #bdc3c7; margin-bottom: 15px; }

        .back-link { margin-top: 20px; display: inline-block; color: #e67e22; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>

    <nav>
        <a href="home.php" class="logo">WRU-<span>IoT</span></a>
        <a href="home.php" style="text-decoration: none; color: #7f8c8d;">Back to Home</a>
    </nav>

    <div class="container">
        <div class="search-header">
            <h2>Search Results for <span>"<?php echo htmlspecialchars($query); ?>"</span></h2>
            <p><?php echo count($results); ?> matches found.</p>
        </div>

        <?php if (!empty($results)): ?>
            <?php foreach ($results as $row): ?>
                <div class="result-card">
                    <div class="user-info">
                        <div class="user-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="user-details">
                            <h3><?php echo htmlspecialchars($row['full_name']); ?></h3>
                            <p><i class="fas fa-id-card"></i> <?php echo htmlspecialchars($row['university_id']); ?> | <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($row['email']); ?></p>
                        </div>
                    </div>
                    <span class="role-badge <?php echo strtolower($row['role']); ?>">
                        <?php echo htmlspecialchars($row['role']); ?>
                    </span>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-results">
                <i class="fas fa-search"></i>
                <h3>No Users Found</h3>
                <p>We couldn't find any user matching your search criteria.</p>
                <a href="home.php" class="back-link">Try again</a>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>