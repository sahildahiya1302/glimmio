<?php
declare(strict_types=1);
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';

$payload = file_get_contents('php://input');
$data = json_decode($payload, true);

if (!$data || !isset($data['product_id']) || !isset($data['stock'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payload']);
    exit;
}

$productId = intval($data['product_id']);
$stock = intval($data['stock']);

try {
    $stmt = db_query('UPDATE products SET stock = :stock WHERE id = :id', [
        ':stock' => $stock,
        ':id' => $productId,
    ]);
    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Product not found']);
    } else {
        echo json_encode(['success' => true, 'message' => 'Product stock updated']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
