<?php
session_start();
require_once '../db.php';

$error = '';
$success = '';
$token = $_GET['token'] ?? '';
$user = null;

if (empty($token)) {
    $error = "Invalid or missing password reset token.";
} else {
    $stmt = $pdo->prepare("SELECT id, email, reset_expires_at FROM customers WHERE reset_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user) {
        $error = "Invalid password reset token.";
    } elseif (strtotime($user['reset_expires_at']) < time()) {
        $error = "Password reset token has expired. Please request a new one.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user && empty($error)) {
    $password = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $updateStmt = $pdo->prepare("UPDATE customers SET password = ?, reset_token = NULL, reset_expires_at = NULL WHERE id = ?");
        if ($updateStmt->execute([$hashedPassword, $user['id']])) {
            header("Location: login.php?reset=success");
            exit;
        } else {
            $error = "Failed to update password. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - ඒ රtin (E RATIN)</title>
    <link rel="stylesheet" href="auth.css">
    <style>
        .error-message {
            color: #ef4444;
            background: #fee2e2;
            border: 1px solid #f87171;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            text-align: center;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<div class="auth-container">
    <div class="auth-card">
        <a href="../index.php"><img src="../logo.jpg" alt="Logo" class="auth-logo"></a>
        
        <h2 class="auth-title">New Password</h2>
        <p class="auth-subtitle">Enter your new secure password</p>

        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php if (strpos($error, 'expired') !== false || strpos($error, 'Invalid') !== false): ?>
                <a href="login.php" class="btn-primary" style="display: block; text-align: center; text-decoration: none; margin-top: 20px;">Return to Login</a>
            <?php endif; ?>
        <?php else: ?>
            <form action="" method="POST">
                <div class="form-group">
                    <input type="password" name="new_password" class="form-control" placeholder="New Password (min. 6 chars)" required>
                </div>
                <div class="form-group">
                    <input type="password" name="confirm_password" class="form-control" placeholder="Confirm New Password" required>
                </div>
                <button type="submit" class="btn-primary">Reset Password</button>
            </form>
        <?php endif; ?>
        
        <p class="toggle-text" style="margin-top: 15px;"><a href="login.php" class="toggle-link">Back to Login</a></p>
    </div>
</div>

</body>
</html>
