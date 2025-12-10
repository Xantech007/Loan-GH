<?php
// dashboard.php - FINAL VERSION WITH REAL DATA
session_start();
require '../config/db.php';

// === 1. Security: Must be logged in ===
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['member_id'])) {
    header('Location: ./login.php');
    exit();
}

$member_id = (int)$_SESSION['member_id'];

// === 2. Fetch Member Profile (always fresh) ===
$stmt = $conn->prepare("SELECT full_name, email, phone, date_registered FROM members WHERE member_id = ?");
$stmt->bind_param("i", $member_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->num_rows === 1 ? $result->fetch_assoc() : null;

if (!$user) {
    session_destroy();
    header('Location: ./login.php');
    exit();
}

$display_id = "MEM" . str_pad($member_id, 6, "0", STR_PAD_LEFT);
$member_since = date("M d, Y", strtotime($user['date_registered']));

$pageTitle = 'Dashboard';
include './includes/member_header.php';

// === 3. Real Financial Data (safe if tables don't exist yet) ===

// Active Loans
$active_loans = 0;
if ($conn->query("SHOW TABLES LIKE 'loans'")->num_rows > 0) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM loans WHERE member_id = ? AND status = 'approved'");
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $active_loans = $stmt->get_result()->fetch_array()[0];
}

// Outstanding Balance
$outstanding = 0;
if ($conn->query("SHOW TABLES LIKE 'loan_repayments'")->num_rows > 0) {
    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(amount_due - amount_paid), 0) 
        FROM loan_repayments r 
        JOIN loans l ON r.loan_id = l.loan_id 
        WHERE l.member_id = ? AND r.status != 'paid'
    ");
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $outstanding = $stmt->get_result()->fetch_array()[0];
}

// Next Due Date
$next_due = 'No upcoming payments';
if ($conn->query("SHOW TABLES LIKE 'loan_repayments'")->num_rows > 0) {
    $stmt = $conn->prepare("
        SELECT due_date FROM loan_repayments r
        JOIN loans l ON r.loan_id = l.loan_id
        WHERE l.member_id = ? AND r.status != 'paid' AND r.due_date >= CURDATE()
        ORDER BY r.due_date ASC LIMIT 1
    ");
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_array();
    if ($res) $next_due = date("M d, Y", strtotime($res[0]));
}

// Recent Activities (from activities table or fallback)
$activities = [];
if ($conn->query("SHOW TABLES LIKE 'activities'")->num_rows > 0) {
    $stmt = $conn->prepare("
        SELECT title, description, created_at 
        FROM activities 
        WHERE member_id = ? 
        ORDER BY created_at DESC LIMIT 6
    ");
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $activities = $stmt->get_result();
} else {
    // Fallback static activities if table doesn't exist yet
    $activities = new mysqli_result($conn); // dummy
    $activities->free_result = function() {};
}
?>

<div class="main dashboard-main">
    <div class="page-header">
        <h2 class="main-header">Welcome back, <?= htmlspecialchars($user['full_name']) ?>!</h2>
        <h5>Get an overview of your account status, recent activities, and quick access to key features.</h5>
    </div>

    <!-- Real Stats Cards -->
    <div class="dashboard-grid">
        <div class="dashboard-card">
            <div class="dashboard-card-top">
                <h3>Active Loans</h3>
                <i class="fa-regular fa-credit-card"></i>
            </div>
            <p><strong><?= $active_loans ?></strong> Active Loan<?= $active_loans == 1 ? '' : 's' ?></p>
        </div>

        <div class="dashboard-card">
            <div class="dashboard-card-top">
                <h3>Outstanding Balance</h3>
                <i class="fa-solid fa-money-bills"></i>
            </div>
            <p><strong>GHS <?= number_format($outstanding, 2) ?></strong></p>
        </div>

        <div class="dashboard-card">
            <div class="dashboard-card-top">
                <h3>Upcoming Due Date</h3>
                <i class="fa-regular fa-calendar"></i>
            </div>
            <p><strong><?= $next_due ?></strong></p>
        </div>
    </div>

    <div class="dashboard-summary">
        <div class="overview dashboard-account-summary">
            <h3>Account Summary</h3>
            <div class="grid-dashboard-summary">
                <p><strong>Member ID:</strong> <span class="highlight"><?= $display_id ?></span></p>
                <p><strong>Full Name:</strong> <span><?= htmlspecialchars($user['full_name']) ?></span></p>
                <p><strong>Email:</strong> <span><?= htmlspecialchars($user['email']) ?></span></p>
                <p><strong>Phone:</strong> <span><?= htmlspecialchars($user['phone']) ?></span></p>
                <p><strong>Member Since:</strong> <span><?= $member_since ?></span></p>
                <p><strong>Account Status:</strong> <span style="color:green;font-weight:bold;">Active</span></p>
            </div>
        </div>

        <div class="overview recent-activities-account-summary">
            <h3>Recent Activities</h3>
            <?php if ($activities->num_rows > 0): ?>
                <ul style="margin:15px 0; line-height:1.9; color:#444;">
                    <?php while ($act = $activities->fetch_assoc()): ?>
                        <li>
                            <strong><?= htmlspecialchars($act['title']) ?></strong><br>
                            <small><?= htmlspecialchars($act['description']) ?> — 
                                <?= date("M d, Y \a\\t g:i A", strtotime($act['created_at'])) ?>
                            </small>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p style="color:#666; font-style:italic;">No recent activity.</p>
            <?php endif; ?>
        </div>
    </div>

    <a href="apply_loan.php" class="dashboard-btn">Apply for a New Loan</a>
</div>

<?php 
$activities->free_result ?? null;
include './includes/member_footer.php'; 
?>
