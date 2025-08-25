<?php
// backend/themes/duplicate-theme.php
// Duplicate an existing theme by copying its database entry and files

declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$themeId = intval($_POST['theme_id'] ?? 0);
if (!$themeId) {
    http_response_code(400);
    echo json_encode(['error' => 'theme_id required']);
    exit;
}

$theme = db_query('SELECT name, settings FROM themes WHERE id = :id', [':id' => $themeId])->fetch(PDO::FETCH_ASSOC);
if (!$theme) {
    http_response_code(404);
    echo json_encode(['error' => 'Theme not found']);
    exit;
}

$newName = $theme['name'] . ' Copy';
try {
    db_query('INSERT INTO themes (name, settings) VALUES (:name, :settings)', [
        ':name' => $newName,
        ':settings' => $theme['settings']
    ]);
    $newId = (int)db()->lastInsertId();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'DB insert failed']);
    exit;
}

$src = __DIR__ . '/../../themes/theme' . $themeId;
if (!is_dir($src)) {
    $src = __DIR__ . '/../../themes/default';
}
$dest = __DIR__ . '/../../themes/theme' . $newId;

function copy_dir(string $src, string $dest): void {
    if (!is_dir($dest)) {
        mkdir($dest, 0777, true);
    }
    foreach (scandir($src) as $file) {
        if ($file === '.' || $file === '..') continue;
        $srcPath = $src . '/' . $file;
        $destPath = $dest . '/' . $file;
        if (is_dir($srcPath)) {
            copy_dir($srcPath, $destPath);
        } else {
            copy($srcPath, $destPath);
        }
    }
}

if (is_dir($src)) {
    copy_dir($src, $dest);
}

// ensure blocks directory exists for ecommerce-compatible structure
$blocksDir = $dest . '/blocks';
if (!is_dir($blocksDir) && is_dir($dest . '/snippets')) {
    mkdir($blocksDir, 0777, true);
    foreach (scandir($dest . '/snippets') as $file) {
        if ($file === '.' || $file === '..') continue;
        copy($dest . '/snippets/' . $file, $blocksDir . '/' . $file);
    }
}

$configPath = $dest . '/theme.json';
$conf = is_file($configPath) ? json_decode(file_get_contents($configPath), true) : [];
$conf['name'] = $newName;
if (!isset($conf['platform'])) {
    $conf['platform'] = 'native';
}
file_put_contents($configPath, json_encode($conf, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo json_encode(['success' => true, 'theme_id' => $newId]);
