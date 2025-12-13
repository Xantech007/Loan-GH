<?php
// members/dashboard.php - Modern & Stylish CedisPay Dashboard
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT username, email, full_name, balance FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        session_destroy();
        header('Location: ../login.php');
        exit();
    }

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

    // Stats
    $total_loans = count($loans);
    $pending = count(array_filter($loans, fn($l) => $l['status'] === 'pending'));
    $approved = count(array_filter($loans, fn($l) => $l['status'] === 'approved'));
    $total_applied = array_sum(array_column($loans, 'amount'));

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
        header h1 {
            font-size: 2.8rem;
            font-weight: 700;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }
        header p {
            font-size: 1.2rem;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }
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
        .section {
            display: none;
            animation: fadeIn 0.6s ease;
        }
        .section.active { display: block; }
        @keyframes fadeIn { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }

        /* Cards Grid */
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
            position: relative;
            overflow: hidden;
        }
        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,31,63,0.2);
        }
        .card i {
            font-size: 2.8rem;
            margin-bottom: 15px;
            opacity: 0.9;
        }
        .card.balance i { color: var(--accent); }
        .card.loans i { color: var(--success); }
        .card.pending i { color: var(--warning); }
        .card.approved i { color: var(--success); }
        .card h3 {
            font-size: 1.1rem;
            margin-bottom: 10px;
            color: #555;
        }
        .card p {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--dark);
        }

        /* Loan Form */
        .loan-form {
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: var(--shadow);
            max-width: 600px;
            margin: 0 auto;
        }
        .form-group {
            margin-bottom: 25px;
        }
        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--primary);
        }
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
        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0,31,63,0.4);
        }

        /* Loan History Table */
        .table-container {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: var(--primary);
            color: white;
            padding: 20px;
            text-align: left;
            font-weight: 600;
        }
        td {
            padding: 18px 20px;
            border-bottom: 1px solid #eee;
        }
        tr:hover {
            background: #f8fbff;
        }
        .status {
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: capitalize;
        }
        .status.pending { background: #fff3cd; color: #856404; }
        .status.approved { background: #d4edda; color: #155724; }
        .status.rejected { background: #f8d7da; color: #721c24; }
        .status.paid { background: #d1ecf1; color: #0c5460; }

        /* Profile */
        .profile-card {
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: var(--shadow);
            max-width: 600px;
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
        .profile-info strong {
            color: var(--primary);
        }

        /* Responsive */
        @media (max-width: 768px) {
            header h1 { font-size: 2.2rem; }
            .nav-tabs button { padding: 12px 20px; font-size: 0.95rem; min-width: 120px; }
            .cards-grid { grid-template-columns: 1fr; }
            .loan-form, .profile-card { padding: 30px; }
            th, td { padding: 14px; font-size: 0.95rem; }
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
                <form action="process_loan.php" method="POST">
                    <div class="form-group">
                        <label>Loan Amount (GHS)</label>
                        <input type="number" name="amount" min="500" step="100" required placeholder="e.g. 5000">
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
        </div>

        <!-- Loan History -->
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

        <!-- Profile -->
        <div id="profile" class="section">
            <div class="profile-card">
                <div class="profile-avatar">
                    <?= strtoupper(substr($user['full_name'] ?? $user['username'], 0, 2)) ?>
                </div>
                <h2 style="color:var(--primary); margin-bottom:20px;"><?= htmlspecialchars($user['full_name'] ?? $user['username']) ?></h2>
                <div class="profile-info">
                    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                    <p><strong>Balance:</strong> GHS <?= number_format($user['balance'] ?? 0, 2) ?></p>
                    <p><strong>Total Loans:</strong> <?= $total_loans ?></p>
                    <p><strong>Member Since:</strong> December 2025</p>
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
