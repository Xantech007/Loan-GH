<?php
// members/my_loans.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
require_once '../config/db.php';

$user_id = $_SESSION['user_id'];

// Fetch user
$stmt = $pdo->prepare("SELECT full_name, username FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Fetch all loans
$stmt = $pdo->prepare("SELECT * FROM loans WHERE user_id = ? ORDER BY applied_at DESC");
$stmt->execute([$user_id]);
$loans = $stmt->fetchAll();

// Stats
$total_loans = count($loans);
$pending = count(array_filter($loans, fn($l) => $l['status'] === 'pending'));
$approved = count(array_filter($loans, fn($l) => in_array($l['status'], ['approved', 'active'])));
$rejected = count(array_filter($loans, fn($l) => $l['status'] === 'rejected'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Loans - CedisPay</title>
    <style>
        body {font-family:Arial,sans-serif; margin:0; background:#f5f7fa; color:#333;}
        .container {display:flex; min-height:100vh;}
        .sidebar {width:260px; background:#001f3f; color:white; padding:20px 0;}
        .sidebar a {display:block; padding:14px 25px; color:white; text-decoration:none;}
        .sidebar a:hover {background:rgba(255,255,255,0.1);}
        .main {flex:1; padding:30px;}
        .navbar {background:#001f3f; color:white; padding:15px 30px; margin:-30px -30px 30px -30px; border-radius:8px 8px 0 0; display:flex; justify-content:space-between; align-items:center;}
        .card {background:white; padding:25px; border-radius:8px; box-shadow:0 3px 10px rgba(0,0,0,0.1); margin-bottom:25px;}
        .card h2 {color:#001f3f; border-bottom:2px solid #001f3f; padding-bottom:8px;}
        table {width:100%; border-collapse:collapse; margin-top:10px;}
        table th {background:#001f3f; color:white; padding:12px;}
        table td {padding:12px; border-bottom:1px solid #eee;}
        .btn {background:#001f3f; color:white; padding:8px 16px; border-radius:4px; text-decoration:none; font-size:14px;}
        .btn:hover {background:#002b55;}
        .stats {display:grid; grid-template-columns:repeat(auto-fit, minmax(180px,1fr)); gap:20px; margin:30px 0;}
        .stat {background:white; padding:20px; text-align:center; border-radius:8px; box-shadow:0 3px 10px rgba(0,0,0,0.1);}
        .stat h3 {margin:0; font-size:2em; color:#001f3f;}
        .status-pending {color:#ffc107;}
        .status-approved, .status-active {color:#28a745;}
        .status-rejected {color:#dc3545;}
    </style>
</head>
<body>

<div class="container">
    <?php include 'includes/sidebar.php'; ?>

    <div class="main">
        <div class="navbar">
            <div><strong>CedisPay</strong> - My Loans</div>
            <div>
                <span><?= htmlspecialchars($user['full_name'] ?? $user['username']) ?></span>
                <a href="logout.php" class="btn" style="margin-left:15px;">Logout</a>
            </div>
        </div>

        <!-- Loan Stats -->
        <div class="stats">
            <div class="stat">
                <h3><?= $total_loans ?></h3>
                <p>Total Loans</p>
            </div>
            <div class="stat">
                <h3><?= $pending ?></h3>
                <p>Pending</p>
            </div>
            <div class="stat">
                <h3><?= $approved ?></h3>
                <p>Approved/Active</p>
            </div>
            <div class="stat">
                <h3><?= $rejected ?></h3>
                <p>Rejected</p>
            </div>
        </div>

        <div class="card">
            <h2>My Loan Applications</h2>
            <?php if (empty($loans)): ?>
                <p>No loan applications found. <a href="apply_loan.php" class="btn">Apply for a Loan</a></p>
            <?php else: ?>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Amount</th>
                        <th>Term</th>
                        <th>Purpose</th>
                        <th>Status</th>
                        <th>Applied</th>
                        <th>Action</th>
                    </tr>
                    <?php foreach ($loans as $loan): ?>
                    <tr>
                        <td>#<?= $loan['id'] ?></td>
                        <td>GH₵ <?= number_format($loan['amount'], 2) ?></td>
                        <td><?= $loan['term'] ?> months</td>
                        <td><?= htmlspecialchars(substr($loan['purpose'], 0, 40)) . (strlen($loan['purpose']) > 40 ? '...' : '') ?></td>
                        <td><strong class="status-<?= $loan['status'] ?>"><?= ucfirst($loan['status']) ?></strong></td>
                        <td><?= date('d M Y', strtotime($loan['applied_at'])) ?></td>
                        <td><a href="loan_details.php?id=<?= $loan['id'] ?>" class="btn">View Details</a></td>
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
