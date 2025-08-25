<?php
// admin/api/save-layout.php
require_once __DIR__ . '/../../config.php';
header('Content-Type: application/json');

// Simple error logging function
function log_error($message) {
    error_log(date('[Y-m-d H:i:s] ') . $message . PHP_EOL, 3, __DIR__ . '/../../logs/save-layout-errors.log');
}

// Read the input JSON and provide clear error handling
$raw = file_get_contents('php://input');
$input = json_decode($raw, true);
if ($input === null && json_last_error() !== JSON_ERROR_NONE) {
    $err = 'Malformed JSON payload: ' . json_last_error_msg();
    log_error($err . " Raw: " . substr($raw, 0, 2000));
    echo json_encode(['success' => false, 'error' => $err]);
    http_response_code(400);
    exit;
}

if (
    !$input || 
    !isset($input['page']) || 
    !isset($input['layout']) ||
    !is_array($input['layout']) || 
    !isset($input['layout']['sections']) || 
    !isset($input['layout']['order'])
) {
    $errorMsg = 'Invalid layout format. Expecting { sections: {}, order: [] }';
    log_error($errorMsg);
    echo json_encode([
        'success' => false, 
        'error' => $errorMsg
    ]);
    http_response_code(400);
    exit;
}

$page = $input['page'];
$layout = $input['layout'];

// Validate page name (allow nested paths)
if (strpos($page, '..') !== false || !preg_match('/^[A-Za-z0-9._\/-]+$/', $page)) {
    $errorMsg = 'Invalid page name: ' . $page;
    log_error($errorMsg);
    echo json_encode(['success' => false, 'error' => $errorMsg]);
    http_response_code(400);
    exit;
}

// Path to save layout file
$layoutFile = THEME_PATH . "/templates/{$page}.json";
$dir = dirname($layoutFile);

// Ensure directory exists and is writable
if (!is_dir($dir)) {
    if (!mkdir($dir, 0755, true)) {
        $errorMsg = 'Failed to create directory: ' . $dir;
        log_error($errorMsg);
        echo json_encode(['success' => false, 'error' => $errorMsg]);
        http_response_code(500);
        exit;
    }
}
if (!is_writable($dir)) {
    $errorMsg = 'Cannot write layout file: Directory not writable: ' . $dir;
    log_error($errorMsg);
    echo json_encode(['success' => false, 'error' => $errorMsg]);
    http_response_code(500);
    exit;
}

// Save layout
$jsonData = json_encode($layout, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
if ($jsonData === false) {
    $errorMsg = 'Failed to encode layout JSON: ' . json_last_error_msg();
    log_error($errorMsg);
    echo json_encode(['success' => false, 'error' => $errorMsg]);
    http_response_code(500);
    exit;
}

if (@file_put_contents($layoutFile, $jsonData) === false) {
    $errorMsg = 'Failed to write layout file: ' . $layoutFile;
    log_error($errorMsg);
    echo json_encode(['success' => false, 'error' => $errorMsg]);
    http_response_code(500);
    exit;
}

session_start();
$_SESSION['live_preview'][$page] = convertCompiledLayoutToEditor($layout);

echo json_encode(['success' => true]);
exit;
?>
