<?php
// logout.php - Secure Logout for CedisPay (Member & Admin)

session_start();

// Destroy all session data
$_SESSION = array();

// If a session cookie exists, delete it
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session
session_destroy();

// Redirect based on role or default to home/login
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    // If it was an admin (in case session not fully cleared)
    header("Location: admin/login.php");
} else {
    // Regular member
    header("Location: login.php");
}

exit();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging Out • CedisPay</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #001f3f, #003366);
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
            margin: 0;
        }
        .logout-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 50px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 90%;
        }
        .spinner {
            width: 60px;
            height: 60px;
            border: 6px solid rgba(255,255,255,0.3);
            border-top: 6px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 30px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        h2 {
            font-size: 2rem;
            margin-bottom: 15px;
        }
        p {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 30px;
        }
        .btn {
            background: white;
            color: #001f3f;
            padding: 14px 30px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            transition: 0.3s;
        }
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <div class="spinner"></div>
        <h2>Logging you out securely...</h2>
        <p>Please wait while we end your session.</p>
        <p id="redirect-text">You will be redirected shortly.</p>
    </div
