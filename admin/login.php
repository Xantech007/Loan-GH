<?php
// admin/login.php - Updated Admin Login with Proper Session Variables

session_start();
require_once '../config/db.php'; // MySQLi connection ($conn)

// Prevent login if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        try {
            // Query the admin table
            $stmt = $conn->prepare("SELECT id, username, full_name, password FROM admin WHERE username = ? LIMIT 1");
            if (!$stmt) {
                $error = "Database error. Please try again later.";
            } else {
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $result = $stmt->get_result();
                $admin = $result->fetch_assoc();
                $stmt->close();

                if ($admin) {
                    // IMPORTANT: In production, use password_verify() with hashed passwords!
                    // This example assumes plain text (as in your original) - change to hashed ASAP
                    if ($password === $admin['password']) {
                        // Regenerate session ID for security
                        session_regenerate_id(true);

                        // Set consistent session variables used across admin pages
                        $_SESSION['admin_id']       = $admin['id'];
                        $_SESSION['username']       = $admin['username'];
                        $_SESSION['full_name']      = $admin['full_name'] ?? $admin['username'];
                        $_SESSION['role']           = 'admin';
                        $_SESSION['admin_logged_in']= true;

                        // Redirect to admin dashboard
                        header('Location: dashboard.php');
                        exit();
                    } else {
                        $error = "Invalid username or password.";
                    }
                } else {
                    $error = "Invalid username or password.";
                }
            }
        } catch (Exception $e) {
            $error = "Login error. Please try again later.";
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
            color: #333;
        }
        .login-container {
            background: #fff;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            max-width: 420px;
            width: 100%;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header i {
            font-size: 3.5rem;
            color: #001f3f;
            margin-bottom: 15px;
        }
        .login-header h3 {
            color: #001f3f;
            font-weight: 700;
        }
        .form-control {
            padding: 12px 16px;
            border-radius: 10px;
        }
        .form-control:focus {
            border-color: #001f3f;
            box-shadow: 0 0 0 4px rgba(0, 31, 63, 0.1);
        }
        .btn-primary {
            background: #001f3f;
            border: none;
            padding: 14px;
            border-radius: 10px;
            font-weight: 600;
        }
        .btn-primary:hover {
            background: #003366;
            transform: translateY(-2px);
        }
        .alert {
            border-radius: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <i class="fas fa-user-shield"></i>
            <h3>Admin Login</h3>
            <p class="text-muted">Access the CedisPay administration panel</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" name="username" id="username" class="form-control" placeholder="Enter your username" value="<?= htmlspecialchars($username ?? '') ?>" required autofocus>
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">
                <i class="fas fa-sign-in-alt"></i> Login Securely
            </button>
        </form>

        <div class="text-center mt-4">
            <small class="text-muted">
                &copy; <?= date('Y') ?> CedisPay • Admin Portal
            </small>
        </div>
    </div>
</body>
</html>
