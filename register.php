<?php
// register.php - CedisPay Registration (NO Member ID shown)
session_start();
require 'config/db.php'; // Adjust path if needed

$success = $error = "";

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = "Invalid request. Please try again.";
    } else {
        $full_name = trim($_POST['full_name'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $phone     = trim($_POST['phone'] ?? '');
        $password  = $_POST['password'] ?? '';
        $confirm   = $_POST['confirm_password'] ?? '';

        if (empty($full_name) || empty($email) || empty($phone) || empty($password) || empty($confirm)) {
            $error = "All fields are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } elseif ($password !== $confirm) {
            $error = "Passwords do not match.";
        } elseif (strlen($password) < 8) {
            $error = "Password must be at least 8 characters long.";
        } else {
            // Check if email or phone already exists
            $check = $pdo->prepare("SELECT id FROM users WHERE email = ? OR phone = ?");
            $check->execute([$email, $phone]);
            if ($check->rowCount() > 0) {
                $error = "This email or phone number is already registered.";
            } else {
                // Hash password and insert user
                $hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("
                    INSERT INTO users (full_name, email, phone, password, created_at)
                    VALUES (?, ?, ?, ?, NOW())
                ");
                if ($stmt->execute([$full_name, $email, $phone, $hash])) {
                    $success = "Account created successfully!<br>
                                You can now <a href='login.php' style='color:#001f3f;font-weight:bold;text-decoration:underline;'>log in here</a>.";
                } else {
                    $error = "Something went wrong. Please try again later.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join CedisPay • Create Account</title>
    <style>
        :root {
            --primary: #001f3f;
            --primary-light: #003366;
            --white: #fff;
            --light: #f8f9fa;
            --gray: #ddd;
            --error: #e74c3c;
            --success: #27ae60;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #001f3f, #003366);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .main-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            width: 90%;
            max-width: 1000px;
            background: var(--white);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0,0,0,0.25);
        }
        .col-left {
            background: linear-gradient(rgba(0,31,63,0.92), rgba(0,51,102,0.92)), url('assets/register-bg.jpg');
            background-size: cover;
            background-position: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px;
            color: white;
            text-align: center;
        }
        .col-left img {
            width: 200px;
            margin-bottom: 20px;
        }
        .col-left h1 { font-size: 2.3rem; margin-bottom: 15px; }
        .col-left p { opacity: 0.95; font-size: 1.1rem; }

        .container {
            padding: 50px 45px;
            background: var(--white);
        }
        .logo { text-align: center; margin-bottom: 10px; }
        .logo img { height: 60px; }
        h2 {
            text-align: center;
            color: var(--primary);
            margin: 10px 0 35px;
            font-size: 28px;
            font-weight: 600;
        }
        .form-group {
            position: relative;
            margin-bottom: 28px;
        }
        .form-group input {
            width: 100%;
            padding: 18px 0 8px;
            font-size: 16px;
            border: none;
            border-bottom: 2px solid var(--gray);
            background: transparent;
            outline: none;
        }
        .form-group label {
            position: absolute;
            top: 18px;
            left: 0;
            color: #999;
            pointer-events: none;
            transition: 0.3s;
            font-size: 16px;
        }
        .form-group input:focus ~ label,
        .form-group input:valid ~ label {
            top: -5px;
            font-size: 13px;
            color: var(--primary);
            font-weight: 600;
        }
        .form-group input:focus { border-bottom-color: var(--primary); }

        button {
            width: 100%;
            padding: 16px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 17px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }
        button:hover {
            background: var(--primary-light);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,31,63,0.2);
        }

        .alert {
            padding: 15px;
            margin: 20px 0;
            border-radius: 10px;
            text-align: center;
            font-weight: 500;
        }
        .alert-error { background: #fdf2f2; color: var(--error); border: 1px solid #fabcbc; }
        .alert-success { background: #f0fdf4; color: var(--success); border: 1px solid #bbf7d0; }

        .back-to-login {
            text-align: center;
            margin-top: 30px;
            font-size: 15px;
        }
        .back-to-login a {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
        }
        .back-to-login a:hover { text-decoration: underline; }

        @media (max-width: 768px) {
            .main-container { grid-template-columns: 1fr; }
            .col-left { display: none; }
            .container { padding: 40px 30px; }
        }
    </style>
</head>
<body>

<div class="main-container">
    <!-- Left Side -->
    <div class="col-left">
        <img src="assets/cedispay-logo-white.png" alt="CedisPay">
        <h1>Join CedisPay Today</h1>
        <p>Get instant access to fast, reliable loans with transparent terms.</p>
    </div>

    <!-- Right Side - Form -->
    <div class="container">
        <div class="logo">
            <img src="assets/profile_3135715.png" alt="Icon">
        </div>
        <h2>Create Your Account</h2>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <div class="form-group">
                <input type="text" name="full_name" required>
                <label>Full Name</label>
            </div>

            <div class="form-group">
                <input type="email" name="email" required>
                <label>Email Address</label>
            </div>

            <div class="form-group">
                <input type="text" name="phone" required>
                <label>Phone Number</label>
            </div>

            <div class="form-group">
                <input type="password" name="password" required minlength="8">
                <label>Create Password</label>
            </div>

            <div class="form-group">
                <input type="password" name="confirm_password" required minlength="8">
                <label>Confirm Password</label>
            </div>

            <button type="submit">Create Account</button>
        </form>

        <div class="back-to-login">
            Already have an account? <a href="login.php">Log in here</a>
        </div>
    </div>
</div>

</body>
</html>
