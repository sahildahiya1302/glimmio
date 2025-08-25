<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

$product_id = $_GET['product_id'] ?? null;

if (!$product_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Product ID is required']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT id, author, rating, title, content, created_at, verified 
        FROM reviews 
        WHERE product_id = ? 
        ORDER BY created_at DESC
    ");
    
    $stmt->execute([$product_id]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($reviews);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error loading reviews']);
}
