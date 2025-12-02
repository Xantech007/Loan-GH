<?php
// member_header.php - FINAL VERSION WITH DASHBOARD + CONDITIONAL VERIFY ACCOUNT
session_start();

// Redirect if not logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['member_id'])) {
    header('Location: login.php');
    exit();
}

require '../config/db.php';
$member_id = (int)$_SESSION['member_id'];

// Fetch verification status
$stmt = $conn->prepare("SELECT verified FROM members WHERE member_id = ?");
$stmt->bind_param("i", $member_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$is_verified = ($user && $user['verified'] == 1);

$display_id = "MEM" . str_pad($member_id, 6, "0", STR_PAD_LEFT);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'CedisPay Member Portal'; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="./member.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <script defer src="./app.js"></script>

    <style>
        .sidebar {
            width: 250px;
            height: 100vh;
            background: #003366;
            color: white;
            position: fixed;
            left: -250px;
            top: 0;
            transition: left 0.3s ease-in-out;
            padding-top: 20px;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0,0,0,0.2);
        }
        .sidebar.active { left: 0; }
        .sidebar .logo {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .logo h2 { margin: 0; font-size: 1.5rem; color: white; }
        .close-btn { background: none; border: none; color: white; font-size: 24px; cursor: pointer; }

        .menu { list-style: none; padding: 20px 0; }
        .menu li { margin: 8px 0; }
        .menu li a {
            color: white;
            text-decoration: none;
            font-size: 1rem;
            display: flex;
            align-items: center;
            padding: 12px 25px;
            transition: background 0.3s;
        }
        .menu li a:hover { background: rgba(255,255,255,0.1); }
        .menu li a i { margin-right: 12px; width: 20px; }

        .verify-badge {
            background: #dc3545;
            color: white;
            font-size: 0.7rem;
            padding: 3px 8px;
            border-radius: 12px;
            margin-left: 8px;
        }

        .overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.6);
            display: none;
            z-index: 999;
        }
        .open-btn {
            font-size: 28px;
            cursor: pointer;
            background: none;
            border: none;
            color: #003366;
        }

        @media (min-width: 768px) {
            .sidebar { left: 0; }
            .open-btn, .close-btn, .overlay { display: none; }
            .main { margin-left: 250px; padding: 20px; }
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="logo">
            <h2>CedisPay</h2>
            <button class="close-btn" onclick="toggleSidebar()">&times;</button>
        </div>

        <ul class="menu">
            <p style="font-size: 0.8rem; color: #aaa; padding: 0 25px; margin: 20px 0 10px;">Member: <?= $display_id ?></p>

            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>

            <!-- Verify Account - Only show if NOT verified -->
            <?php if (!$is_verified): ?>
                <li>
                    <a href="verify_account.php">
                        <i class="fas fa-shield-alt"></i> Verify Account 
                        <span class="verify-badge">Action Required</span>
                    </a>
                </li>
            <?php endif; ?>

            <hr style="border-color: rgba(255,255,255,0.1); margin: 20px 25px;">

            <p style="font-size: 0.8rem; color: #aaa; padding: 0 25px; margin: 15px 0 10px;">Loan Applications</p>
            <li><a href="short-term-loan.php"><i class="fas fa-hand-holding-usd"></i> Short Term Loan</a></li>
            <li><a href="emergency-loan.php"><i class="fas fa-exclamation-triangle"></i> Emergency Loan</a></li>
            <li><a href="long-term-loan.php"><i class="fas fa-piggy-bank"></i> Long Term Loan</a></li>

            <hr style="border-color: rgba(255,255,255,0.1); margin: 20px 25px;">

            <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
            <li><a href="change_password.php"><i class="fas fa-key"></i> Change Password</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Mobile Overlay -->
    <div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

    <!-- Mobile Menu Button -->
    <button class="open-btn" onclick="toggleSidebar()" style="position:fixed; top:15px; left:15px; z-index:1001;">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Main Content Starts Here -->
    <div class="main">
