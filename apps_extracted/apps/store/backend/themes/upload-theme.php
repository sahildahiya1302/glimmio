<?php
// backend/themes/upload-theme.php
// Upload a zipped theme and extract it as a new theme

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

if (!isset($_FILES['theme_zip']) || $_FILES['theme_zip']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'No ZIP uploaded']);
    exit;
}

$zipFile = $_FILES['theme_zip']['tmp_name'];
$origName = basename($_FILES['theme_zip']['name']);
$name = pathinfo($origName, PATHINFO_FILENAME);

try {
    db_query('INSERT INTO themes (name) VALUES (:name)', [':name' => $name]);
    $newId = (int)db()->lastInsertId();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'DB insert failed']);
    exit;
}

$destDir = __DIR__ . '/../../themes/theme' . $newId;
if (!is_dir($destDir)) {
    mkdir($destDir, 0777, true);
}

$zip = new ZipArchive();
if ($zip->open($zipFile) === true) {
    $zip->extractTo($destDir);
    $zip->close();
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to extract ZIP']);
    exit;
}

// Remove macOS metadata folder if present
if (is_dir($destDir . '/__MACOSX')) {
    $it = new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator($destDir . '/__MACOSX', \FilesystemIterator::SKIP_DOTS),
        \RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($it as $file) {
        $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
    }
    rmdir($destDir . '/__MACOSX');
}

// If archive contained a single root folder, move its contents up
$entries = array_values(array_diff(scandir($destDir), ['.', '..']));
if (count($entries) === 1 && is_dir($destDir . '/' . $entries[0])) {
    $inner = $destDir . '/' . $entries[0];
    foreach (scandir($inner) as $f) {
        if ($f === '.' || $f === '..') continue;
        rename($inner . '/' . $f, $destDir . '/' . $f);
    }
    rmdir($inner);
}

// Normalize Shopify folder names
if (is_dir($destDir . '/layout') && !is_dir($destDir . '/layouts')) {
    rename($destDir . '/layout', $destDir . '/layouts');
}

function detectPlatform(string $dir): string {
    if (is_file($dir . '/config/settings_schema.json') || glob($dir . '/sections/*.liquid')) {
        return 'shopify';
    }
    if (is_file($dir . '/style.css') && is_file($dir . '/functions.php')) {
        return 'woocommerce';
    }
    if (is_file($dir . '/shopflo.config.json')) {
        return 'shopflo';
    }
    return 'unknown';
}

$platform = detectPlatform($destDir);

// Extract WooCommerce theme name if available
if ($platform === 'woocommerce' && is_file($destDir . '/style.css')) {
    $style = file_get_contents($destDir . '/style.css');
    if (preg_match('/^\s*Theme Name:\s*(.+)$/mi', $style, $m)) {
        $name = trim($m[1]);
    }
}

// Ensure standard directories
$dirs = ['sections', 'templates', 'assets', 'snippets', 'blocks', 'config', 'layouts'];
foreach ($dirs as $d) {
    if (!is_dir($destDir . '/' . $d)) {
        mkdir($destDir . '/' . $d, 0777, true);
    }
}

// Map WooCommerce folder structure to our conventions
if ($platform === 'woocommerce') {
    if (is_dir($destDir . '/template-parts')) {
        foreach (scandir($destDir . '/template-parts') as $f) {
            if ($f === '.' || $f === '..') continue;
            $src = $destDir . '/template-parts/' . $f;
            $dest = $destDir . '/sections/' . $f;
            if (is_file($src) && !is_file($dest)) {
                rename($src, $dest);
            }
        }
    }
    if (is_dir($destDir . '/snippets')) {
        foreach (scandir($destDir . '/snippets') as $f) {
            if ($f === '.' || $f === '..') continue;
            $src = $destDir . '/snippets/' . $f;
            $dest = $destDir . '/blocks/' . $f;
            if (is_file($src) && !is_file($dest)) {
                rename($src, $dest);
            }
        }
    }
    if (is_dir($destDir . '/woocommerce')) {
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($destDir . '/woocommerce', FilesystemIterator::SKIP_DOTS));
        foreach ($it as $file) {
            $rel = substr($file->getPathname(), strlen($destDir . '/woocommerce') + 1);
            $target = $destDir . '/templates/' . $rel;
            if (!is_dir(dirname($target))) {
                mkdir(dirname($target), 0777, true);
            }
            rename($file->getPathname(), $target);
        }
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($destDir . '/woocommerce', FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($it as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($destDir . '/woocommerce');
    }
}

// Build section schema files from Liquid templates
foreach (glob($destDir . '/sections/*.liquid') as $liquidFile) {
    $schemaPath = preg_replace('/\.liquid$/', '.schema.json', $liquidFile);
    if (!is_file($schemaPath)) {
        $schema = parseLiquidSchema($liquidFile);
        if ($schema) {
            file_put_contents($schemaPath, json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }
    }
}

$configPath = $destDir . '/theme.json';
$themeConf = [
    'name' => $name,
    'platform' => $platform
];
file_put_contents($configPath, json_encode($themeConf, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

// Import settings_data.json into DB for this theme
$settingsFile = $destDir . '/config/settings_data.json';
if (is_file($settingsFile)) {
    $settings = json_decode(file_get_contents($settingsFile), true);
    if (is_array($settings)) {
        foreach ($settings as $k => $v) {
            db_query('INSERT INTO theme_settings (theme_id, `key`, `value`) VALUES (:tid,:k,:v)', [
                ':tid' => $newId,
                ':k' => $k,
                ':v' => is_scalar($v) ? (string)$v : json_encode($v)
            ]);
        }
    }
}

echo json_encode(['success' => true, 'theme_id' => $newId, 'platform' => $platform]);