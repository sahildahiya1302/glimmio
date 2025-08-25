<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$id = intval($data['id'] ?? 0);

if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'ID is required']);
    exit;
}

try {
    $stmt = db_query('DELETE FROM customers WHERE id = :id', [':id' => $id]);
    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Customer not found']);
    } else {
        echo json_encode(['success' => true, 'message' => 'Customer deleted']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
