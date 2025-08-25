<?php
// API endpoint to get schema.json for a given section type

require_once __DIR__ . '/../../config.php';

$sectionType = $_GET['section'] ?? null;

if (!$sectionType) {
    http_response_code(400);
    echo json_encode(['error' => 'Section type required']);
    exit;
}

$schemaFile = THEME_PATH . "/sections/{$sectionType}.schema.json";

if (file_exists($schemaFile)) {
    header('Content-Type: application/json');
    echo file_get_contents($schemaFile);
    exit;
}

$liquidFile = THEME_PATH . "/sections/{$sectionType}.liquid";
if (is_file($liquidFile)) {
    $content = file_get_contents($liquidFile);
    if (preg_match('/{%\s*schema\s*%}(.*?){%\s*endschema\s*%}/s', $content, $m)) {
        header('Content-Type: application/json');
        echo trim($m[1]);
        exit;
    }
}

http_response_code(404);
echo json_encode(['error' => 'Schema not found']);
exit;
