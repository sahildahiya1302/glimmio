<?php
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$productId = isset($data['product_id']) ? (int)$data['product_id'] : 0;
$customerId = isset($data['customer_id']) ? (int)$data['customer_id'] : null;
$rating = isset($data['rating']) ? (int)$data['rating'] : 0;
$comment = trim($data['comment'] ?? '');

if ($productId <= 0 || $rating <= 0 || $rating > 5) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

try {
    db_query('INSERT INTO reviews (product_id, customer_id, rating, comment, created_at) VALUES (:pid, :cid, :rating, :comment, NOW())', [
        ':pid' => $productId,
        ':cid' => $customerId,
        ':rating' => $rating,
        ':comment' => $comment
    ]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}

