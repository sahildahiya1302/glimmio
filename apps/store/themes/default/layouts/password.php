<?php
// themes/default/layouts/password.php
// Store Lock Page - Production Level

// Load global theme settings
$settings = json_decode(file_get_contents(__DIR__ . '/../config/settings_data.json'), true);

// Load active locale strings
$locale = 'en'; // Default locale, can be dynamically set
$localeStrings = json_decode(file_get_contents(__DIR__ . "/../locales/{$locale}.json"), true);

// Function to get theme setting with fallback
function getSetting($key, $default = null) {
    global $settings;
    return $settings[$key] ?? $default;
}

// Function to get asset URL
function asset($path) {
    return "/themes/default/assets/{$path}";
}

session_start();

// Apply password protection only on frontend (exclude admin paths)
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
if (strpos($requestUri, '/admin') !== 0) {
    // Check if store is locked
    $storeLocked = getSetting('store_locked', true);

    // Handle password form submission
    $passwordError = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $inputPassword = $_POST['password'] ?? '';
        $storedPassword = getSetting('store_password', '');

        // Simple rate limiting using session
        if (!isset($_SESSION['password_attempts'])) {
            $_SESSION['password_attempts'] = 0;
        }
        if ($_SESSION['password_attempts'] >= 5) {
            $passwordError = $localeStrings['too_many_attempts'] ?? 'Too many attempts. Please try again later.';
        } else {
            if ($inputPassword === $storedPassword) {
                $_SESSION['authenticated'] = true;
                header('Location: /');
                exit;
            } else {
                $_SESSION['password_attempts']++;
                $passwordError = $localeStrings['incorrect_password'] ?? 'Incorrect password. Please try again.';
            }
        }
    }

    // Redirect if already authenticated
    if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
        header('Location: /');
        exit;
    }
} else {
    // For admin paths, no password protection here
    // Admin authentication handled separately
}

?><!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($locale); ?>">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title><?php echo htmlspecialchars(getSetting('password_page_title', 'Store Locked')); ?></title>
    <link rel="icon" href="<?php echo asset('images/favicon.ico'); ?>" type="image/x-icon" />
    <link rel="stylesheet" href="<?php echo asset('reset.css'); ?>" />
    <link rel="stylesheet" href="<?php echo asset('grid.css'); ?>" />
    <link rel="stylesheet" href="<?php echo asset('theme.css'); ?>" />
    <link rel="stylesheet" href="<?php echo asset('components/forms.css'); ?>" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet" />
    <script defer src="<?php echo asset('theme.js'); ?>"></script>
    <script defer src="<?php echo asset('custom.js'); ?>"></script>
</head>
<body class="password-page" style="background-color: <?php echo htmlspecialchars(getSetting('background_color', '#fff')); ?>;">
    <div class="password-container" role="main" aria-labelledby="password-heading">
        <img src="<?php echo asset('images/logo.png'); ?>" alt="<?php echo htmlspecialchars(getSetting('store_name', 'My Store')); ?> Logo" class="password-logo" />
        <h1 id="password-heading"><?php echo htmlspecialchars(getSetting('password_page_heading', 'This store is password protected')); ?></h1>
        <p><?php echo htmlspecialchars(getSetting('password_page_text', 'Please enter the password to continue.')); ?></p>

        <?php if ($passwordError): ?>
            <div class="password-error" role="alert"><?php echo htmlspecialchars($passwordError); ?></div>
        <?php endif; ?>

        <form method="POST" action="" class="password-form" novalidate>
            <label for="password" class="visually-hidden"><?php echo htmlspecialchars($localeStrings['password_label'] ?? 'Password'); ?></label>
            <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="<?php echo htmlspecialchars($localeStrings['password_placeholder'] ?? 'Enter password'); ?>" />
            <button type="submit" class="btn btn-primary"><?php echo htmlspecialchars($localeStrings['submit_button'] ?? 'Submit'); ?></button>
        </form>

        <?php if (getSetting('show_newsletter_signup', false)): ?>
            <?php include __DIR__ . '/../sections/newsletter.php'; ?>
        <?php endif; ?>

        <?php if (getSetting('show_contact_link', false)): ?>
            <p><a href="/page.contact"><?php echo htmlspecialchars($localeStrings['contact_us'] ?? 'Contact Us'); ?></a></p>
        <?php endif; ?>
    </div>
</body>
</html>
