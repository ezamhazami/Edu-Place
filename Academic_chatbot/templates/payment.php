<?php

$tutorName = isset($_POST['tutor_name']) ? htmlspecialchars($_POST['tutor_name']) : '';
$category  = isset($_POST['category']) ? htmlspecialchars($_POST['category']) : '';

$paid = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    $paid = true;
    $buyerName  = htmlspecialchars($_POST['buyer_name'] ?? '');
    $buyerEmail = htmlspecialchars($_POST['buyer_email'] ?? '');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<link rel="stylesheet" href="payment.css" />
<title>Payment</title>
<style>
    :root {
    --bg1: #421de9;
    --bg2: #b44398;
    --card-bg: rgba(255, 255, 255, 0.12);
    --card-stroke: rgba(255, 255, 255, 0.25);
    --white: #ffffff;
    --muted: rgba(255, 255, 255, 0.8);
    --accent: #ffffff;
}

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    display: grid;
    place-items: center;
    padding: 24px;
    color: var(--white);
}

.card {
    width: 100%;
    max-width: 560px;
    background: var(--card-bg);
    border: 1px solid var(--card-stroke);
    border-radius: 20px;
    margin-top: 100px;
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
    padding: 24px;
}

.header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 16px;
}

.title {
    font-size: 1.4rem;
    font-weight: 700;
    margin: 0;
}

.pill {
    padding: 6px 10px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.14);
    border: 1px solid var(--card-stroke);
    font-size: 0.85rem;
}

.meta {
    margin: 0 0 18px 0;
    font-size: 0.95rem;
    color: var(--muted);
}

form {
    display: grid;
    gap: 14px;
}

label {
    font-size: 0.9rem;
    margin-bottom: 6px;
    display: inline-block;
}

input,
select {
    width: 100%;
    padding: 12px 14px;
    border-radius: 12px;
    border: 1px solid var(--card-stroke);
    background: rgba(255, 255, 255, 0.08);
    color: var(--white);
    outline: none;
}

input::placeholder {
    color: rgba(255, 255, 255, 0.6);
}

.row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}


.btn {
    display: inline-block;
    padding: 12px 18px;
    border-radius: 12px;
    font-weight: 600;
    text-align: center;
    cursor: pointer;
    transition: transform 0.05s ease, background 0.3s ease, color 0.3s ease;
    text-decoration: none;
    
}


.btn-primary {
    background: var(--accent);
    color: #111;
    border: none;
}

.btn-primary:active {
    transform: translateY(1px);
}


.btn-back {
    background: transparent;
    color: var(--white);
    border: 2px solid var(--white);
}

.btn-back:hover {
    background: rgba(255, 255, 255, 0.15);
}


.note {
    font-size: 0.85rem;
    color: var(--muted);
    margin-top: 8px;
    text-align: center;
}

.success {
    text-align: center;
    padding: 24px 12px;
}

.success h2 {
    margin: 0 0 8px 0;
    font-size: 1.6rem;
}

.success p {
    color: var(--muted);
}

.back {
    display: inline-block;
    margin-top: 16px;
    color: var(--white);
    text-decoration: underline;
}

select {
    width: 100%;
    padding: 12px 14px;
    border-radius: 12px;
    border: 1px solid var(--card-stroke);
    background: rgba(255, 255, 255, 0.08);
    color: var(--white);
    outline: none;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    cursor: pointer;
}

select option {
    background: #2d145d;
    color: var(--white);
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
        main{
            margin-left: 240px;
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
    <div class="card">
        <?php if ($paid): ?>
            <div class="success">
                <h2>Payment Successful 🎉</h2>
                <p>Thanks <?= $buyerName ?: 'there' ?>! Your subscription for <strong><?= $tutorName ?: 'Selected Tutor' ?></strong> (<?= $category ?: 'Category' ?>) is confirmed.</p>
                <a href="find_tuto.php" class="back">Back to Home</a>
            </div>
        <?php else: ?>
            <div class="header">
                <h1 class="title">Complete Your Payment</h1>
                <span class="pill"><?= $category ? $category : 'No Category Selected' ?></span>
            </div>
            <p class="meta">
                Tutor: <strong><?= $tutorName ?: 'Not specified' ?></strong>
            </p>

            <form method="POST" action="payment.php" onsubmit="return validatePayment()">
                <input type="hidden" name="tutor_name" value="<?= $tutorName ?>">
                <input type="hidden" name="category" value="<?= $category ?>">

                <div>
                    <label for="buyer_name">Your Name</label>
                    <input id="buyer_name" name="buyer_name" type="text" placeholder="e.g. Ali Bin Abu" required>
                </div>

                <div>
                    <label for="buyer_email">Email</label>
                    <input id="buyer_email" name="buyer_email" type="email" placeholder="you@example.com" required>
                </div>

                <div>
                    <label for="plan">Plan</label>
                    <select id="plan" name="plan" required>
                        <option value="" disabled selected>Choose a plan</option>
                        <option value="monthly">Monthly — RM49</option>
                        <option value="quarterly">Quarterly — RM129</option>
                        <option value="yearly">Yearly — RM459</option>
                    </select>
                </div>

                <div>
                    <label>Card Details</label>
                    <input name="card_number" inputmode="numeric" maxlength="19" placeholder="1234 5678 9012 3456" required>
                </div>

                <div class="row">
                    <div>
                        <label>Expiry (MM/YY)</label>
                        <input name="card_exp" placeholder="MM/YY" pattern="^(0[1-9]|1[0-2])\/\d{2}$" required>
                    </div>
                    <div>
                        <label>CVV</label>
                        <input name="card_cvv" inputmode="numeric" maxlength="4" placeholder="123" required>
                    </div>
                </div>

                <!-- Mock submit: confirm_payment acts as a flag -->
                <input type="hidden" name="confirm_payment" value="1">
                <button class="btn" type="submit">Pay Now</button>
                <button class="btn btn-back" type="button" onclick="history.back()">Back</button>
                <div class="note">Demo page — no real charge will be made.</div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
