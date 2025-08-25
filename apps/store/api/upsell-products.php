<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../functions.php';

header('Content-Type: application/json');

// Get current cart items
$cart = $_SESSION['cart'] ?? [];
$cartProductIds = array_keys($cart);

// Get recently viewed products
$recentlyViewed = getRecentlyViewed();
$excludeIds = array_merge($cartProductIds, $recentlyViewed);

// Fetch upsell products (products not in cart or recently viewed)
$placeholders = implode(',', array_fill(0, count($excludeIds), '?'));
$sql = "SELECT p.*, pv.price, pv.image_url as image 
        FROM products p 
        JOIN product_variants pv ON pv.product_id = p.id 
        WHERE p.id NOT IN ($placeholders) 
        GROUP BY p.id 
        ORDER BY RAND() 
        LIMIT 4";

$stmt = db()->prepare($sql);
$stmt->execute($excludeIds);
$products = $stmt->fetchAll();

// Format products
$formattedProducts = array_map(function($product) {
    return [
        'id' => $product['id'],
        'title' => $product['title'],
        'price' => $product['price'],
        'image' => $product['image'] ?? '/assets/images/placeholder.png',
        'handle' => $product['handle']
    ];
}, $products);

echo json_encode(['success' => true, 'products' => $formattedProducts]);
?>
