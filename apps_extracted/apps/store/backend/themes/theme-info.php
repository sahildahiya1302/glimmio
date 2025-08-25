<?php
// backend/themes/theme-info.php
// Return theme config and platform info

declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../functions.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$themeId = intval($_GET['theme_id'] ?? 0);
if (!$themeId) {
    http_response_code(400);
    echo json_encode(['error' => 'Theme ID required']);
    exit;
}

$themeDir = __DIR__ . '/../../themes/theme' . $themeId;
if (!is_dir($themeDir)) {
    http_response_code(404);
    echo json_encode(['error' => 'Theme not found']);
    exit;
}

$config = [];
$configPath = $themeDir . '/theme.json';
if (is_file($configPath)) {
    $config = json_decode(file_get_contents($configPath), true) ?: [];
}

$config['sections'] = listAvailableSections('theme' . $themeId);

echo json_encode($config);

