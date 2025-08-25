<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../functions.php';
require_once __DIR__ . '/../utils/logger.php';

$logger = new Logger();
$csrfToken = csrf_token();

if (empty($_SESSION['otp_user_id']) || empty($_SESSION['otp_code'])) {
    header('Location: /backend/auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token.';
    } else {
        $code = trim($_POST['code'] ?? '');
        if (hash_equals($_SESSION['otp_code'], $code)) {
            // Regenerate session ID once 2FA is validated
            session_regenerate_id(true);
            $_SESSION['user_id'] = $_SESSION['otp_user_id'];
            $logger->info('2FA verified', ['user_id' => $_SESSION['user_id']]);
            log_activity($_SESSION["user_id"], "login", ["ip" => $_SERVER["REMOTE_ADDR"] ?? ""]);
            unset($_SESSION['otp_user_id'], $_SESSION['otp_code'], $_SESSION['otp_email']);
            header('Location: /admin/dashboard.php');
            exit;
        } else {
            $error = 'Invalid verification code.';
            $logger->warning('2FA verification failed', ['user_id' => $_SESSION['otp_user_id']]);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Two-Factor Authentication</title>
    <link rel="stylesheet" href="/admin/assets/admin.css" />
</head>
<body>
<div class="auth-container">
    <h1>Two-Factor Authentication</h1>
    <?php if (!empty($error)): ?>
        <div class="error-message"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" action="/backend/auth/verify-2fa.php">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>" />
        <label for="code">Verification Code:</label><br />
        <input type="text" id="code" name="code" required /><br /><br />
        <button type="submit">Verify</button>
    </form>
</div>
</body>
</html>
