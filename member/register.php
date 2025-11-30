<?php
// register.php
session_start();
require '../config/db.php'; // Your existing DB connection

$success = $error = "";

// Simple CSRF token (optional but recommended)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = "Invalid request. Please try again.";
    } else {
        $full_name   = trim($_POST['full_name'] ?? '');
        $phone       = trim($_POST['phone'] ?? '');
        $id_number   = trim($_POST['id_number'] ?? ''); // Ghana Card / Voter ID / etc.
        $password    = $_POST['password'] ?? '';
        $confirm_pwd = $_POST['confirm_password'] ?? '';

        // Basic validation
        if (empty($full_name) || empty($phone) || empty($id_number) || empty($password)) {
            $error = "All fields are required.";
        } elseif ($password !== $confirm_pwd) {
            $error = "Passwords do not match.";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters.";
        } else {
            // Check if phone or ID already exists
            $check = $conn->prepare("SELECT member_id FROM members WHERE phone = ? OR id_number = ?");
            $check->bind_param("ss", $phone, $id_number);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                $error = "A member with this phone number or ID already exists.";
            } else {
                // Insert minimal member record
                $stmt = $conn->prepare("INSERT INTO members 
                    (full_name, phone, id_number, date_registered) 
                    VALUES (?, ?, ?, NOW())");
                $stmt->bind_param("sss", $full_name, $phone, $id_number);
                
                if ($stmt->execute()) {
                    $member_id = $conn->insert_id;

                    // Create secure login account
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $username = "MEM" . str_pad($member_id, 6, "0", STR_PAD_LEFT); // e.g. MEM000123

                    $user_stmt = $conn->prepare("INSERT INTO users (username, password, member_id) VALUES (?, ?, ?)");
                    $user_stmt->bind_param("ssi", $username, $hashed_password, $member_id);
                    $user_stmt->execute();

                    $success = "Registration successful! Your Member ID is: <strong>$username</strong><br>
                                You can now <a href='login.php'>log in</a>.";
                } else {
                    $error = "Something went wrong. Please try again.";
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
    <title>Register | CedisPay</title>
    <style>
        :root {
            --primary-color: #003366;
            --primary-color-light: #004488;
            --text-color: #333;
            --background-color: #f4f4f4;
            --white: #ffffff;
            --error-color: #ff3860;
            --success-color: #28a745;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: "Arial", sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            min-height: 100vh;
        }
        .container {
            background-color: var(--white);
            padding: 3rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            height: 680px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .login-form h2 {
            text-align: center;
            color: var(--primary-color);
            margin: 10px 0 40px 0;
            font-size: 28px;
        }
        .form-group {
            position: relative;
            margin-bottom: 1.8rem;
        }
        .form-group input {
            width: 100%;
            padding: 10px 0;
            font-size: 16px;
            color: var(--text-color);
            border: none;
            border-bottom: 1px solid #ddd;
            outline: none;
            background: transparent;
        }
        .form-group label {
            position: absolute;
            top: 10px;
            left: 0;
            font-size: 16px;
            color: #999;
            pointer-events: none;
            transition: 0.2s ease all;
        }
        .form-group input:focus ~ label,
        .form-group input:valid ~ label {
            top: -20px;
            font-size: 14px;
            color: var(--primary-color);
        }
        .form-group input:focus { border-bottom: 2px solid var(--primary-color); }
        button {
            width: 100%;
            padding: 12px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
        }
        button:hover { background-color: var(--primary-color-light); }
        .alert {
            padding: 12px;
            margin: 20px 0;
            border-radius: 5px;
            text-align: center;
            font-size: 15px;
        }
        .alert-error { background:#ffebee; color:var(--error-color); border:1px solid #ffcdd2; }
        .alert-success { background:#e8f5e9; color:var(--success-color); border:1px solid #c8e6c9; }
        .logo { text-align: center; margin-bottom: 20px; }
        .logo img { width: 120px; }
        .back-to-login {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }
        .back-to-login a { color: var(--primary-color); text-decoration: none; }
        .back-to-login a:hover { text-decoration: underline; }
        @media (max-width: 480px) {
            .container { padding: 2rem; height: auto; }
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="logo">
            <img src="../assets/profile_3135715.png" alt="CedisPay Logo">
        </div>

        <form class="login-form" method="POST">
            <h2>Join CedisPay</h2>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>

            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <div class="form-group">
                <input type="text" name="full_name" required>
                <label>Full Name</label>
            </div>

            <div class="form-group">
                <input type="text" name="phone" required placeholder="e.g. 0241234567">
                <label>Phone Number</label>
            </div>

            <div class="form-group">
                <input type="text" name="id_number" required placeholder="Ghana Card, Voter ID, etc.">
                <label>National ID Number</label>
            </div>

            <div class="form-group">
                <input type="password" name="password" required minlength="6">
                <label>Password</label>
            </div>

            <div class="form-group">
                <input type="password" name="confirm_password" required minlength="6">
                <label>Confirm Password</label>
            </div>

            <button type="submit">Create Account</button>
        </form>

        <div class="back-to-login">
            Already have an account? <a href="login.php">Log in here</a>
        </div>
    </div>

</body>
</html>
