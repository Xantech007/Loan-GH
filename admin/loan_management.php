<?php
// admin/loan_management.php - Updated CedisPay Admin Loan Management (Matches current tables)

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
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $loan_id = (int)$_GET['id'];

    if ($action === 'approve' || $action === 'reject') {
        $new_status = $action === 'approve' ? 'approved' : 'rejected';

        try {
            $stmt = $pdo->prepare("UPDATE loans SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $loan_id]);

            // Optional: Add notification or log here in future
            $success_message = "Loan application has been " . ucfirst($new_status) . " successfully.";
        } catch (Exception $e) {
            $error_message = "Error updating loan status. Please try again.";
        }
    }
}

// Fetch all loan applications with user details
try {
    $stmt = $pdo->prepare("
        SELECT l.id, l.amount, l.term, l.purpose, l.status, l.created_at,
               u.full_name, u.email, u.phone, u.is_verified
        FROM loans l
        JOIN users u ON l.user_id = u.id
        ORDER BY l.created_at DESC
    ");
    $stmt->execute();
    $loans = $stmt->fetchAll();
} catch (Exception $e) {
    $error_message = "Unable to load loan applications.";
    $loans = [];
}
?>

<div class="main p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary"><i class="fas fa-money-check-alt"></i> Loan Management</h2>
        <div>
            <span class="badge bg-info fs-6"><?= count($loans) ?> Total Applications</span>
        </div>
    </div>

    <p class="lead text-muted">Review, approve, or reject loan applications from members. Only verified members can apply.</p>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error_message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-lg">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-list"></i> All Loan Applications</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Applicant</th>
                            <th>Contact</th>
                            <th>Amount</th>
                            <th>Term</th>
                            <th>Purpose</th>
                            <th>Status</th>
                            <th>Applied On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($loans) > 0): ?>
                            <?php foreach ($loans as $index => $loan): ?>
                                <tr>
                                    <td><strong>#<?= str_pad($loan['id'], 4, '0', STR_PAD_LEFT) ?></strong></td>
                                    <td>
                                        <div>
                                            <strong><?= htmlspecialchars($loan['full_name']) ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars($loan['email']) ?></small>
                                            <?php if ($loan['is_verified']): ?>
                                                <span class="badge bg-success ms-2">Verified</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning ms-2">Unverified</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($loan['phone'] ?? 'N/A') ?></td>
                                    <td><strong>GHS <?= number_format($loan['amount'], 2) ?></strong></td>
                                    <td><?= $loan['term'] ?> months</td>
                                    <td><?= ucfirst($loan['purpose']) ?></td>
                                    <td>
                                        <?php
                                        $status_class = match($loan['status']) {
                                            'approved' => 'bg-success',
                                            'rejected' => 'bg-danger',
                                            'paid' => 'bg-info',
                                            default => 'bg-warning'
                                        };
                                        ?>
                                        <span class="badge <?= $status_class ?> fs-6"><?= ucfirst($loan['status']) ?></span>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($loan['created_at'])) ?></td>
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
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3"></i><br>
                                    No loan applications found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4 text-center">
        <a href="dashboard.php" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>

<?php include './includes/admin_footer.php'; ?>
