<?php
session_start();
require_once __DIR__ . '/../../config.php';
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
$data = json_decode(file_get_contents('php://input'), true);
$section = $data['section'] ?? '';
$code = $data['code'] ?? '';
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
    // default to PHP if neither exists
    $path = $phpPath;
}

if (file_put_contents($path, $code) === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save']);
    exit;
}
echo json_encode(['success' => true]);
