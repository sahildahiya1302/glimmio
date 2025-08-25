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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid collection ID']);
    exit;
}

try {
    $conn = db();

    // Delete collection products links
    $stmt = $conn->prepare("DELETE FROM collection_product WHERE collection_id = ?");
    $stmt->execute([$id]);

    // Delete collection rules
    $stmt = $conn->prepare("DELETE FROM collection_rules WHERE collection_id = ?");
    $stmt->execute([$id]);

    // Delete the collection itself
    $stmt = $conn->prepare("DELETE FROM collections WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode(['success' => true, 'message' => 'Collection deleted successfully']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
