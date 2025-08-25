<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';
require_once __DIR__ . '/../utils/logger.php';
require_once __DIR__ . '/../utils/rate_limiter.php';

$logger = new Logger();
$csrfToken = csrf_token();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token.';
    } elseif (!verify_recaptcha($_POST['g-recaptcha-response'] ?? '')) {
        $error = 'reCAPTCHA verification failed.';
        $logger->warning('reCAPTCHA failed during registration');
    }

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
        $logger->warning('Registration attempt with invalid email', ['email' => $email]);
    } elseif (!$password) {
        $error = 'Please enter a password.';
        $logger->warning('Registration attempt with empty password', ['email' => $email]);
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
        $logger->warning('Registration attempt with password mismatch', ['email' => $email]);
    } else {
        $existingUser = db_query('SELECT id FROM users WHERE email = :email', [':email' => $email])->fetch();
        if ($existingUser) {
            $error = 'Email is already registered.';
            $logger->warning('Registration attempt with existing email', ['email' => $email]);
        } else {
            // Always assign 'user' role on registration; admin role must be manually assigned in DB
            $role = 'user';

            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            db_query('INSERT INTO users (email, password, role, created_at) VALUES (:email, :password, :role, NOW())', [
                ':email' => $email,
                ':password' => $passwordHash,
                ':role' => $role,
            ]);
            $logger->info('New user registered', ['email' => $email, 'role' => $role]);
            $_SESSION['flash_message'] = 'Registration successful. Please log in.';
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
    <title>Register</title>
    <link rel="stylesheet" href="/admin/assets/admin.css" />
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
    <div class="register-container">
        <h1>Register</h1>
        <?php if (!empty($error)): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" action="/backend/auth/register.php">
            <label for="email">Email:</label><br />
            <input type="email" id="email" name="email" required value="<?= htmlspecialchars($email ?? '') ?>" /><br /><br />
            <label for="password">Password:</label><br />
            <input type="password" id="password" name="password" required /><br /><br />
            <label for="confirm_password">Confirm Password:</label><br />
            <input type="password" id="confirm_password" name="confirm_password" required /><br /><br />
            <div class="g-recaptcha" data-sitekey="<?= htmlspecialchars(RECAPTCHA_SITE_KEY) ?>"></div><br />
            <button type="submit">Register</button>
        </form>
    </div>
</body>
</html>
