<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/navbar.php';
include '../config/db.php'; // Correct path from member/ to config/

$user_id = $_SESSION['user_id'];

// Fetch user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Fetch recent loans
$loans = $pdo->prepare("SELECT * FROM loans WHERE user_id = ? ORDER BY applied_at DESC LIMIT 5");
$loans->execute([$user_id]);
$loans = $loans->fetchAll();

// Recent payments
$payments = $pdo->prepare("
    SELECT p.*, l.amount AS loan_amount 
    FROM payments p 
    JOIN loans l ON p.loan_id = l.id 
    WHERE l.user_id = ? 
    ORDER BY p.paid_at DESC LIMIT 5
");
$payments->execute([$user_id]);
$payments = $payments->fetchAll();

// Notifications
$notifs = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT 5");
$notifs->execute([$user_id]);
$notifications = $notifs->fetchAll();

// Calculate total outstanding
$total_outstanding = 0;
foreach ($loans as $loan) {
    if (in_array($loan['status'], ['approved', 'active'])) {
        $total_outstanding += $loan['amount'] + ($loan['amount'] * $loan['interest_rate'] / 100);
    }
}
?>

<div class="main-content">

    <div class="card">
        <h2>Welcome back, <?php echo htmlspecialchars($user['full_name']); ?>!</h2>
        <p>Manage your loans and repayments with ease on CedisPay.</p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
        <div class="card">
            <h2>Account Overview</h2>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($user['full_name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone'] ?? 'Not set'); ?></p>
            <p><strong>Member Since:</strong> <?php echo date('M d, Y', strtotime($user['created_at'])); ?></p>
            <a href="profile.php" class="btn">Update Profile</a>
        </div>

        <div class="card">
            <h2>Loan Summary</h2>
            <h3 style="color:#001f3f; font-size:2em;">GH₵ <?php echo number_format($total_outstanding, 2); ?></h3>
            <p>Total Amount Owed</p>
            <hr>
            <p>Active Loans: <strong><?php echo count(array_filter($loans, fn($l) => $l['status'] == 'active')); ?></strong></p>
            <p>Pending Approval: <strong><?php echo count(array_filter($loans, fn($l) => $l['status'] == 'pending')); ?></strong></p>
            <a href="apply_loan.php" class="btn">Apply for New Loan</a>
        </div>
    </div>

    <div class="card">
        <h2>Recent Loans</h2>
        <?php if (empty($loans)): ?>
            <p>You have no loan records yet. <a href="apply_loan.php">Apply now</a></p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Loan ID</th>
                        <th>Amount</th>
                        <th>Term</th>
                        <th>Status</th>
                        <th>Date Applied</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($loans as $loan): ?>
                    <tr>
                        <td>#<?php echo $loan['id']; ?></td>
                        <td>GH₵ <?php echo number_format($loan['amount'], 2); ?></td>
                        <td><?php echo $loan['term']; ?> months</td>
                        <td>
                            <span style="padding:5px 10px; border-radius:4px; font-size:0.9em; color:white; background: 
                                <?php echo $loan['status'] == 'active' ? 'green' : ($loan['status'] == 'pending' ? '#001f3f' : 'red'); ?>">
                                <?php echo ucfirst($loan['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($loan['applied_at'])); ?></td>
                        <td><a href="loan_details.php?id=<?php echo $loan['id']; ?>" class="btn">View</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="card">
        <h2>Recent Payments</h2>
        <?php if (empty($payments)): ?>
            <p>No payment records found.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr><th>Date</th><th>Amount</th><th>Method</th><th>Reference</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $p): ?>
                    <tr>
                        <td><?php echo date('M d, Y', strtotime($p['paid_at'])); ?></td>
                        <td>GH₵ <?php echo number_format($p['amount'], 2); ?></td>
                        <td><?php echo ucwords(str_replace('_', ' ', $p['payment_method'])); ?></td>
                        <td><?php echo $p['reference'] ?: '—'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <?php if (!empty($notifications)): ?>
    <div class="card">
        <h2>Notifications</h2>
        <?php foreach ($notifications as $n): ?>
            <div class="notification">
                <?php echo htmlspecialchars($n['message']); ?>
                <small style="float:right; color:#666;"><?php echo date('M d, Y H:i', strtotime($n['created_at'])); ?></small>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>

#### 2. `member/includes/header.php`
```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CedisPay - Member Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary: #001f3f;    /* Dark Blue */
            --light: #f8f9fa;
            --white: #ffffff;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background: var(--white);
            color: #333;
        }
        .container { display: flex; min-height: 100vh; }
        .main-content { flex: 1; padding: 20px; background: #f9f9fc; }
        .card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            border: 1px solid #eee;
        }
        .btn {
            background: var(--primary);
            color: white;
            padding: 10px 18px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            display: inline-block;
            font-size: 0.95em;
            transition: 0.3s;
        }
        .btn:hover { background: #003366; transform: translateY(-2px); }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th { background: var(--primary); color: white; padding: 12px; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        .notification { background: #e3f2fd; border-left: 5px solid var(--primary); padding: 15px; margin: 10px 0; border-radius: 0 8px 8px 0; }
    </style>
</head>
<body>
    <div class="container">
