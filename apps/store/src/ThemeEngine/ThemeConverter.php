<?php
declare(strict_types=1);

class ThemeConverter {
    private $themePath;
    
    public function __construct(string $themePath) {
        $this->themePath = $themePath;
    }
    
    public function convertShopifyToCustom(): bool {
        if (!is_dir($this->themePath . '/layout') && !is_dir($this->themePath . '/templates')) {
            return false;
        }
        
        // Create required directories
        $this->createMissingDirectories();
        
        // Process Shopify structure
        $this->processShopifyStructure();
        
        // Create config
        $this->createShopifyConfig();
        
        return true;
    }
    
    private function processShopifyStructure(): void {
        // Handle layout directory
        if (is_dir($this->themePath . '/layout')) {
            $layoutFiles = glob($this->themePath . '/layout/*.liquid');
            foreach ($layoutFiles as $layoutFile) {
                $filename = basename($layoutFile);
                $targetPath = $this->themePath . '/templates/' . str_replace('.liquid', '.html', $filename);
                copy($layoutFile, $targetPath);
            }
        }
        
        // Handle templates directory
        if (is_dir($this->themePath . '/templates')) {
            $templateFiles = glob($this->themePath . '/templates/**/*.liquid', GLOB_BRACE);
            foreach ($templateFiles as $templateFile) {
                $relativePath = str_replace($this->themePath . '/', '', $templateFile);
                $targetPath = $this->themePath . '/' . str_replace('.liquid', '.html', $relativePath);
                
                $targetDir = dirname($targetPath);
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }
                
                copy($templateFile, $targetPath);
            }
        }
        
        // Handle sections
        if (is_dir($this->themePath . '/sections')) {
            $sectionFiles = glob($this->themePath . '/sections/*.liquid');
            foreach ($sectionFiles as $sectionFile) {
                $filename = basename($sectionFile);
                $targetPath = $this->themePath . '/sections/' . str_replace('.liquid', '.html', $filename);
                copy($sectionFile, $targetPath);
            }
        }
        
        // Handle snippets
        if (is_dir($this->themePath . '/snippets')) {
            $snippetFiles = glob($this->themePath . '/snippets/*.liquid');
            foreach ($snippetFiles as $snippetFile) {
                $filename = basename($snippetFile);
                $targetPath = $this->themePath . '/snippets/' . str_replace('.liquid', '.html', $filename);
                copy($snippetFile, $targetPath);
            }
        }
        
