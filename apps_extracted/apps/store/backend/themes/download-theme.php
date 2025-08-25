<?php
// backend/themes/download-theme.php
// Download a theme directory as a zip file

declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../functions.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Unauthorized');
}

$themeId = intval($_GET['theme_id'] ?? 0);
if (!$themeId) {
    http_response_code(400);
    exit('theme_id required');
}

$themeDir = __DIR__ . '/../../themes/theme' . $themeId;
if (!is_dir($themeDir)) {
    http_response_code(404);
    exit('Theme not found');
}

$tmp = tempnam(sys_get_temp_dir(), 'theme');
$zip = new ZipArchive();
$zip->open($tmp, ZipArchive::CREATE);
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($themeDir, FilesystemIterator::SKIP_DOTS)
);
foreach ($files as $file) {
    $path = $file->getPathname();
    $rel  = substr($path, strlen($themeDir) + 1);
    $zip->addFile($path, $rel);
}
$zip->close();

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="theme'.$themeId.'.zip"');
readfile($tmp);
unlink($tmp);

