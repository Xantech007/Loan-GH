<?php
// members/verify-account.php - Enhanced with Selfie + ID Front/Back Upload

session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';

// Create uploads directory if not exists
$upload_dir = 'uploads/verification/' . $user_id . '/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

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

        $required_uploads = ['selfie', 'id_front', 'id_back'];
        $uploaded = true;
        $files_saved = [];

        foreach ($required_uploads as $field) {
            if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
                $uploaded = false;
                break;
            }

            $file = $_FILES[$field];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png'];
            if (!in_array($ext, $allowed)) {
                $uploaded = false;
                $message = '<div class="alert-error">Only JPG, JPEG, or PNG files are allowed.</div>';
                break;
            }

            $new_name = $field . '.' . $ext;
            $destination = $upload_dir . $new_name;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $files_saved[$field] = $destination;
            } else {
                $uploaded = false;
                $message = '<div class="alert-error">Failed to upload one or more files. Please try again.</div>';
                break;
            }
        }

        if ($uploaded && !empty($id_type) && !empty($id_number)) {
            // Save verification details (in real app: set to pending admin review)
            $update = $pdo->prepare("
                UPDATE users 
                SET is_verified = 1,
                    loan_max = 20000.00,
                    verification_method = ?,
                    id_number = ?,
                    verification_status = 'approved',
                    verified_at = NOW()
                WHERE id = ?
            ");

            if ($update->execute([$id_type, $id_number, $user_id])) {
                $message = '<div class="alert-success">
                    <i class="fas fa-check-circle"></i> 
                    <strong>Congratulations!</strong><br>
                    Your account has been successfully verified.<br>
                    You can now apply for loans up to <strong>GHS 20,000.00</strong>.<br><br>
                    <a href="dashboard.php" class="submit-btn" style="display:inline-block; text-decoration:none; padding:16px 40px; margin-top:10px;">
                        Go to Dashboard
                    </a>
                </div>';
            } else {
                $message = '<div class="alert-error">Verification processing failed. Please contact support.</div>';
            }
        } elseif (empty($message)) {
            $message = '<div class="alert-error">Please complete all fields and upload all required images.</div>';
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
            max-width: 700px;
            width: 100%;
            box-shadow: var(--shadow);
            overflow: hidden;
        }
        .verify-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            padding: 50px 40px;
            text-align: center;
        }
        .verify-header i {
            font-size: 4.5rem;
            margin-bottom: 20px;
        }
        .verify-header h1 {
            font-size: 2.4rem;
            margin-bottom: 15px;
        }
        .verify-header p {
            opacity: 0.9;
            font-size: 1.15rem;
            line-height: 1.7;
        }
        .verify-body {
            padding: 50px 40px;
        }
        .benefits {
            background: #f0f8ff;
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 40px;
            border-left: 6px solid var(--accent);
        }
        .benefits h3 {
            color: var(--primary);
            margin-bottom: 20px;
            font-size: 1.4rem;
        }
        .benefits ul {
            list-style: none;
        }
        .benefits li {
            padding: 12px 0 12px 35px;
            position: relative;
            font-size: 1.1rem;
        }
        .benefits li i {
            position: absolute;
            left: 0;
            top: 14px;
            color: var(--success);
            font-size: 1.3rem;
        }
        .form-group {
            margin-bottom: 28px;
        }
        .form-group label {
            display: block;
            margin-bottom: 12px;
            font-weight: 600;
            color: var(--primary);
            font-size: 1.15rem;
        }
        .form-group select, .form-group input[type="text"] {
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
        .upload-box {
            border: 3px dashed #ccc;
            border-radius: 16px;
            padding: 40px;
            text-align: center;
            background: #fafbff;
            transition: var(--transition);
            cursor: pointer;
        }
        .upload-box:hover {
            border-color: var(--primary);
            background: #f0f5ff;
        }
        .upload-box i {
            font-size: 3.5rem;
            color: #999;
            margin-bottom: 15px;
        }
        .upload-box p {
            font-size: 1.1rem;
            color: #666;
            margin: 10px 0;
        }
        .upload-box input[type="file"] {
            display: none;
        }
        .submit-btn {
            width: 100%;
            padding: 20px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.3rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: 0 10px 30px rgba(0,31,63,0.3);
            margin-top: 20px;
        }
        .submit-btn:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0,31,63,0.4);
        }
        .alert-success, .alert-error {
            padding: 25px;
            border-radius: 16px;
            margin: 20px 0;
            text-align: center;
            font-size: 1.2rem;
            line-height: 1.7;
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
            font-size: 1.1rem;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
        @media (max-width: 768px) {
            .verify-header { padding: 40px 20px; }
            .verify-header h1 { font-size: 2rem; }
            .verify-body { padding: 40px 25px; }
            .upload-box { padding: 30px; }
            .upload-box i { font-size: 3rem; }
        }
    </style>
</head>
<body>

<div class="verify-container">
    <div class="verify-header">
        <i class="fas fa-shield-alt"></i>
        <h1>Verify Your Identity</h1>
        <p>Upload a clear selfie and photos of your ID to unlock higher loan limits and full access.</p>
    </div>

    <div class="verify-body">
        <?= $message ?>

        <?php if (empty($message) || strpos($message, 'alert-error') !== false): ?>
            <div class="benefits">
                <h3>Why Verify Your Account?</h3>
                <ul>
                    <li><i class="fas fa-check-circle"></i> Increase your loan limit to <strong>GHS 20,000</strong></li>
                    <li><i class="fas fa-check-circle"></i> Faster and priority loan processing</li>
                    <li><i class="fas fa-check-circle"></i> Access to all premium features</li>
                    <li><i class="fas fa-check-circle"></i> Enhanced security and trust</li>
                </ul>
            </div>

            <form method="POST" enctype="multipart/form-data">
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

                <div class="form-group">
                    <label>1. Upload Your Selfie (Face Clearly Visible)</label>
                    <label class="upload-box" for="selfie">
                        <i class="fas fa-camera"></i>
                        <p><strong>Click to upload selfie</strong></p>
                        <p>JPG or PNG • Max 5MB</p>
                    </label>
                    <input type="file" name="selfie" id="selfie" accept="image/jpeg,image/png" required>
                </div>

                <div class="form-group">
                    <label>2. Upload Front of ID Card</label>
                    <label class="upload-box" for="id_front">
                        <i class="fas fa-id-card"></i>
                        <p><strong>Click to upload front side</strong></p>
                        <p>Ensure all details are clear and visible</p>
                    </label>
                    <input type="file" name="id_front" id="id_front" accept="image/jpeg,image/png" required>
                </div>

                <div class="form-group">
                    <label>3. Upload Back of ID Card</label>
                    <label class="upload-box" for="id_back">
                        <i class="fas fa-id-card"></i>
                        <p><strong>Click to upload back side</strong></p>
                        <p>Include any information on the back</p>
                    </label>
                    <input type="file" name="id_back" id="id_back" accept="image/jpeg,image/png" required>
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fas fa-paper-plane"></i> Submit for Verification
                </button>
            </form>

            <div class="back-link">
                <a href="dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // Improve UX: Show file name when selected
    document.querySelectorAll('input[type="file"]').forEach(input => {
        input.addEventListener('change', function() {
            const label = this.closest('.form-group').querySelector('p strong');
            if (this.files.length > 0) {
                label.textContent = this.files[0].name;
            }
        });
    });
</script>

</body>
</html>
