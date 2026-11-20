<?php
session_start();
require_once 'db_connect.php';

// Check if a search query was provided
$query = isset($_GET['query']) ? trim($_GET['query']) : '';

$results_students = [];
$results_courses = [];

if (!empty($query)) {
    $search_term = "%$query%";

    // 1. Search for Students (by Name or University ID)
    $stmt1 = $conn->prepare("SELECT university_id, full_name, email FROM users WHERE (full_name LIKE ? OR university_id LIKE ?) AND role = 'student'");
    $stmt1->execute([$search_term, $search_term]);
    $results_students = $stmt1->fetchAll();

    // 2. Search for Courses (by Name or Code)
    $stmt2 = $conn->prepare("SELECT course_name, course_code FROM courses WHERE course_name LIKE ? OR course_code LIKE ?");
    $stmt2->execute([$search_term, $search_term]);
    $results_courses = $stmt2->fetchAll();
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
        :root {
            --primary-bg: #2c3e50; /* Sidebar color */
            --accent-orange: #e67e22; /* Dashboard orange */
            --light-bg: #f4f7f6;
            --text-dark: #2c3e50;
        }

        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: var(--light-bg); 
            margin: 0; 
            padding: 40px 20px; 
        }

        .results-container { 
            max-width: 900px; 
            margin: auto; 
            background: white; 
            padding: 35px; 
            border-radius: 20px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.08); 
        }

        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #f1f2f6;
            padding-bottom: 15px;
        }

        h2 { color: var(--text-dark); margin: 0; font-size: 24px; }
        h2 span { color: var(--accent-orange); }

        .back-btn { 
            text-decoration: none; 
            color: var(--primary-bg); 
            font-weight: 600; 
            font-size: 14px;
            transition: 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .back-btn:hover { color: var(--accent-orange); }

        .result-group { margin-bottom: 25px; }

        .result-item { 
            padding: 20px; 
            border-radius: 12px;
            border: 1px solid #edf2f7;
            margin-bottom: 12px;
            transition: 0.3s ease;
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .result-item:hover { 
            background: #fff9f4; 
            border-color: var(--accent-orange);
            transform: translateX(5px);
        }

        .icon-box {
            width: 45px;
            height: 45px;
            background: var(--light-bg);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent-orange);
            font-size: 20px;
        }

        .info-box { flex-grow: 1; }

        .category-label { 
            font-weight: 800; 
            color: var(--accent-orange); 
            text-transform: uppercase; 
            font-size: 11px; 
            letter-spacing: 1px;
            display: block;
            margin-bottom: 4px;
        }

        .info-box p { margin: 0; font-size: 16px; color: var(--text-dark); }
        .info-box small { color: #7f8c8d; }

        .no-results { 
            color: #7f8c8d; 
            text-align: center; 
            padding: 60px 20px; 
        }

        .no-results i { color: #dcdde1; margin-bottom: 15px; }
    </style>
</head>
<body>

<div class="results-container">
    <div class="header-section">
        <h2>Results for: <span>"<?php echo htmlspecialchars($query); ?>"</span></h2>
        <a href="home.php" class="back-btn"><i class="fas fa-home"></i> Back to Home</a>
    </div>

    <?php if (empty($results_students) && empty($results_courses)): ?>
        <div class="no-results">
            <i class="fas fa-search-minus fa-4x"></i>
            <p>No matches found in our database for this query.</p>
            <a href="home.php" class="back-btn" style="justify-content: center; margin-top: 10px;">Try another search</a>
        </div>
    <?php else: ?>

        <?php if (!empty($results_students)): ?>
            <div class="result-group">
                <?php foreach ($results_students as $student): ?>
                    <div class="result-item">
                        <div class="icon-box"><i class="fas fa-user-graduate"></i></div>
                        <div class="info-box">
                            <span class="category-label">Student</span>
                            <p><strong><?php echo htmlspecialchars($student['full_name']); ?></strong></p>
                            <small>ID: <?php echo htmlspecialchars($student['university_id']); ?> | <?php echo htmlspecialchars($student['email']); ?></small>
                        </div>
                        <i class="fas fa-chevron-right" style="color: #dcdde1;"></i>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($results_courses)): ?>
            <div class="result-group">
                <?php foreach ($results_courses as $course): ?>
                    <div class="result-item">
                        <div class="icon-box"><i class="fas fa-book-open"></i></div>
                        <div class="info-box">
                            <span class="category-label">Course</span>
                            <p><strong><?php echo htmlspecialchars($course['course_name']); ?></strong></p>
                            <small>Code: <?php echo htmlspecialchars($course['course_code']); ?></small>
                        </div>
                        <i class="fas fa-chevron-right" style="color: #dcdde1;"></i>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    <?php endif; ?>
</div>

</body>
</html>