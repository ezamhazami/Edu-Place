<?php
$current_page = basename($_SERVER['SCRIPT_NAME']);
$subscribed_tutors = [
    [ 'name' => 'Ezam Hazami', 'qualification' => 'MSc Mathematics', 'experience' => '5 years', 'category' => 'Math',
            'timetable' => [
            ['day' => 'Monday', 'time' => '10:00 AM - 12:00 PM'],
            ['day' => 'Thursday', 'time' => '2:00 PM - 4:00 PM']],
            'resource_link' => 'https://drive.google.com/drive/folders/1jPLvgwQs4XqhTSTO9mR0QPCd4t7A8OXI?usp=drive_link'],
    [ 'name' => 'Ahmad Daniel', 'qualification' => 'PhD Statistics', 'experience' => '8 years', 'category' => 'Statistics',
            'timetable' => [
            ['day' => 'Tuesday', 'time' => '11:00 AM - 1:00 PM'],
            ['day' => 'Friday', 'time' => '3:00 PM - 5:00 PM']]
        , 'resource_link' => 'https://drive.google.com/drive/folders/1jPLvgwQs4XqhTSTO9mR0QPCd4t7A8OXI?usp=drive_link'  ]
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Tutor Subscription</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            color:#fff;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            overflow-x: hidden;
            padding-top:90px;
        }

        /* Animated background particles */
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s infinite ease-in-out;
        }

        .particle:nth-child(1) { width: 80px; height: 80px; left: 10%; animation-delay: 0s; }
        .particle:nth-child(2) { width: 60px; height: 60px; left: 20%; animation-delay: 1s; }
        .particle:nth-child(3) { width: 100px; height: 100px; left: 70%; animation-delay: 2s; }
        .particle:nth-child(4) { width: 40px; height: 40px; left: 80%; animation-delay: 3s; }
        .particle:nth-child(5) { width: 120px; height: 120px; left: 50%; animation-delay: 4s; }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-100px) rotate(180deg); }
        }

        /* Header */
        header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            padding: 20px 50px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 100;
            transition: all 0.3s ease;
        }

        .logo {
            font-size: 28px;
            font-weight: 800;
            background: linear-gradient(45deg, #fffefe, #f1bdba);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            user-select: none;
        }
        main {
            margin-left: 240px;
            padding: 20px;
            font-family: 'Inter', sans-serif;
            color: white;
        }

        .navigation {
            display: flex;
            align-items: center;
            gap: 30px;
        }

        .navigation a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            font-size: 16px;
            padding: 10px 20px;
            border-radius: 25px;
            transition: all 0.3s ease;
            position: relative; 
            overflow: hidden;
        }

        .navigation a::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .navigation a:hover::before {
            left: 100%;
        }

        .navigation a:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        .btn-login {
            background: linear-gradient(45deg, #ff6b6b, #4ecdc4);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(255, 107, 107, 0.4);
        }
        .sidebar {
            position: fixed;
            top: 80px;
            left: 0;
            width: 220px;
            height: calc(100vh - 80px);
            background: linear-gradient(135deg,rgb(48, 78, 212) 0%, #764ba2 100%);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255, 255, 255, 0.2);
            padding-top: 20px;
            color: white;
            font-family: 'Inter', sans-serif;
            z-index: 90;
        }

        .sidebar nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar nav ul li {
            margin: 15px 0;
        }

        .sidebar nav ul li a {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            display: block;
            border-radius: 5px;
            transition: background 0.3s ease;
        }

        .sidebar nav ul li a:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .category-select {
            margin: 20px 0;
        }

        .tutor-list {
            margin-top: 20px;
        }

        .tutor-item {
            background: #fff;
            /* pure white card */
            color: #333;
            /* dark text for readability */
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .tutor-item h3 {
            margin: 0 0 10px 0;
            color: #222;
        }

        .tutor-item p {
            margin: 4px 0;
        }

        .tutor-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }



        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }
        .category-select {
            margin: 20px 0;
            display: flex;
            align-items: center;
            gap: 15px;
            font-family: 'Inter', sans-serif;
            color: white;
        }

        .category-select label {
            font-weight: 600;
            font-size: 16px;
        }

        .category-select select {
            padding: 10px 15px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            background: rgba(255, 255, 255, 0.9);
            color: #333;
            font-weight: 500;
            outline: none;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .category-select select:hover {
            background: #fff;
            box-shadow: 0 6px 15px rgba(0,0,0,0.15);
        }

        .category-select select:focus {
            border: 2px solid #421de9;
            box-shadow: 0 0 8px rgba(66,29,233,0.6);
        }
        button[type="submit"] {
            background: linear-gradient(135deg, #421de9, #b44398);
            color: #fff;
            border: none;
            padding: 12px 24px;
            border-radius: 30px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        button[type="submit"]:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 18px rgba(0,0,0,0.25);
            background: linear-gradient(135deg, #5d3df0, #c654a9);
        }

        button[type="submit"]:active {
            transform: scale(0.97);
        }

    </style>
</head>

<body>
<div class="bg-animation">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>

    <!-- Header -->
    <header>
        <div class="logo">Edu-place</div>
        <nav class="navigation">
            <a href="http://localhost/Academic_Chatbot/templates/find_tuto.php">Find Tutor</a>
            <a href="http://localhost:5000/aiService">AI Academic Assistance</a>
            <a href="http://localhost/Academic_Chatbot/templates/study-resource.php">Study Resources</a>
            <a href="http://127.0.0.1:5500/templates/index.html">Tutor Match</a>
            <button class="btn-login">Login</button>
        </nav>
    </header>

    <!-- Sidebar -->
    <div class="sidebar">
        <nav>
            <ul>
                <li><a href="find_tuto.php" class="<?php if ($current_page == 'find_tuto.php') echo 'active'; ?>">Find Tutor</a></li>
                <li><a href="tuto_subscription.php" class="<?php if ($current_page == 'tuto_subscription.php') echo 'active'; ?>">Tutor Subscription</a></li>
            </ul>
        </nav>
    </div>

    <!-- Main content -->
    <main>
        <h1>Your Subscribed Tutors</h1>

        <?php if (!empty($subscribed_tutors)): ?>
            <div class="tutor-list">
                <?php foreach ($subscribed_tutors as $tutor): ?>
                    <div class="tutor-item">
                        <h3><?= htmlspecialchars($tutor['name']) ?></h3>
                        <p><strong>Qualification:</strong> <?= htmlspecialchars($tutor['qualification']) ?></p>
                        <p><strong>Experience:</strong> <?= htmlspecialchars($tutor['experience']) ?></p>
                        <p><strong>Category:</strong> <?= htmlspecialchars($tutor['category']) ?></p>
                        <h4>📅 Timetable</h4>
                    <ul>
                        <?php foreach ($tutor['timetable'] as $slot): ?>
                            <li><?= htmlspecialchars($slot['day']) ?> - <?= htmlspecialchars($slot['time']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                        <a href="<?= htmlspecialchars($tutor['resource_link']) ?>" class="btn" target="_blank">View Resources</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>You have not subscribed to any tutors yet.</p>
        <?php endif; ?>
    </main>
</body>
</html>
