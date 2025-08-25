<?php
// backend/themes/create-theme.php
// API endpoint to create a blank theme by copying the default theme

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

$name = trim($_POST['name'] ?? 'New Theme');
if ($name === '') {
    $name = 'New Theme';
}

// Insert new theme row
try {
    db_query('INSERT INTO themes (name) VALUES (:name)', [':name' => $name]);
    $newId = (int)db()->lastInsertId();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'DB insert failed']);
    exit;
}

$src = realpath(__DIR__ . '/../../themes/default');
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

if ($src && is_dir($src)) {
    copy_dir($src, $dest);
}

// Ensure new ecommerce-style blocks directory exists
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
$conf['name'] = $name;
$conf['platform'] = 'native';
file_put_contents($configPath, json_encode($conf, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo json_encode(['success' => true, 'theme_id' => $newId]);