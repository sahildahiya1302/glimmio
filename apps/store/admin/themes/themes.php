<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';
require_once __DIR__ . '/../../src/ThemeEngine/ThemeDetector.php';
require_once __DIR__ . '/../../src/ThemeEngine/ThemeConverter.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /backend/auth/login.php');
    exit;
}

$pageTitle = 'Theme Management';

$message = '';
$error   = '';

function copy_dir(string $src, string $dest): void {
    if (!is_dir($dest)) {
        mkdir($dest, 0777, true);
    }
    foreach (scandir($src) as $file) {
        if ($file === '.' || $file === '..') continue;
        $srcPath  = $src . '/' . $file;
        $destPath = $dest . '/' . $file;
        if (is_dir($srcPath)) {
            copy_dir($srcPath, $destPath);
        } else {
            copy($srcPath, $destPath);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action  = $_POST['action'] ?? '';
    $themeId = $_POST['theme_id'] ?? null;

    if ($action === 'activate' && $themeId) {
        db_query('UPDATE themes SET active = 0');
        db_query('UPDATE themes SET active = 1 WHERE id = :id', [':id' => $themeId]);
        $_SESSION['flash_message'] = 'Theme activated successfully.';
    } elseif ($action === 'duplicate' && $themeId) {
        $theme = db_query('SELECT name, settings FROM themes WHERE id = :id', [':id' => $themeId])->fetch(PDO::FETCH_ASSOC);
        if ($theme) {
            db_query('INSERT INTO themes (name, settings) VALUES (:name, :settings)', [
                ':name'     => $theme['name'] . ' Copy',
                ':settings' => $theme['settings']
            ]);
            $newId  = (int)db()->lastInsertId();
            $srcDir = __DIR__ . '/../../themes/theme' . $themeId;
            if (!is_dir($srcDir)) {
                $srcDir = __DIR__ . '/../../themes/default';
            }
            $destDir = __DIR__ . '/../../themes/theme' . $newId;
            copy_dir($srcDir, $destDir);
            $_SESSION['flash_message'] = 'Theme duplicated successfully.';
        }
    } elseif ($action === 'delete' && $themeId) {
        db_query('DELETE FROM themes WHERE id = :id', [':id' => $themeId]);
        $_SESSION['flash_message'] = 'Theme deleted successfully.';
    }

    header('Location: /admin/themes/themes.php');
    exit;
}

$themes = db_query('SELECT id, name, created_at, active FROM themes ORDER BY created_at DESC')->fetchAll();
if (!$themes) {
    $themes[] = ['id' => 1, 'name' => 'Default Theme', 'created_at' => date('Y-m-d'), 'active' => 1];
} else {
    $hasDefault = false;
    $hasActive  = false;
    foreach ($themes as $t) {
        if ((int)$t['id'] === 1) {
            $hasDefault = true;
            if (!empty($t['active'])) {
                $hasActive = true;
            }
        } elseif (!empty($t['active'])) {
            $hasActive = true;
        }
    }
    if (!$hasDefault) {
        array_unshift($themes, ['id' => 1, 'name' => 'Default Theme', 'created_at' => date('Y-m-d'), 'active' => $hasActive ? 0 : 1]);
        if (!$hasActive) {
            $hasActive = true;
        }
    }
    if (!$hasActive) {
        $themes[0]['active'] = 1; // ensure default theme active
    }
}

foreach ($themes as &$theme) {
    if (!isset($theme['active'])) {
        $theme['active'] = 0;
    }
    $folder       = ($theme['id'] == 1) ? 'default' : 'theme' . $theme['id'];
    $themeDir     = __DIR__ . '/../../themes/' . $folder;
    $theme['path'] = $themeDir;
    $theme['preview']   = getThemeScreenshot($folder);
    $theme['templates'] = glob($themeDir . '/templates/*.{json,liquid,php,html}', GLOB_BRACE) ?: [];
}
unset($theme);

$activeTheme = 'default';
foreach ($themes as $t) {
    if (!empty($t['active'])) {
        $activeTheme = ($t['id'] == 1) ? 'default' : 'theme' . $t['id'];
        break;
    }
}

// Helper functions
function validateThemeStructure($themePath) {
    // Support for Shopify theme structure
    if (is_dir($themePath . '/layout') && file_exists($themePath . '/layout/theme.liquid')) {
        return true;
    }
    
    // Check for WooCommerce theme structure
    if (file_exists($themePath . '/style.css') && file_exists($themePath . '/index.php')) {
        return true;
    }
    
    // Check for WooCommerce templates directory
    if (is_dir($themePath . '/templates') && file_exists($themePath . '/templates/index.php')) {
        return true;
    }
    
    // Check for Shopify templates directory
    if (is_dir($themePath . '/templates') && glob($themePath . '/templates/*.liquid')) {
        return true;
    }
    
    // Check for our custom structure
    $requiredFiles = ['templates', 'config'];
    foreach ($requiredFiles as $dir) {
        if (!is_dir($themePath . '/' . $dir)) {
            return false;
        }
    }
    
    $templates = glob($themePath . '/templates/*.{json,liquid,php,html}', GLOB_BRACE);
    return !empty($templates);
}

function createThemeConfig($themePath, $themeName) {
    $configPath = $themePath . '/config/settings_data.json';
    if (!file_exists($configPath)) {
        $config = [
            'name' => $themeName,
            'version' => '1.0.0',
            'author' => 'Store Admin',
            'description' => 'Custom theme',
            'settings' => [
                'colors' => [
                    'primary' => '#007bff',
                    'secondary' => '#6c757d',
                    'success' => '#28a745',
                    'danger' => '#dc3545'
                ],
                'typography' => [
                    'font_family' => 'Arial, sans-serif',
                    'font_size' => '16px'
                ]
            ]
        ];
        file_put_contents($configPath, json_encode($config, JSON_PRETTY_PRINT));
    }
}

function createThemeFromBase($themeName, $baseTheme) {
    $themesDir = __DIR__ . '/../../themes/';
    $sourcePath = $themesDir . $baseTheme;
    $targetPath = $themesDir . $themeName;
    
    if (!is_dir($sourcePath)) {
        return ['success' => false, 'error' => 'Base theme not found'];
    }
    
    if (is_dir($targetPath)) {
        return ['success' => false, 'error' => 'Theme already exists'];
    }
    
    if (copyDirectory($sourcePath, $targetPath)) {
        createThemeConfig($targetPath, $themeName);
        return ['success' => true, 'message' => "Theme '$themeName' created successfully"];
    }
    
    return ['success' => false, 'error' => 'Failed to create theme'];
}

function duplicateTheme($sourceTheme, $newThemeName) {
    return createThemeFromBase($newThemeName, $sourceTheme);
}

function deleteTheme($themeName) {
    $themesDir = __DIR__ . '/../../themes/';
    $themePath = $themesDir . $themeName;
    
    if (!is_dir($themePath) || $themeName === 'default') {
        return ['success' => false, 'error' => 'Cannot delete default theme'];
    }
    
    return deleteDirectory($themePath) 
        ? ['success' => true, 'message' => "Theme '$themeName' deleted successfully"]
        : ['success' => false, 'error' => 'Failed to delete theme'];
}

function downloadTheme($themeName, $themePath) {
    $zip = new ZipArchive();
    $zipName = tempnam(sys_get_temp_dir(), 'theme_');
    
    if ($zip->open($zipName, ZipArchive::CREATE) === true) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($themePath),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($themePath) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }
        
        $zip->close();
        
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $themeName . '.zip"');
        header('Content-Length: ' . filesize($zipName));
        readfile($zipName);
        unlink($zipName);
    }
}

