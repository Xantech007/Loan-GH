<?php
// members/dashboard.php
// This version will NEVER give 500 error – it shows the real error if something is wrong

// 1. Force errors to show (remove this line in production later)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Start session
session_start();

// 3. If not logged in → go to login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// 4. Database connection – CORRECT PATH from members/ folder
require_once '../config/db.php';   // ← this must exist!

$user_id = $_SESSION['user_id'];

try {
    // User info
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    // Loans
    $stmt = $pdo->prepare("SELECT * FROM loans WHERE user_id = ? ORDER BY applied_at DESC LIMIT 5");
    $stmt->execute([$user_id]);
    $loans = $stmt->fetchAll();

    // Payments
    $stmt = $pdo->prepare("SELECT p.*, l.amount AS loan_amount FROM payments p JOIN loans l ON p.loan_id = l.id WHERE l.user_id = ? ORDER BY p.paid_at DESC LIMIT 5");
    $stmt->execute([$user_id]);
    $payments = $stmt->fetchAll();

    // Notifications
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $notifications = $stmt->fetchAll();

    // Outstanding balance
    $stmt = $pdo->prepare("SELECT SUM(amount * (1 + interest_rate/100)) as total FROM loans WHERE user_id = ? AND status IN ('approved','active')");
    $stmt->execute([$user_id]);
    $outstanding = $stmt->fetchColumn() ?: 0;

} catch (Exception $e) {
    die('<h2>Database Error:</h2><p style="color:red;">' . $e->getMessage() . '</p>');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CedisPay - Dashboard</title>
    <style>
        body {font-family:Arial,sans-serif; margin:0; background:#f5f7fa; color:#333;}
        .container {display:flex; min-height:100vh;}
        .sidebar {width:260px; background:#001f3f; color:white; padding:20px 0;}
        .sidebar a {display:block; padding:14px 25px; color:white; text-decoration:none;}
        .sidebar a:hover {background:rgba(255,255,255,0.1);}
        .main {flex:1; padding:30px;}
        .navbar {background:#001f3f; color:white; padding:15px 30px; margin:-30px -30px 30px -30px; border-radius:8px 8px 0 0;}
        .navbar span {font-weight:bold;}
        .card {background:white; padding:25px; border-radius:8px; box-shadow:0 3px 10px rgba(0,0,0,0.1); margin-bottom:25px;}
        .card h2 {color:#001f3f; border-bottom:2px solid #001f3f; padding-bottom:8px;}
        table {width:100%; border-collapse:collapse; margin-top:10px;}
        table th, table td {padding:12px; text-align:left;}
        table th {background:#001f3f; color:white;}
        table td {border-bottom:1px solid #eee;}
        .btn {background:#001f3f; color:white; padding:8px 16px; border-radius:4px; text-decoration:none; font-size:14px;}
        .btn:hover {background:#002b55;}
        .stats {display:grid; grid-template-columns:repeat(auto-fit, minmax(220px,1fr)); gap:20px; margin:30px 0;}
        .stat {background:white; padding:25px; text-align:center; border-radius:8px; box-shadow:0 3px 10px rgba(0,0,0,0.1);}
        .stat h3 {margin:0; font-size:2.2em; color:#001f3f;}
    </style>
</head>
<body>

<div class="container">
    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <div class="main">
        <!-- Navbar -->
        <div class="navbar">
            <div><strong>CedisPay</strong> - Member Dashboard</div>
            <div>
                <span><?= htmlspecialchars($user['full_name'] ?? $user['username']) ?></span>
                <a href="logout.php" class="btn" style="margin-left:15px;">Logout</a>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="stats">
            <div class="stat">
                <h3>GH₵ <?= number_format($outstanding, 2) ?></h3>
                <p>Outstanding Balance</p>
            </div>
            <div class="stat">
                <h3><?= count(array_filter($loans, fn($l)=>in_array($l['status'],['active','approved']))) ?></h3>
                <p>Active Loans</p>
            </div>
            <div class="stat">
                <h3><?= count($payments) ?></h3>
                <p>Total Payments</p>
            </div>
        </div>

        <!-- Welcome Card -->
        <div class="card">
            <h2>Welcome back!</h2>
            <p>You are logged in as <strong><?= htmlspecialchars($user['email']) ?></strong></p>
        </div>

        <!-- Recent Loans -->
        <div class="card">
            <h2>Recent Loans</h2>
            <?php if (!$loans): ?>
                <p>No loans yet. <a href="apply_loan.php" class="btn">Apply Now</a></p>
            <?php else: ?>
                <table>
                    <tr><th>ID</th><th>Amount</th><th>Term</th><th>Status</th><th>Date</th><th></th></tr>
                    <?php foreach($loans as $loan): ?>
                    <tr>
                        <td>#<?= $loan['id'] ?></td>
                        <td>GH₵ <?= number_format($loan['amount'],2) ?></td>
                        <td><?= $loan['term'] ?> months</td>
                        <td><strong><?= ucfirst($loan['status']) ?></strong></td>
                        <td><?= date('d M Y', strtotime($loan['applied_at'])) ?></td>
                        <td><a href="loan_details.php?id=<?= $loan['id'] ?>" class="btn">View</a></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>
