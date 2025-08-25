<?php
// admin/api/save-page-layout.php

require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$page = $data['page'] ?? null;
$layoutArray = $data['layout'] ?? null;

if (
    !$page ||
    strpos($page, '..') !== false ||
    !preg_match('/^[A-Za-z0-9._\/-]+$/', $page) ||
    !is_array($layoutArray)
) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

// Convert flat layout array into Shopify-style { sections: {}, order: [] }
$structuredLayout = [ 'sections' => [], 'order' => [] ];
foreach ($layoutArray as $section) {
    if (!isset($section['id'], $section['type'])) continue;
    $structuredLayout['sections'][$section['id']] = [
        'type' => $section['type'],
        'settings' => $section['settings'] ?? [],
        'blocks' => $section['blocks'] ?? []
    ];
    $structuredLayout['order'][] = $section['id'];
}

$layoutFile = THEME_PATH . "/templates/{$page}.json";
$dir = dirname($layoutFile);
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}
if (!is_writable($dir)) {
    http_response_code(500);
    echo json_encode(['error' => 'Cannot write layout file']);
    exit;
}

if (file_put_contents($layoutFile, json_encode($structuredLayout, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save layout']);
    exit;
}

echo json_encode(['success' => true]);
exit;
?>
