<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../db.php';

header('Content-Type: application/json');

try {
    $stmt = db()->query('SELECT id, title FROM product_sets ORDER BY title');
    echo json_encode(['success' => true, 'sets' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
