<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /backend/auth/login.php');
    exit;
}

$file = THEME_PATH . '/config/checkout_success.json';
$data = is_file($file) ? json_decode(file_get_contents($file), true) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = $_POST['json'] ?? '';
    if ($json) {
        file_put_contents($file, $json);
        $_SESSION['flash_message'] = 'Checkout success settings saved.';
    }
    header('Location: /admin/checkout_settings.php');
    exit;
}

$pageTitle = 'Checkout Success Settings';
include __DIR__ . '/components/header.php';
?>
<h1>Checkout Success Settings</h1>
<?php if (!empty($_SESSION['flash_message'])): ?>
<div class="flash-message"><?= htmlspecialchars($_SESSION['flash_message']) ?></div>
<?php unset($_SESSION['flash_message']); endif; ?>
<form method="post">
    <textarea name="json" rows="20" style="width:100%;"><?= htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT)) ?></textarea><br>
    <button class="btn btn-primary">Save</button>
</form>
<?php include __DIR__ . '/components/footer.php'; ?>
