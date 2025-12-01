<?php
// register.php - Final working version
session_start();
require '../config/db.php';

$success = $error = "";

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = "Invalid request.";
    } else {
        $full_name = trim($_POST['full_name'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $phone     = trim($_POST['phone'] ?? '');
        $password  = $_POST['password'] ?? '';
        $confirm   = $_POST['confirm_password'] ?? '';

        if (empty($full_name) || empty($email) || empty($phone) || empty($password) || empty($confirm)) {
            $error = "All fields are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email address.";
        } elseif ($password !== $confirm) {
            $error = "Passwords do not match.";
        } elseif (strlen($password) < 8) {
            $error = "Password must be at least 8 characters.";
        } else {
            // Check if email or phone already exists
            $check = $conn->prepare("SELECT member_id FROM members WHERE email = ? OR phone = ?");
            $check->bind_param("ss", $email, $phone);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                $error = "Email or phone already registered.";
            } else {
                // 1. Insert into members → auto-generates member_id (AUTO_INCREMENT)
                $stmt = $conn->prepare("INSERT INTO members (full_name, email, phone, date_registered) VALUES (?, ?, ?, NOW())");
                $stmt->bind_param("sss", $full_name, $email, $phone);
                $stmt->execute();

                $member_id = $conn->insert_id;                    // ← this is the auto-generated ID
                $username  = "MEM" . str_pad($member_id, 6, "0", STR_PAD_LEFT);  // ← MEM000001, MEM000002...

                // 2. Save password in users table
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $user_stmt = $conn->prepare("INSERT INTO users (username, password, member_id) VALUES (?, ?, ?)");
                $user_stmt->bind_param("ssi", $username, $hash, $member_id);
                $user_stmt->execute();

                $success = "Account created successfully!<br><strong>Your Member ID: $username</strong><br>You can now <a href='login.php'>log in</a>.";
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
    <title>Register • CedisPay</title>
    <style>
        :root{--primary-color:#003366;--primary-color-light:#004488;--text-color:#333;--background-color:#f4f4f4;--white:#fff;--error-color:#ff3860;--success-color:#28a745;}
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:Arial,sans-serif;background:var(--background-color);color:var(--text-color);min-height:100vh;display:flex;justify-content:center;align-items:center;}
        .main-container{display:grid;grid-template-columns:1fr 1fr;width:90%;max-width:1000px;box-shadow:0 10px 30px rgba(0,0,0,.1);border-radius:10px;overflow:hidden;}
        .col-2{background:var(--primary-color);display:flex;align-items:center;justify-content:center;}
        .col-2 img{max-width:70%;opacity:.9;}
        .container{background:var(--white);padding:3rem 2.5rem;}
        .logo{text-align:center;margin-bottom:20px;}
        .logo img{width:120px;}
        h2{text-align:center;color:var(--primary-color);margin:10px 0 40px;font-size:28px;}
        .form-group{position:relative;margin-bottom:2rem;}
        .form-group input{width:100%;padding:12px 0 8px;border:none;border-bottom:1px solid #ddd;outline:none;background:transparent;font-size:16px;}
        .form-group label{position:absolute;top:12px;left:0;color:#999;pointer-events:none;transition:.3s;font-size:16px;}
        .form-group input:focus~label,.form-group input:valid~label{top:-12px;font-size:13px;color:var(--primary-color);font-weight:500;}
        .form-group input:focus{border-bottom:2px solid var(--primary-color);}
        button{width:100%;padding:14px;background:var(--primary-color);color:white;border:none;border-radius:6px;font-size:16px;cursor:pointer;margin-top:10px;}
        button:hover{background:var(--primary-color-light);}
        .alert{padding:12px;margin:20px 0;border-radius:6px;text-align:center;}
        .alert-error{background:#ffebee;color:var(--error-color);border:1px solid #ffcdd2;}
        .alert-success{background:#e8f5e9;color:var(--success-color);border:1px solid #c8e6c9;}
        .back-to-login{text-align:center;margin-top:25px;}
        .back-to-login a{color:var(--primary-color);font-weight:600;text-decoration:none;}
        @media(max-width:768px){.main-container{grid-template-columns:1fr;}.col-2{display:none;}}
    </style>
</style>
</head>
<body>
<div class="main-container">
    <div class="container">
        <div class="logo"><img src="../assets/profile_3135715.png" alt="CedisPay"></div>
        <h2>Join CedisPay</h2>
        <?php if($error): ?><div class="alert alert-error"><?=htmlspecialchars($error)?></div><?php endif; ?>
        <?php if($success): ?><div class="alert alert-success"><?=$success?></div><?php endif; ?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
            <div class="form-group"><input type="text" name="full_name" required><label>Full Name</label></div>
            <div class="form-group"><input type="email" name="email" required><label>Email Address</label></div>
            <div class="form-group"><input type="text" name="phone" required><label>Phone Number</label></div>
            <div class="form-group"><input type="password" name="password" required minlength="8"><label>Create Password</label></div>
            <div class="form-group"><input type="password" name="confirm_password" required minlength="8"><label>Confirm Password</label></div>
            <button type="submit">Create Account</button>
        </form>
        <div class="back-to-login">Already have an account? <a href="login.php">Log in</a></div>
    </div>
    <div class="col-2"><img src="../assets/cedispay-logo-white.png" alt="CedisPay"></div>
</div>
</body>
</html>
