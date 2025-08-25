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
    } elseif (!rate_limit('login_' . ($_SERVER['REMOTE_ADDR'] ?? ''), 50, 900)) {
        $error = 'Too many login attempts. Please try again later.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!verify_recaptcha($_POST['g-recaptcha-response'] ?? '')) {
            $error = 'reCAPTCHA verification failed.';
            $logger->warning('reCAPTCHA failed', ['email' => $email]);
        } elseif (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
            $logger->warning('Login attempt with invalid email', ['email' => $email]);
        } elseif (!$password) {
            $error = 'Please enter your password.';
            $logger->warning('Login attempt with empty password', ['email' => $email]);
        } else {
            $user = db_query('SELECT id, email, password, role FROM users WHERE email = :email', [':email' => $email])->fetch();
            if ($user && password_verify($password, $user['password'])) {
                // Regenerate session ID to prevent fixation
                session_regenerate_id(true);
                if ($user['role'] === 'admin') {
                    // --- OTP system temporarily disabled ---
                    /*
                    $otp = random_int(100000, 999999);
                    $_SESSION['otp_user_id'] = $user['id'];
                    $_SESSION['otp_code'] = (string)$otp;
                    $_SESSION['otp_email'] = $email;
                    send_mail($email, 'Your verification code', "Your login code is: $otp");
                    $logger->info('2FA code sent', ['user_id' => $user['id']]);
                    header('Location: /backend/auth/verify-2fa.php');
                    exit;
                    */

                    // Direct login (OTP bypass)
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];
                    $logger->info('Admin logged in without 2FA (OTP bypass)', ['user_id' => $user['id']]);
                    header('Location: /admin/dashboard/dashboard.php');
                    exit;
                } else {
                    $error = 'Access denied. You do not have admin privileges.';
                    $logger->warning('Access denied for non-admin user', ['user_id' => $user['id'], 'email' => $email]);
                }
            } else {
                $error = 'Invalid email or password.';
                $logger->warning('Invalid login attempt', ['email' => $email]);
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login / Register</title>
    <link rel="stylesheet" href="/admin/assets/admin.css" />
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <style>
        .hidden { display: none; }
        .toggle-link { cursor: pointer; color: blue; text-decoration: underline; }
    </style>
</head>
<body>
    <div class="auth-container">
        <div id="login-form" class="auth-form">
            <h1>Login</h1>
            <?php if (!empty($error)): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="post" action="/backend/auth/login.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>" />
                <label for="email">Email:</label><br />
                <input type="email" id="email" name="email" required value="<?= htmlspecialchars($email ?? '') ?>" /><br /><br />
                <label for="password">Password:</label><br />
                <input type="password" id="password" name="password" required /><br /><br />
                <div class="g-recaptcha" data-sitekey="<?= htmlspecialchars(RECAPTCHA_SITE_KEY) ?>"></div><br />
                <button type="submit">Login</button>
            </form>
            <p>Don't have an account? <span class="toggle-link" onclick="toggleForms()">Register here</span></p>
        </div>

        <div id="register-form" class="auth-form hidden">
            <h1>Register</h1>
            <form method="post" action="/backend/auth/register.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>" />
                <label for="reg_email">Email:</label><br />
                <input type="email" id="reg_email" name="email" required /><br /><br />
                <label for="reg_password">Password:</label><br />
                <input type="password" id="reg_password" name="password" required /><br /><br />
                <label for="reg_confirm_password">Confirm Password:</label><br />
                <input type="password" id="reg_confirm_password" name="confirm_password" required /><br /><br />
                <div class="g-recaptcha" data-sitekey="<?= htmlspecialchars(RECAPTCHA_SITE_KEY) ?>"></div><br />
                <button type="submit">Register</button>
            </form>
            <p>Already have an account? <span class="toggle-link" onclick="toggleForms()">Login here</span></p>
        </div>
    </div>

    <script>
        function toggleForms() {
            const loginForm = document.getElementById('login-form');
            const registerForm = document.getElementById('register-form');
            loginForm.classList.toggle('hidden');
            registerForm.classList.toggle('hidden');
        }
    </script>
</body>
</html>
