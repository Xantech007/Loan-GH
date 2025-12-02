<?php
// change_password.php - FINAL & SECURE VERSION (2025)
session_start();

// Security: Must be logged in with new session system
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['member_id'])) {
    header('Location: login.php');
    exit();
}

require '../config/db.php';
$member_id = (int)$_SESSION['member_id'];

$message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $current_password = $_POST['current_password'] ?? '';
    $new_password     = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Basic validation
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $message = "<div class='alert alert-danger'>All fields are required.</div>";
    } elseif ($new_password !== $confirm_password) {
        $message = "<div class='alert alert-danger'>New passwords do not match!</div>";
    } elseif (strlen($new_password) < 8) {
        $message = "<div class='alert alert-danger'>New password must be at least 8 characters long.</div>";
    } else {
        // Fetch the stored hashed password
        $stmt = $conn->prepare("SELECT password FROM members WHERE member_id = ?");
        $stmt->bind_param("i", $member_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows !== 1) {
            $message = "<div class='alert alert-danger'>Account not found.</div>";
        } else {
            $user = $result->fetch_assoc();
            $stored_hash = $user['password'];

            // Verify current password
            if (!password_verify($current_password, $stored_hash)) {
                $message = "<div class='alert alert-danger'>Current password is incorrect!</div>";
            } else {
                // Hash the new password
                $new_hash = password_hash($new_password, PASSWORD_DEFAULT);

                // Update password in members table
                $update = $conn->prepare("UPDATE members SET password = ? WHERE member_id = ?");
                $update->bind_param("si", $new_hash, $member_id);

                if ($update->execute()) {
                    $message = "<div class='alert alert-success'>
                        <strong>Success!</strong> Your password has been changed successfully.
                    </div>";
                } else {
                    $message = "<div class='alert alert-danger'>Error updating password. Please try again.</div>";
                }
                $update->close();
            }
        }
        $stmt->close();
    }
}

$pageTitle = 'Change Password';
include './includes/member_header.php';
?>

<div class="main container-settings">
    <button class="open-btn" onclick="toggleSidebar()">Menu</button>

    <div class="page-header">
        <h2 class="main-header">Change Password</h2>
        <h5>Keep your account secure with a strong password</h5>
    </div>

    <div class="form-container-settings" style="max-width: 600px; margin: 0 auto;">
        <?php echo $message; ?>

        <form action="change_password.php" method="POST" style="background:white; padding:30px; border-radius:12px; box-shadow:0 5px 20px rgba(0,0,0,0.1);">
            <div class="info-grid">
                <div class="info-item">
                    <label for="current_password"><strong>Current Password</strong></label>
                    <input type="password" 
                           name="current_password" 
                           id="current_password" 
                           class="form-control" 
                           required 
                           placeholder="Enter your current password">
                </div>

                <div class="info-item">
                    <label for="new_password"><strong>New Password</strong></label>
                    <input type="password" 
                           name="new_password" 
                           id="new_password" 
                           class="form-control" 
                           required 
                           minlength="8"
                           placeholder="At least 8 characters">
                </div>

                <div class="info-item">
                    <label for="confirm_password"><strong>Confirm New Password</strong></label>
                    <input type="password" 
                           name="confirm_password" 
                           id="confirm_password" 
                           class="form-control" 
                           required 
                           placeholder="Type the new password again">
                </div>
            </div>

            <div style="margin-top: 30px; text-align: center;">
                <button type="submit" class="btn btn-primary" style="padding:12px 40px; font-size:1.1em;">
                    Update Password
                </button>
                <a href="dashboard.php" class="btn btn-secondary" style="margin-left:15px; padding:12px 30px;">
                    Back to Dashboard
                </a>
            </div>

            <div style="margin-top: 20px; font-size: 0.9em; color: #666; text-align:center;">
                <p><strong>Password Requirements:</strong> Minimum 8 characters</p>
            </div>
        </form>
    </div>
</div>

<?php include './includes/member_footer.php'; ?>
