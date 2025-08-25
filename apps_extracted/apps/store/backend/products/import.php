<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request');
    }

    if (!isset($_FILES['csv']) || $_FILES['csv']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('CSV file missing or invalid');
    }

    $map = $_POST['map'] ?? [];
    $mode = $_POST['mode'] ?? 'append'; // append, overwrite, replace

    $validModes = ['append', 'overwrite', 'replace'];
    if (!in_array($mode, $validModes)) throw new Exception("Invalid import mode");

    $required = ['title', 'sku', 'price', 'image', 'type'];
    foreach ($required as $field) {
        if (empty($map[$field])) {
            throw new Exception("Missing mapping for required field: $field");
        }
    }

    if ($mode === 'replace') {
        db()->exec("DELETE FROM product_variants");
        db()->exec("DELETE FROM products");
        db()->exec("DELETE FROM product_images");
    }

    $tmpName = $_FILES['csv']['tmp_name'];
    if (!file_exists($tmpName)) throw new Exception("Uploaded file not found");

    $handle = fopen($tmpName, 'r');
    if (!$handle) throw new Exception('Failed to read uploaded file');

    $headers = fgetcsv($handle);
    if (!$headers) throw new Exception('Invalid CSV header');
    $headers = array_map('trim', $headers);

    $products = [];
    while (($row = fgetcsv($handle)) !== false) {
        if (count($row) !== count($headers)) continue;
        $data = array_combine($headers, $row);
        if (!$data) continue;

        $sku = trim($data[$map['sku']] ?? '');
        $title = trim($data[$map['title']] ?? '');
        if (!$sku || !$title) continue;

        $key = strtolower(preg_replace('/[^a-z0-9]+/', '-', $title));
        $products[$key][] = array_map('trim', $data);
    }
    fclose($handle);

    if (empty($products)) throw new Exception("No valid products found in CSV");

    $existingHandles = fetch_existing_handles();

    foreach ($products as $group) {
        $first = $group[0];
        $productTitle = $first[$map['title']];
        $handle = generate_unique_handle($productTitle, $existingHandles);

        $collectionId = get_or_create_collection($first[$map['type']]);

        // If overwrite mode and handle exists, delete old product + variants
        $existingId = get_product_id_by_handle($handle);
        if ($mode === 'overwrite' && $existingId) {
            db()->prepare("DELETE FROM product_variants WHERE product_id = ?")->execute([$existingId]);
            db()->prepare("DELETE FROM products WHERE id = ?")->execute([$existingId]);
            $existingHandles = array_diff($existingHandles, [$handle]);
        }

        // Insert new product
        $stmt = db()->prepare("INSERT INTO products (title, type, description, vendor, tags, handle, published, collection_id, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $productTitle,
            $first[$map['type']],
            $first[$map['body'] ?? 'Body (HTML)'] ?? '',
            $first[$map['vendor'] ?? 'Vendor'] ?? '',
            $first[$map['tags'] ?? 'Tags'] ?? '',
            $handle,
            true,
            $collectionId,
            date('Y-m-d H:i:s'),
            date('Y-m-d H:i:s')
        ]);
        $productId = db()->lastInsertId();

        // Insert variants
        foreach ($group as $variant) {
            $stmt2 = db()->prepare("INSERT INTO product_variants (product_id, sku, price, option_label, image_url, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt2->execute([
                $productId,
                $variant[$map['sku']],
                $variant[$map['price']],
                $variant[$map['option'] ?? 'Option1 Value'] ?? '',
                $variant[$map['image']],
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s')
            ]);
        }

        $existingHandles[] = $handle;
    }

    echo json_encode(['success' => true]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}


// ------------------------------------
// Helper Functions
// ------------------------------------

function get_or_create_collection(string $name): int {
    $stmt = db()->prepare("SELECT id FROM collections WHERE title = ?");
    $stmt->execute([$name]);
    if ($row = $stmt->fetch()) return (int)$row['id'];

    $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/', '-', $name), '-'));
    $stmt = db()->prepare("INSERT INTO collections (title, slug, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
    $stmt->execute([$name, $slug]);
    return (int)db()->lastInsertId();
}

function fetch_existing_handles(): array {
    $stmt = db()->query("SELECT handle FROM products");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function get_product_id_by_handle(string $handle): ?int {
    $stmt = db()->prepare("SELECT id FROM products WHERE handle = ?");
    $stmt->execute([$handle]);
    $id = $stmt->fetchColumn();
    return $id ? (int)$id : null;
}

function generate_unique_handle(string $title, array &$existing): string {
    $base = strtolower(trim(preg_replace('/[^a-z0-9]+/', '-', $title), '-'));
    $handle = $base;
    $i = 1;
    while (in_array($handle, $existing)) {
        $handle = $base . '-' . $i++;
    }
    $existing[] = $handle;
    return $handle;
}
