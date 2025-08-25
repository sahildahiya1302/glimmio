<?php
// API endpoint to save a versioned layout for a given page

require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$page = $data['page'] ?? null;
$layout = $data['layout'] ?? null;
$version = $data['version'] ?? null; // optional version label or timestamp

// Basic validation - allow nested paths, prevent traversal
if (!$page || strpos($page, '..') !== false || !preg_match('/^[A-Za-z0-9._\/-]+$/', $page) || !$layout) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

$versionsDir = THEME_PATH . "/layout_versions/{$page}";

if (!is_dir($versionsDir)) {
    mkdir($versionsDir, 0755, true);
}

$latestFile = "{$versionsDir}/layout_latest.json";
foreach (glob($versionsDir . '/layout_*.json') as $file) {
    @unlink($file);
}
if (file_put_contents($latestFile, json_encode($layout, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to save layout version']);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Layout version saved']);
exit;
?>