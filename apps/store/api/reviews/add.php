<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$product_id = $input['product_id'] ?? null;
$author = $input['author'] ?? null;
$rating = $input['rating'] ?? null;
$title = $input['title'] ?? null;
$content = $input['content'] ?? null;

if (!$product_id || !$author || !$rating || !$title || !$content) {
    http_response_code(400);
    echo json_encode(['error' => 'All fields are required']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO reviews (product_id, author, rating, title, content, created_at) 
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([
        $product_id,
        $author,
        $rating,
        $title,
        $content
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Review submitted successfully'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error submitting review'
    ]);
}
