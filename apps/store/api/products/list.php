<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

$limit = $_GET['limit'] ?? 10;
$offset = $_GET['offset'] ?? 0;
$category = $_GET['category'] ?? null;
$search = $_GET['search'] ?? null;

try {
    $query = "SELECT * FROM products WHERE 1=1";
    $params = [];
    
    if ($category) {
        $query .= " AND category = ?";
        $params[] = $category;
    }
    
    if ($search) {
        $query .= " AND (title LIKE ? OR description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    $query .= " ORDER BY id DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'products' => $products,
        'total' => count($products)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error loading products']);
}
