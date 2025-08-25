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
$category = trim($data['category'] ?? '');
$sectionJson = $data['section_json'] ?? '';

if (!$name || !$type || !$sectionJson) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid data']);
    exit;
}

$stmt = $pdo->prepare('INSERT INTO section_presets (name, type, category, section_json, created_by) VALUES (?, ?, ?, ?, ?)');
$stmt->execute([$name, $type, $category, json_encode($sectionJson), $_SESSION['user_id']]);

echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