function getAvailableThemes() {
    $themesDir = __DIR__ . '/../../themes/';
    $themes = [];
    
    foreach (glob($themesDir . '*', GLOB_ONLYDIR) as $themeDir) {
        $themeName = basename($themeDir);
        $configPath = $themeDir . '/config/settings_data.json';
        
        $theme = [
            'name' => $themeName,
            'id' => $themeName,
            'path' => $themeDir,
            'active' => ($_SESSION['active_theme'] ?? 'default') === $themeName,
            'preview' => getThemeScreenshot($themeName),
            'templates' => []
        ];
        
        if (file_exists($configPath)) {
            $config = json_decode(file_get_contents($configPath), true);
            $theme = array_merge($theme, $config);
        }
        
        $templatesDir = $themeDir . '/templates';
        if (is_dir($templatesDir)) {
            foreach (glob($templatesDir . '/*.{json,liquid,php,html}', GLOB_BRACE) as $template) {
                $theme['templates'][] = basename($template);
            }
        }
        
        $themes[] = $theme;
    }
    
    return $themes;
}

function copyDirectory($source, $dest) {
    if (!is_dir($dest)) {
        mkdir($dest, 0755, true);
    }
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $item) {
        if ($item->isDir()) {
            mkdir($dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
        } else {
            copy($item->getPathname(), $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
        }
    }
    
    return true;
}

function deleteDirectory($dir) {
    if (!is_dir($dir)) {
        return false;
    }
    
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $path = $dir . DIRECTORY_SEPARATOR . $file;
        if (is_dir($path)) {
            deleteDirectory($path);
        } else {
            @unlink($path);
        }
    }
    
    return @rmdir($dir);
}

