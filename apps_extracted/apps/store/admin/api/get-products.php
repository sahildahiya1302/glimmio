<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../db.php';

header('Content-Type: application/json');

try {
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 1000;
    $stmt = db()->prepare('SELECT id, title FROM products ORDER BY title LIMIT :l');
    $stmt->bindValue(':l', $limit, PDO::PARAM_INT);
    $stmt->execute();
    echo json_encode(['success' => true, 'products' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
