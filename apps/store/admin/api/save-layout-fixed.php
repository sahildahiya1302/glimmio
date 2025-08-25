<?php
// admin/api/save-layout-fixed.php - Fixed version for layout saving
require_once __DIR__ . '/../../config.php';
header('Content-Type: application/json');

function log_error($message) {
    error_log(date('[Y-m-d H:i:s] ') . $message . PHP_EOL, 3, __DIR__ . '/../../logs/save-layout-errors.log');
}

// Read and validate input
$raw = file_get_contents('php://input');
$input = json_decode($raw, true);

if ($input === null) {
    $err = 'Invalid JSON: ' . json_last_error_msg();
    log_error($err);
    echo json_encode(['success' => false, 'error' => $err]);
    http_response_code(400);
    exit;
}

// Accept both old and new format
$page = $input['page'] ?? '';
$layout = $input['layout'] ?? $input;

// Normalize layout format
if (isset($layout['sections']) && isset($layout['order'])) {
    // New format
    $sections = $layout['sections'];
    $order = $layout['order'];
} else if (is_array($layout)) {
    // Old array format
    $sections = [];
    $order = [];
    foreach ($layout as $index => $section) {
        $id = $section['id'] ?? "section_{$index}";
        $sections[$id] = $section;
        $order[] = $id;
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid layout format']);
    http_response_code(400);
    exit;
}

// Validate page name
if (!preg_match('/^[A-Za-z0-9._\/-]+$/', $page)) {
    echo json_encode(['success' => false, 'error' => 'Invalid page name']);
    http_response_code(400);
    exit;
}

// Ensure directory exists
$layoutFile = THEME_PATH . "/templates/{$page}.json";
$dir = dirname($layoutFile);

if (!is_dir($dir)) {
    if (!mkdir($dir, 0755, true)) {
        echo json_encode(['success' => false, 'error' => 'Cannot create directory']);
        http_response_code(500);
        exit;
    }
}

// Save layout
$normalizedLayout = [
    'sections' => $sections,
    'order' => $order
];

$jsonData = json_encode($normalizedLayout, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
if (file_put_contents($layoutFile, $jsonData) === false) {
    echo json_encode(['success' => false, 'error' => 'Failed to write file']);
    http_response_code(500);
    exit;
}

echo json_encode(['success' => true]);
exit;
?>
