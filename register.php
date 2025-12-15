<?php
// register.php - CedisPay Registration (MEM username + Auto-login)
session_start();
require 'config/db.php';

$success = "";
$error   = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm_password'] ?? '';

    // =========================
    // VALIDATION
    // =========================
    if (empty($full_name) || empty($email) || empty($password) || empty($confirm)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 1) {
        $error = "Password must be at least 1 character.";
    } else {
        try {

            // Check if email already exists
            $check = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $check->execute([$email]);

            if ($check->rowCount() > 0) {
                $error = "This email is already registered.";
            } else {

                // =========================
                // GENERATE USERNAME (MEM000001)
                // =========================
                $getLastUser = $pdo->query("
                    SELECT username 
                    FROM users 
                    WHERE username LIKE 'MEM%' 
                    ORDER BY id DESC 
                    LIMIT 1
                ");

                $lastUser = $getLastUser->fetch(PDO::FETCH_ASSOC);

                if ($lastUser) {
                    $lastNumber = (int) substr($lastUser['username'], 3);
                    $newNumber  = $lastNumber + 1;
                } else {
                    $newNumber = 1;
                }

                $username = 'MEM' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);

                // Hash password
                $hash = password_hash($password, PASSWORD_DEFAULT);

                // =========================
                // INSERT USER
                // =========================
                $stmt = $pdo->prepare("
                    INSERT INTO users (
                        username,
                        full_name,
                        email,
                        phone,
                        password,
                        balance,
                        created_at
                    ) VALUES (
                        ?, ?, ?, ?, ?, 0.00, NOW()
                    )
                ");

                $stmt->execute([
                    $username,
                    $full_name,
                    $email,
                    $phone,
                    $hash
                ]);

                // =========================
                // AUTO LOGIN
                // =========================
                $_SESSION['user_id']   = $pdo->lastInsertId();
                $_SESSION['username']  = $username;
                $_SESSION['email']     = $email;
                $_SESSION['full_name'] = $full_name;
                $_SESSION['logged_in'] = true;

                // Redirect to dashboard
                header("Location: members/dashboard.php");
                exit();
            }
        } catch (PDOException $e) {
            $error = "Something went wrong. Please try again later.";
            // DEBUG ONLY:
            // $error = $e->getMessage();
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
        .container {
            padding: 50px 45px;
            background: var(--white);
        }
        h2 {
            text-align: center;
            color: var(--primary);
            margin: 10px 0 35px;
            font-size: 28px;
        }
        .form-group {
            position: relative;
            margin-bottom: 28px;
        }
        .form-group input {
            width: 100%;
            padding: 18px 0 8px;
            border: none;
            border-bottom: 2px solid var(--gray);
            outline: none;
        }
        .form-group label {
            position: absolute;
            top: 18px;
            left: 0;
            color: #999;
            transition: 0.3s;
        }
        .form-group input:focus ~ label,
        .form-group input:valid ~ label {
            top: -5px;
            font-size: 13px;
            color: var(--primary);
            font-weight: 600;
        }
        button {
            width: 100%;
            padding: 16px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 17px;
            cursor: pointer;
        }
        .alert-error {
            padding: 15px;
            margin-bottom: 20px;
            background: #fdf2f2;
            color: var(--error);
            border-radius: 10px;
            text-align: center;
        }
        @media (max-width: 768px) {
            .main-container { grid-template-columns: 1fr; }
            .col-left { display: none; }
        }
    </style>
</head>
<body>

<div class="main-container">

    <div class="col-left">
        <img src="assets/cedispay-logo-white.png" alt="CedisPay">
        <h1>Join CedisPay Today</h1>
        <p>Get instant access to fast, reliable loans.</p>
    </div>

    <div class="container">
        <h2>Create Your Account</h2>

        <?php if ($error): ?>
            <div class="alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
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
                <input type="password" name="password" required minlength="1">
                <label>Create Password</label>
            </div>
            <div class="form-group">
                <input type="password" name="confirm_password" required minlength="1">
                <label>Confirm Password</label>
            </div>
            <button type="submit">Create Account</button>
        </form>
    </div>
</div>

</body>
</html>
