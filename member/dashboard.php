<?php
// dashboard.php - UPDATED & WORKING VERSION
session_start();

// NEW SESSION CHECK (matches your current login.php)
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ./login.php');
    exit();
}

// Optional: extra security
session_regenerate_id(true);

$pageTitle = 'Dashboard';
include './includes/member_header.php';

// Safely get user data from session (set in login.php)
$member_id     = $_SESSION['member_id'] ?? 0;
$display_id    = $_SESSION['display_id'] ?? 'MEM000000';  // e.g. MEM000007
$full_name     = htmlspecialchars($_SESSION['full_name'] ?? 'Member');
$email         = htmlspecialchars($_SESSION['email'] ?? 'Not set');
$phone         = htmlspecialchars($_SESSION['phone'] ?? 'Not set');
?>

<div class="main dashboard-main">
    <div class="page-header">
        <h2 class="main-header">Welcome back, <?= $full_name ?>!</h2>
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
                <p><strong>Full Name:</strong> <span><?= $full_name ?></span></p>
                <p><strong>Email:</strong> <span><?= $email ?></span></p>
                <p><strong>Phone:</strong> <span><?= $phone ?></span></p>
                <p><strong>Account Status:</strong> <span id="active-span" style="color:green;font-weight:bold;">Active</span></p>
            </div>
        </div>

        <div class="overview recent-activities-account-summary">
            <h3>Recent Activities</h3>
            <ul style="margin:15px 0; line-height:1.8;">
                <li>Loan Payment of GHS 1,000 made on Feb 1, 2025.</li>
                <li>New Loan Application submitted on Jan 25, 2025.</li>
                <li>Profile Updated on Jan 15, 2025.</li>
            </ul>
        </div>
    </div>

    <a href="apply_loan.php" class="dashboard-btn">Apply for a New Loan</a>
</div>

<?php include './includes/member_footer.php'; ?>
