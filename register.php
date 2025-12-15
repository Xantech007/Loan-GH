<?php
// register.php - CedisPay Registration with Auto Login
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

    /* =========================
       VALIDATION
    ========================== */
    if (
        empty($full_name) ||
        empty($email) ||
        empty($phone) ||
        empty($password) ||
        empty($confirm)
    ) {
        $error = "All fields are required.";
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    }
    elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    }
    elseif (strlen($password) < 1) {
        $error = "Password must be at least 1 character.";
    }
    else {
        try {
            // Check if email or phone exists
            $check = $pdo->prepare(
                "SELECT id FROM users WHERE email = ? OR phone = ? LIMIT 1"
            );
            $check->execute([$email, $phone]);

            if ($check->rowCount() > 0) {
                $error = "This email or phone number is already registered.";
            } else {

                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert user
                $stmt = $pdo->prepare("
                    INSERT INTO users 
                        (full_name, email, phone, password, balance, created_at)
                    VALUES 
                        (?, ?, ?, ?, 0.00, NOW())
                ");

                $stmt->execute([
                    $full_name,
                    $email,
                    $phone,
                    $hashed_password
                ]);

                // ✅ Get newly created user ID
                $user_id = $pdo->lastInsertId();

                /* =========================
                   AUTO LOGIN
                ========================== */
                $_SESSION['user_id']   = $user_id;
                $_SESSION['email']     = $email;
                $_SESSION['full_name'] = $full_name;
                $_SESSION['logged_in'] = true;

                // Redirect to dashboard
                header("Location: members/dashboard.php");
                exit();
            }
        }
        catch (PDOException $e) {
            $error = "Something went wrong. Please try again later.";

            // DEBUG (enable only if needed)
            // $error = "DB Error: " . $e->getMessage();
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
    --gray: #ddd;
    --error: #e74c3c;
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
    background: linear-gradient(rgba(0,31,63,.92), rgba(0,51,102,.92)),
                url('assets/register-bg.jpg');
    background-size: cover;
    background-position: center;
    padding: 40px;
    color: white;
    text-align: center;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.container {
    padding: 50px 45px;
}
h2 {
    text-align: center;
    color: var(--primary);
    margin-bottom: 30px;
}
.form-group {
    position: relative;
    margin-bottom: 25px;
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
    transition: .3s;
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
    font-size: 16px;
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
        <h1>Join CedisPay</h1>
        <p>Fast, secure access to your account.</p>
    </div>

    <div class="container">
        <h2>Create Account</h2>

        <?php if ($error): ?>
            <div class="alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
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
