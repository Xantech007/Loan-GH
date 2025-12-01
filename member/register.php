<?php
session_start();
require '../config/db.php';

$success = $error = "";

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = "Invalid request.";
    } else {
        $full_name = trim($_POST['full_name'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $phone     = trim($_POST['phone'] ?? '');
        $password  = $_POST['password'] ?? '';
        $confirm   = $_POST['confirm_password'] ?? '';

        if (empty($full_name) || empty($email) || empty($phone) || empty($password) || empty($confirm)) {
            $error = "All fields are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email address.";
        } elseif ($password !== $confirm) {
            $error = "Passwords do not match.";
        } elseif (strlen($password) < 8) {
            $error = "Password must be at least 8 characters.";
        } else {
            // Check if email or phone already exists
            $check = $conn->prepare("SELECT member_id FROM members WHERE email = ? OR phone = ?");
            $check->bind_param("ss", $email, $phone);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                $error = "Email or phone already registered.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);

                // Insert — member_id is AUTO_INCREMENT so we don't touch it
                $stmt = $conn->prepare("INSERT INTO members (full_name, email, phone, password, date_registered) VALUES (?, ?, ?, ?, NOW())");
                $stmt->bind_param("ssss", $full_name, $email, $phone, $hash);
                $stmt->execute();

                $member_id = $conn->insert_id;  // ← This now works! Real auto-generated ID
                $username  = "MEM" . str_pad($member_id, 6, "0", STR_PAD_LEFT);

                // Save the generated Member ID back into the same row
                $upd = $conn->prepare("UPDATE members SET username = ? WHERE member_id = ?");
                $upd->bind_param("si", $username, $member_id);
                $upd->execute();

                $success = "Account created successfully!<br><strong>Your Member ID: $username</strong><br>You can now <a href='login.php'>log in</a>.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Register • CedisPay</title><style>:root{--primary:#003366;--light:#004488;--err:#ff3860;--suc:#28a745;}*{margin:0;padding:0;box-sizing:border-box;}body{font-family:Arial;background:#f4f4f4;display:flex;justify-content:center;align-items:center;min-height:100vh;}.main{display:grid;grid-template-columns:1fr 1fr;width:90%;max-width:1000px;box-shadow:0 10px 30px rgba(0,0,0,.1);border-radius:10px;overflow:hidden;}.left{background:white;padding:3rem 2.5rem;}.right{background:var(--primary);display:flex;align-items:center;justify-content:center;}.right img{max-width:70%;}.logo{text-align:center;margin-bottom:20px;}h2{text-align:center;color:var(--primary);margin:10px 0 40px;font-size:28px;}.fg{position:relative;margin-bottom:2rem;}.fg input{width:100%;padding:12px 0 8px;border:none;border-bottom:1px solid #ddd;background:transparent;font-size:16px;outline:none;}.fg label{position:absolute;top:12px;left:0;color:#999;transition:.3s;pointer-events:none;}.fg input:focus~label,.fg input:valid~label{top:-12px;font-size:13px;color:var(--primary);font-weight:500;}.fg input:focus{border-bottom:2px solid var(--primary);}button{width:100%;padding:14px;background:var(--primary);color:white;border:none;border-radius:6px;font-size:16px;cursor:pointer;margin-top:10px;}button:hover{background:var(--light);}.alert{padding:12px;margin:20px 0;border-radius:6px;text-align:center;}.e{background:#ffebee;color:var(--err);border:1px solid #ffcdd2;}.s{background:#e8f5e9;color:var(--suc);border:1px solid #c8e6c9;}@media(max-width:768px){.main{grid-template-columns:1fr;}.right{display:none;}}</style></head><body>
<div class="main">
  <div class="left">
    <div class="logo"><img src="../assets/profile_3135715.png" width="120" alt="CedisPay"></div>
    <h2>Join CedisPay</h2>
    <?php if($error)  echo "<div class='alert e'>".htmlspecialchars($error)."</div>"; ?>
    <?php if($success) echo "<div class='alert s'>$success</div>"; ?>
    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
      <div class="fg"><input type="text" name="full_name" required><label>Full Name</label></div>
      <div class="fg"><input type="email" name="email" required><label>Email Address</label></div>
      <div class="fg"><input type="text" name="phone" required><label>Phone Number</label></div>
      <div class="fg"><input type="password" name="password" required minlength="8"><label>Create Password</label></div>
      <div class="fg"><input type="password" name="confirm_password" required minlength="8"><label>Confirm Password</label></div>
      <button type="submit">Create Account</button>
    </form>
    <p style="text-align:center;margin-top:25px;">Already have an account? <a href="login.php" style="color:var(--primary);font-weight:600;">Log in</a></p>
  </div>
  <div class="right"><img src="../assets/cedispay-logo-white.png" alt="CedisPay"></div>
</div>
</body></html>
