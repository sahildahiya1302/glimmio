<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $user = db_query('SELECT id FROM users WHERE email = :email', [':email' => $email])->fetch();
        if ($user) {
            // Generate a password reset token and expiration
            $token = bin2hex(random_bytes(16));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Store token and expiration in database
            db_query('UPDATE users SET reset_token = :token, reset_expires = :expires WHERE id = :id', [
                ':token' => $token,
                ':expires' => $expires,
                ':id' => $user['id'],
            ]);

            // Send reset email
            $resetLink = "https://yourdomain.com/backend/auth/reset-password.php?token=$token";
            $subject = 'Password Reset Request';
            $message = "Click the following link to reset your password: $resetLink\nThis link will expire in 1 hour.";
            send_mail($email, $subject, $message);

            $_SESSION['flash_message'] = 'Password reset link has been sent to your email.';
            header('Location: /backend/auth/forgot-password.php');
            exit;
        } else {
            $error = 'Email address not found.';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Forgot Password</title>
    <link rel="stylesheet" href="/admin/assets/admin.css" />
</head>
<body>
    <div class="forgot-password-container">
        <h1>Forgot Password</h1>
        <?php if (!empty($error)): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['flash_message'])): ?>
            <div class="flash-message"><?= htmlspecialchars($_SESSION['flash_message']) ?></div>
            <?php unset($_SESSION['flash_message']); ?>
        <?php endif; ?>
        <form method="post" action="/backend/auth/forgot-password.php">
            <label for="email">Email:</label><br />
            <input type="email" id="email" name="email" required value="<?= htmlspecialchars($email ?? '') ?>" /><br /><br />
            <button type="submit">Send Reset Link</button>
        </form>
    </div>
</body>
</html>
