<?php
// dashboard.php - FINAL FIXED VERSION
session_start();
require '../config/db.php';

// Security: Must be logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['member_id'])) {
    header('Location: ./login.php');
    exit();
}

$member_id = (int)$_SESSION['member_id'];

// Fetch fresh member profile
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

include './includes/member_header.php'; // This now opens <html>, <body>, sidebar, and <div class="main">
?>

<div class="page-header text-center text-md-start mb-5">
    <h2 class="main-header fw-bold">Welcome back, <?= htmlspecialchars($user['full_name']) ?>!</h2>
    <p class="text-muted lead">Here's an overview of your account status and recent activities.</p>
</div>

<!-- Stats Cards -->
<div class="dashboard-grid row row-cols-1 row-cols-md-3 g-4 mb-5">
    <div class="col">
        <div class="dashboard-card card h-100 shadow-sm border-0">
            <div class="card-body d-flex justify-content-between align-items-start">
                <div>
                    <h5 class="card-title text-muted">Active Loans</h5>
                    <h3 class="fw-bold text-primary"><?= $active_loans ?? 0 ?></h3>
                    <p class="mb-0 text-muted small">Active Loan<?= ($active_loans ?? 0) == 1 ? '' : 's' ?></p>
                </div>
                <i class="fa-regular fa-credit-card fa-2x text-primary opacity-75"></i>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="dashboard-card card h-100 shadow-sm border-0">
            <div class="card-body d-flex justify-content-between align-items-start">
                <div>
                    <h5 class="card-title text-muted">Outstanding Balance</h5>
                    <h3 class="fw-bold text-danger">GHS <?= number_format($outstanding ?? 0, 2) ?></h3>
                </div>
                <i class="fa-solid fa-money-bills fa-2x text-danger opacity-75"></i>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="dashboard-card card h-100 shadow-sm border-0">
            <div class="card-body d-flex justify-content-between align-items-start">
                <div>
                    <h5 class="card-title text-muted">Next Due Date</h5>
                    <h4 class="fw-bold text-success"><?= $next_due ?? 'No upcoming payments' ?></h4>
                </div>
                <i class="fa-regular fa-calendar fa-2x text-success opacity-75"></i>
            </div>
        </div>
    </div>
</div>

<!-- Account Summary + Recent Activities -->
<div class="row g-4">
    <div class="col-lg-6">
        <div class="overview card shadow-sm h-100">
            <div class="card-body">
                <h4 class="card-title mb-4">Account Summary</h4>
                <div class="grid-dashboard-summary">
                    <p><strong>Member ID:</strong> <span class="highlight"><?= $display_id ?></span></p>
                    <p><strong>Full Name:</strong> <?= htmlspecialchars($user['full_name']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                    <p><strong>Phone:</strong> <?= htmlspecialchars($user['phone']) ?></p>
                    <p><strong>Member Since:</strong> <?= $member_since ?></p>
                    <p><strong>Status:</strong> <span class="text-success fw-bold">Active</span></p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="overview card shadow-sm h-100">
            <div class="card-body">
                <h4 class="card-title mb-4">Recent Activities</h4>
                <?php if (isset($activities) && $activities->num_rows > 0): ?>
                    <ul class="list-unstyled" style="line-height: 2;">
                        <?php while ($act = $activities->fetch_assoc()): ?>
                            <li class="border-bottom pb-2 mb-2">
                                <strong><?= htmlspecialchars($act['title']) ?></strong><br>
                                <small class="text-muted">
                                    <?= htmlspecialchars($act['description']) ?> —
                                    <?= date("M d, Y \a\\t g:i A", strtotime($act['created_at'])) ?>
                                </small>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted fst-italic">No recent activity.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="text-center mt-5">
    <a href="apply_loan.php" class="btn btn-primary btn-lg px-5 py-3 dashboard-btn">
        <i class="fas fa-plus-circle me-2"></i> Apply for a New Loan
    </a>
</div>

<?php
// Free result if exists
if (isset($activities)) $activities->free_result();

// Close </div><!-- /.main --> and </body></html>
include './includes/member_footer.php';
?>
