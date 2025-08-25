<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../db.php';

header('Content-Type: application/json');

try {
  $stmt = db()->prepare(
    "SELECT c.*, COUNT(cp.product_id) AS product_count
     FROM collections c
     LEFT JOIN collection_product cp ON cp.collection_id = c.id
     GROUP BY c.id ORDER BY c.created_at DESC"
  );
  $stmt->execute();
  $collections = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode(['success' => true, 'collections' => $collections]);
} catch (Throwable $e) {
  echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
