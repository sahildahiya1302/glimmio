<?php
require_once __DIR__ . '/../../config.php';
$data = json_decode(file_get_contents('php://input'), true);
$rel = ltrim($data['file'] ?? '', '/');
$content = $data['content'] ?? '';

$path = resolveThemeFile($rel, ['php','liquid','json','css','js','html']);
if (!$path) {
    $path = realpath(THEME_PATH . '/' . $rel);
}
$themeRoot = realpath(THEME_PATH);

if (!$rel || !$path || strpos(realpath($path), $themeRoot) !== 0 || !is_writable($path)) {
    http_response_code(403);
    exit('Cannot write to file.');
}
file_put_contents($path, $content);
echo 'OK';
