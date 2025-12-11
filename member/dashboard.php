<?php
include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/navbar.php';
include '../config/db.php'; // Connection file (adjust path if needed)

$user_id = $_SESSION['user_id'];

// Fetch user details
$stmt_user = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt_user->execute([$user_id]);
$user = $stmt_user->fetch();

// Fetch loans
$stmt_loans = $pdo->prepare("SELECT * FROM loans WHERE user_id = ? ORDER BY applied_at DESC LIMIT 5");
$stmt_loans->execute([$user_id]);
$loans = $stmt_loans->fetchAll();

// Fetch recent payments
$stmt_payments = $pdo->prepare("SELECT p.*, l.amount AS loan_amount FROM payments p JOIN loans l ON p.loan_id = l.id WHERE l.user_id = ? ORDER BY p.paid_at DESC LIMIT 5");
$stmt_payments->execute([$user_id]);
$payments = $stmt_payments->fetchAll();

// Fetch notifications
$stmt_notifs = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT 5");
$stmt_notifs->execute([$user_id]);
$notifications = $stmt_notifs->fetchAll();

// Calculate total outstanding balance (example calculation)
$total_outstanding = 0;
foreach ($loans as $loan) {
    if (in_array($loan['status'], ['approved', 'active'])) {
        $total_outstanding += $loan['amount'] * (1 + $loan['interest_rate']/100);
    }
}
?>

<div class="main-content">
    <div class="card">
        <h2>Welcome to Your CedisPay Dashboard</h2>
        <p>Here you can manage your loans, view payments, and stay updated with notifications.</p>
    </div>

    <div class="card">
        <h2>User Profile Summary</h2>
        <p><strong>Full Name:</strong> <?php echo htmlspecialchars($user['full_name'] ?? 'N/A'); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></p>
        <p><strong>Address:</strong> <?php echo htmlspecialchars($user['address'] ?? 'N/A'); ?></p>
        <p><strong>Account Created:</strong> <?php echo $user['created_at']; ?></p>
        <a href="profile.php" class="btn">Edit Profile</a>
    </div>

    <div class="card">
        <h2>Financial Overview</h2>
        <p><strong>Total Outstanding Balance:</strong> GH₵ <?php echo number_format($total_outstanding, 2); ?></p>
        <p><strong>Active Loans:</strong> <?php echo count(array_filter($loans, fn($l) => $l['status'] === 'active')); ?></p>
        <p><strong>Pending Loans:</strong> <?php echo count(array_filter($loans, fn($l) => $l['status'] === 'pending')); ?></p>
    </div>

    <div class="card">
        <h2>Recent Loans</h2>
        <?php if (empty($loans)): ?>
            <p>No loans found. <a href="apply_loan.php">Apply for a loan now</a>.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Amount</th>
                        <th>Interest Rate</th>
                        <th>Term (Months)</th>
                        <th>Status</th>
                        <th>Applied On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($loans as $loan): ?>
                        <tr>
                            <td><?php echo $loan['id']; ?></td>
                            <td>GH₵ <?php echo number_format($loan['amount'], 2); ?></td>
                            <td><?php echo $loan['interest_rate']; ?>%</td>
                            <td><?php echo $loan['term']; ?></td>
                            <td><?php echo ucfirst($loan['status']); ?></td>
                            <td><?php echo $loan['applied_at']; ?></td>
                            <td><a href="loan_details.php?id=<?php echo $loan['id']; ?>" class="btn">View</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <a href="my_loans.php" class="btn">View All Loans</a>
    </div>

    <div class="card">
        <h2>Recent Payments</h2>
        <?php if (empty($payments)): ?>
            <p>No payments found.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Loan ID</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Paid On</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td><?php echo $payment['id']; ?></td>
                            <td><?php echo $payment['loan_id']; ?></td>
                            <td>GH₵ <?php echo number_format($payment['amount'], 2); ?></td>
                            <td><?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?></td>
                            <td><?php echo $payment['paid_at']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <a href="payments.php" class="btn">View All Payments</a>
    </div>

    <div class="card">
        <h2>Notifications</h2>
        <?php if (empty($notifications)): ?>
            <p>No new notifications.</p>
        <?php else: ?>
            <?php foreach ($notifications as $notif): ?>
                <div class="notification">
                    <p><strong><?php echo ucfirst($notif['type']); ?>:</strong> <?php echo htmlspecialchars($notif['message']); ?></p>
                    <small><?php echo $notif['created_at']; ?></small>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <a href="notifications.php" class="btn">View All Notifications</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
