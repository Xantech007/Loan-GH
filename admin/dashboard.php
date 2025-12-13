<?php
// admin/dashboard.php - Modern & Updated CedisPay Admin Dashboard (2025)

session_start();
require '../config/db.php'; // Using PDO for consistency with member dashboard

// Admin authentication
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$pageTitle = "Admin Dashboard";
include './includes/admin_header.php';

try {
    // Key Stats
    $totalMembers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $unverifiedUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE is_verified = 0")->fetchColumn();
    $verifiedUsers = $totalMembers - $unverifiedUsers;
    $totalLoans = $pdo->query("SELECT COUNT(*) FROM loans")->fetchColumn();
    $pendingLoans = $pdo->query("SELECT COUNT(*) FROM loans WHERE status = 'pending'")->fetchColumn();
    $approvedLoans = $pdo->query("SELECT COUNT(*) FROM loans WHERE status = 'approved'")->fetchColumn();
    $totalLoanAmount = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM loans WHERE status = 'approved'")->fetchColumn();

    // Recent Verifications (for admin review)
    $recentVerifications = $pdo->prepare("
        SELECT u.id, u.full_name, u.email, u.verification_method, u.id_number, u.verified_at
        FROM users u
        WHERE u.is_verified = 1
        ORDER BY u.verified_at DESC
        LIMIT 5
    ");
    $recentVerifications->execute();
    $verifications = $recentVerifications->fetchAll();

    // Recent Loan Applications
    $recentLoans = $pdo->prepare("
        SELECT l.id, u.full_name, l.amount, l.term, l.purpose, l.status, l.created_at
        FROM loans l
        JOIN users u ON l.user_id = u.id
        ORDER BY l.created_at DESC
        LIMIT 7
    ");
    $recentLoans->execute();
    $loansList = $recentLoans->fetchAll();

    // Loan Status Distribution for Chart
    $statusQuery = $pdo->query("
        SELECT status, COUNT(*) as count 
        FROM loans 
        GROUP BY status
    ");
    $statuses = [];
    $counts = [];
    $colors = [
        'pending' => '#f39c12',
        'approved' => '#27ae60',
        'rejected' => '#e74c3c',
        'paid' => '#3498db'
    ];
    while ($row = $statusQuery->fetch()) {
        $statuses[] = ucfirst($row['status']);
        $counts[] = $row['count'];
    }

} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}
?>

<div class="main p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary"><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h2>
        <small class="text-muted">Last updated: <?= date('M d, Y - h:i A') ?></small>
    </div>

    <p class="lead text-muted">Welcome back, <strong><?= htmlspecialchars($_SESSION['full_name'] ?? 'Admin') ?></strong>. Here's an overview of CedisPay operations.</p>

    <!-- KPI Cards -->
    <div class="row g-4 mb-5">
        <div class="col-md-3 col-sm-6">
            <div class="kpi-card shadow-lg border-start border-primary border-5">
                <div class="d-flex align-items-center">
                    <i class="fas fa-users fa-3x text-primary me-4"></i>
                    <div>
                        <h5 class="kpi-label mb-1">Total Members</h5>
                        <h3 class="kpi-value mb-0"><?= number_format($totalMembers) ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="kpi-card shadow-lg border-start border-success border-5">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle fa-3x text-success me-4"></i>
                    <div>
                        <h5 class="kpi-label mb-1">Verified Users</h5>
                        <h3 class="kpi-value mb-0"><?= number_format($verifiedUsers) ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="kpi-card shadow-lg border-start border-warning border-5">
                <div class="d-flex align-items-center">
                    <i class="fas fa-clock fa-3x text-warning me-4"></i>
                    <div>
                        <h5 class="kpi-label mb-1">Pending Verifications</h5>
                        <h3 class="kpi-value mb-0"><?= number_format($unverifiedUsers) ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="kpi-card shadow-lg border-start border-info border-5">
                <div class="d-flex align-items-center">
                    <i class="fas fa-hand-holding-usd fa-3x text-info me-4"></i>
                    <div>
                        <h5 class="kpi-label mb-1">Total Loans Disbursed</h5>
                        <h3 class="kpi-value mb-0">GHS <?= number_format($totalLoanAmount, 2) ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Loan Status Chart -->
        <div class="col-lg-6">
            <div class="card shadow-lg h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Loan Applications by Status</h5>
                </div>
                <div class="card-body">
                    <canvas id="loanStatusChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Verifications -->
        <div class="col-lg-6">
            <div class="card shadow-lg h-100">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-id-card"></i> Recently Verified Accounts</h5>
                    <a href="verifications.php" class="btn btn-light btn-sm">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>ID Type</th>
                                    <th>Verified On</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($verifications) > 0): ?>
                                    <?php foreach ($verifications as $v): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($v['full_name']) ?></strong></td>
                                        <td><?= ucfirst(str_replace('_', ' ', $v['verification_method'])) ?></td>
                                        <td><?= date('M d, Y', strtotime($v['verified_at'])) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="text-center text-muted py-4">No recent verifications</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Loan Applications -->
    <div class="card shadow-lg mt-4">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-file-alt"></i> Recent Loan Applications</h5>
            <a href="loan_management.php" class="btn btn-warning btn-sm">Manage Loans</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-primary">
                        <tr>
                            <th>#</th>
                            <th>Member</th>
                            <th>Amount</th>
                            <th>Term</th>
                            <th>Purpose</th>
                            <th>Status</th>
                            <th>Applied</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($loansList) > 0): ?>
                            <?php foreach ($loansList as $index => $loan): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><strong><?= htmlspecialchars($loan['full_name']) ?></strong></td>
                                <td>GHS <?= number_format($loan['amount'], 2) ?></td>
                                <td><?= $loan['term'] ?> months</td>
                                <td><?= ucfirst($loan['purpose']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $loan['status'] === 'approved' ? 'success' : ($loan['status'] === 'pending' ? 'warning' : 'danger') ?>">
                                        <?= ucfirst($loan['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('M d, Y', strtotime($loan['created_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">No loan applications yet</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mt-5 text-center">
        <h4 class="text-primary mb-4">Quick Actions</h4>
        <div class="row justify-content-center g-3">
            <div class="col-md-3 col-sm-6">
                <a href="verifications.php" class="btn btn-outline-warning btn-lg w-100 py-4 shadow">
                    <i class="fas fa-id-badge fa-2x mb-2"></i><br>
                    Review Verifications
                </a>
            </div>
            <div class="col-md-3 col-sm-6">
                <a href="loan_management.php" class="btn btn-outline-primary btn-lg w-100 py-4 shadow">
                    <i class="fas fa-money-check-alt fa-2x mb-2"></i><br>
                    Manage Loans
                </a>
            </div>
            <div class="col-md-3 col-sm-6">
                <a href="members.php" class="btn btn-outline-success btn-lg w-100 py-4 shadow">
                    <i class="fas fa-users-cog fa-2x mb-2"></i><br>
                    Manage Members
                </a>
            </div>
            <div class="col-md-3 col-sm-6">
                <a href="reports.php" class="btn btn-outline-info btn-lg w-100 py-4 shadow">
                    <i class="fas fa-chart-bar fa-2x mb-2"></i><br>
                    View Reports
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('loanStatusChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($statuses) ?>,
            datasets: [{
                data: <?= json_encode($counts) ?>,
                backgroundColor: <?= json_encode(array_values($colors)) ?>,
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { padding: 20, font: { size: 14 } }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) label += ': ';
                            label += context.parsed + ' applications';
                            return label;
                        }
                    }
                }
            }
        }
    });
</script>

<?php include './includes/admin_footer.php'; ?>
