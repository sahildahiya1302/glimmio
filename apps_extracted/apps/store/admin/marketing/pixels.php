<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /backend/auth/login.php');
    exit;
}

$pageTitle = 'Pixel Integrations';
require __DIR__ . '/../components/header.php';
?>

<h1>Pixel Integrations</h1>
<p>Configure tracking pixels for various ad platforms.</p>

<?php require __DIR__ . '/../components/footer.php'; ?>
