<?php
require_once __DIR__ . '/../../config.php';

$rel = ltrim($_GET['file'] ?? '', '/');
$path = resolveThemeFile($rel, ['php','liquid','json','css','js','html']);
if (!$path) {
    $path = realpath(THEME_PATH . '/' . $rel);
}
$themeRoot = realpath(THEME_PATH);

if (!$rel || !$path || strpos(realpath($path), $themeRoot) !== 0 || !file_exists($path)) {
    http_response_code(403);
    exit('Access denied or file not found.');
}
echo file_get_contents($path);
