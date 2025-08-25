<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /backend/auth/login.php');
    exit;
}

$name = $_POST['name'] ?? '';
$type = $_POST['type'] ?? 'Manual';

// Generate slug robustly
$slug = strtolower($name);
$slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
$slug = trim($slug, '-');

if (!$name) {
    echo json_encode(['success' => false, 'message' => 'Name is required']);
    exit;
}

try {
    $db = db();

    // Insert collection
    $stmt = $db->prepare("INSERT INTO collections (name, slug, type) VALUES (?, ?, ?)");
    $stmt->execute([$name, $slug, $type]);

    $collectionId = $db->lastInsertId();

    // Auto-populate products with matching type
    $stmt = $db->prepare("SELECT id FROM products WHERE type = ?");
    $stmt->execute([$type]);
    $productIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if ($productIds) {
        // Assuming collection_products mapping table exists
        $insertStmt = $db->prepare("INSERT IGNORE INTO collection_products (collection_id, product_id) VALUES (?, ?)");
        foreach ($productIds as $pid) {
            $insertStmt->execute([$collectionId, $pid]);
        }
    }

    echo json_encode(['success' => true, 'collection_id' => $collectionId]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
