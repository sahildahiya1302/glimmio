<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../functions.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? 'view';

switch ($action) {
    case 'add':
        handleAddToCart();
        break;
    case 'remove':
        handleRemoveFromCart();
        break;
    case 'view':
    default:
        handleViewCart();
        break;
}

function handleAddToCart() {
    $data = json_decode(file_get_contents('php://input'), true);
    $productId = $data['product_id'] ?? null;
    $quantity = $data['quantity'] ?? 1;

    if (!$productId) {
        echo json_encode(['success' => false, 'message' => 'Product ID required']);
        return;
    }

    $_SESSION['cart'] = $_SESSION['cart'] ?? [];
    $_SESSION['cart'][$productId] = ($_SESSION['cart'][$productId] ?? 0) + $quantity;

    echo json_encode(['success' => true, 'cart' => $_SESSION['cart']]);
}

function handleRemoveFromCart() {
    $productId = $_GET['product_id'] ?? null;
    if (!$productId) {
        echo json_encode(['success' => false, 'message' => 'Product ID required']);
        return;
    }

    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
    }

    echo json_encode(['success' => true, 'cart' => $_SESSION['cart']]);
}

function handleViewCart() {
    $cart = $_SESSION['cart'] ?? [];
    echo json_encode(['items' => $cart]);
}
?>
