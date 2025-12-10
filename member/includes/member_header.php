<?php
// ./includes/member_header.php
session_start();

// Security: Redirect if not logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit();
}

require '../config/db.php';
$member_id = (int)$_SESSION['member_id'];

// Fetch verification status and member name
$stmt = $conn->prepare("SELECT full_name, verified FROM members WHERE member_id = ?");
$stmt->bind_param("i", $member_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

$is_verified = $user_data && $user_data['verified'] == 1;
$full_name = $user_data['full_name'] ?? 'Member';
$display_id = "MEM" . str_pad($member_id, 6, "0", STR_PAD_LEFT);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : ''; ?>CedisPay Member Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap & Fonts -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="./member.css">
    
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">

    <script defer src="./app.js"></script>

    <style>
        :root {
            --sidebar-width: 250px;
            --primary-blue: #3b82f6;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
        }

        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--primary-blue);
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            transform: translateX(-100%);
            transition: transform 0.3s ease-in-out;
            padding-top: 20px;
            z-index: 1000;
            overflow-y: auto;
        }

        .sidebar.active {
            transform: translateX(0);
        }

        .sidebar .logo {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .logo h2 {
            margin: 0;
            font-size: 1.5rem;
            color: white;
        }

        .close-btn {
            background: none;
            border: none;
            color: white;
            font-size: 28px;
            cursor: pointer;
            padding: 0;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .menu {
            list-style: none;
            padding: 20px;
            margin: 0;
        }

        .menu p {
            font-size: 0.9rem;
            border-top: 1px solid rgba(255, 255, 255, 0.3);
            padding-top: 15px;
            margin: 20px 0 30px;
            opacity: 0.9;
        }

        .menu li {
            margin: 12px 0;
        }

        .menu li a {
            color: white;
            text-decoration: none;
            font-size: 1rem;
            display: flex;
            align-items: center;
            padding: 10px 12px;
            border-radius: 8px;
            transition: background 0.3s;
        }

        .menu li a:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .menu li a i {
            margin-right: 12px;
            width: 24px;
            text-align: center;
        }

        .action-required {
            background: #ef4444;
            color: white;
            font-size: 0.7rem;
            padding: 4px 8px;
            border-radius: 12px;
            margin-left: 10px;
            font-weight: bold;
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 999;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s;
        }

        .overlay.active {
            opacity: 1;
            pointer-events: all;
        }

        .open-btn {
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1001;
            background: white;
            color: var(--primary-blue);
            border: none;
            font-size: 26px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .main {
            min-height: 100vh;
            padding: 20px;
            transition: margin-left 0.3s ease;
            background: #f8f9fa;
        }

        /* Desktop View */
        @media (min-width: 768px) {
            .sidebar {
                transform: translateX(0) !important;
            }

            .open-btn,
            .close-btn,
            .overlay {
                display: none !important;
            }

            .main {
                margin-left: var(--sidebar-width);
                padding: 30px;
            }
        }

        /* Mobile adjustments */
        @media (max-width: 767px) {
            .main {
                padding-top: 70px;
                padding-left: 15px;
                padding-right: 15px;
            }
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="logo">
            <h2>CedisPay</h2>
            <button class="close-btn" onclick="toggleSidebar()">×</button>
        </div>

        <ul class="menu">
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>

            <!-- Show Verify Account if not verified -->
            <?php if (!$is_verified): ?>
                <li>
                    <a href="verify_account.php">
                        <i class="fas fa-shield-alt"></i> Verify Account
                        <span class="action-required">Required</span>
                    </a>
                </li>
            <?php endif; ?>

            <p>Apply for loans:</p>
            <li><a href="./short-term-loan.php"><i class="fas fa-hand-holding-usd"></i> Short Term Loan</a></li>
            <li><a href="./emergency-loan.php"><i class="fas fa-exclamation-triangle"></i> Emergency Loan</a></li>
            <li><a href="./long-term-loan.php"><i class="fas fa-piggy-bank"></i> Long Term Loan</a></li>
            <li><a href="./settings.php"><i class="fas fa-cog"></i> Settings</a></li>
            <li><a href="./logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Overlay (mobile only) -->
    <div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

    <!-- Hamburger Button (mobile only) -->
    <button class="open-btn" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Main Content Area Starts -->
    <div class="main">
