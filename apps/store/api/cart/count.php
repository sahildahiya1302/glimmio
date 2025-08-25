<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

header('Content-Type: application/json');

session_start();

try {
    $cart = $_SESSION['cart'] ?? [];
    $itemCount = 0;
    
    foreach ($cart as $item) {
        $itemCount += $item['quantity'] ?? 0;
    }
    
    echo json_encode([
        'success' => true,
        'count' => $itemCount
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error getting cart count'
    ]);
}
