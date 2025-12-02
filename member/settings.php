<?php
// settings.php - FULL PROFILE + EDIT PROFILE (One File, 2025 Final Version)
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['member_id'])) {
    header('Location: login.php');
    exit();
}

require '../config/db.php';
$member_id = (int)$_SESSION['member_id'];
$display_id = "MEM" . str_pad($member_id, 6, "0", STR_PAD_LEFT);

$success = $error = "";
$edit_mode = isset($_GET['edit']) || $_SERVER['REQUEST_METHOD'] === 'POST';

// Fetch current member data
$stmt = $conn->prepare("SELECT * FROM members WHERE member_id = ?");
$stmt->bind_param("i", $member_id);
$stmt->execute();
$member = $stmt->get_result()->fetch_assoc();

if (!$member) {
    session_destroy();
    header('Location: login.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $edit_mode) {
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $dob       = $_POST['dob'] ?? null;
    $gender    = $_POST['gender'] ?? null;
    $address   = trim($_POST['residential_address'] ?? '');

    if (empty($full_name) || empty($email) || empty($phone)) {
        $error = "Full Name, Email, and Phone are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please invalid email.";
    } else {
        $update = $conn->prepare("
            UPDATE members SET 
                full_name = ?, email = ?, phone = ?, 
                dob = ?, gender = ?, residential_address = ?
            WHERE member_id = ?
        ");
        $update->bind_param("ssssssi", $full_name, $email, $phone, $dob, $gender, $address, $member_id);

        if ($update->execute()) {
            $success = "Profile updated successfully!";
            // Refresh member data
            $stmt->execute();
            $member = $stmt->get_result()->fetch_assoc();
        } else {
            $error = "Update failed. Try again.";
        }
    }
}

$member_since = date("F d, Y", strtotime($member['date_registered'] ?? 'now'));
$pageTitle = 'Settings';
include './includes/member_header.php';
?>

<div class="main container-settings">
    <button class="open-btn" onclick="toggleSidebar()">&#9776;</button>

    <div class="page-header">
        <h2 class="main-header">Account Settings</h2>
        <h5>View and update your personal information</h5>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success" style="margin:20px 0; padding:15px; background:#d4edda; color:#155724; border-radius:8px;">
            <?= $success ?>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error" style="margin:20px 0; padding:15px; background:#f8d7da; color:#721c24; border-radius:8px;">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <div class="form-container-settings container-settings-form">
        <form method="POST">
            <div class="profile-header" style="display:flex; align-items:center; gap:25px; margin-bottom:30px; padding-bottom:20px; border-bottom:2px solid #eee;">
                <img src="../assets/avatar.png" alt="Profile" style="width:100px; height:100px; border-radius:50%; border:4px solid #003366;">
                <div style="flex:1;">
                    <h2 style="margin:0; color:#003366;"><?= htmlspecialchars($member['full_name']) ?></h2>
                    <p style="margin:5px 0; font-size:1.1em;">
                        <strong>Member ID:</strong> 
                        <span style="color:#003366; font-weight:bold;"><?= $display_id ?></span>
                    </p>
                    <p style="margin:0; color:#666;">Member since <?= $member_since ?></p>
                </div>
                <?php if (!$edit_mode): ?>
                    <a href="?edit=1" class="btn btn-primary" style="padding:12px 25px;">Edit Profile</a>
                <?php else: ?>
                    <button type="submit" class="btn btn-primary" style="padding:12px 30px;">Save Changes</button>
                    <a href="settings.php" class="btn btn-secondary" style="margin-left:10px; padding:12px 25px; background:#666;">Cancel</a>
                <?php endif; ?>
            </div>

            <!-- Basic Information -->
            <div class="settings-section-member">
                <h3>Basic Information</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Full Name <span style="color:red;">*</span></label>
                        <?php if ($edit_mode): ?>
                            <input type="text" name="full_name" value="<?= htmlspecialchars($member['full_name']) ?>" required>
                        <?php else: ?>
                            <p><?= htmlspecialchars($member['full_name']) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="info-item">
                        <label>Email Address <span style="color:red;">*</span></label>
                        <?php if ($edit_mode): ?>
                            <input type="email" name="email" value="<?= htmlspecialchars($member['email']) ?>" required>
                        <?php else: ?>
                            <p><?= htmlspecialchars($member['email']) ?></p>
                        <?php endif; ?>
                    </div>
                    <?php if ($edit_mode || !empty($member['dob'])): ?>
                    <div class="info-item">
                        <label>Date of Birth</label>
                        <?php if ($edit_mode): ?>
                            <input type="date" name="dob" value="<?= $member['dob'] ?? '' ?>">
                        <?php else: ?>
                            <p><?= $member['dob'] ? date("F d, Y", strtotime($member['dob'])) : 'Not set' ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    <?php if ($edit_mode || !empty($member['gender'])): ?>
                    <div class="info-item">
                        <label>Gender</label>
                        <?php if ($edit_mode): ?>
                            <select name="gender">
                                <option value="">Select</option>
                                <option value="male" <?= ($member['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                                <option value="female" <?= ($member['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                                <option value="other" <?= ($member['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        <?php else: ?>
                            <p><?= ucfirst($member['gender'] ?? 'Not set') ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Contact & Address -->
            <div class="settings-section-member">
                <h3>Contact Information</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Phone Number <span style="color:red;">*</span></label>
                        <?php if ($edit_mode): ?>
                            <input type="text" name="phone" value="<?= htmlspecialchars($member['phone']) ?>" required>
                        <?php else: ?>
                            <p><?= htmlspecialchars($member['phone']) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="info-item fix-address">
                        <label>Residential Address</label>
                        <?php if ($edit_mode): ?>
                            <textarea name="residential_address" rows="3" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; font-family:inherit;"><?= htmlspecialchars($member['residential_address'] ?? '') ?></textarea>
                        <?php else: ?>
                            <p><?= nl2br(htmlspecialchars($member['residential_address'] ?? 'Not provided')) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Account Status -->
            <div class="settings-section-member">
                <h3>Account</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Account Status</label>
                        <p style="color:green; font-weight:bold;">Active</p>
                    </div>
                </div>
                <a href="change_password.php" class="btn btn-primary" style="margin-top:20px; display:inline-block;">
                    Change Password
                </a>
            </div>
        </form>
    </div>
</div>

<?php include './includes/member_footer.php'; ?>
