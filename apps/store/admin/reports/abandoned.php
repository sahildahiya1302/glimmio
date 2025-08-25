<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /backend/auth/login.php');
    exit;
}

$pageTitle = 'Abandoned Cart Report';
require __DIR__ . '/../components/header.php';
?>

<h1>Abandoned Cart Report</h1>
<p>Track recovery rates for abandoned carts.</p>

<?php require __DIR__ . '/../components/footer.php'; ?>
