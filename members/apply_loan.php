<?php
// members/apply_loan.php
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

// Fetch user info for navbar
$stmt = $pdo->prepare("SELECT full_name, username, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = trim($_POST['amount']);
    $term = $_POST['term'];
    $purpose = trim($_POST['purpose']);

    if ($amount > 0 && !empty($purpose)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO loans (user_id, amount, interest_rate, term, purpose, status) VALUES (?, ?, 12.50, ?, ?, 'pending')");
            $stmt->execute([$user_id, $amount, $term, $purpose]);
            $message = '<div style="background:#d4edda; color:#155724; padding:15px; border-radius:8px; margin:20px 0;">Loan application submitted successfully! We will review it shortly.</div>';
        } catch (Exception $e) {
            $message = '<div style="background:#f8d7da; color:#721c24; padding:15px; border-radius:8px; margin:20px 0;">Error: Could not submit application.</div>';
        }
    } else {
        $message = '<div style="background:#f8d7da; color:#721c24; padding:15px; border-radius:8px; margin:20px 0;">Please fill all fields correctly.</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Loan - CedisPay</title>
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
        table th, table td {padding:12px; text-align:left;}
        table th {background:#001f3f; color:white;}
        table td {border-bottom:1px solid #eee;}
        .btn {background:#001f3f; color:white; padding:10px 20px; border-radius:4px; text-decoration:none; font-size:14px; cursor:pointer;}
        .btn:hover {background:#002b55;}
        input, select, textarea {width:100%; padding:12px; margin:8px 0; border:1px solid #ccc; border-radius:6px; box-sizing:border-box; font-size:16px;}
        label {font-weight:bold; margin-top:15px; display:block;}
        .form-row {display:grid; grid-template-columns:1fr 1fr; gap:20px;}
        @media (max-width:768px) {.form-row {grid-template-columns:1fr;}}
    </style>
</head>
<body>

<div class="container">
    <?php include 'includes/sidebar.php'; ?>

    <div class="main">
        <div class="navbar">
            <div><strong>CedisPay</strong> - Apply for Loan</div>
            <div>
                <span><?= htmlspecialchars($user['full_name'] ?? $user['username']) ?></span>
                <a href="logout.php" class="btn" style="margin-left:15px;">Logout</a>
            </div>
        </div>

        <div class="card">
            <h2>Apply for a New Loan</h2>
            <?= $message ?>
            
            <form method="POST">
                <label>Loan Amount (GH₵)</label>
                <input type="number" name="amount" min="100" step="50" placeholder="e.g. 5000" required>

                <div class="form-row">
                    <div>
                        <label>Loan Term</label>
                        <select name="term" required>
                            <option value="6">6 Months</option>
                            <option value="12" selected>12 Months</option>
                            <option value="24">24 Months</option>
                            <option value="36">36 Months</option>
                        </select>
                    </div>
                    <div>
                        <label>Interest Rate</label>
                        <input type="text" value="12.50% per annum" disabled style="background:#f0f0f0;">
                    </div>
                </div>

                <label>Purpose of Loan</label>
                <textarea name="purpose" rows="5" placeholder="Briefly describe why you need this loan..." required></textarea>

                <button type="submit" class="btn" style="width:100%; margin-top:20px; padding:15px;">Submit Application</button>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>
