<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['success' => false, 'message' => 'Invalid request']);
  exit;
}

$id = intval($_POST['id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$slug = trim($_POST['slug'] ?? '');
$description = trim($_POST['description'] ?? '');
$seoTitle = trim($_POST['seo_title'] ?? '');
$seoDesc = trim($_POST['seo_description'] ?? '');
$ruleBased = isset($_POST['rule_based']) ? 1 : 0;
$manualProducts = $_POST['manual_products'] ?? [];
$rules = $_POST['rules'] ?? [];

if (!$id || !$title || !$slug) {
  echo json_encode(['success' => false, 'message' => 'Missing fields']);
  exit;
}

try {
  $stmt = db()->prepare("UPDATE collections SET title = ?, slug = ?, description = ?, seo_title = ?, seo_description = ?, rule_based = ?, rules = ?, manual_products = ?, updated_at = NOW() WHERE id = ?");
  $stmt->execute([
    $title,
    $slug,
    $description,
    $seoTitle,
    $seoDesc,
    $ruleBased,
    $ruleBased ? json_encode($rules) : null,
    !$ruleBased ? json_encode($manualProducts) : null,
    $id
  ]);

  // Sync manual collection products
  if (!$ruleBased) {
    $stmtDel = db()->prepare("DELETE FROM collection_product WHERE collection_id = ?");
    $stmtDel->execute([$id]);

    $stmtIns = db()->prepare("INSERT IGNORE INTO collection_product (collection_id, product_id) VALUES (?, ?)");
    foreach ($manualProducts as $pid) {
      $stmtIns->execute([$id, (int)$pid]);
    }
  }

  echo json_encode(['success' => true]);
} catch (Throwable $e) {
  echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
