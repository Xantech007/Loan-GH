<?php
// admin/member_management.php - Updated CedisPay Member Management (Matches current 'users' table)

session_start();
require '../config/db.php'; // Using PDO for consistency

// Admin authentication
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$pageTitle = "Member Management";
include './includes/admin_header.php';

// Handle Delete Member (with confirmation)
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delete_id = (int)$_GET['id'];
    try {
        // Optional: Prevent deleting self or last admin
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
        $stmt->execute([$delete_id]);
        $success_message = "Member deleted successfully.";
    } catch (Exception $e) {
        $error_message = "Unable to delete member.";
    }
}

// Fetch all members (from users table)
try {
    $stmt = $pdo->prepare("
        SELECT id, full_name, email, phone, balance, is_verified, created_at
        FROM users 
        WHERE role != 'admin'
        ORDER BY created_at DESC
    ");
    $stmt->execute();
    $members = $stmt->fetchAll();
} catch (Exception $e) {
    $error_message = "Unable to load members.";
    $members = [];
}
?>

<div class="main p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary"><i class="fas fa-users-cog"></i> Member Management</h2>
        <div>
            <span class="badge bg-primary fs-6"><?= count($members) ?> Total Members</span>
        </div>
    </div>

    <p class="lead text-muted">View and manage all registered members. You can review their verification status, balance, and account details.</p>

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
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-users"></i> All Members</h5>
            <a href="dashboard.php" class="btn btn-light btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Member</th>
                            <th>Contact</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($members) > 0): ?>
                            <?php foreach ($members as $index => $member): ?>
                                <tr>
                                    <td><strong><?= $index + 1 ?></strong></td>
                                    <td>
                                        <div>
                                            <strong><?= htmlspecialchars($member['full_name']) ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars($member['email']) ?></small>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($member['phone'] ?? 'Not provided') ?></td>
                                    <td><strong>GHS <?= number_format($member['balance'] ?? 0, 2) ?></strong></td>
                                    <td>
                                        <?php if ($member['is_verified']): ?>
                                            <span class="badge bg-success fs-6">Verified</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning fs-6">Unverified</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($member['created_at'])) ?></td>
                                    <td>
                                        <a href="member_details.php?id=<?= $member['id'] ?>" 
                                           class="btn btn-info btn-sm me-1" 
                                           title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="?action=delete&id=<?= $member['id'] ?>" 
                                           class="btn btn-danger btn-sm" 
                                           title="Delete Member"
                                           onclick="return confirm('Are you sure you want to delete this member? This action cannot be undone.')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-users-slash fa-3x mb-3"></i><br>
                                    No members registered yet.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4 text-center">
        <small class="text-muted">
            <i class="fas fa-info-circle"></i> 
            Admins cannot be deleted from this panel for security reasons.
        </small>
    </div>
</div>

<?php include './includes/admin_footer.php'; ?>
