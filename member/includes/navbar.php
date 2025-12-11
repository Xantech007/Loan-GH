<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}
include '../../config/db.php'; // Adjust path if needed

// Fetch user name for navbar
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT full_name FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$full_name = $user['full_name'] ?? 'User';
?>

<div class="navbar">
    <div class="logo">CedisPay</div>
    <div class="user-info">
        <span>Welcome, <?php echo htmlspecialchars($full_name); ?></span>
        <a href="logout.php" class="btn">Logout</a>
    </div>
</div>
