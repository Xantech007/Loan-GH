<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit; }
require_once '../config/db.php';

$stmt = $pdo->prepare("SELECT * FROM loans WHERE user_id = ? ORDER BY applied_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$loans = $stmt->fetchAll();
?>
<?php include 'includes/header.php'; include 'includes/sidebar.php'; ?>
<div class="main">
    <?php include 'includes/navbar.php'; ?>
    <div class="card">
        <h2>My Loans</h2>
        <table>
            <tr><th>ID</th><th>Amount</th><th>Term</th><th>Status</th><th>Applied</th><th>Action</th></tr>
            <?php foreach($loans as $loan): ?>
            <tr>
                <td>#<?= $loan['id'] ?></td>
                <td>GH₵ <?= number_format($loan['amount'],2) ?></td>
                <td><?= $loan['term'] ?> months</td>
                <td><strong><?= ucfirst($loan['status']) ?></strong></td>
                <td><?= date('d M Y', strtotime($loan['applied_at'])) ?></td>
                <td><a href="loan_details.php?id=<?= $loan['id'] ?>" class="btn btn-sm">View</a></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
