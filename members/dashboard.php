<?php
// members/dashboard.php

// 1. Show errors while developing
require_once '../config/db.php';

// 2. Start session
session_start();

// 3. Check if user is logged in
if (!!! THIS WAS MISSING !!!)
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// 4. Include DB connection (correct relative path from members/ folder)
require_once '../config/db.php';

$user_id = $_SESSION['user_id'];

try {
    // === Fetch User ===
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        session_destroy();
        header("Location: ../login.php");
        exit();
    }

    // === Fetch Recent Loans ===
    $stmt = $pdo->prepare("SELECT * FROM loans WHERE user_id = ? ORDER BY applied_at DESC LIMIT 5");
    $stmt->execute([$user_id]);
    $loans = $stmt->fetchAll();

    // === Fetch Recent Payments ===
    $stmt = $pdo->prepare("
        SELECT p.*, l.amount AS loan_amount 
        FROM payments p 
        JOIN loans l ON p.loan_id = l.id 
        WHERE l.user_id = ? 
        ORDER BY p.paid_at DESC LIMIT 5
    ");
    $stmt->execute([$user_id]);
    $payments = $stmt->fetchAll();

    // === Fetch Unread Notifications ===
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT 10");
    $stmt->execute([$user_id]);
    $notifications = $stmt->fetchAll();

    // === Calculate Outstanding Balance ===
    $stmt = $pdo->prepare("SELECT SUM(amount * (1 + interest_rate/100)) as total FROM loans WHERE user_id = ? AND status IN ('approved', 'active')");
    $stmt->execute([$user_id]);
    $outstanding = $stmt->fetch()['total'] ?? 0;

} catch (Exception $e) {
    die("Error loading dashboard: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CedisPay</title>
    <style>
        :root {
            --primary: #001f3f;
            --light: #f8f9fa;
        }
        body { font-family: 'Segoe UI', sans-serif; margin:0; background:#f4f6f9; }
        .container { display:flex; min-height:100vh; }
        .sidebar { width:260px; background:var(--primary); color:white; padding:20px 0; }
        .sidebar h2 { padding:0 20px; margin-bottom:30px; }
        .sidebar a { display:block; color:white; padding:12px 20px; text-decoration:none; }
        .sidebar a:hover, .sidebar a.active { background:rgba(255,255,255,0.1); }
        .main { flex:1; padding:20px; }
        .navbar { background:var(--primary); color:white; padding:15px 30px; display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; border-radius:8px; }
        .card { background:white; border-radius:8px; padding:25px; margin-bottom:25px; box-shadow:0 2px 10px rgba(0,0,0,0.1); }
        .card h2 { color:var(--primary); margin-top:0; border-bottom:2px solid var(--primary); padding-bottom:10px; }
        table { width:100%; border-collapse:collapse; margin-top:15px; }
        table th, table td { padding:12px; text-align:left; border-bottom:1px solid #eee; }
        table th { background:var(--primary); color:white; }
        .btn { background:var(--primary); color:white; padding:8px 16px; border-radius:4px; text-decoration:none; font-size:14px; }
        .btn:hover { background:#003366; }
        .notification { background:#e3a86ff; color:white; padding:15px; border-radius:6px; margin-bottom:10px; }
        .stats { display:grid; grid-template-columns:repeat(auto-fit, minmax(200px,1fr)); gap:20px; margin-bottom:30px; }
        .stat-card { background:white; padding:20px; border-radius:8px; text-align:center; box-shadow:0 2px 8px rgba(0,0,0,0.1); }
        .stat-card h3 { margin:0; font-size:2em; color:var(--primary); }
    </style>
</head>
<body>

<div class="container">
    <?php include 'includes/sidebar.php'; ?>

    <div class="main">
        <?php include 'includes/navbar.php'; ?>

        <!-- Quick Stats -->
        <div class="stats">
            <div class="stat-card">
                <h3>GH₵ <?php echo number_format($outstanding, 2); ?></h3>
                <p>Outstanding Balance</p>
            </div>
            <div class="stat-card">
                <h3><?php echo count(array_filter($loans, fn($l) => $l['status'] == 'active')); ?></h3>
                <p>Active Loans</p>
            </div>
            <div class="stat-card">
                <h3><?php echo count($payments); ?></h3>
                <p>Payments Made</p>
            </div>
        </div>

        <div class="card">
            <h2>Welcome back, <?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?>!</h2>
            <p>Manage your loans and repayments easily from your CedisPay dashboard.</p>
        </div>

        <?php if (!empty($notifications)): ?>
        <div class="card">
            <h2>Notifications</h2>
            <?php foreach ($notifications as $n): ?>
                <div class="notification">
                    <?php echo htmlspecialchars($n['message']); ?>
                    <small style="float:right; opacity:0.9;"><?php echo date('M j, Y g:i A', strtotime($n['created_at'])); ?></small>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="card">
            <h2>Recent Loans</h2>
            <?php if (empty($loans)): ?>
                <p>No loan applications yet. <a href="apply_loan.php" class="btn">Apply Now</a></p>
            <?php else: ?>
                <table>
                    <tr>
                        <th>Loan ID</th>
                        <th>Amount</th>
                        <th>Term</th>
                        <th>Status</th>
                        <th>Applied</th>
                        <th>Action</th>
                    </tr>
                    <?php foreach ($loans as $loan): ?>
                    <tr>
                        <td>#<?php echo $loan['id']; ?></td>
                        <td>GH₵ <?php echo number_format($loan['amount'], 2); ?></td>
                        <td><?php echo $loan['term']; ?> months</td>
                        <td><strong><?php echo ucfirst($loan['status']); ?></strong></td>
                        <td><?php echo date('M j, Y', strtotime($loan['applied_at'])); ?></td>
                        <td><a href="loan_details.php?id=<?php echo $loan['id']; ?>" class="btn">View</a></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>

        <?php include 'includes/footer.php'; ?>
    </div>
</div>

</body>
</html>
