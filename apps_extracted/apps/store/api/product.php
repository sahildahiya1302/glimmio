<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

header('Content-Type: application/json');

$product_id = $_GET['id'] ?? null;

if (!$product_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Product ID is required']);
    exit;
}

try {
    $product = getProductById($product_id);
    
    if (!$product) {
        http_response_code(404);
        echo json_encode(['error' => 'Product not found']);
        exit;
    }
    
    echo json_encode($product);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error loading product']);
}
