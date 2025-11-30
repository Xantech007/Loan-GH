<?php
// register.php - CedisPay Member Registration
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
        $full_name   = trim($_POST['full_name'] ?? '');
        $phone       = trim($_POST['phone'] ?? '');
        $id_number   = trim($_POST['id_number'] ?? '');
        $password    = $_POST['password'] ?? '';
        $confirm     = $_POST['confirm_password'] ?? '';

        if (empty($full_name) || empty($phone) || empty($id_number) || empty($password)) {
            $error = "All fields are required.";
        } elseif ($password !== $confirm) {
            $error = "Passwords do not match.";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters.";
        } else {
            // Check duplicate
            $check = $conn->prepare("SELECT member_id FROM members WHERE phone = ? OR id_number = ?");
            $check->bind_param("ss", $phone, $id_number);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                $error = "Phone or ID already registered.";
            } else {
                $stmt = $conn->prepare("INSERT INTO members (full_name, phone, id_number, date_registered) VALUES (?, ?, ?, NOW())");
                $stmt->bind_param("sss", $full_name, $phone, $id_number);
                
                if ($stmt->execute()) {
                    $member_id = $conn->insert_id;
                    $username = "MEM" . str_pad($member_id, 6, "0", STR_PAD_LEFT);
                    $hash = password_hash($password, PASSWORD_DEFAULT);

                    $user_stmt = $conn->prepare("INSERT INTO users (username, password, member_id) VALUES (?, ?, ?)");
                    $user_stmt->bind_param("ssi", $username, $hash, $member_id);
                    $user_stmt->execute();

                    $success = "Account created successfully!<br><strong>Your Member ID: $username</strong><br>You can now log in.";
                } else {
                    $error = "Registration failed. Try again.";
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
    <title>Register • CedisPay</title>
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
        /* Floating label effect - FIXED */
        .form-group input:focus ~ label,
        .form-group input:valid ~ label {
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
            font-size: 15px;
        }
        .alert-error { background: #ffebee; color: var(--error-color); border: 1px solid #ffcdd2; }
        .alert-success { background: #e8f5e9; color: var(--success-color); border: 1px solid #c8e6c9; }
        .back-to-login {
            text-align: center;
            margin-top: 25px;
            font-size: 14px;
        }
        .back-to-login a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }
        .back-to-login a:hover { text-decoration: underline; }

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
        <!-- Left: Form -->
        <div class="container">
            <div class="logo">
                <img src="../assets/profile_3135715.png" alt="CedisPay">
            </div>

            <h2>Join CedisPay</h2>

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
                    <input type="text" name="phone" required>
                    <label>Phone Number (e.g. 0241234567)</label>
                </div>

                <div class="form-group">
                    <input type="text" name="id_number" required>
                    <label>National ID Number</label>
                </div>

                <div class="form-group">
                    <input type="password" name="password" required minlength="6">
                    <label>Create Password</label>
                </div>

                <div class="form-group">
                    <input type="password" name="confirm_password" required minlength="6">
                    <label>Confirm Password</label>
                </div>

                <button type="submit">Create Account</button>
            </form>

            <div class="back-to-login">
                Already have an account? <a href="login.php">Log in</a>
            </div>
        </div>

        <!-- Right: Blue Panel -->
        <div class="col-2">
            <img src="../assets/cedispay-logo-white.png" alt="CedisPay">
            <!-- Or use a nice Ghana/loans illustration -->
        </div>
    </div>

</body>
</html>
