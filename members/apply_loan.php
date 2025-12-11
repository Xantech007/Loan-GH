<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit; }
require_once '../config/db.php';

$msg = '';
if ($_POST) {
    $amount  = trim($_POST['amount']);
    $term    = $_POST['term'];
    $purpose = trim($_POST['purpose']);

    if ($amount > 0) {
        $stmt = $pdo->prepare("INSERT INTO loans (user_id, amount, interest_rate, term, purpose, status) 
                               VALUES (?, ?, 14.5, ?, ?, 'pending')");
        $stmt->execute([$_SESSION['user_id'], $amount, $term, $purpose]);

        // Add notification
        $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)")->execute([
            $_SESSION['user_id'], "Your loan application for GH₵$amount has been submitted."
        ]);

        $msg = '<div class="alert alert-success">Loan application submitted! We\'ll review it within 24 hours.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Loan - CedisPay</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root { --blue: #001f3f; }
        body { margin:0; font-family:'Segoe UI',sans-serif; background:#f4f6f9; }
        .container { display:flex; min-height:100vh; }
        .sidebar { width:260px; background:var(--blue); color:white; padding:20px 0; }
        .sidebar a { padding:25px; text-align:center; border-bottom:1px solid rgba(255,255,255,0.1); }
        .sidebar a { display:block; padding:15px 30px; color:white; text-decoration:none; }
        .sidebar a:hover, .sidebar a.active { background:rgba(255,255,255,0.15); }
        .main { flex:1; padding:40px; }
        .navbar { background:var(--blue); color:white; padding:20px 40px; margin:-40px -40px 40px; border-radius:10px 10px 0 0; display:flex; justify-content:space-between; align-items:center; }
        .card { background:white; padding:35px; border-radius:12px; box-shadow:0 5px 20px rgba(0,0,0,0.1); max-width:800px; margin:0 auto; }
        .card h2 { color:var(--blue); border-bottom:3px solid var(--blue); padding-bottom:12px; }
        input, select, textarea { width:100%; padding:14px; margin:10px 0; border:1px solid #ddd; border-radius:8px; font-size:16px; }
        .btn { background:var(--blue); color:white; padding:14px 30px; border:none; border-radius:8px; font-size:16px; cursor:pointer; }
        .btn:hover { background:#002b300; }
        .alert { padding:15px; border-radius:8px; margin:20px 0; font-weight:bold; }
        .alert-success { background:#d4edda; color:#155724; border:1px solid #c3e6cb; }
    </style>
</head>
<body>
<div class="container">
    <?php include 'includes/sidebar.php'; ?>

    <div class="main">
        <?php include 'includes/navbar.php'; ?>

        <div class="card">
            <h2>Apply for a New Loan</h2>
            <?= $msg ?>

            <form method="POST">
                <label>Loan Amount (GH₵)</label>
                <input type="number" name="amount" min="500" max="100000" step="100" required placeholder="e.g. 5000">

                <label>Repayment Term</label>
                <select name="term" required>
                    <option value="">Select term</option>
                    <option value="6">6 months – 12.5% interest</option>
                    <option value="12">12 months – 14.5% interest</option>
                    <option value="24">24 months – 16.0% interest</option>
                    <option value="36">36 months – 18.0% interest</option>
                </select>

                <label>Purpose of Loan</label>
                <textarea name="purpose" rows="5" required placeholder="Describe why you need this loan..."></textarea>

                <button type="submit" class="btn" style="width:100%; margin-top:20px; font-size:18px;">
                    Submit Application
                </button>
            </form>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
</body>
</html>
