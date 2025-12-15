<?php
// admin/login.php - Compatible with your current db.php on InfinityFree
session_start();
require_once '../config/db.php'; // This provides both $conn (MySQLi) and $pdo (PDO, if possible)

// Prevent access if already logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        $admin = null;

        // Try PDO first (preferred for prepared statements)
        if (isset($pdo) && $pdo instanceof PDO) {
            try {
                $stmt = $pdo->prepare("
                    SELECT id, username, full_name, password
                    FROM admin
                    WHERE username = ?
                    LIMIT 1
                ");
                $stmt->execute([$username]);
                $admin = $stmt->fetch();
            } catch (Exception $e) {
                // PDO failed (e.g., table issue or silent failure) — fall through to MySQLi
                $admin = null;
            }
        }

        // If PDO failed or not available, fall back to MySQLi
        if (!$admin && isset($conn) && $conn instanceof mysqli) {
            $username_escaped = mysqli_real_escape_string($conn, $username);
            $query = "SELECT id, username, full_name, password FROM admin WHERE username = '$username_escaped' LIMIT 1";
            $result = mysqli_query($conn, $query);

            if ($result && mysqli_num_rows($result) > 0) {
                $admin = mysqli_fetch_assoc($result);
            }
        }

        // Now check credentials
        if ($admin) {
            // WARNING: Switch to password_verify() when you hash passwords!
            if ($password === $admin['password']) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $admin['id'];
                $_SESSION['username'] = $admin['username'];
                $_SESSION['full_name'] = $admin['full_name'] ?? $admin['username'];
                $_SESSION['role'] = 'admin';
                $_SESSION['admin_logged_in'] = true;
                header('Location: dashboard.php');
                exit();
            } else {
                $error = "Invalid username or password.";
            }
        } else {
            $error = "Invalid username or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login • CedisPay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #001f3f, #003366);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #fff;
        }
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 50px;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
            max-width: 450px;
            width: 100%;
            text-align: center;
            color: #001f3f;
        }
        .login-header i {
            font-size: 4rem;
            color: #001f3f;
            margin-bottom: 20px;
        }
        .login-header h3 {
            font-weight: 700;
            margin-bottom: 10px;
        }
        .form-control {
            padding: 14px 18px;
            border-radius: 12px;
            border: 2px solid #ddd;
            margin-bottom: 20px;
        }
        .form-control:focus {
            border-color: #001f3f;
            box-shadow: 0 0 0 4px rgba(0, 31, 63, 0.15);
        }
        .btn-primary {
            background: #001f3f;
            border: none;
            padding: 16px;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            width: 100%;
        }
        .btn-primary:hover {
            background: #003366;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 31, 63, 0.3);
        }
        .alert {
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 25px;
        }
        footer {
            margin-top: 30px;
            font-size: 0.9rem;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <i class="fas fa-user-shield"></i>
            <h3>CedisPay Admin</h3>
            <p>Secure Access to Administration Panel</p>
        </div>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        <form method="POST">
            <div>
                <input
                    type="text"
                    name="username"
                    class="form-control"
                    placeholder="Username"
                    value="<?= htmlspecialchars($username ?? '') ?>"
                    required
                    autofocus>
            </div>
            <div>
                <input
                    type="password"
                    name="password"
                    class="form-control"
                    placeholder="Password"
                    required>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-sign-in-alt"></i> Login Securely
            </button>
        </form>
        <footer>
            &copy; <?= date('Y') ?> CedisPay • All Rights Reserved
        </footer>
    </div>
</body>
</html>