        // Handle assets
        if (is_dir($this->themePath . '/assets')) {
            $assetFiles = glob($this->themePath . '/assets/*');
            foreach ($assetFiles as $assetFile) {
                if (is_file($assetFile)) {
                    $filename = basename($assetFile);
                    $targetPath = $this->themePath . '/assets/' . $filename;
                    if (!file_exists($targetPath)) {
                        copy($assetFile, $targetPath);
                    }
                }
            }
        }
    }
    
    private function createShopifyConfig(): void {
        // Create config directory
        if (!is_dir($this->themePath . '/config')) {
            mkdir($this->themePath . '/config', 0755, true);
        }
        
        // Extract theme info from config.yml if exists
        $configYml = $this->themePath . '/config.yml';
        $themeInfo = [
            'name' => 'Shopify Theme',
            'version' => '1.0.0',
            'author' => 'Shopify',
            'description' => 'Imported Shopify theme',
            'settings' => []
        ];
        
        if (file_exists($configYml)) {
            $content = file_get_contents($configYml);
            if (preg_match('/name:\s*(.+)/i', $content, $matches)) {
                $themeInfo['name'] = trim($matches[1]);
            }
            if (preg_match('/version:\s*(.+)/i', $content, $matches)) {
                $themeInfo['version'] = trim($matches[1]);
            }
            if (preg_match('/author:\s*(.+)/i', $content, $matches)) {
                $themeInfo['author'] = trim($matches[1]);
            }
        }
        
        // Process settings_schema.json
        $settingsFile = $this->themePath . '/config/settings_schema.json';
        $settingsData = [];
        
        if (file_exists($settingsFile)) {
            $shopifySettings = json_decode(file_get_contents($settingsFile), true);
            if (is_array($shopifySettings)) {
                foreach ($shopifySettings as $setting) {
                    if (isset($setting['settings'])) {
                        foreach ($setting['settings'] as $config) {
                            if (isset($config['id'])) {
                                $settingsData[$config['id']] = $config['default'] ?? '';
                            }
                        }
                    }
                }
            }
        }
        
        $themeInfo['settings'] = $settingsData;
        
        file_put_contents(
            $this->themePath . '/config/settings_data.json',
            json_encode($themeInfo, JSON_PRETTY_PRINT)
        );
    }
    
    public function convertWooCommerceToCustom(): bool {
        if (!file_exists($this->themePath . '/style.css')) {
            return false;
        }
        
        // Create config directory if it doesn't exist
        if (!is_dir($this->themePath . '/config')) {
            mkdir($this->themePath . '/config', 0755, true);
        }
        
        // Extract theme info from style.css
        $styleContent = file_get_contents($this->themePath . '/style.css');
        $themeInfo = [];
        
        if (preg_match('/Theme Name:\s*(.+)/i', $styleContent, $matches)) {
            $themeInfo['name'] = trim($matches[1]);
        }
        
        if (preg_match('/Version:\s*(.+)/i', $styleContent, $matches)) {
            $themeInfo['version'] = trim($matches[1]);
        }
        
        if (preg_match('/Author:\s*(.+)/i', $styleContent, $matches)) {
            $themeInfo['author'] = trim($matches[1]);
        }
        
        if (preg_match('/Description:\s*(.+)/i', $styleContent, $matches)) {
            $themeInfo['description'] = trim($matches[1]);
        }
        
        $config = array_merge([
            'name' => 'Custom Theme',
            'version' => '1.0.0',
            'author' => 'Store Admin',
            'description' => 'Converted WooCommerce theme',
            'settings' => [
                'colors' => [
                    'primary' => '#007bff',
                    'secondary' => '#6c757d'
                ]
            ]
        ], $themeInfo);
        
        file_put_contents(
            $this->themePath . '/config/settings_data.json',
            json_encode($config, JSON_PRETTY_PRINT)
        );
        
        return true;
    }
    
    public function createMissingDirectories(): void {
        $directories = ['templates', 'sections', 'snippets', 'assets', 'config'];
        
        foreach ($directories as $dir) {
            $path = $this->themePath . '/' . $dir;
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }
    }
    
    public function validateAndFixTheme(): array {
        $issues = [];
        $fixes = [];
        
        // Check for required directories
        $requiredDirs = ['templates'];
        foreach ($requiredDirs as $dir) {
            if (!is_dir($this->themePath . '/' . $dir)) {
                $issues[] = "Missing directory: $dir";
                mkdir($this->themePath . '/' . $dir, 0755, true);
                $fixes[] = "Created directory: $dir";
            }
        }
        
        // Check for at least one template
        $templates = glob($this->themePath . '/templates/*.{json,liquid,php,html}', GLOB_BRACE);
        if (empty($templates)) {
            $issues[] = "No templates found";
            // Create a basic index template
            file_put_contents(
                $this->themePath . '/templates/index.html',
                '<h1>Welcome to Your Theme</h1>'
            );
            $fixes[] = "Created basic index.html template";
        }
        
        // Check for config
        if (!file_exists($this->themePath . '/config/settings_data.json')) {
            $issues[] = "Missing config/settings_data.json";
            $this->createMissingDirectories();
            $this->convertShopifyToCustom();
            $fixes[] = "Created default config";
        }
        
        return ['issues' => $issues, 'fixes' => $fixes];
    }
}
