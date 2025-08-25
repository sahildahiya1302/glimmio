<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

try {
    $db = db();

    $id = (int) ($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $vendor = trim($_POST['vendor'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $tags = trim($_POST['tags'] ?? '');
    $weight = isset($_POST['weight']) ? floatval($_POST['weight']) : null;
    $seoTitle = trim($_POST['seo_title'] ?? '');
    $seoDesc = trim($_POST['seo_description'] ?? '');
    $taxable = isset($_POST['taxable']) ? 1 : 0;
    $requires_shipping = isset($_POST['requires_shipping']) ? 1 : 0;

    if (!$id || !$title || !$type) {
        throw new Exception('Missing product details');
    }

    // Collection resolve
    $collectionId = get_or_create_collection($type);

    // Update product
    $stmt = $db->prepare("UPDATE products SET title = ?, type = ?, vendor = ?, description = ?, tags = ?, weight = ?, seo_title = ?, seo_description = ?, taxable = ?, requires_shipping = ?, collection_id = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$title, $type, $vendor, $description, $tags, $weight, $seoTitle, $seoDesc, $taxable, $requires_shipping, $collectionId, $id]);

    // Process variants
    $variants = $_POST['variants'] ?? [];
    $variantIds = array_column($variants, 'id');

    $processed = [];
    foreach ($variants as $v) {
        $vid = (int)($v['id'] ?? 0);
        $sku = trim($v['sku'] ?? '');
        $price = isset($v['price']) ? (float)$v['price'] : 0;
        $compare = isset($v['compare_price']) ? (float)$v['compare_price'] : null;
        $option = trim(($v['option_name'] ?? '') . ' ' . ($v['option_value'] ?? ''));
        $image = trim($v['image'] ?? '');
        $stock = isset($v['inventory']) ? (int)$v['inventory'] : 0;

        if (!$sku || !$price) continue;

        if ($vid > 0) {
            // Update variant
            $stmt = $db->prepare("UPDATE product_variants SET sku = ?, price = ?, compare_at_price = ?, option_label = ?, image_url = ?, inventory_qty = ?, updated_at = NOW() WHERE id = ? AND product_id = ?");
            $stmt->execute([$sku, $price, $compare, $option, $image, $stock, $vid, $id]);
            $processed[] = $vid;
        } else {
            // Insert new variant
            $stmt = $db->prepare("INSERT INTO product_variants (product_id, sku, price, compare_at_price, option_label, image_url, inventory_qty, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->execute([$id, $sku, $price, $compare, $option, $image, $stock]);
            $processed[] = $db->lastInsertId();
        }
    }

    // Remove deleted variants
    $existing = $db->prepare("SELECT id FROM product_variants WHERE product_id = ?");
    $existing->execute([$id]);
    $existingIds = $existing->fetchAll(PDO::FETCH_COLUMN);

    $toDelete = array_diff($existingIds, $processed);
    if (count($toDelete)) {
        $in = implode(',', array_fill(0, count($toDelete), '?'));
        $stmt = $db->prepare("DELETE FROM product_variants WHERE id IN ($in) AND product_id = ?");
        $stmt->execute([...$toDelete, $id]);
    }

    echo json_encode(['success' => true]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

// Reuse helper
function get_or_create_collection(string $name): int {
    $db = db();
    $stmt = $db->prepare("SELECT id FROM collections WHERE title = ?");
    $stmt->execute([$name]);
    if ($row = $stmt->fetch()) return (int)$row['id'];

    $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $name));
    $stmt = $db->prepare("INSERT INTO collections (title, slug, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
    $stmt->execute([$name, $slug]);
    return (int)$db->lastInsertId();
}
