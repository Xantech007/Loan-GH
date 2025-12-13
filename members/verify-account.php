<?php
// members/verify-account.php - CedisPay Account Verification Page

session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';

try {
    $stmt = $pdo->prepare("SELECT full_name, email, phone, is_verified FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        session_destroy();
        header('Location: ../login.php');
        exit();
    }

    // If already verified, redirect to dashboard
    if ($user['is_verified'] == 1) {
        header('Location: dashboard.php');
        exit();
    }

    // Process form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id_type = trim($_POST['id_type'] ?? '');
        $id_number = trim($_POST['id_number'] ?? '');

        if (empty($id_type) || empty($id_number)) {
            $message = '<div class="alert-error">Please select ID type and enter ID number.</div>';
        } else {
            // In a real system: Save uploaded documents, send to admin for review
            // Here: Auto-approve for demo (or set status to pending_review)

            $update = $pdo->prepare("
                UPDATE users 
                SET is_verified = 1, 
                    loan_max = 20000.00,
                    verification_method = ?,
                    id_number = ?
                WHERE id = ?
            ");
            if ($update->execute([$id_type, $id_number, $user_id])) {
                $_SESSION['verified'] = true;
                $message = '<div class="alert-success">
                    <i class="fas fa-check-circle"></i> 
                    <strong>Congratulations!</strong><br>
                    Your account has been successfully verified.<br>
                    You can now apply for loans up to GHS 20,000.00.<br><br>
                    <a href="dashboard.php" style="background:var(--primary); color:white; padding:12px 30px; border-radius:12px; text-decoration:none; font-weight:600;">
                        Go to Dashboard
                    </a>
                </div>';
            } else {
                $message = '<div class="alert-error">Verification failed. Please try again.</div>';
            }
        }
    }

} catch (Exception $e) {
    $message = '<div class="alert-error">System error. Please try again later.</div>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Account • CedisPay</title>
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
            --shadow: 0 15px 35px rgba(0,31,63,0.15);
            --transition: all 0.4s ease;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: var(--dark);
        }
        .verify-container {
            background: white;
            border-radius: 20px;
            max-width: 600px;
            width: 100%;
            box-shadow: var(--shadow);
            overflow: hidden;
        }
        .verify-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            padding: 40px;
            text-align: center;
        }
        .verify-header i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.9;
        }
        .verify-header h1 {
            font-size: 2.2rem;
            margin-bottom: 15px;
        }
        .verify-header p {
            opacity: 0.9;
            font-size: 1.1rem;
            line-height: 1.6;
        }
        .verify-body {
            padding: 50px 40px;
        }
        .benefits {
            background: #f0f8ff;
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 35px;
            border-left: 5px solid var(--accent);
        }
        .benefits h3 {
            color: var(--primary);
            margin-bottom: 15px;
            font-size: 1.3rem;
        }
        .benefits ul {
            list-style: none;
            padding-left: 0;
        }
        .benefits li {
            padding: 10px 0;
            padding-left: 30px;
            position: relative;
            font-size: 1.05rem;
        }
        .benefits li i {
            position: absolute;
            left: 0;
            top: 12px;
            color: var(--success);
            font-size: 1.2rem;
        }
        .form-group {
            margin-bottom: 25px;
        }
        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--primary);
            font-size: 1.1rem;
        }
        .form-group select, .form-group input {
            width: 100%;
            padding: 16px;
            border: 2px solid #ddd;
            border-radius: 12px;
            font-size: 1.1rem;
            transition: var(--transition);
        }
        .form-group select:focus, .form-group input:focus {
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
            box-shadow: 0 10px 30px rgba(0,31,63,0.3);
        }
        .submit-btn:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0,31,63,0.4);
        }
        .alert-success, .alert-error {
            padding: 20px;
            border-radius: 16px;
            margin: 20px 0;
            text-align: center;
            font-size: 1.1rem;
            line-height: 1.6;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .back-link {
            text-align: center;
            margin-top: 30px;
        }
        .back-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
        @media (max-width: 768px) {
            .verify-header { padding: 30px 20px; }
            .verify-header h1 { font-size: 1.8rem; }
            .verify-body { padding: 40px 25px; }
        }
    </style>
</head>
<body>

<div class="verify-container">
    <div class="verify-header">
        <i class="fas fa-shield-alt"></i>
        <h1>Verify Your Account</h1>
        <p>Complete verification to unlock higher loan limits and full access to CedisPay services.</p>
    </div>

    <div class="verify-body">
        <?= $message ?>

        <?php if (empty($message) || strpos($message, 'alert-error') !== false): ?>
            <div class="benefits">
                <h3>Benefits of Verification</h3>
                <ul>
                    <li><i class="fas fa-check-circle"></i> Increase loan limit up to <strong>GHS 20,000</strong></li>
                    <li><i class="fas fa-check-circle"></i> Faster loan approval process</li>
                    <li><i class="fas fa-check-circle"></i> Access to premium features</li>
                    <li><i class="fas fa-check-circle"></i> Enhanced account security</li>
                </ul>
            </div>

            <form method="POST">
                <div class="form-group">
                    <label for="id_type">Type of Identification</label>
                    <select name="id_type" id="id_type" required>
                        <option value="">Select ID Type</option>
                        <option value="ghana_card">Ghana Card</option>
                        <option value="drivers_license">Driver's License</option>
                        <option value="voters_id">Voter's ID</option>
                        <option value="passport">Passport</option>
                        <option value="ssnit">SSNIT Card</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="id_number">ID Number</label>
                    <input type="text" name="id_number" id="id_number" placeholder="Enter your ID number" required>
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fas fa-verified"></i> Submit for Verification
                </button>
            </form>

            <div class="back-link">
                <a href="dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
