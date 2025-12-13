<?php
// members/dashboard.php - Updated Verification Status: 0 = Not Verified, 1 = Pending, 2 = Verified

session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';

try {
    // Fetch user data
    $stmt = $pdo->prepare("
        SELECT username, email, full_name, phone, balance, is_verified,
               loan_min, loan_max, created_at
        FROM users
        WHERE id = ?
        LIMIT 1
    ");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        session_destroy();
        header('Location: ../login.php');
        exit();
    }

    // Verification status mapping
    $verification_status = match ((int)$user['is_verified']) {
        0 => ['label' => 'Not Verified', 'badge' => 'bg-danger', 'icon' => 'fa-times-circle'],
        1 => ['label' => 'Pending Verification', 'badge' => 'bg-warning', 'icon' => 'fa-clock'],
        2 => ['label' => 'Verified', 'badge' => 'bg-success', 'icon' => 'fa-check-circle'],
        default => ['label' => 'Unknown', 'badge' => 'bg-secondary', 'icon' => 'fa-question-circle']
    };

    // Loan access: Only fully verified (2) users can apply
    $can_apply_loan = ($user['is_verified'] == 2);

    // Dynamic loan limits - Full limit only when verified (2)
    $loan_min = $user['loan_min'] ?? 500.00;
    $loan_max = $user['loan_max'] ?? ($user['is_verified'] == 2 ? 20000.00 : 5000.00);

    // Handle Profile Update
    if (isset($_POST['update_profile'])) {
        $full_name = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        if (!empty($full_name)) {
            $update = $pdo->prepare("UPDATE users SET full_name = ?, phone = ? WHERE id = ?");
            if ($update->execute([$full_name, $phone, $user_id])) {
                $message .= '<div class="alert-success">Profile updated successfully!</div>';
                $user['full_name'] = $full_name;
                $user['phone'] = $phone;
            } else {
                $message .= '<div class="alert-error">Failed to update profile.</div>';
            }
        }
    }

    // Handle Password Change
    if (isset($_POST['change_password'])) {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_new'] ?? '';

        $pwd_stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $pwd_stmt->execute([$user_id]);
        $hash_row = $pwd_stmt->fetch();

        if (password_verify($current, $hash_row['password'])) {
            if ($new === $confirm && strlen($new) >= 8) {
                $new_hash = password_hash($new, PASSWORD_DEFAULT);
                $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                if ($update->execute([$new_hash, $user_id])) {
                    $message .= '<div class="alert-success">Password changed successfully!</div>';
                } else {
                    $message .= '<div class="alert-error">Failed to change password.</div>';
                }
            } else {
                $message .= '<div class="alert-error">New passwords do not match or are too short (min 8 characters).</div>';
            }
        } else {
            $message .= '<div class="alert-error">Current password is incorrect.</div>';
        }
    }

    // Fetch loans
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

    $total_loans = count($loans);
    $pending = count(array_filter($loans, fn($l) => $l['status'] === 'pending'));
    $approved = count(array_filter($loans, fn($l) => $l['status'] === 'approved'));

} catch (Exception $e) {
    die("Database error. Please try again later.");
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
        :root {
            --primary: #001f3f;
            --primary-light: #003366;
            --accent: #00aaff;
            --success: #27ae60;
            --warning: #f39c12;
            --danger: #e74c3c;
            --gray: #f8f9fa;
            --dark: #2c3e50;
            --light: #ecf0f1;
            --shadow: 0 10px 30px rgba(0,31,63,0.15);
            --transition: all 0.3s ease;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
            min-height: 100vh;
            color: var(--dark);
        }
        header {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            padding: 30px 20px;
            text-align: center;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
        }
        header::after {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="M0,0 L100,20 L100,100 L0,80 Z" fill="rgba(255,255,255,0.05)"/></svg>');
            background-size: cover;
        }
        header h1 { font-size: 2.8rem; font-weight: 700; margin-bottom: 8px; position: relative; z-index: 1; }
        header p { font-size: 1.2rem; opacity: 0.9; position: relative; z-index: 1; }
        .nav-tabs {
            display: flex;
            justify-content: center;
            background: white;
            padding: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            flex-wrap: wrap;
            gap: 8px;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .nav-tabs button {
            padding: 14px 28px;
            background: transparent;
            border: none;
            font-size: 1rem;
            font-weight: 600;
            color: var(--dark);
            cursor: pointer;
            border-radius: 50px;
            transition: var(--transition);
            min-width: 140px;
        }
        .nav-tabs button.active, .nav-tabs button:hover {
            background: var(--primary);
            color: white;
            box-shadow: 0 5px 15px rgba(0,31,63,0.3);
        }
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .section { display: none; animation: fadeIn 0.6s ease; }
        .section.active { display: block; }
        @keyframes fadeIn { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        .card {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }
        .card:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0,31,63,0.2); }
        .card i { font-size: 2.8rem; margin-bottom: 15px; opacity: 0.9; }
        .card.balance i { color: var(--accent); }
        .card.loans i { color: var(--success); }
        .card.pending i { color: var(--warning); }
        .card.approved i { color: var(--success); }
        .card h3 { font-size: 1.1rem; margin-bottom: 10px; color: #555; }
        .card p { font-size: 2.2rem; font-weight: 700; color: var(--dark); }
        .loan-form {
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: var(--shadow);
            max-width: 700px;
            margin: 0 auto;
        }
        .form-group { margin-bottom: 25px; }
        .form-group label { display: block; margin-bottom: 10px; font-weight: 600; color: var(--primary); }
        .form-group input, .form-group select {
            width: 100%;
            padding: 16px;
            border: 2px solid #ddd;
            border-radius: 12px;
            font-size: 1.1rem;
            transition: var(--transition);
        }
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(0,31,63,0.1);
        }
        .submit-btn {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: 0 8px 25px rgba(0,31,63,0.3);
        }
        .submit-btn:hover { transform: translateY(-3px); box-shadow: 0 15px 35px rgba(0,31,63,0.4); }
        .limits-table {
            width: 100%;
            margin: 30px 0;
            border-collapse: collapse;
            background: #f8fbff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }
        .limits-table th, .limits-table td {
            padding: 20px;
            text-align: center;
        }
        .limits-table th {
            background: var(--primary);
            color: white;
            font-size: 1.1rem;
        }
        .limits-table td {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--primary);
        }
        .verify-card {
            background: #fff3cd;
            border-radius: 16px;
            padding: 40px;
            text-align: center;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }
        .verify-link-btn {
            display: inline-block;
            background: var(--warning);
            color: white;
            padding: 16px 40px;
            border-radius: 12px;
            font-size: 1.2rem;
            font-weight: 600;
            text-decoration: none;
            margin-top: 20px;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }
        .verify-link-btn:hover {
            background: #e67e22;
            transform: translateY(-3px);
        }
        .pending-card {
            background: #fffbe6;
            border-left: 5px solid var(--warning);
            border-radius: 16px;
            padding: 30px;
            text-align: center;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }
        .table-container {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }
        table { width: 100%; border-collapse: collapse; }
        th { background: var(--primary); color: white; padding: 20px; text-align: left; font-weight: 600; }
        td { padding: 18px 20px; border-bottom: 1px solid #eee; }
        tr:hover { background: #f8fbff; }
        .status { padding: 8px 16px; border-radius: 50px; font-size: 0.9rem; font-weight: 600; text-transform: capitalize; }
        .status.pending { background: #fff3cd; color: #856404; }
        .status.approved { background: #d4edda; color: #155724; }
        .status.rejected { background: #f8d7da; color: #721c24; }
        .status.paid { background: #d1ecf1; color: #0c5460; }
        .profile-card {
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: var(--shadow);
            max-width: 700px;
            margin: 0 auto;
            text-align: center;
        }
        .profile-avatar {
            width: 120px;
            height: 120px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            margin: 0 auto 20px;
            font-weight: bold;
        }
        .profile-info p {
            font-size: 1.2rem;
            margin: 15px 0;
            color: #555;
        }
        .profile-info strong { color: var(--primary); }
        .alert-success, .alert-error {
            padding: 15px;
            border-radius: 12px;
            margin: 20px 0;
            text-align: center;
            font-weight: 500;
        }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .edit-form h3 {
            color: var(--primary);
            margin: 40px 0 20px;
            text-align: left;
        }
        @media (max-width: 768px) {
            header h1 { font-size: 2.2rem; }
            .nav-tabs button { padding: 12px 20px; font-size: 0.95rem; min-width: 120px; }
            .cards-grid { grid-template-columns: 1fr; }
            .loan-form, .profile-card { padding: 30px; }
            th, td { padding: 14px; font-size: 0.95rem; }
            .limits-table td { font-size: 1.3rem; }
        }
    </style>
</head>
<body>
    <header>
        <h1>CedisPay</h1>
        <p>Hello, <?= htmlspecialchars($user['full_name'] ?? $user['username']) ?>! Welcome back</p>
    </header>

    <div class="nav-tabs">
        <button class="active" onclick="showSection('dashboard')">Dashboard</button>
        <button onclick="showSection('apply-loan')">Apply Loan</button>
        <button onclick="showSection('loan-history')">Loan History</button>
        <button onclick="showSection('profile')">Profile</button>
        <button onclick="logout()">Logout</button>
    </div>

    <div class="container">
        <?= $message ?>

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
        </div>

        <!-- Apply Loan -->
        <div id="apply-loan" class="section">
            <div class="loan-form">
                <h2 style="text-align:center; margin-bottom:30px; color:var(--primary);">Apply for a New Loan</h2>

                <?php if (!$can_apply_loan): ?>
                    <?php if ($user['is_verified'] == 0): ?>
                        <div class="verify-card">
                            <i class="fas fa-shield-alt" style="font-size:4rem; color:#856404; margin-bottom:20px;"></i>
                            <h3 style="color:#856404; margin-bottom:15px;">Account Verification Required</h3>
                            <p style="font-size:1.1rem; margin-bottom:20px;">
                                To apply for loans and unlock higher limits (up to GHS 20,000),
                                you must complete the verification process.
                            </p>
                            <a href="verify-account.php" class="verify-link-btn">
                                <i class="fas fa-check-circle"></i> Start Verification
                            </a>
                        </div>
                    <?php elseif ($user['is_verified'] == 1): ?>
                        <div class="pending-card">
                            <i class="fas fa-clock" style="font-size:4rem; color:#f39c12; margin-bottom:20px;"></i>
                            <h3 style="color:#f39c12; margin-bottom:15px;">Verification Pending</h3>
                            <p style="font-size:1.1rem; margin-bottom:20px;">
                                Your verification documents are under review.<br>
                                You will be notified once approved. This usually takes 24-48 hours.
                            </p>
                            <p class="text-muted"><small>Current max limit: GHS 5,000</small></p>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <table class="limits-table">
                        <thead>
                            <tr>
                                <th>Minimum Loan Amount</th>
                                <th>Maximum Loan Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>GHS <?= number_format($loan_min, 2) ?></td>
                                <td>GHS <?= number_format($loan_max, 2) ?></td>
                            </tr>
                        </tbody>
                    </table>

                    <form action="process_loan.php" method="POST">
                        <div class="form-group">
                            <label>Loan Amount (GHS)</label>
                            <input type="number" name="amount" min="<?= $loan_min ?>" max="<?= $loan_max ?>" step="100" required placeholder="e.g. 5000">
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
                <?php endif; ?>
            </div>
        </div>

        <!-- Loan History -->
        <div id="loan-history" class="section">
            <h2 style="text-align:center; margin-bottom:30px; color:var(--primary);">Your Loan History</h2>
            <?php if (empty($loans)): ?>
                <div class="card" style="text-align:center; padding:50px;">
                    <i class="fas fa-inbox" style="font-size:4rem; color:#ccc; margin-bottom:20px;"></i>
                    <p>No loan applications yet. 
                        <?= $can_apply_loan ? 'Apply for your first loan today!' : 'Complete verification to get started.' ?>
                    </p>
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

        <!-- Profile -->
        <div id="profile" class="section">
            <div class="profile-card">
                <div class="profile-avatar">
                    <?= strtoupper(substr($user['full_name'] ?? $user['username'], 0, 2)) ?>
                </div>
                <h2 style="color:var(--primary); margin-bottom:20px;"><?= htmlspecialchars($user['full_name'] ?? $user['username']) ?></h2>

                <p style="background:<?= $verification_status['badge'] === 'bg-success' ? '#d4edda' : ($verification_status['badge'] === 'bg-warning' ? '#fffbe6' : '#f8d7da') ?>; 
                         color:<?= $verification_status['badge'] === 'bg-success' ? '#155724' : ($verification_status['badge'] === 'bg-warning' ? '#856404' : '#721c24') ?>; 
                         padding:12px 20px; border-radius:50px; display:inline-block; font-weight:600;">
                    <i class="fas <?= $verification_status['icon'] ?>"></i> <?= $verification_status['label'] ?>
                </p>

                <div class="profile-info">
                    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                    <p><strong>Phone:</strong> <?= htmlspecialchars($user['phone'] ?? 'Not set') ?></p>
                    <p><strong>Balance:</strong> GHS <?= number_format($user['balance'] ?? 0, 2) ?></p>
                    <p><strong>Loan Limit:</strong> GHS <?= number_format($loan_min, 2) ?> - GHS <?= number_format($loan_max, 2) ?></p>
                    <p><strong>Total Loans:</strong> <?= $total_loans ?></p>
                    <p><strong>Member Since:</strong> <?= date('F Y', strtotime($user['created_at'] ?? 'now')) ?></p>
                </div>

                <!-- Update Profile -->
                <div class="edit-form">
                    <h3>Update Profile</h3>
                    <form method="POST">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                        </div>
                        <button type="submit" name="update_profile" class="submit-btn">Update Profile</button>
                    </form>
                </div>

                <!-- Change Password -->
                <div class="edit-form">
                    <h3>Change Password</h3>
                    <form method="POST">
                        <div class="form-group">
                            <label>Current Password</label>
                            <input type="password" name="current_password" required>
                        </div>
                        <div class="form-group">
                            <label>New Password (min 8 characters)</label>
                            <input type="password" name="new_password" minlength="8" required>
                        </div>
                        <div class="form-group">
                            <label>Confirm New Password</label>
                            <input type="password" name="confirm_new" minlength="8" required>
                        </div>
                        <button type="submit" name="change_password" class="submit-btn">Change Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showSection(id) {
            document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
            document.querySelectorAll('.nav-tabs button').forEach(b => b.classList.remove('active'));
            document.getElementById(id).classList.add('active');
            document.querySelector(`button[onclick="showSection('${id}')"]`).classList.add('active');
        }

        function logout() {
            if (confirm('Are you sure you want to log out?')) {
                window.location.href = '../logout.php';
            }
        }
    </script>
</body>
</html>
