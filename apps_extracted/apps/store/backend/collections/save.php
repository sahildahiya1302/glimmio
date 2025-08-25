<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $rule_based = isset($_POST['rule_based']) && $_POST['rule_based'] == '1';

    if (!$title) throw new Exception("Title is required");

    if (!$slug) {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $title));
    }

    $now = date('Y-m-d H:i:s');
    $conn = db();

    if ($id > 0) {
        // Update collection
        $stmt = $conn->prepare("UPDATE collections SET title = ?, slug = ?, rule_based = ?, updated_at = ? WHERE id = ?");
        $stmt->execute([$title, $slug, $rule_based, $now, $id]);
    } else {
        // Create collection
        $stmt = $conn->prepare("INSERT INTO collections (title, slug, rule_based, created_at, updated_at) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $slug, $rule_based, $now, $now]);
        $id = (int)$conn->lastInsertId();
    }

    // Remove old links and rules
    $conn->prepare("DELETE FROM collection_product WHERE collection_id = ?")->execute([$id]);
    $conn->prepare("DELETE FROM collection_rules WHERE collection_id = ?")->execute([$id]);

    if ($rule_based) {
        // Save Rules
        $fields = $_POST['rule_field'] ?? [];
        $operators = $_POST['rule_operator'] ?? [];
        $values = $_POST['rule_value'] ?? [];

        for ($i = 0; $i < count($fields); $i++) {
            $field = trim($fields[$i]);
            $operator = trim($operators[$i]);
            $value = trim($values[$i]);

            if (!$field || !$operator || !$value) continue;

            $stmt = $conn->prepare("INSERT INTO collection_rules (collection_id, field, operator, value) VALUES (?, ?, ?, ?)");
            $stmt->execute([$id, $field, $operator, $value]);
        }

        // Re-evaluate and assign products
        $products = $conn->query("SELECT * FROM products")->fetchAll(PDO::FETCH_ASSOC);

        foreach ($products as $product) {
            $match = evaluate_collection_rules($product, $id);
            if ($match) {
                $stmt = $conn->prepare("INSERT IGNORE INTO collection_product (collection_id, product_id) VALUES (?, ?)");
                $stmt->execute([$id, $product['id']]);
            }
        }

    } else {
        // Manual product IDs
        $manual = $_POST['manual_products'] ?? [];
        foreach ($manual as $pid) {
            $pid = (int)$pid;
            if ($pid) {
                $stmt = $conn->prepare("INSERT IGNORE INTO collection_product (collection_id, product_id) VALUES (?, ?)");
                $stmt->execute([$id, $pid]);
            }
        }
    }

    echo json_encode(['success' => true]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}


// --------------------
// Helper: Evaluate Rules
// --------------------
function evaluate_collection_rules(array $product, int $collectionId): bool {
    $stmt = db()->prepare("SELECT field, operator, value FROM collection_rules WHERE collection_id = ?");
    $stmt->execute([$collectionId]);
    $rules = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rules as $rule) {
        $field = $rule['field'];
        $operator = strtolower($rule['operator']);
        $value = strtolower($rule['value']);

        $productValue = strtolower($product[$field] ?? '');

        switch ($operator) {
            case 'equals':
                if ($productValue !== $value) return false;
                break;
            case 'not equals':
            case '!=':
                if ($productValue === $value) return false;
                break;
            case 'contains':
                if (!str_contains($productValue, $value)) return false;
                break;
            case 'not contains':
                if (str_contains($productValue, $value)) return false;
                break;
            case 'starts with':
                if (!str_starts_with($productValue, $value)) return false;
                break;
            case 'ends with':
                if (!str_ends_with($productValue, $value)) return false;
                break;
            default:
                continue 2;
        }
    }

    return true;
}
