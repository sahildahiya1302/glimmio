<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../functions.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? 'view';

switch ($action) {
    case 'add':
        $data = json_decode(file_get_contents('php://input'), true);
        $productId = (int)($data['product_id'] ?? 0);
        if ($productId) {
            addToWishlist($productId);
            echo json_encode(['success' => true, 'items' => getWishlist()]);
            return;
        }
        echo json_encode(['success' => false, 'message' => 'Product ID required']);
        break;
    case 'remove':
        $id = (int)($_GET['product_id'] ?? 0);
        if ($id) {
            removeFromWishlist($id);
            echo json_encode(['success' => true, 'items' => getWishlist()]);
            return;
        }
        echo json_encode(['success' => false, 'message' => 'Product ID required']);
        break;
    case 'view':
    default:
        echo json_encode(['items' => getWishlist()]);
        break;
}