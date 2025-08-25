<?php
session_start();
require_once __DIR__ . '/../../config.php';
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
$section = $_GET['section'] ?? '';
if (!preg_match('/^[a-zA-Z0-9_-]+$/', $section)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid section']);
    exit;
}
$phpPath = THEME_PATH . "/sections/{$section}.php";
$liquidPath = THEME_PATH . "/sections/{$section}.liquid";
$path = null;
if (is_file($phpPath)) {
    $path = $phpPath;
} elseif (is_file($liquidPath)) {
    $path = $liquidPath;
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Not found']);
    exit;
}
header('Content-Type: application/json');
echo json_encode(['code' => file_get_contents($path)]);
