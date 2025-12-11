<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit; }
require_once '../config/db.php';

$msg = '';
if ($_POST) {
    $amount = $_POST['amount'];
    $term   = $_POST['term'];
    $purpose = $_POST['purpose'];

    $stmt = $pdo->prepare("INSERT INTO loans (user_id, amount, interest_rate, term, purpose, status) VALUES (?, ?, 12.5, ?, ?, 'pending')");
    $stmt->execute([$_SESSION['user_id'], $amount, $term, $purpose]);
    
    // Notification for admin (you can extend this)
    $msg = '<div class="alert alert-success">Loan application submitted successfully! We will review it shortly.</div>';
}
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>
<div class="main">
    <?php include 'includes/navbar.php'; ?>
    <div class="card">
        <h2>Apply for a New Loan</h2>
        <?= $msg ?>
        <form method="POST">
            <table style="width:100%;">
                <tr><td><label>Loan Amount (GH₵)</label></td><td><input type="number" name="amount" required class="form-control" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;"></td></tr>
                <tr><td><label>Term (Months)</label></td><td>
                    <select name="term" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">
                        <option value="6">6 months</option>
                        <option value="12">12 months</option>
                        <option value="24">24 months</option>
                        <option value="36">36 months</option>
                    </select>
                </td></tr>
                <tr><td><label>Purpose</label></td><td><textarea name="purpose" rows="4" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;"></textarea></td></tr>
                <tr><td></td><td><button type="submit" class="btn style="width:100%;">Submit Application</button></td></tr>
            </table>
        </form>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
