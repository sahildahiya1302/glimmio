<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$total = db_query('SELECT COUNT(*) FROM orders')->fetchColumn();
$orders = db_query('SELECT id, customer_id, total_amount, status, created_at FROM orders ORDER BY created_at DESC LIMIT :limit OFFSET :offset', [
    ':limit' => $perPage,
    ':offset' => $offset,
])->fetchAll();

echo json_encode([
    'page' => $page,
    'per_page' => $perPage,
    'total' => (int)$total,
    'orders' => $orders,
]);
