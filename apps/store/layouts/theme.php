<?php
/**
 * Multi-Platform Theme Wrapper
 * This file serves as the main theme wrapper/layout for all page types
 * Supports Liquid, PHP, HTML, and JSON templates
 */

declare(strict_types=1);

// Prevent direct access
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__) . '/');
}

require_once ABSPATH . 'config.php';
require_once ABSPATH . 'functions.php';
require_once ABSPATH . 'theme-engine.php';

// Get current theme and page context
$themeId = $_SESSION['active_theme'] ?? THEME_ID;
$themeFolder = ($themeId === 1) ? 'default' : 'theme' . $themeId;
$themePath = ABSPATH . 'themes/' . $themeFolder;

// Determine current page type
$pageType = $pageType ?? 'index';
$context = $context ?? [];

// Add global context variables
$globalContext = [
    'theme' => [
        'id' => $themeId,
        'folder' => $themeFolder,
        'path' => $themePath,
        'url' => '/themes/' . $themeFolder
    ],
    'shop' => [
        'name' => getStoreName(),
        'url' => getShopUrl(),
        'currency' => getStoreCurrency(),
        'currency_symbol' => getStoreCurrencySymbol()
    ],
    'request' => [
        'path' => $_SERVER['REQUEST_URI'],
        'query' => $_GET,
        'method' => $_SERVER['REQUEST_METHOD']
    ],
    'user' => [
        'logged_in' => isset($_SESSION['user_id']),
        'id' => $_SESSION['user_id'] ?? null
    ]
];

// Merge contexts
$fullContext = array_merge($globalContext, $context);

// Detect template type and render accordingly
$detector = new ThemeDetector($themePath);
$templateType = $detector->detectThemeType($pageType);

// Set appropriate headers
header('Content-Type: text/html; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');

// Add theme-specific assets
function addThemeAssets($themePath, $themeUrl) {
    $assets = [];
    
    // Check for theme-specific CSS
    $cssFiles = [
        'css/style.css',
        'css/theme.css',
        'assets/css/style.css',
        'assets/css/theme.css'
    ];
    
    foreach ($cssFiles as $cssFile) {
        if (file_exists($themePath . '/' . $cssFile)) {
            $assets['css'][] = $themeUrl . '/' . $cssFile;
        }
    }
    
    // Check for theme-specific JS
    $jsFiles = [
        'js/theme.js',
        'assets/js/theme.js',
        'js/main.js',
        'assets/js/main.js'
    ];
    
    foreach ($jsFiles as $jsFile) {
        if (file_exists($themePath . '/' . $jsFile)) {
            $assets['js'][] = $themeUrl . '/' . $jsFile;
        }
    }
    
    return $assets;
}

$themeAssets = addThemeAssets($themePath, '/themes/' . $themeFolder);

// Include theme-specific header
$headerFile = $themePath . '/layout/header.' . $templateType;
if (file_exists($headerFile)) {
    include $headerFile;
} else {
    // Default header for all theme types
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($pageTitle ?? 'Store'); ?></title>
        <meta name="description" content="<?php echo htmlspecialchars($pageDescription ?? ''); ?>">
        
        <!-- Theme CSS -->
        <?php foreach ($themeAssets['css'] ?? [] as $css): ?>
            <link rel="stylesheet" href="<?php echo htmlspecialchars($css); ?>">
        <?php endforeach; ?>
        
        <!-- Theme JavaScript SDK -->
        <script src="/src/JavaScript/ThemeSDK.js"></script>
        
        <!-- Theme-specific head content -->
        <?php if (file_exists($themePath . '/layout/head.php')): ?>
            <?php include $themePath . '/layout/head.php'; ?>
        <?php endif; ?>
    </head>
    <body class="theme-<?php echo $templateType; ?>">
    <?php
}

// Include navigation
$navFile = $themePath . '/layout/navigation.' . $templateType;
if (file_exists($navFile)) {
    include $navFile;
}

// Main content area
echo '<main id="main-content" role="main">';
echo $content_for_layout ?? '';
echo '</main>';

// Include sidebar if needed
$sidebarFile = $themePath . '/layout/sidebar.' . $templateType;
if (file_exists($sidebarFile)) {
    include $sidebarFile;
}

// Include footer
$footerFile = $themePath . '/layout/footer.' . $templateType;
if (file_exists($footerFile)) {
    include $footerFile;
} else {
    // Default footer
    ?>
        <!-- Theme JS -->
        <?php foreach ($themeAssets['js'] ?? [] as $js): ?>
            <script src="<?php echo htmlspecialchars($js); ?>"></script>
        <?php endforeach; ?>
        
        <!-- Theme-specific footer content -->
        <?php if (file_exists($themePath . '/layout/footer.php')): ?>
            <?php include $themePath . '/layout/footer.php'; ?>
        <?php endif; ?>
    </body>
    </html>
    <?php
}

// Helper functions for theme compatibility
function theme_asset($path) {
    global $themeUrl;
    return $themeUrl . '/' . ltrim($path, '/');
}

function theme_include($file) {
    global $themePath;
    $filePath = $themePath . '/' . ltrim($file, '/');
    if (file_exists($filePath)) {
        include $filePath;
        return true;
    }
    return false;
}

function theme_render($template, $context = []) {
    global $themePath, $templateType;
    return renderTemplate($template, $context);
}

// Initialize ThemeSDK for JavaScript integration
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize ThemeSDK with theme-specific settings
    window.themeSDK = new ThemeSDK({
        apiBaseUrl: '/api',
        themeType: '<?php echo $templateType; ?>',
        themeId: '<?php echo $themeId; ?>',
        debug: <?php echo isset($_GET['debug']) ? 'true' : 'false'; ?>
    });
    
    // Initialize theme-specific functionality
    window.themeSDK.init();
});
</script>
