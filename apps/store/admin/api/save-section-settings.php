<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

// Validate and sanitize input
$pageRaw = $data['page'] ?? '';
$sectionId = $data['section_id'] ?? '';
$settings = $data['settings'] ?? [];

if (!is_string($pageRaw) || strpos($pageRaw, '..') !== false || !preg_match('/^[A-Za-z0-9._\/-]+$/', $pageRaw)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid page name']);
    exit;
}
if (!$sectionId || !is_string($sectionId)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid section ID']);
    exit;
}
if (!is_array($settings)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Settings must be an array']);
    exit;
}

$page = $pageRaw;
$layoutPath = THEME_PATH . "/templates/{$page}.json";
$dir = dirname($layoutPath);
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

// If layout file doesn't exist, create a base one
if (!file_exists($layoutPath)) {
    $emptyLayout = [
        'sections' => [],
        'order' => []
    ];
    file_put_contents($layoutPath, json_encode($emptyLayout, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

$layout = json_decode(file_get_contents($layoutPath), true);
if (!is_array($layout)) {
    $layout = ['sections' => [], 'order' => []];
}

// Ensure structure
if (!isset($layout['sections']) || !is_array($layout['sections'])) {
    $layout['sections'] = [];
}
if (!isset($layout['order']) || !is_array($layout['order'])) {
    $layout['order'] = [];
}

// Update or insert section
if (!isset($layout['sections'][$sectionId])) {
    // If not found, create new section block
    $layout['sections'][$sectionId] = [
        'type' => explode('-', $sectionId)[0],
        'settings' => $settings,
        'blocks' => []
    ];
    if (!in_array($sectionId, $layout['order'], true)) {
        $layout['order'][] = $sectionId;
    }
} else {
    $layout['sections'][$sectionId]['settings'] = $settings;
}

// Save back
$saved = file_put_contents($layoutPath, json_encode($layout, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

if ($saved !== false) {
    session_start();
    $_SESSION['live_preview'][$page] = convertCompiledLayoutToEditor($layout);
    echo json_encode(['success' => true, 'message' => 'Section settings saved']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to write layout file']);
}
exit;
