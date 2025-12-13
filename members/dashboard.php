<?php
// members/dashboard.php - Fully Updated for CedisPay (Uses PDO for consistency & security)

session_start();
require '../config/db.php';  // Includes safe $pdo and $conn (with redeclaration protection)

// Redirect to login if not logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // Fetch user details
    $stmt = $pdo->prepare("SELECT username, email, full_name, balance FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        // User not found - force logout
        session_destroy();
        header('Location: ../login.php');
        exit();
    }

    // Fetch user's loans
    $stmt_loans = $pdo->prepare("SELECT * FROM loans WHERE user_id = ? ORDER BY created_at DESC");
    $stmt_loans->execute([$user_id]);
    $loans = $stmt_loans->fetchAll();

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
    <style>
        :root {
            --primary: #001f3f;
            --primary-light: #003366;
            --white: #fff;
            --light: #f8f9fa;
            --gray: #ddd;
            --success: #27ae60;
            --warning: #f39c12;
            --danger: #e74c3c;
        }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--light);
            color: #333;
        }
        header {
            background-color: var(--primary);
            color: var(--white);
            padding: 30px 20px;
            text-align: center;
        }
        header h1 {
            margin: 0;
            font-size: 2.2rem;
        }
        header p {
            margin: 10px 0 0;
            opacity: 0.9;
        }
        .nav-buttons {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
            padding: 20px;
            background-color: var(--white);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .nav-buttons button {
            background-color: var(--primary);
            color: var(--white);
            border: none;
            padding: 12px 24px;
            cursor: pointer;
            font-size: 16px;
            border-radius: 8px;
            transition: 0.3s;
        }
        .nav-buttons button:hover {
            background-color: var(--primary-light);
            transform: translateY(-2px);
        }
        .container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .section {
            display: none;
            background: var(--white);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }
        .section.active {
            display: block;
        }
        h2 {
            color: var(--primary);
            margin-bottom: 20px;
            font-size: 1.8rem;
            border-bottom: 3px solid var(--primary);
            padding-bottom: 10px;
        }
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .stat-card {
            background: var(--primary);
            color: var(--white);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        .stat-card h3 {
            margin: 0 0 10px;
            font-size: 1.1rem;
            opacity: 0.9;
        }
        .stat-card p {
            font-size: 1.8rem;
            margin: 0;
            font-weight: bold;
        }
        form label {
            display: block;
            margin: 15px 0 5px;
            font-weight: 600;
            color: var(--primary);
        }
        form input, form select {
            width: 100%;
            padding: 12px;
            border: 2px solid var(--gray);
            border-radius: 8px;
            font-size: 16px;
        }
        form input:focus, form select:focus {
            outline: none;
            border-color: var(--primary);
        }
        button[type="submit"] {
            width: 100%;
            padding: 15px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 17px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
            transition: 0.3s;
        }
        button[type="submit"]:hover {
            background: var(--primary-light);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--gray);
        }
        th {
            background-color: var(--primary);
            color: white;
            font-weight: 600;
        }
        tr:hover {
            background-color: #f1f5f9;
        }
        .status-pending { color: var(--warning); font-weight: bold; }
        .status-approved { color: var(--success); font-weight: bold; }
        .status-rejected { color: var(--danger); font-weight: bold; }
        .status-paid { color: var(--success); font-weight: bold; }
        .profile-info p {
            font-size: 16px;
            margin: 12px 0;
        }
        .profile-info strong {
            color: var(--primary);
        }
        @media (max-width: 768px) {
            .nav-buttons {
                flex-direction: column;
                align-items: center;
            }
            .nav-buttons button {
                width: 80%;
            }
        }
    </style>
</head>
<body>

    <header>
        <h1>CedisPay</h1>
        <p>Welcome back, <?= htmlspecialchars($user['full_name'] ?? $user['username']) ?>!</p>
    </header>

    <div class="nav-buttons">
        <button onclick="showSection('dashboard')">Dashboard</button>
        <button onclick="showSection('apply-loan')">Apply for Loan</button>
        <button onclick="showSection('loan-history')">Loan History</button>
        <button onclick="showSection('profile')">My Profile</button>
        <button onclick="logout()">Logout</button>
    </div>

    <div class="container">

        <!-- Dashboard Section -->
        <div id="dashboard" class="section active">
            <h2>Dashboard Overview</h2>
            <div class="dashboard-stats">
                <div class="stat-card">
                    <h3>Account Balance</h3>
                    <p>GHS <?= number_format($user['balance'] ?? 0, 2) ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Loans</h3>
                    <p><?= count($loans) ?></p>
                </div>
                <div class="stat-card">
                    <h3>Active Applications</h3>
                    <p><?= count(array_filter($loans, fn($l) => $l['status'] === 'pending')) ?></p>
                </div>
                <div class="stat-card">
                    <h3>Approved Loans</h3>
                    <p><?= count(array_filter($loans, fn($l) => $l['status'] === 'approved')) ?></p>
                </div>
            </div>
        </div>

        <!-- Apply for Loan Section -->
        <div id="apply-loan" class="section">
            <h2>Apply for a New Loan</h2>
            <form action="process_loan.php" method="POST">
                <label for="amount">Loan Amount (GHS)</label>
                <input type="number" id="amount" name="amount" min="100" step="50" required placeholder="e.g. 5000">

                <label for="term">Loan Term (Months)</label>
                <input type="number" id="term" name="term" min="3" max="36" required placeholder="e.g. 12">

                <label for="purpose">Purpose of Loan</label>
                <select id="purpose" name="purpose" required>
                    <option value="">Select purpose</option>
                    <option value="personal">Personal</option>
                    <option value="business">Business Startup/Expansion</option>
                    <option value="education">Education</option>
                    <option value="medical">Medical Expenses</option>
                    <option value="home">Home Improvement</option>
                    <option value="other">Other</option>
                </select>

                <button type="submit">Submit Loan Application</button>
            </form>
        </div>

        <!-- Loan History Section -->
        <div id="loan-history" class="section">
            <h2>Your Loan History</h2>
            <?php if (empty($loans)): ?>
                <p>You have not applied for any loans yet.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
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
                                <td>#<?= str_pad($loan['id'], 5, '0', STR_PAD_LEFT) ?></td>
                                <td>GHS <?= number_format($loan['amount'], 2) ?></td>
                                <td><?= $loan['term'] ?> months</td>
                                <td><?= ucfirst($loan['purpose']) ?></td>
                                <td class="status-<?= $loan['status'] ?>"><?= ucfirst(str_replace('_', ' ', $loan['status'])) ?></td>
                                <td><?= date('M j, Y', strtotime($loan['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Profile Section -->
        <div id="profile" class="section">
            <h2>My Profile</h2>
            <div class="profile-info">
                <p><strong>Full Name:</strong> <?= htmlspecialchars($user['full_name'] ?? 'Not set') ?></p>
                <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                <p><strong>Account Balance:</strong> GHS <?= number_format($user['balance'] ?? 0, 2) ?></p>
                <p><strong>Member Since:</strong> <?= date('F Y') ?> <!-- You can add joined date column if needed --></p>
            </div>
        </div>

    </div>

    <script>
        function showSection(sectionId) {
            document.querySelectorAll('.section').forEach(sec => {
                sec.classList.remove('active');
            });
            document.getElementById(sectionId).classList.add('active');
        }

        function logout() {
            if (confirm('Are you sure you want to log out?')) {
                window.location.href = '../logout.php'; // Create a logout.php to destroy session
            }
        }
    </script>

</body>
</html>
