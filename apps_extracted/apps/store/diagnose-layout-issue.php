<?php
// diagnose-layout-issue.php - Diagnostic script for layout saving issues
echo "=== Theme Layout Diagnostic Tool ===\n\n";

// Check PHP version
echo "PHP Version: " . PHP_VERSION . "\n";

// Check if directories exist
$themePath = __DIR__ . '/themes';
$defaultThemePath = $themePath . '/default';
$templatesPath = $defaultThemePath . '/templates';

echo "\n=== Directory Structure ===\n";
echo "Theme path exists: " . (is_dir($themePath) ? "YES" : "NO") . "\n";
echo "Default theme path exists: " . (is_dir($defaultThemePath) ? "YES" : "NO") . "\n";
echo "Templates path exists: " . (is_dir($templatesPath) ? "YES" : "NO") . "\n";

// Check permissions
echo "\n=== Permissions ===\n";
echo "Theme path writable: " . (is_writable($themePath) ? "YES" : "NO") . "\n";
echo "Default theme writable: " . (is_writable($defaultThemePath) ? "YES" : "NO") . "\n";
echo "Templates writable: " . (is_writable($templatesPath) ? "YES" : "NO") . "\n";

// Check specific files
echo "\n=== Template Files ===\n";
$templateFiles = glob($templatesPath . '/*.json');
foreach ($templateFiles as $file) {
    echo basename($file) . " - " . (is_writable($file) ? "writable" : "not writable") . "\n";
}

// Check permissions
echo "\n=== Permissions ===\n";
echo "Theme path writable: " . (is_writable($themePath) ? "YES" : "NO") . "\n";
echo "Default theme writable: " . (is_writable($defaultThemePath) ? "YES" : "NO") . "\n";
echo "Templates writable: " . (is_writable($templatesPath) ? "YES" : "NO") . "\n";

// Check specific files
echo "\n=== Template Files ===\n";
$templateFiles = glob($templatesPath . '/*.json');
foreach ($templateFiles as $file) {
    echo basename($file) . " - " . (is_writable($file) ? "writable" : "not writable") . "\n";
}

// Check permissions
echo "\n=== Permissions ===\n";
echo "Theme path writable: " . (is_writable($themePath) ? "YES" : "NO") . "\n";
echo "Default theme writable: " . (is_writable($defaultThemePath) ? "YES" : "NO") . "\n";
echo "Templates writable: " . (is_writable($templatesPath) ? "YES" : "NO") . "\n";

// Check specific files
echo "\n=== Template Files ===\n";
$templateFiles = glob($templatesPath . '/*.json');
foreach ($templateFiles as $file) {
    echo basename($file) . " - " . (is_writable($file) ? "writable" : "not writable") . "\n";
}

// Check specific files
echo "\n=== Template Files ===\n";
$templateFiles = glob($templatesPath . '/*.json');
foreach ($templateFiles as $file) {
    echo basename($file) . " - " . (is_writable($file) ? "writable" : "not writable") . "\n";

// Check specific files
echo "\n=== Template Files ===\n";
$templateFiles = glob($templatesPath . '/*.json');
foreach ($templateFiles as $file) {
    echo basename($file) . " - " . (is_writable($file) ? "writable" : "not writable") . "\n";

// Check specific files
echo "\n=== Template Files ===\n";
$templateFiles = glob($templatesPath . '/*.json');
foreach ($templateFiles as $file) .
