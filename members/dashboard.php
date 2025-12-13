<?php
// members/dashboard.php - Enhanced CedisPay Dashboard with Verification, Dynamic Limits, & Profile Editing

session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$edit_success = $password_success = $error = '';

// Handle Profile Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'edit_profile') {
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        if (empty($full_name) || empty($email) || empty($phone)) {
            $error = "All fields are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format.";
        } else {
            // Check for unique email/phone (excluding current user)
            $check = $pdo->prepare("SELECT id FROM users WHERE (email = ? OR phone = ?) AND id != ?");
            $check->execute([$email, $phone, $user_id]);

            if ($check->rowCount() > 0) {
                $error = "Email or phone already in use.";
            } else {
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?");
                if ($stmt->execute([$full_name, $email, $phone, $user_id])) {
                    $edit_success = "Profile updated successfully!";
                    // Update session if needed
                    $_SESSION['full_name'] = $full_name;
                    $_SESSION['email'] = $email;
                } else {
                    $error = "Failed to update profile. Try again.";
                }
            }
        }
    } elseif ($_POST['action'] === 'change_password') {
        $old_pass = $_POST['old_password'] ?? '';
        $new_pass = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (empty($old_pass) || empty($new_pass) || empty($confirm)) {
            $error = "All password fields are required.";
        } elseif ($new_pass !== $confirm) {
            $error = "New passwords do not match.";
        } elseif (strlen($new_pass) < 8) {
            $error = "New password must be at least 8 characters.";
        } else {
            // Verify old password
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user_pass = $stmt->fetchColumn();

            if (!password_verify($old_pass, $user_pass)) {
                $error = "Incorrect old password.";
            } else {
                $hash = password_hash($new_pass, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                if ($stmt->execute([$hash, $user_id])) {
                    $password_success = "Password changed successfully!";
                } else {
                    $error = "Failed to change password. Try again.";
                }
            }
        }
    }
}

