<?php
// study-resources.php
include 'config.php';

// Simple POST handler: add a new resource (from the per-category form)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_resource') {
    // Basic server-side validation
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $url = isset($_POST['url']) ? trim($_POST['url']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';

    if ($category_id > 0 && $title !== '' && $url !== '') {
        $stmt = $conn->prepare("INSERT INTO resources (category_id, title, url, description) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $category_id, $title, $url, $description);
        $stmt->execute();
        $stmt->close();
    }
    // Redirect to avoid resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch categories
$cats = $conn->query("SELECT * FROM categories ORDER BY id");

// Fetch resources grouped by category id
$resources = [];
$resAll = $conn->query("SELECT * FROM resources ORDER BY created_at DESC");
while ($r = $resAll->fetch_assoc()) {
    $resources[$r['category_id']][] = $r;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Study Resources</title>
  <style>
    /* Page + header styles (matching your theme) */
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

    /* Container */
    .container { max-width:1100px; margin:0 auto; padding: 20px; }

    h1 { text-align:center; margin-bottom:18px; font-size:28px; }

    /* Category card */
    .category {
      background: rgba(255,255,255,0.08);
      border-radius:14px;
      padding:18px;
      margin-bottom:22px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.08);
    }
    .category-head { display:flex; justify-content:space-between; align-items:center; gap:12px; }
    .category-head h2 { margin:0; font-size:20px; }
    .category-head p.desc { margin:0; color:#e7e7ff88; font-size:14px; }

    /* Resource bubbles */
    .resources { margin-top:12px; display:flex; flex-wrap:wrap; gap:10px; }
    .bubble {
      background: #fff;
      color: #222;
      padding:10px 14px;
      border-radius:999px;
      display:inline-flex;
      align-items:center;
      gap:12px;
      box-shadow:0 3px 12px rgba(0,0,0,0.12);
      max-width:100%;
    }
    .bubble .meta { display:flex; flex-direction:column; }
    .bubble .meta .title { font-weight:600; font-size:14px; }
    .bubble .meta .small { font-size:12px; color:#555; }

    .bubble a.visit {
      background:#667eea; color:#fff; padding:6px 10px; border-radius:10px; text-decoration:none; font-weight:600;
    }

    /* Add form */
    .add-form { margin-top:14px; display:flex; gap:8px; flex-wrap:wrap; align-items:center; }
    .add-form input[type="text"], .add-form input[type="url"] {
      padding:8px 10px; border-radius:8px; border: none; min-width:200px;
    }
    .add-form textarea { padding:8px; border-radius:8px; border:none; min-width:200px; min-height:40px; }
    .add-form button { padding:8px 12px; border-radius:8px; border:none; background:#764ba2; color:#fff; cursor:pointer; }

    /* Responsive */
    @media (max-width:720px) {
      .add-form { flex-direction:column; align-items:stretch; }
      .bubble { width:100%; justify-content:space-between; }
    }
  </style>
</head>
<body>
  <!-- Animated Background -->
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

  <div class="container">
    <h1>Study Resources</h1>

    <?php if ($cats && $cats->num_rows > 0): ?>
      <?php while($cat = $cats->fetch_assoc()): ?>
        <div class="category">
          <div class="category-head">
            <div>
              <h2><?php echo htmlspecialchars($cat['name']); ?></h2>
              <p class="desc"><?php echo htmlspecialchars($cat['description']); ?></p>
            </div>
            <div style="font-size:12px;color:#e7e7ff88;">Category ID: <?php echo $cat['id']; ?></div>
          </div>

          <div class="resources">
            <?php if (!empty($resources[$cat['id']])): ?>
              <?php foreach ($resources[$cat['id']] as $r): ?>
                <div class="bubble">
                  <div class="meta">
                    <div class="title"><?php echo htmlspecialchars($r['title']); ?></div>
                    <div class="small"><?php echo htmlspecialchars($r['description'] ?: 'No description'); ?></div>
                  </div>
                  <a class="visit" href="<?php echo htmlspecialchars($r['url']); ?>" target="_blank" rel="noopener">Visit</a>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div style="color:#e7e7ffcc;">No resources yet. Add one below.</div>
            <?php endif; ?>
          </div>

          <!-- Add resource form (posts to same page) -->
          <form class="add-form" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <input type="hidden" name="action" value="add_resource">
            <input type="hidden" name="category_id" value="<?php echo $cat['id']; ?>">
            <input type="text" name="title" placeholder="Resource title (e.g. Intro to CSS)" required>
            <input type="url" name="url" placeholder="https://example.com" required>
            <input type="text" name="description" placeholder="Short description (optional)">
            <button type="submit">Add Resource</button>
          </form>

        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p>No categories found. Create categories in phpMyAdmin (table `categories`).</p>
    <?php endif; ?>

  </div>
</body>
</html>