function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function getThemeScreenshot(string $themeFolder): ?string {
    $themeDir = __DIR__ . '/../../themes/' . $themeFolder;
    $previewFile = $themeDir . '/preview.png';
    if (file_exists($previewFile) && filesize($previewFile) > 0) {
        return '/themes/' . $themeFolder . '/preview.png';
    }

    $baseUrl = defined('BASE_URL') ? BASE_URL : '';
    $url = $baseUrl . '/?preview=1&theme=' . urlencode($themeFolder);
    $data = fetchPageSpeedInsights($url, 'desktop');
    if ($data) {
        $shot = $data['lighthouseResult']['audits']['final-screenshot']['details']['data'] ?? '';
        if ($shot) {
            $shot = explode(',', $shot, 2);
            $imgData = base64_decode(end($shot));
            if ($imgData !== false) {
                if (!is_dir(dirname($previewFile))) {
                    mkdir(dirname($previewFile), 0777, true);
                }
                file_put_contents($previewFile, $imgData);
                return '/themes/' . $themeFolder . '/preview.png';
            }
        }
    }
    return null;
}

// Performance metrics for active theme
if (!function_exists('fetchPageSpeedInsights')) {
    function fetchPageSpeedInsights(string $url, string $strategy = 'mobile'): ?array
    {
        $query = http_build_query([
            'url' => $url,
            'strategy' => $strategy,
            'category' => 'performance'
        ]);
        $apiUrl = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed?' . $query;
        $context = stream_context_create(['http' => ['timeout' => 15]]);
        $json = @file_get_contents($apiUrl, false, $context);
        if (!$json) {
            return null;
        }
        $data = json_decode($json, true);
        if (!$data || !isset($data['lighthouseResult'])) {
            return null;
        }
        return $data;
    }
}

