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
$name = trim($data['name'] ?? '');
$type = trim($data['type'] ?? '');
$layoutJson = $data['layout_json'] ?? '';
$tags = trim($data['tags'] ?? '');

if (!$name || !$type || !$layoutJson) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid data']);
    exit;
}

$stmt = $pdo->prepare('INSERT INTO page_templates (name, type, layout_json, tags, created_by) VALUES (?, ?, ?, ?, ?)');
$stmt->execute([$name, $type, json_encode($layoutJson), $tags, $_SESSION['user_id']]);

echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
