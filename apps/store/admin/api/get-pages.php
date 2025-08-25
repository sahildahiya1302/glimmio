<?php
// API endpoint to list available pages (template JSON files) for theme editor page selector

require_once __DIR__ . '/../../config.php';


$templatesDir = THEME_PATH . '/templates';

$pages = [];

$it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($templatesDir, FilesystemIterator::SKIP_DOTS)
);
foreach ($it as $file) {
    if (!$file->isFile()) continue;
    $ext = $file->getExtension();
    if ($ext !== 'json' && $ext !== 'liquid') continue;
    $relPath = substr($file->getPathname(), strlen($templatesDir) + 1);
    $relPath = preg_replace('/\.(json|liquid)$/', '', $relPath);
    $pages[] = str_replace('\\', '/', $relPath);
}

sort($pages);

header('Content-Type: application/json');
echo json_encode($pages);
exit;
