<?php
// login.php - FINAL WORKING VERSION (everything in members table only)
session_start();
require '../config/db.php';

$error = "";

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = "Invalid request.";
    } else {
        $login_input = trim($_POST['login_input'] ?? '');
        $password    = $_POST['password'] ?? '';

        if (empty($login_input) || empty($password)) {
            $error = "Please enter your login details.";
        } else {
            // Login using: username (MEM000001), email, OR phone
            $sql = "SELECT member_id, username, full_name, password 
                    FROM members 
                    WHERE username = ? 
                       OR email = ? 
                       OR phone = ? 
                    LIMIT 1";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $login_input, $login_input, $login_input);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();

                if (password_verify($password, $user['password'])) {
                    // Success!
                    session_regenerate_id(true);
                    $_SESSION['user_id']    = $user['member_id'];
                    $_SESSION['username']   = $user['username'];
                    $_SESSION['full_name']  = $user['full_name'] ?? 'Member';
                    $_SESSION['logged_in']  = true;

                    header("Location: dashboard.php");
                    exit();
                }
            }
            $error = "Invalid Member ID, email, phone, or password.";
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
        :root{--primary-color:#003366;--primary-color-light:#004488;--text-color:#333;--background-color:#f4f4f4;--white:#fff;--error-color:#ff3860;}
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
        .form-group input{width:100%;padding:20px 0 8px;font-size:16px;border:none;border-bottom:1px solid #ddd;outline:none;background:transparent;}
        .form-group label{position:absolute;top:20px;left:0;color:#999;pointer-events:none;transition:.3s;font-size:16px;}
        .form-group input:focus~label,.form-group input:not(:placeholder-shown)~label{top:-12px;font-size:13px;color:var(--primary-color);font-weight:500;}
        .form-group input:focus{border-bottom:2px solid var(--primary-color);}
        button{width:100%;padding:14px;background:var(--primary-color);color:white;border:none;border-radius:6px;font-size:16px;cursor:pointer;margin-top:10px;}
        button:hover{background:var(--primary-color-light);}
        .alert{padding:12px;margin:20px 0;border-radius:6px;background:#ffebee;color:var(--error-color);border:1px solid #ffcdd2;text-align:center;}
        .forgot-password{display:block;text-align:right;margin:15px 0;color:var(--primary-color);font-size:14px;text-decoration:none;}
        .dont-have{text-align:center;margin-top:30px;font-size:15px;}
        .dont-have a{color:var(--primary-color);font-weight:600;text-decoration:none;}
        @media(max-width:768px){.main-container{grid-template-columns:1fr;}.col-2{display:none;}}
    </style>
</head>
<body>
<div class="main-container">
    <div class="container">
        <div class="logo"><img src="../assets/profile_3135715.png" alt="CedisPay"></div>
        <h2>Member Login</h2>

        <?php if($error): ?>
            <div class="alert"><?=htmlspecialchars($error)?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">

            <div class="form-group">
                <input type="text" name="login_input" required placeholder=" ">
                <label>Member ID, Email or Phone</label>
            </div>

            <div class="form-group">
                <input type="password" name="password" required placeholder=" ">
                <label>Password</label>
            </div>

            <a href="#" class="forgot-password">Forgot Password?</a>
            <button type="submit">Log In</button>
        </form>

        <div class="dont-have">
            Don't have an account? <a href="register.php">Apply to join</a>
        </div>
    </div>

    <div class="col-2">
        <img src="../assets/cedispay-logo-white.png" alt="CedisPay">
    </div>
</div>
</body>
</html>
