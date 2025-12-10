<!-- ./includes/member_header.php -->
<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['member_id'])) {
    header('Location: login.php');
    exit();
}
require '../config/db.php';
$member_id = (int)$_SESSION['member_id'];

$stmt = $conn->prepare("SELECT verified, full_name FROM members WHERE member_id = ?");
$stmt->bind_param("i", $member_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$is_verified = $row && $row['verified'] == 1;
$full_name = $row['full_name'] ?? 'Member';
$display_id = "MEM" . str_pad($member_id, 6, "0", STR_PAD_LEFT);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : ''; ?>CedisPay Member Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="./member.css">
    <script defer src="./app.js"></script>
    <style>
        :root {
            --primary: #003366;
            --sidebar-width: 250px;
        }
        body { margin: 0; font-family: 'Segoe UI', sans-serif; background: #f8f9fa; }
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--primary);
            color: white;
            position: fixed;
            left: 0;
            top: 0;
            transition: transform 0.3s ease-in-out;
            z-index: 1000;
            padding-top: 20px;
        }
        .sidebar.hidden { transform: translateX(-100%); }
        .logo { display: flex; justify-content: space-between; align-items: center; padding: 15px 20px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .logo h2 { margin: 0; font-size: 1.5rem; }
        .close-btn { background:none; border:none; color:white; font-size:28px; cursor:pointer; }
        .menu { list-style:none; padding:20px 0; margin:0; }
        .menu li a {
            color:white; text-decoration:none; padding:14px 25px; display:flex; align-items:center;
            transition: background 0.3s;
        }
        .menu li a:hover { background:rgba(255,255,255,0.15); }
        .menu i { width:24px; margin-right:12px; text-align:center; }
        .action-required {
            background:#dc3545; color:white; font-size:0.7rem; padding:3px 8px;
            border-radius:12px; margin-left:8px; font-weight:bold;
        }
        .overlay {
            position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999;
            opacity:0; pointer-events:none; transition:opacity 0.3s;
        }
        .overlay.active { opacity:1; pointer-events:all; }
        .open-btn {
            position:fixed; top:15px; left:15px; z-index:1001;
            background:none; border:none; font-size:28px; color:var(--primary);
            cursor:pointer;
        }
        .main {
            min-height: 100vh;
            padding: 20px;
            background: #f8f9fa;
            transition: margin-left 0.3s ease;
        }
        @media (min-width: 768px) {
            .sidebar { transform: translateX(0) !important; }
            .open-btn, .close-btn, .overlay { display: none !important; }
            .main { margin-left: var(--sidebar-width); padding: 30px; }
        }
        @media (max-width: 767px) {
            .main { margin-left: 0 !important; padding-top: 70px; }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar <?php echo $is_verified ? '' : 'has-alert'; ?>" id="sidebar">
        <div class="logo">
            <h2>CedisPay</h2>
            <button class="close-btn" onclick="toggleSidebar()">×</button>
        </div>
        <ul class="menu">
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <?php if (!$is_verified): ?>
            <li>
                <a href="verify_account.php">
                    <i class="fas fa-shield-alt"></i> Verify Account
                    <span class="action-required">Required</span>
                </a>
            </li>
            <?php endif; ?>
            <li><a href="short-term-loan.php"><i class="fas fa-hand-holding-usd"></i> Short Term Loan</a></li>
            <li><a href="emergency-loan.php"><i class="fas fa-exclamation-triangle"></i> Emergency Loan</a></li>
            <li><a href="long-term-loan.php"><i class="fas fa-piggy-bank"></i> Long Term Loan</a></li>
            <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Overlay & Hamburger -->
    <div class="overlay" id="overlay" onclick="toggleSidebar()"></div>
    <button class="open-btn" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Main Content Starts Here -->
    <div class="main">
