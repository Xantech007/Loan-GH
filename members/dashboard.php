<?php
// members/dashboard.php
// Include the database connection
include '../config/db.php';

// Assume user is logged in, fetch user data (replace with actual session check)
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect if not logged in
    exit();
}
$user_id = $_SESSION['user_id'];

// Fetch user details (example query)
$user_query = "SELECT * FROM users WHERE id = $user_id";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);

// Fetch user's loans (example query)
$loans_query = "SELECT * FROM loans WHERE user_id = $user_id";
$loans_result = mysqli_query($conn, $loans_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CedisPay Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: white;
            color: #001f3f; /* Dark blue */
        }
        header {
            background-color: #001f3f;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .nav-buttons {
            display: flex;
            justify-content: center;
            margin: 20px 0;
        }
        .nav-buttons button {
            background-color: #001f3f;
            color: white;
            border: none;
            padding: 10px 20px;
            margin: 0 10px;
            cursor: pointer;
            font-size: 16px;
            border-radius: 5px;
        }
        .nav-buttons button:hover {
            background-color: #004080;
        }
        .section {
            display: none;
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
            background-color: #f9f9f9;
            border: 1px solid #001f3f;
            border-radius: 5px;
        }
        .active {
            display: block;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin: 10px 0 5px;
        }
        input, select {
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #001f3f;
            border-radius: 5px;
        }
        button[type="submit"] {
            background-color: #001f3f;
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            border-radius: 5px;
        }
        button[type="submit"]:hover {
            background-color: #004080;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #001f3f;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #001f3f;
            color: white;
        }
    </style>
</head>
<body>
    <header>
        <h1>CedisPay Dashboard</h1>
        <p>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</p>
    </header>

    <div class="nav-buttons">
        <button onclick="showSection('dashboard')">Dashboard</button>
        <button onclick="showSection('apply-loan')">Apply for Loan</button>
        <button onclick="showSection('loan-history')">Loan History</button>
        <button onclick="showSection('profile')">Profile</button>
        <button onclick="logout()">Logout</button>
    </div>

    <div id="dashboard" class="section active">
        <h2>Your Dashboard</h2>
        <p>Balance: $<?php echo number_format($user['balance'], 2); ?></p>
        <p>Active Loans: <?php echo mysqli_num_rows($loans_result); ?></p>
        <!-- Add more dashboard stats as needed -->
    </div>

    <div id="apply-loan" class="section">
        <h2>Apply for a Loan</h2>
        <form action="process_loan.php" method="POST"> <!-- Replace with actual processing script -->
            <label for="amount">Loan Amount:</label>
            <input type="number" id="amount" name="amount" required>

            <label for="term">Loan Term (months):</label>
            <input type="number" id="term" name="term" required>

            <label for="purpose">Purpose:</label>
            <select id="purpose" name="purpose">
                <option value="personal">Personal</option>
                <option value="business">Business</option>
                <option value="education">Education</option>
            </select>

            <button type="submit">Submit Application</button>
        </form>
    </div>

    <div id="loan-history" class="section">
        <h2>Loan History</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Amount</th>
                    <th>Term</th>
                    <th>Status</th>
                    <th>Date Applied</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($loan = mysqli_fetch_assoc($loans_result)) { ?>
                    <tr>
                        <td><?php echo $loan['id']; ?></td>
                        <td>$<?php echo number_format($loan['amount'], 2); ?></td>
                        <td><?php echo $loan['term']; ?> months</td>
                        <td><?php echo ucfirst($loan['status']); ?></td>
                        <td><?php echo $loan['created_at']; ?></td>
                    </tr>
                <?php } ?>
                <?php mysqli_data_seek($loans_result, 0); // Reset result pointer ?>
            </tbody>
        </table>
    </div>

    <div id="profile" class="section">
        <h2>Your Profile</h2>
        <p>Username: <?php echo htmlspecialchars($user['username']); ?></p>
        <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
        <!-- Add form to update profile if needed -->
    </div>

    <script>
        function showSection(sectionId) {
            document.querySelectorAll('.section').forEach(section => {
                section.classList.remove('active');
            });
            document.getElementById(sectionId).classList.add('active');
        }

        function logout() {
            // Handle logout (e.g., redirect to logout script)
            window.location.href = 'logout.php';
        }
    </script>
</body>
</html>
