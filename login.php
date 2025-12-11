<?php
// login.php - Updated for CedisPay (uses 'users' table + redirects to members/dashboard.php)
session_start();
require 'config/db.php'; // Adjust path if login.php is in a subfolder

$error = "";

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = "Invalid request. Please try again.";
    } else {
        $login_input = trim($_POST['login_input'] ?? '');
        $password    = $_POST['password'] ?? '';

        if (empty($login_input) || empty($password)) {
            $error = "Please enter your login details.";
        } else {
            // Allow login with: username, email, or phone
            $sql = "SELECT id, username, full_name, email, phone, password 
                    FROM users 
                    WHERE username = ? 
                       OR email = ? 
                       OR phone = ? 
                    LIMIT 1";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$login_input, $login_input, $login_input]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // Login successful
                session_regenerate_id(true);

                $_SESSION['user_id']    = $user['id'];
                $_SESSION['username']   = $user['username'];
                $_SESSION['full_name']  = $user['full_name'] ?? 'Member';
                $_SESSION['email']      = $user['email'];
                $_SESSION['logged_in']  = true;

                // Redirect to dashboard inside members folder
                header("Location: members/dashboard.php");
                exit();
            } else {
                $error = "Invalid username, email, phone, or password.";
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
    <title>Login • CedisPay</title>
    <style>
        :root {
            --primary:#001f3f;
            --primary-light:#003366;
            --white:#fff;
            --light:#f8f9fa;
            --gray:#ddd;
            --error:#e74c3c;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #001f3f, #003366);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
        }
        .main-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            width: 90%;
            max-width: 1000px;
            background: var(--white);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
        .col-left {
            background: linear-gradient(rgba(0,31,63,0.95), rgba(0,51,102,0.95)), url('assets/login-bg.jpg');
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
            width: 180px;
            margin-bottom: 20px;
            filter: drop-shadow(0 4px 10px rgba(0,0,0,0.3));
        }
        .col-left h1 {
            font-size: 2.2rem;
            margin-bottom: 10px;
        }
        .col-left p {
            opacity: 0.9;
            font-size: 1.1rem;
        }
        .container {
            padding: 50px 40px;
            background: var(--white);
        }
        .logo {
            text-align: center;
            margin-bottom: 10px;
        }
        .logo img {
            height: 60px;
        }
        h2 {
            text-align: center;
            color: var(--primary);
            margin: 10px 0 30px;
            font-size: 26px;
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
            transition: 0.3s;
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
        .form-group input:focus {
            border-bottom-color: var(--primary);
        }
        .forgot-password {
            display: block;
            text-align: right;
            margin: 10px 0 20px;
            color: var(--primary);
            font-size: 14px;
            text-decoration: none;
        }
        .forgot-password:hover {
            text-decoration: underline;
        }
        button {
            width: 100%;
            padding: 15px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 17px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }
        button:hover {
            background: var(--primary-light);
            transform: translateY(-2px);
        }
        .alert {
            padding: 14px;
            margin: 20px 0;
            background: #ffebee;
            color: var(--error);
            border-radius: 8px;
            border-left: 5px solid var(--error);
            text-align: center;
            font-weight: 500;
        }
        .dont-have {
            text-align: center;
            margin-top: 30px;
            font-size: 15px;
        }
        .dont-have a {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
        }
        .dont-have a:hover {
            text-decoration: underline;
        }
        @media (max-width: 768px) {
            .main-container {
                grid-template-columns: 1fr;
            }
            .col-left {
                display: none;
            }
            .container {
                padding: 40px 30px;
            }
        }
    </style>
</head>
<body>

<div class="main-container">
    <!-- Left Side - Branding -->
    <div class="col-left">
        <img src="assets/cedispay-logo-white.png" alt="CedisPay">
        <h1>Welcome Back!</h1>
        <p>Access your loan dashboard and manage your finances with ease.</p>
    </div>

    <!-- Right Side - Login Form -->
    <div class="container">
        <div class="logo">
            <img src="assets/profile_3135715.png" alt="CedisPay Icon">
        </div>
        <h2>Member Login</h2>

        <?php if ($error): ?>
            <div class="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <div class="form-group">
                <input type="text" name="login_input" required>
                <label>Username, Email or Phone</label>
            </div>

            <div class="form-group">
                <input type="password" name="password" required>
                <label>Password</label>
            </div>

            <a href="forgot-password.php" class="forgot-password">Forgot Password?</a>

            <button type="submit">Log In Securely</button>
        </form>

        <div class="dont-have">
            New to CedisPay? <a href="register.php">Apply for Membership</a>
        </div>
    </div>
</div>

</body>
</html>
