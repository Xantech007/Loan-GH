<?php
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: members/dashboard.php");
    exit;
}

$errors = [];
$email = $username = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'config/db.php'; // Connection file

    $email_or_username = trim($_POST['email_or_username']);
    $password = $_POST['password'];

    if (empty($email_or_username) || empty($password)) {
        $errors[] = "Both fields are required.";
    } else {
        try {
            // Check if input is email or username
            $field = filter_var($email_or_username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username');
            $column = strpos($email_or_username, '@') ? 'email' : 'username';

            $stmt = $pdo->prepare("SELECT id, full_name, password FROM users WHERE $column = ? LIMIT 1");
            $stmt->execute([$email_or_username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Success! Regenerate session ID for security
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['logged_in_at'] = time();

                // Redirect to dashboard
                header("Location: members/dashboard.php");
                exit;
            } else {
                $errors[] = "Invalid login credentials.";
            }
        } catch (Exception $e) {
            $errors[] = "Login error. Please try again later.";
        }
    }

    // Keep entered value
    $email_or_username = htmlspecialchars($email_or_username);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | CedisPay</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #001f3f, #003366);
            color: #333;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            padding: 40px 50px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 420px;
            text-align: center;
        }
        .logo {
            font-size: 2.5rem;
            font-weight: bold;
            color: #001f3f;
            margin-bottom: 10px;
        }
        .tagline {
            color: #555;
            margin-bottom: 30px;
            font-size: 1.1rem;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #001f3f;
            font-weight: 600;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 14px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border 0.3s;
        }
        input:focus {
            outline: none;
            border-color: #001f3f;
            box-shadow: 0 0 0 3px rgba(0, 31, 63, 0.1);
        }
        .btn-login {
            width: 100%;
            background-color: #001f3f;
            color: white;
            padding: 14px;
            font-size: 1.1rem;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-login:hover {
            background-color: #003366;
        }
        .error {
            background: #ffe6e6;
            color: #d00;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 0.95rem;
            border-left: 5px solid #d00;
        }
        .footer-links {
            margin-top: 25px;
            font-size: 0.9rem;
        }
        .footer-links a {
            color: #001f3f;
            text-decoration: none;
        }
        .footer-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="logo">CedisPay</div>
    <p class="tagline">Fast • Secure • Reliable Loans</p>

    <?php if (!empty($errors)): ?>
        <?php foreach ($errors as $error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endforeach; ?>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="email_or_username">Email or Username</label>
            <input 
                type="text" 
                id="email_or_username" 
                name="email_or_username" 
                value="<?= $email_or_username ?? '' ?>" 
                required 
                autocomplete="username"
                placeholder="Enter your email or username">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input 
                type="password" 
                id="password" 
                name="password" 
                required 
                autocomplete="current-password"
                placeholder="Enter your password">
        </div>

        <button type="submit" class="btn-login">Login to Dashboard</button>
    </form>

    <div class="footer-links">
        <p><a href="register.php">Create an account</a> • <a href="forgot_password.php">Forgot password?</a></p>
        <p style="margin-top:15px; color:#666; font-size:0.85rem;">
            © <?= date('Y') ?> CedisPay. All rights reserved.
        </p>
    </div>
</div>

</body>
</html>
