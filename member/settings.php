<?php
// settings.php - FULLY WORKING VERSION (2025)
session_start();

// === 1. Security Check - Must be logged in with new session system ===
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['member_id'])) {
    header('Location: login.php');
    exit();
}

require '../config/db.php'; // Database connection

$member_id = (int)$_SESSION['member_id']; // This is the real auto-increment ID (e.g. 7)

// === 2. Fetch real member data from 'members' table ===
$stmt = $conn->prepare("
    SELECT full_name, email, phone, 
           date_registered,
           -- Add these columns if they exist in your table:
           dob, gender, residential_address, status
    FROM members 
    WHERE member_id = ?
");
$stmt->bind_param("i", $member_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    session_destroy();
    header('Location: login.php');
    exit();
}

$member = $result->fetch_assoc();
$stmt->close();

// Format Member ID as MEM000001
$display_id = "MEM" . str_pad($member_id, 6, "0", STR_PAD_LEFT);

$pageTitle = 'Settings';
include './includes/member_header.php';
?>

<div class="main container-settings">
    <button class="open-btn" onclick="toggleSidebar()">&#9776;</button>

    <div class="page-header">
        <h2 class="main-header">Account Settings</h2>
        <h5>Manage your account preferences, security settings, and personal information.</h5>
    </div>

    <div class="form-container-settings container-settings-form">
        <form action="#" method="POST">
            <div class="profile-header">
                <img src="../assets/avatar.png" alt="Profile Picture" style="width:80px; height:80px; border-radius:50%;">
                <div>
                    <h2><?= htmlspecialchars($member['full_name']) ?></h2>
                    <p><strong>Member ID:</strong> <?= $display_id ?></p>
                </div>
            </div>

            <!-- Basic Information -->
            <div class="settings-section-member">
                <h3>Basic Information</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Full Name</label>
                        <input type="text" value="<?= htmlspecialchars($member['full_name']) ?>" readonly>
                    </div>
                    <div class="info-item">
                        <label>Email Address</label>
                        <input type="email" value="<?= htmlspecialchars($member['email']) ?>" readonly>
                    </div>
                    <?php if (!empty($member['dob'])): ?>
                    <div class="info-item">
                        <label>Date of Birth</label>
                        <input type="date" value="<?= htmlspecialchars($member['dob']) ?>" readonly>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($member['gender'])): ?>
                    <div class="info-item">
                        <label>Gender</label>
                        <input type="text" value="<?= htmlspecialchars(ucfirst($member['gender'])) ?>" readonly>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="settings-section-member">
                <h3>Contact Information</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Phone Number</label>
                        <input type="text" value="<?= htmlspecialchars($member['phone']) ?>" readonly>
                    </div>
                    <?php if (!empty($member['residential_address'])): ?>
                    <div class="info-item fix-address">
                        <label>Residential Address</label>
                        <input type="text" value="<?= htmlspecialchars($member['residential_address']) ?>" readonly>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Account Status -->
            <div class="settings-section-member">
                <h3>Account Status</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Membership Status</label>
                        <input type="text" 
                               value="<?= htmlspecialchars($member['status'] ?? 'Active') ?>" 
                               style="color:green; font-weight:bold;" readonly>
                    </div>
                    <div class="info-item">
                        <label>Member Since</label>
                        <input type="text" 
                               value="<?= date("M d, Y", strtotime($member['date_registered'])) ?>" readonly>
                    </div>
                </div>

                <div style="margin-top: 25px;">
                    <a href="change_password.php" class="btn btn-primary">
                        Change Password
                    </a>
                    <a href="edit_profile.php" class="btn btn-secondary" style="margin-left:10px; background:#666;">
                        Edit Profile (Coming Soon)
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include './includes/member_footer.php'; ?>
