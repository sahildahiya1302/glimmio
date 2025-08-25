<?php
declare(strict_types=1);
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';

$payload = file_get_contents('php://input');
$data = json_decode($payload, true);

if (!$data || !isset($data['order_id']) || !isset($data['status'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payload']);
    exit;
}

$orderId = intval($data['order_id']);
$status = trim($data['status']);

try {
    $stmt = db_query('UPDATE orders SET status = :status WHERE id = :id', [
        ':status' => $status,
        ':id' => $orderId,
    ]);
    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Order not found']);
    } else {
        echo json_encode(['success' => true, 'message' => 'Order status updated']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
