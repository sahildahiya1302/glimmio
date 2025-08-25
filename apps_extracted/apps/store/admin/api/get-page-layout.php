<?php
// API endpoint to get JSON layout for a given page

require_once __DIR__ . '/../../config.php';

$page = $_GET['page'] ?? 'index';

// allow nested paths but prevent directory traversal
if (strpos($page, '..') !== false || !preg_match('/^[A-Za-z0-9._\/-]+$/', $page)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid page']);
    exit;
}

$layoutFile = THEME_PATH . "/templates/{$page}.json";
$liquidFile = THEME_PATH . "/templates/{$page}.liquid";

if (file_exists($layoutFile)) {
    header('Content-Type: application/json');
    $content = file_get_contents($layoutFile);
    $data = json_decode($content, true);
} elseif (file_exists($liquidFile)) {
    header('Content-Type: application/json');
    $data = ['sections' => [], 'order' => [], 'liquid' => true, 'code' => file_get_contents($liquidFile)];
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Layout not found']);
    exit;
}

// Convert from old format with "sections" as object with keys to new format with array of sections
if (isset($data['sections']) && !is_array($data['sections'])) {
    $sections = [];
    foreach ($data['order'] ?? [] as $key) {
        if (isset($data['sections'][$key])) {
            $section = $data['sections'][$key];
            // Add the key as an id for reference if needed
            $section['id'] = $key;
            $sections[] = $section;
        }
    }
    $data['sections'] = $sections;
}

echo json_encode($data);
exit;
