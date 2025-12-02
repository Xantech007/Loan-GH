<?php
// dashboard.php - FINAL VERSION: Pulls fresh data from members table
session_start();

// 1. Security check – must be logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['member_id'])) {
    header('Location: ./login.php');
    exit();
}

require '../config/db.php'; // Database connection

$member_id = (int)$_SESSION['member_id']; // Always ensure it's an integer

// 2. Fetch fresh, real data from members table
$stmt = $conn->prepare("SELECT member_id, full_name, email, phone, date_registered FROM members WHERE member_id = ?");
$stmt->bind_param("i", $member_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    // User not found? Force logout
    session_destroy();
    header('Location: ./login.php');
    exit();
}

$user = $result->fetch_assoc();

// Format Member ID as MEM000001
$display_id = "MEM" . str_pad($user['member_id'], 6, "0", STR_PAD_LEFT);

$pageTitle = 'Dashboard';
include './includes/member_header.php';
?>

<div class="main dashboard-main">
    <div class="page-header">
        <h2 class="main-header">Welcome back, <?= htmlspecialchars($user['full_name']) ?>!</h2>
        <h5>Get an overview of your account status, recent activities, and quick access to key features.</h5>
    </div>

    <div class="dashboard-grid">
        <div class="dashboard-card">
            <div class="dashboard-card-top">
                <h3>Active Loans</h3>
                <i class="fa-regular fa-credit-card"></i>
            </div>
            <p>3 Active Loans</p>
        </div>
        <div class="dashboard-card">
            <div class="dashboard-card-top">
                <h3>Outstanding Balance</h3>
                <i class="fa-solid fa-money-bills"></i>
            </div>
            <p>GHS 7,500</p>
        </div>
        <div class="dashboard-card">
            <div class="dashboard-card-top">
                <h3>Upcoming Due Date</h3>
                <i class="fa-regular fa-calendar"></i>
            </div>
            <p>Feb 20, 2025</p>
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
                <p><strong>Member Since:</strong> <span><?= date("M d, Y", strtotime($user['date_registered'])) ?></span></p>
                <p><strong>Account Status:</strong> <span id="active-span" style="color:green;font-weight:bold;">Active</span></p>
            </div>
        </div>

        <div class="overview recent-activities-account-summary">
            <h3>Recent Activities</h3>
            <ul style="margin:15px 0; line-height:1.8; color:#555;">
                <li>Loan Payment of GHS 1,000 made on Feb 1, 2025.</li>
                <li>New Loan Application submitted on Jan 25, 2025.</li>
                <li>Profile Updated on Jan 15, 2025.</li>
                <li>Account activated on <?= date("M d, Y", strtotime($user['date_registered'])) ?>.</li>
            </ul>
        </div>
    </div>

    <a href="apply_loan.php" class="dashboard-btn">Apply for a New Loan</a>
</div>

<?php include './includes/member_footer.php'; ?>
