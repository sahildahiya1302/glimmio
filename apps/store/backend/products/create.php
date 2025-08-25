<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
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

    if (!$title || !$type) {
        throw new Exception('Missing product title or type');
    }

    $handle = generate_unique_handle($title, fetch_existing_handles());

    $collectionId = get_or_create_collection($type);

    // Insert product
    $stmt = db()->prepare("INSERT INTO products (title, type, vendor, description, tags, handle, published, taxable, requires_shipping, weight, seo_title, seo_description, collection_id, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, 1, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
    $stmt->execute([$title, $type, $vendor, $description, $tags, $handle, $taxable, $requires_shipping, $weight, $seoTitle, $seoDesc, $collectionId]);

    $productId = (int)db()->lastInsertId();

    // Handle variants (nested arrays from form)
    $variants = $_POST['variants'] ?? [];
    foreach ($variants as $v) {
        $sku = trim($v['sku'] ?? '');
        $price = isset($v['price']) ? (float)$v['price'] : 0;
        $compare = isset($v['compare_price']) ? (float)$v['compare_price'] : null;
        $option = trim(($v['option_name'] ?? '') . ' ' . ($v['option_value'] ?? ''));
        $image = trim($v['image'] ?? '');
        $stock = isset($v['inventory']) ? (int)$v['inventory'] : 0;

        if (!$sku || !$price) {
            continue;
        }

        $stmt2 = db()->prepare("INSERT INTO product_variants (product_id, sku, price, compare_at_price, option_label, image_url, inventory_qty, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt2->execute([$productId, $sku, $price, $compare, $option, $image, $stock]);
    }

    echo json_encode(['success' => true, 'product_id' => $productId]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

// ------------------
// Helpers
// ------------------
function get_or_create_collection(string $name): int {
    // Extract last segment after '>' if present
    if (strpos($name, '>') !== false) {
        $parts = explode('>', $name);
        $name = trim(end($parts));
    }

    $stmt = db()->prepare("SELECT id FROM collections WHERE title = ?");
    $stmt->execute([$name]);
    if ($row = $stmt->fetch()) return (int)$row['id'];

    $slug = slugify($name);
    $stmt = db()->prepare("INSERT INTO collections (title, slug, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
    $stmt->execute([$name, $slug]);
    return (int)db()->lastInsertId();
}

function fetch_existing_handles(): array {
    $stmt = db()->query("SELECT handle FROM products");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function generate_unique_handle(string $title, array &$existing): string {
    $base = slugify($title);
    $handle = $base;
    $i = 1;
    while (in_array($handle, $existing)) {
        $handle = $base . '-' . $i++;
    }
    $existing[] = $handle;
    return $handle;
}
