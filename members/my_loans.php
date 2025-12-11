<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit; }
require_once '../config/db.php';

$stmt = $pdo->prepare("SELECT * FROM loans WHERE user_id = ? ORDER BY applied_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$loans = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Loans - CedisPay</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root { --blue: #001f3f; }
        body { margin:0; font-family:'Segoe UI',sans-serif; background:#f4f6f9; }
        .container { display:flex; min-height:100vh; }
        .sidebar { width:260px; background:var(--blue); color:white; }
        .main { flex:1; padding:40px; }
        .navbar { background:var(--blue); color:white; padding:20px 40px; margin:-40px -40px 40px; border-radius:10px 10px 0 0; display:flex; justify-content:space-between; }
        .card { background:white; padding:30px; border-radius:12px; box-shadow:0 5px 20px rgba(0,0,0,0.1); }
        .card h2 { color:var(--blue); border-bottom:3px solid var(--blue); padding-bottom:12px; }
        table { width:100%; border-collapse:collapse; margin-top:20px; }
        table th { background:var(--blue); color:white; padding:16px; text-align:left; }
        table td { padding:16px; border-bottom:1px solid #eee; }
        .status-pending   { color:#856404; background:#fff3cd; padding:6px 12px; border-radius:20px; font-size:14px; }
        .status-approved  { color:#155724; background:#d4edda; padding:6px 12px; border-radius:20px; }
        .status-active    { color:#0c5460; background:#d1ecf1; padding:6px 12px; border-radius:20px; }
        .status-paid      { color:#155724; background:#e2f0d9; padding:6px 12px; border-radius:20px; }
        .btn { background:var(--blue); color:white; padding:10px 20px; border-radius:6px; text-decoration:none; font-size:14px; }
        .btn:hover { background:#002b55; }
    </style>
</head>
<body>
<div class="container">
    <?php include 'includes/sidebar.php'; ?>

    <div class="main">
        <?php include 'includes/navbar.php'; ?>

        <div class="card">
            <h2>My Loans (<?= count($loans) ?>)</h2>

            <?php if (empty($loans)): ?>
                <p>You have no loan applications yet. <a href="apply_loan.php" class="btn">Apply Now</a></p>
            <?php else: ?>
                <table>
                    <tr>
                        <th>Loan ID</th>
                        <th>Amount</th>
                        <th>Term</th>
                        <th>Interest</th>
                        <th>Status</th>
                        <th>Applied</th>
                        <th>Action</th>
                    </tr>
                    <?php foreach($loans as $loan): ?>
                    <tr>
                        <td><strong>#<?= str_pad($loan['id'], 5, '0', STR_PAD_LEFT) ?></strong></td>
                        <td>GH₵ <?= number_format($loan['amount'],2) ?></td>
                        <td><?= $loan['term'] ?> months</td>
                        <td><?= $loan['interest_rate'] ?>%</td>
                        <td><span class="status-<?= $loan['status'] ?>"><?= ucfirst($loan['status']) ?></span></td>
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
