<?php
declare(strict_types=1);
require_once __DIR__ . '/../../db.php';
header('Content-Type: application/json');

try {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    if ($id <= 0) throw new Exception('Invalid product ID');

    // Delete variants first due to foreign key
    db_query("DELETE FROM product_variants WHERE product_id = ?", [$id]);
    db_query("DELETE FROM product_images WHERE product_id = ?", [$id]);
    db_query("DELETE FROM collection_products WHERE product_id = ?", [$id]);
    db_query("DELETE FROM product_set_products WHERE product_id = ?", [$id]);

    db_query("DELETE FROM products WHERE id = ?", [$id]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
