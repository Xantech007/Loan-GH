<?php
// login.php - CedisPay Member Login
session_start();
require '../config/db.php';

$error = "";

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = "Invalid request. Please try again.";
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $error = "Please enter your Member ID and password.";
        } else {
            $stmt = $conn->prepare("SELECT u.*, m.full_name FROM users u 
                                   LEFT JOIN members m ON u.member_id = m.member_id 
                                   WHERE u.username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user && password_verify($password, $user['password'])) {
                // Regenerate session ID to prevent fixation
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['member_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'] ?? 'Member';
                $_SESSION['logged_in'] = true;

                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid Member ID or password.";
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
            --primary-color: #003366;
            --primary-color-light: #004488;
            --text-color: #333;
            --background-color: #f4f4f4;
            --white: #ffffff;
            --error-color: #ff3860;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: "Arial", sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .main-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            width: 90%;
            max-width: 1000px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border-radius: 10px;
            overflow: hidden;
        }
        .col-2 {
            background: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .col-2 img {
            max-width: 70%;
            opacity: 0.9;
        }
        .container {
            background-color: var(--white);
            padding: 3rem 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo img {
            width: 120px;
        }
        h2 {
            text-align: center;
            color: var(--primary-color);
            margin: 10px 0 40px 0;
            font-size: 28px;
        }
        .form-group {
            position: relative;
            margin-bottom: 2rem;
        }
        .form-group input {
            width: 100%;
            padding: 12px 0 8px 0;
            font-size: 16px;
            color: var(--text-color);
            border: none;
            border-bottom: 1px solid #ddd;
            outline: none;
            background: transparent;
        }
        .form-group label {
            position: absolute;
            top: 12px;
            left: 0;
            font-size: 16px;
            color: #999;
            pointer-events: none;
            transition: 0.3s ease all;
        }
        /* Fixed: Labels float correctly when typing or filled */
        .form-group input:focus ~ label,
        .form-group input:not(:placeholder-shown):valid ~ label {
            top: -12px;
            font-size: 13px;
            color: var(--primary-color);
            font-weight: 500;
        }
        .form-group input:focus {
            border-bottom: 2px solid var(--primary-color);
        }
        button {
            width: 100%;
            padding: 14px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
            transition: 0.3s;
        }
        button:hover {
            background-color: var(--primary-color-light);
        }
        .alert {
            padding: 12px;
            margin: 20px 0;
            border-radius: 6px;
            text-align: center;
            background: #ffebee;
            color: var(--error-color);
            border: 1px solid #ffcdd2;
        }
        .forgot-password {
            display: block;
            text-align: right;
            color: var(--primary-color);
            text-decoration: none;
            font-size: 14px;
            margin: 15px 0;
        }
        .forgot-password:hover {
            text-decoration: underline;
        }
        .dont-have-account {
            text-align: center;
            margin-top: 30px;
            font-size: 15px;
        }
        .dont-have-account a {
            color: var(--primary-color);
            font-weight: 600;
            text-decoration: none;
        }
        .dont-have-account a:hover {
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-container { grid-template-columns: 1fr; }
            .col-2 { display: none; }
            .container { padding: 2rem; border-radius: 10px; }
        }
        @media (max-width: 480px) {
            .container { padding: 1.5rem; }
            h2 { font-size: 24px; }
        }
    </style>
</head>
<body>

    <div class="main-container">
        <!-- Left: Login Form -->
        <div class="container">
            <div class="logo">
                <img src="../assets/profile_3135715.png" alt="CedisPay">
            </div>

            <h2>Member Login</h2>

            <?php if ($error): ?>
                <div class="alert"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                <div class="form-group">
                    <input type="text" name="username" required placeholder=" ">
                    <label>Member ID (e.g. MEM000123)</label>
                </div>

                <div class="form-group">
                    <input type="password" name="password" required placeholder=" ">
                    <label>Password</label>
                </div>

                <a href="#" class="forgot-password">Forgot Password?</a>

                <button type="submit">Log In</button>
            </form>

            <div class="dont-have-account">
                Don't have an account? <a href="register.php">Apply to join</a>
            </div>
        </div>

        <!-- Right: Blue Panel -->
        <div class="col-2">
            <img src="../assets/cedispay-logo-white.png" alt="CedisPay">
            <!-- Replace with your white logo or nice illustration -->
        </div>
    </div>

</body>
</html>
