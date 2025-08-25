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

$themeId = intval($data['theme_id'] ?? 0);
$layoutJson = $data['layout_json'] ?? '';

if (!$themeId) {
    http_response_code(400);
    echo json_encode(['error' => 'Theme ID is required']);
    exit;
}

if (!$layoutJson) {
    http_response_code(400);
    echo json_encode(['error' => 'Layout JSON is required']);
    exit;
}

try {
    $stmt = db_query('UPDATE themes SET layout_json = :layout_json WHERE id = :id', [
        ':layout_json' => $layoutJson,
        ':id' => $themeId,
    ]);
    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Theme not found']);
    } else {
        echo json_encode(['success' => true, 'message' => 'Theme layout saved']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
