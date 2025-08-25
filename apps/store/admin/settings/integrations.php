<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /backend/auth/login.php');
    exit;
}

$pageTitle = 'Integrations & APIs';
require __DIR__ . '/../components/header.php';
?>

<h1>Integrations & APIs</h1>
<p>Configure external services like payment gateways and delivery partners.</p>

<?php require __DIR__ . '/../components/footer.php'; ?>
