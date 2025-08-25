<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';

$token = $_GET['token'] ?? '';
if (!$token) {
    die('Invalid or missing token.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (!$password) {
        $error = 'Please enter a new password.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        $user = db_query('SELECT id, reset_expires FROM users WHERE reset_token = :token', [':token' => $token])->fetch();
        if (!$user) {
            $error = 'Invalid token.';
        } elseif (strtotime($user['reset_expires']) < time()) {
            $error = 'Token has expired.';
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            db_query('UPDATE users SET password = :password, reset_token = NULL, reset_expires = NULL WHERE id = :id', [
                ':password' => $passwordHash,
                ':id' => $user['id'],
            ]);
            $_SESSION['flash_message'] = 'Password has been reset. Please log in.';
            header('Location: /backend/auth/login.php');
            exit;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Reset Password</title>
    <link rel="stylesheet" href="/admin/assets/admin.css" />
</head>
<body>
    <div class="reset-password-container">
        <h1>Reset Password</h1>
        <?php if (!empty($error)): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" action="/backend/auth/reset-password.php?token=<?= htmlspecialchars($token) ?>">
            <label for="password">New Password:</label><br />
            <input type="password" id="password" name="password" required /><br /><br />
            <label for="confirm_password">Confirm New Password:</label><br />
            <input type="password" id="confirm_password" name="confirm_password" required /><br /><br />
            <button type="submit">Reset Password</button>
        </form>
    </div>
</body>
</html>