try {
    // Fetch user with verification_status
    $stmt = $pdo->prepare("SELECT username, email, full_name, phone, balance, verification_status FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        session_destroy();
        header('Location: ../login.php');
        exit();
    }

    // Fetch loan limits (default if not set)
    $stmt_limits = $pdo->prepare("SELECT min_amount, max_amount FROM user_loan_limits WHERE user_id = ? LIMIT 1");
    $stmt_limits->execute([$user_id]);
    $limits = $stmt_limits->fetch();

    if (!$limits) {
        // Insert defaults if none
        $pdo->prepare("INSERT INTO user_loan_limits (user_id) VALUES (?)")->execute([$user_id]);
        $limits = ['min_amount' => 500.00, 'max_amount' => 10000.00];
    }

    // Fetch loans (same as before)
    $stmt_loans = $pdo->prepare("
        SELECT *, 
               CASE 
                   WHEN status = 'pending' THEN 1
                   WHEN status = 'approved' THEN 2
                   WHEN status = 'rejected' THEN 3
                   WHEN status = 'paid' THEN 4
                   ELSE 5 
               END as status_order
        FROM loans 
        WHERE user_id = ? 
        ORDER BY status_order, created_at DESC
    ");
    $stmt_loans->execute([$user_id]);
    $loans = $stmt_loans->fetchAll();

    // Stats (same)
    $total_loans = count($loans);
    $pending = count(array_filter($loans, fn($l) => $l['status'] === 'pending'));
    $approved = count(array_filter($loans, fn($l) => $l['status'] === 'approved'));

} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CedisPay • Member Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* [Previous styles remain the same, adding a few for new elements] */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 12px;
            text-align: center;
        }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }
        .verify-btn {
            display: block;
            max-width: 300px;
            margin: 30px auto;
            padding: 18px;
            background: linear-gradient(135deg, var(--warning), #d39e00);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
            transition: var(--transition);
            box-shadow: 0 8px 25px rgba(243,156,18,0.3);
        }
        .verify-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(243,156,18,0.4);
        }
        .locked-section {
            text-align: center;
            padding: 50px;
            background: white;
            border-radius: 16px;
            box-shadow: var(--shadow);
        }
        .locked-section i {
            font-size: 4rem;
            color: var(--danger);
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <header>
        <h1>CedisPay</h1>
        <p>Hello, <?= htmlspecialchars($user['full_name'] ?? $user['username'] ?? 'Member') ?>! Welcome back</p>
    </header>

    <div class="nav-tabs">
        <button class="active" onclick="showSection('dashboard')">Dashboard</button>
        <button onclick="showSection('apply-loan')">Apply Loan</button>
        <button onclick="showSection('loan-history')">Loan History</button>
        <button onclick="showSection('profile')">Profile</button>
        <button onclick="logout()">Logout</button>
    </div>

    <div class="container">

        <!-- Dashboard Overview -->
        <div id="dashboard" class="section active">
            <div class="cards-grid">
                <div class="card balance">
                    <i class="fas fa-wallet"></i>
                    <h3>Account Balance</h3>
                    <p>GHS <?= number_format($user['balance'] ?? 0, 2) ?></p>
                </div>
                <div class="card loans">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <h3>Total Loans Applied</h3>
                    <p><?= $total_loans ?></p>
                </div>
                <div class="card pending">
                    <i class="fas fa-clock"></i>
                    <h3>Pending Applications</h3>
                    <p><?= $pending ?></p>
                </div>
                <div class="card approved">
                    <i class="fas fa-check-circle"></i>
                    <h3>Approved Loans</h3>
                    <p><?= $approved ?></p>
                </div>
            </div>

            <!-- Dynamic Verify Button -->
            <?php if ($user['verification_status'] === 'unverified'): ?>
                <a href="../verify_account.php" class="verify-btn">
                    <i class="fas fa-shield-alt"></i> Verify Your Account Now
                </a>
            <?php endif; ?>
        </div>

        <!-- Apply Loan (Locked if not verified) -->
        <div id="apply-loan" class="section">
            <?php if ($user['verification_status'] !== 'verified'): ?>
                <div class="locked-section">
                    <i class="fas fa-lock"></i>
                    <h2>Account Verification Required</h2>
                    <p>You must verify your account to apply for loans. Please complete verification first.</p>
                    <a href="../verify_account.php" class="submit-btn" style="max-width:300px; margin:20px auto 0;">
                        Verify Now
                    </a>
                </div>
            <?php else: ?>
                <div class="loan-form">
                    <h2 style="text-align:center; margin-bottom:30px; color:var(--primary);">Apply for a New Loan</h2>
                    <p style="text-align:center; margin-bottom:20px; font-weight:600;">
                        Your Loan Limits: GHS <?= number_format($limits['min_amount'], 2) ?> - GHS <?= number_format($limits['max_amount'], 2) ?>
                    </p>
                    <form action="process_loan.php" method="POST">
                        <div class="form-group">
                            <label>Loan Amount (GHS)</label>
                            <input type="number" name="amount" min="<?= $limits['min_amount'] ?>" max="<?= $limits['max_amount'] ?>" step="100" required placeholder="e.g. 5000">
                        </div>
                        <div class="form-group">
                            <label>Loan Term (Months)</label>
                            <input type="number" name="term" min="3" max="36" required placeholder="e.g. 12">
                        </div>
                        <div class="form-group">
                            <label>Purpose of Loan</label>
                            <select name="purpose" required>
                                <option value="">Select purpose</option>
                                <option value="personal">Personal</option>
                                <option value="business">Business</option>
                                <option value="education">Education</option>
                                <option value="medical">Medical</option>
                                <option value="home">Home Improvement</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <button type="submit" class="submit-btn">
                            <i class="fas fa-paper-plane"></i> Submit Application
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <!-- Loan History (same as before) -->
        <div id="loan-history" class="section">
            <h2 style="text-align:center; margin-bottom:30px; color:var(--primary);">Your Loan History</h2>
            <?php if (empty($loans)): ?>
                <div class="card" style="text-align:center; padding:50px;">
                    <i class="fas fa-inbox" style="font-size:4rem; color:#ccc; margin-bottom:20px;"></i>
                    <p>No loan applications yet. Apply for your first loan today!</p>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Loan ID</th>
                                <th>Amount</th>
                                <th>Term</th>
                                <th>Purpose</th>
                                <th>Status</th>
                                <th>Applied On</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($loans as $loan): ?>
                            <tr>
                                <td><strong>#<?= str_pad($loan['id'], 6, '0', STR_PAD_LEFT) ?></strong></td>
                                <td>GHS <?= number_format($loan['amount'], 2) ?></td>
                                <td><?= $loan['term'] ?> months</td>
                                <td><?= ucfirst($loan['purpose']) ?></td>
                                <td><span class="status <?= $loan['status'] ?>"><?= ucfirst(str_replace('_', ' ', $loan['status'])) ?></span></td>
                                <td><?= date('M d, Y', strtotime($loan['created_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Enhanced Profile with Edit Forms -->
        <div id="profile" class="section">
            <div class="profile-card">
                <div class="profile-avatar">
                    <?= strtoupper(substr($user['full_name'] ?? $user['username'] ?? 'M', 0, 2)) ?>
                </div>
                <h2 style="color:var(--primary); margin-bottom:20px;"><?= htmlspecialchars($user['full_name'] ?? $user['username'] ?? 'Member') ?></h2>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if ($edit_success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($edit_success) ?></div>
                <?php endif; ?>
                <?php if ($password_success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($password_success) ?></div>
                <?php endif; ?>

                <!-- Edit Profile Form -->
                <h3 style="margin:30px 0 15px; color:var(--primary);">Edit Profile</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="edit_profile">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
                    </div>
                    <button type="submit" class="submit-btn">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </form>

                <!-- Change Password Form -->
                <h3 style="margin:40px 0 15px; color:var(--primary);">Change Password</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="change_password">
                    <div class="form-group">
                        <label>Old Password</label>
                        <input type="password" name="old_password" required>
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" minlength="8" required>
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" minlength="8" required>
                    </div>
                    <button type="submit" class="submit-btn">
                        <i class="fas fa-key"></i> Update Password
                    </button>
                </form>
            </div>
        </div>

    </div>

    <script>
        // [Previous script remains the same]
    </script>

</body>
</html>
