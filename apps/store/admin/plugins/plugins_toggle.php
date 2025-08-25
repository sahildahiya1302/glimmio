<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /backend/auth/login.php');
    exit;
}

$id = intval($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';

if ($id > 0 && in_array($action, ['enable', 'disable'])) {
    $enabled = $action === 'enable' ? 1 : 0;
    db_query('UPDATE plugins SET enabled = :enabled WHERE id = :id', [
        ':enabled' => $enabled,
        ':id' => $id,
    ]);
}

header('Location: /admin/plugins.php');
exit;
?>
