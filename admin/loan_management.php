<?php
// admin/loan_management.php - Modern CedisPay Admin Loan Management

session_start();
require '../config/db.php'; // Using PDO for consistency

// Admin authentication
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$pageTitle = "Loan Management";
include './includes/admin_header.php';

// Handle Approve / Reject Actions
$message = '';
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $loan_id = (int)$_GET['id'];

    if ($action === 'approve' || $action === 'reject') {
        $new_status = $action === 'approve' ? 'approved' : 'rejected';

        try {
            $update = $pdo->prepare("UPDATE loans SET status = ? WHERE id = ?");
            if ($update->execute([$new_status, $loan_id])) {
                $message = '<div class="alert alert-success">Loan application has been <strong>' . ucfirst($new_status) . '</strong>.</div>';
            } else {
                $message = '<div class="alert alert-danger">Failed to update loan status.</div>';
            }
        } catch (Exception $e) {
            $message = '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}

// Filter by status
$status_filter = $_GET['status'] ?? 'all';
$where = '';
$params = [];
if ($status_filter !== 'all') {
    $where = "WHERE l.status = ?";
    $params[] = $status_filter;
}

// Fetch all loan applications with user details
try {
    $sql = "
        SELECT l.id, l.amount, l.term, l.purpose, l.status, l.created_at,
               u.full_name, u.email, u.phone
        FROM loans l
        JOIN users u ON l.user_id = u.id
        $where
        ORDER BY l.created_at DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $loans = $stmt->fetchAll();
} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}
?>

<div class="main p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary"><i class="fas fa-money-check-alt"></i> Loan Management</h2>
        <div>
            <select class="form-select d-inline-block w-auto" onchange="if(this.value) window.location.href='?status='+this.value">
                <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>All Applications</option>
                <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="approved" <?= $status_filter === 'approved' ? 'selected' : '' ?>>Approved</option>
                <option value="rejected" <?= $status_filter === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                <option value="paid" <?= $status_filter === 'paid' ? 'selected' : '' ?>>Paid</option>
            </select>
        </div>
    </div>

    <p class="lead text-muted">Review, approve, or reject loan applications from members.</p>

    <?= $message ?>

    <div class="card shadow-lg">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-list-ul"></i> Loan Applications (<?= count($loans) ?>)</h5>
        </div>
        <div class="card-body p-0">
            <?php if (count($loans) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Applicant</th>
                                <th>Email / Phone</th>
                                <th>Amount</th>
                                <th>Term</th>
                                <th>Purpose</th>
                                <th>Applied On</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($loans as $index => $loan): ?>
                                <tr>
                                    <td><strong>#<?= str_pad($loan['id'], 5, '0', STR_PAD_LEFT) ?></strong></td>
                                    <td>
                                        <strong><?= htmlspecialchars($loan['full_name']) ?></strong>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($loan['email']) ?><br>
                                        <small class="text-muted"><?= htmlspecialchars($loan['phone'] ?? 'N/A') ?></small>
                                    </td>
                                    <td><strong>GHS <?= number_format($loan['amount'], 2) ?></strong></td>
                                    <td><?= $loan['term'] ?> months</td>
                                    <td><?= ucfirst($loan['purpose']) ?></td>
                                    <td><?= date('M d, Y', strtotime($loan['created_at'])) ?></td>
                                    <td>
                                        <?php
                                        $badgeClass = match($loan['status']) {
                                            'approved' => 'bg-success',
                                            'rejected' => 'bg-danger',
                                            'paid' => 'bg-info',
                                            default => 'bg-warning'
                                        };
                                        ?>
                                        <span class="badge <?= $badgeClass ?> fs-6"><?= ucfirst($loan['status']) ?></span>
                                    </td>
                                    <td>
                                        <?php if ($loan['status'] === 'pending'): ?>
                                            <a href="?action=approve&id=<?= $loan['id'] ?>" 
                                               class="btn btn-success btn-sm me-1" 
                                               onclick="return confirm('Approve this loan application?')">
                                                <i class="fas fa-check"></i> Approve
                                            </a>
                                            <a href="?action=reject&id=<?= $loan['id'] ?>" 
                                               class="btn btn-danger btn-sm" 
                                               onclick="return confirm('Reject this loan application?')">
                                                <i class="fas fa-times"></i> Reject
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">Processed</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                    <p class="text-muted">No loan applications found matching the selected filter.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Stats Summary -->
    <div class="row mt-5 g-4">
        <div class="col-md-3">
            <div class="card text-center shadow-sm border-primary">
                <div class="card-body">
                    <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                    <h5>Pending</h5>
                    <h3><?= $pdo->query("SELECT COUNT(*) FROM loans WHERE status = 'pending'")->fetchColumn() ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm border-success">
                <div class="card-body">
                    <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                    <h5>Approved</h5>
                    <h3><?= $pdo->query("SELECT COUNT(*) FROM loans WHERE status = 'approved'")->fetchColumn() ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm border-danger">
                <div class="card-body">
                    <i class="fas fa-times-circle fa-2x text-danger mb-2"></i>
                    <h5>Rejected</h5>
                    <h3><?= $pdo->query("SELECT COUNT(*) FROM loans WHERE status = 'rejected'")->fetchColumn() ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm border-info">
                <div class="card-body">
                    <i class="fas fa-coins fa-2x text-info mb-2"></i>
                    <h5>Total Approved Amount</h5>
                    <h4>GHS <?= number_format($pdo->query("SELECT COALESCE(SUM(amount),0) FROM loans WHERE status='approved'")->fetchColumn(), 2) ?></h4>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include './includes/admin_footer.php'; ?>