if (!function_exists('getPerformanceMetrics')) {
    function getPerformanceMetrics($themeName)
    {
        $baseUrl = defined('BASE_URL') ? BASE_URL : '';
        $url = $baseUrl . '/?preview=1&theme=' . urlencode($themeName);
        $data = fetchPageSpeedInsights($url, 'mobile');

        if (!$data) {
            return [
                'fcp' => 'N/A',
                'lcp' => 'N/A',
                'tbt' => 'N/A',
                'cls' => 'N/A',
                'si' => 'N/A',
                'score' => 'N/A',
                'tips' => []
            ];
        }

        $audits = $data['lighthouseResult']['audits'];
        $perf = $data['lighthouseResult']['categories']['performance'];

        $tips = [];
        foreach ($audits as $audit) {
            if (($audit['score'] ?? 1) < 0.9 && isset($audit['title'])) {
                $tips[] = $audit['title'];
                if (count($tips) >= 3) break;
            }
        }

        return [
            'fcp' => $audits['first-contentful-paint']['displayValue']    ?? 'N/A',
            'lcp' => $audits['largest-contentful-paint']['displayValue']   ?? 'N/A',
            'tbt' => $audits['total-blocking-time']['displayValue']        ?? 'N/A',
            'cls' => $audits['cumulative-layout-shift']['displayValue']    ?? 'N/A',
            'si'  => $audits['speed-index']['displayValue']                ?? 'N/A',
            'score' => isset($perf['score']) ? round($perf['score'] * 100) : 'N/A',
            'tips'  => $tips
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
            color: #333;
        }
        
        .header {
            background: white;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .header h1 {
            color: #2c3e50;
            font-size: 24px;
        }
        
        .header-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .main-content {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 20px;
            margin-top: 20px;
        }
        
        .theme-list {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .theme-card {
            display: flex;
            align-items: center;
            padding: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .theme-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        
        .theme-card.active {
            border-color: #007bff;
            background: #f8f9ff;
        }
        
        .theme-preview {
            width: 80px;
            height: 60px;
            background: #f0f0f0;
            border-radius: 4px;
            margin-right: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .theme-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .theme-info {
            flex: 1;
        }
        
        .theme-name {
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 5px;
        }
        
        .theme-meta {
            font-size: 12px;
            color: #666;
            display: flex;
            gap: 15px;
        }
        
        .theme-actions {
            display: flex;
            gap: 5px;
        }
        
        .action-btn {
            padding: 8px;
            border: none;
            background: none;
            cursor: pointer;
            color: #666;
            transition: color 0.3s ease;
            font-size: 16px;
        }
        
        .action-btn:hover {
            color: #007bff;
        }
        
        .action-btn.delete:hover {
            color: #dc3545;
        }
        
        .preview-panel {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: sticky;
            top: 100px;
        }
        
        .preview-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .device-tabs {
            display: flex;
            gap: 10px;
        }
        
        .device-tab {
            padding: 8px 16px;
            border: 1px solid #ddd;
            background: white;
            cursor: pointer;
            border-radius: 4px;
            transition: all 0.3s ease;
        }
        
        .device-tab.active {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }
        
        .preview-frame {
            width: 100%;
            height: 500px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: white;
        }
        
        .preview-frame.mobile {
            max-width: 375px;
            margin: 0 auto;
        }
        
        .performance-metrics {
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        
        .metric {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .metric-value {
            font-weight: 600;
            color: #007bff;
        }

        .suggestions {
            margin-top: 10px;
            font-size: 13px;
        }
        .suggestions li {
            margin-left: 15px;
            list-style: disc;
        }
        
        .upload-modal, .create-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 8px;
            max-width: 400px;
            width: 90%;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .close {
            cursor: pointer;
            font-size: 24px;
            color: #666;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .empty-state i {
            font-size: 48px;
            margin-bottom: 10px;
            color: #ddd;
        }
        
        .badge {
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-active {
            background: #28a745;
            color: white;
        }
        
        .badge-inactive {
            background: #6c757d;
            color: white;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><i class="fas fa-palette"></i> Theme Management</h1>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="showUploadModal()">
                <i class="fas fa-upload"></i> Upload Theme
            </button>
            <button class="btn btn-success" onclick="showCreateModal()">
                <i class="fas fa-plus"></i> Create Theme
            </button>
        </div>
    </div>

    <div class="container">
        <div class="main-content">
            <div class="theme-list">
                <h2><i class="fas fa-list"></i> All Themes</h2>
                
                <?php if (empty($themes)): ?>
                    <div class="empty-state">
                        <i class="fas fa-folder-open"></i>
                        <h3>No themes found</h3>
                        <p>Upload or create your first theme to get started</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($themes as $theme): ?>
                        <div class="theme-card <?php echo $theme['active'] ? 'active' : ''; ?>">
                            <div class="theme-preview">
                                <?php if ($theme['preview']): ?>
                                    <img src="<?php echo $theme['preview']; ?>" alt="<?php echo $theme['name']; ?>">
                                <?php else: ?>
                                    <i class="fas fa-image"></i>
                                <?php endif; ?>
                            </div>
                            
                            <div class="theme-info">
                                <div class="theme-name">
                                    <?php echo $theme['name']; ?>
                                    <span class="badge <?php echo $theme['active'] ? 'badge-active' : 'badge-inactive'; ?>">
                                        <?php echo $theme['active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </div>
                                <div class="theme-meta">
                                    <span><i class="fas fa-file-code"></i> <?php echo count($theme['templates']); ?> templates</span>
                                    <?php $modTime = is_dir($theme['path']) ? @filemtime($theme['path']) : false; ?>
                                    <span><i class="fas fa-calendar"></i> <?php echo $modTime ? date('M j, Y', $modTime) : 'N/A'; ?></span>
                                </div>
                            </div>
                            
                            <div class="theme-actions">
                                <button class="action-btn" title="Customize" onclick="customizeTheme(<?php echo $theme['id']; ?>)">
                                    <i class="fas fa-paint-brush"></i>
                                </button>
                                <button class="action-btn" title="Edit Code" onclick="editCode(<?php echo $theme['id']; ?>)">
                                    <i class="fas fa-code"></i>
                                </button>
                                <button class="action-btn" title="Duplicate" onclick="duplicateTheme(<?php echo $theme['id']; ?>)">
                                    <i class="fas fa-copy"></i>
                                </button>
                                <?php if (!$theme['active']): ?>
                                    <button class="action-btn" title="Publish" onclick="publishTheme(<?php echo $theme['id']; ?>)">
                                        <i class="fas fa-check-circle"></i>
                                    </button>
                                <?php endif; ?>
                                <?php if ($theme['name'] !== 'default'): ?>
                                    <button class="action-btn delete" title="Delete" onclick="deleteTheme(<?php echo $theme['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                <?php endif; ?>
                                <?php $folder = ($theme['id'] == 1) ? 'default' : 'theme' . $theme['id']; ?>
                                <button class="action-btn" title="Download" onclick="downloadTheme('<?php echo $folder; ?>')">
                                    <i class="fas fa-download"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="preview-panel">
                <div class="preview-header">
                    <h3><i class="fas fa-eye"></i> Live Preview</h3>
                    <div class="device-tabs">
                        <button class="device-tab active" onclick="switchDevice('desktop')">
                            <i class="fas fa-desktop"></i> Desktop
                        </button>
                        <button class="device-tab" onclick="switchDevice('mobile')">
                            <i class="fas fa-mobile-alt"></i> Mobile
                        </button>
                    </div>
                </div>
                
                <iframe id="preview-frame" class="preview-frame" src="/?preview=1&theme=<?php echo $activeTheme; ?>"></iframe>
                
                <div class="performance-metrics">
                    <h4><i class="fas fa-tachometer-alt"></i> Performance Metrics</h4>
                    <?php
                    $metrics = getPerformanceMetrics($activeTheme);
                    ?>
                    <div class="metric">
                        <span>First Contentful Paint (FCP)</span>
                        <span class="metric-value"><?php echo $metrics['fcp']; ?></span>
                    </div>
                    <div class="metric">
                        <span>Largest Contentful Paint (LCP)</span>
                        <span class="metric-value"><?php echo $metrics['lcp']; ?></span>
                    </div>
                    <div class="metric">
                        <span>Total Blocking Time (TBT)</span>
                        <span class="metric-value"><?php echo $metrics['tbt']; ?></span>
                    </div>
                    <div class="metric">
                        <span>Cumulative Layout Shift (CLS)</span>
                        <span class="metric-value"><?php echo $metrics['cls']; ?></span>
                    </div>
                    <div class="metric">
                        <span>Speed Index</span>
                        <span class="metric-value"><?php echo $metrics['si']; ?></span>
                    </div>
                    <div class="metric">
                        <span>Performance Score</span>
                        <span class="metric-value"><?php echo $metrics['score']; ?>/100</span>
                    </div>
                    <?php if (!empty($metrics['tips'])): ?>
                        <ul class="suggestions">
                            <?php foreach ($metrics['tips'] as $tip): ?>
                                <li><?php echo htmlspecialchars($tip); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Modal -->
    <div id="uploadModal" class="upload-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-upload"></i> Upload Theme</h3>
                <span class="close" onclick="hideUploadModal()">&times;</span>
            </div>
            <form id="uploadForm" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Select Theme ZIP File</label>
                    <input type="file" name="theme_zip" accept=".zip" required>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-upload"></i> Upload Theme
                </button>
            </form>
        </div>
    </div>

    <!-- Create Modal -->
    <div id="createModal" class="create-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-plus"></i> Create New Theme</h3>
                <span class="close" onclick="hideCreateModal()">&times;</span>
            </div>
            <form id="createForm" method="post">
                <div class="form-group">
                    <label>Theme Name</label>
                    <input type="text" name="theme_name" placeholder="Enter theme name" required>
                </div>
                <button type="submit" name="create_theme" class="btn btn-success">
                    <i class="fas fa-plus"></i> Create Theme
                </button>
            </form>
        </div>
    </div>

    <script>
        function showUploadModal() {
            document.getElementById('uploadModal').style.display = 'flex';
        }
        
        function hideUploadModal() {
            document.getElementById('uploadModal').style.display = 'none';
        }
        
        function showCreateModal() {
            document.getElementById('createModal').style.display = 'flex';
        }
        
        function hideCreateModal() {
            document.getElementById('createModal').style.display = 'none';
        }
        
        function customizeTheme(themeId) {
            window.open('/admin/themes/theme-editor.php?theme_id=' + encodeURIComponent(themeId), '_blank');
        }

        function editCode(themeId) {
            window.open('/admin/themes/code-editor.php?theme_id=' + encodeURIComponent(themeId), '_blank');
        }
        
        function duplicateTheme(themeId) {
            const form = document.createElement('form');
            form.method = 'post';
            form.innerHTML = `
                <input type="hidden" name="action" value="duplicate">
                <input type="hidden" name="theme_id" value="${themeId}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
        
        function publishTheme(themeId) {
            if (confirm('Activate this theme for your store?')) {
                const form = document.createElement('form');
                form.method = 'post';
                form.innerHTML = `
                    <input type="hidden" name="action" value="activate">
                    <input type="hidden" name="theme_id" value="${themeId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function deleteTheme(themeId) {
            if (confirm('Are you sure you want to delete this theme?')) {
                const form = document.createElement('form');
                form.method = 'post';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="theme_id" value="${themeId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function downloadTheme(themeName) {
            window.location.href = '?download=' + encodeURIComponent(themeName);
        }

        document.getElementById('uploadForm').addEventListener('submit', async e => {
            e.preventDefault();
            const fd = new FormData(e.target);
            const resp = await fetch('/backend/themes/upload-theme.php', { method: 'POST', body: fd });
            if (resp.ok) location.reload();
            else alert('Upload failed');
        });

        document.getElementById('createForm').addEventListener('submit', async e => {
            e.preventDefault();
            const name = e.target.querySelector('input[name="theme_name"]').value;
            const fd = new FormData();
            fd.append('name', name);
            const resp = await fetch('/backend/themes/create-theme.php', { method: 'POST', body: fd });
            if (resp.ok) location.reload();
            else alert('Failed to create theme');
        });
        
        function switchDevice(device) {
            const frame = document.getElementById('preview-frame');
            const tabs = document.querySelectorAll('.device-tab');
            
            tabs.forEach(tab => tab.classList.remove('active'));
            event.target.classList.add('active');
            
            if (device === 'mobile') {
                frame.classList.add('mobile');
            } else {
                frame.classList.remove('mobile');
            }
        }
        
        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('upload-modal') || 
                event.target.classList.contains('create-modal')) {
                event.target.style.display = 'none';
            }
        }
        
        // Auto-refresh preview every 30 seconds
        setInterval(() => {
            const frame = document.getElementById('preview-frame');
            frame.src = frame.src;
        }, 30000);
    </script>
</body>
</html>