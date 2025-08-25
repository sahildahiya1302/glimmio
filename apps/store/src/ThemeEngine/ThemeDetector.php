<?php
declare(strict_types=1);

class ThemeDetector {
    private $themePath;
    private $supportedFormats = ['json', 'liquid', 'php', 'html'];
    
    public function __construct(string $themePath) {
        $this->themePath = $themePath;
    }
    
    public function detectThemeType(string $templateName): string {
        // Check for Shopify structure
        if (is_dir($this->themePath . '/layout') && file_exists($this->themePath . '/layout/theme.liquid')) {
            return 'shopify';
        }
        
        // Check for WooCommerce structure
        if (file_exists($this->themePath . '/style.css') && file_exists($this->themePath . '/index.php')) {
            return 'woocommerce';
        }
        
        // Check for custom structure
        foreach ($this->supportedFormats as $format) {
            $filePath = $this->themePath . "/templates/{$templateName}.{$format}";
            if (file_exists($filePath)) {
                return $format;
            }
        }
        
        // Check for Shopify templates
        $shopifyTemplate = $this->themePath . "/templates/{$templateName}.liquid";
        if (file_exists($shopifyTemplate)) {
            return 'liquid';
        }
        
        // Check for WooCommerce templates
        $wooTemplate = $this->themePath . "/templates/{$templateName}.php";
        if (file_exists($wooTemplate)) {
            return 'php';
        }
        
        // Default to JSON if no specific format found
        return 'json';
    }
    
    public function getThemeSettings(string $themeName): array {
        $settingsFile = $this->themePath . "/config/settings_data.json";
        if (file_exists($settingsFile)) {
            $content = file_get_contents($settingsFile);
            return json_decode($content, true) ?? [];
        }
        
        // Check for Shopify settings
        $shopifySettings = $this->themePath . "/config/settings_schema.json";
        if (file_exists($shopifySettings)) {
            $content = file_get_contents($shopifySettings);
            return json_decode($content, true) ?? [];
        }
        
        return [];
    }
    
    public function isShopifyTheme(): bool {
        return is_dir($this->themePath . '/layout') && 
               file_exists($this->themePath . '/layout/theme.liquid') &&
               is_dir($this->themePath . '/templates') &&
               is_dir($this->themePath . '/sections');
    }
    
    public function isWooCommerceTheme(): bool {
        return file_exists($this->themePath . '/style.css') && 
               file_exists($this->themePath . '/index.php') &&
               is_dir($this->themePath . '/templates');
    }
    
    public function getThemeInfo(): array {
        $info = [
            'type' => 'custom',
            'name' => basename($this->themePath),
            'version' => '1.0.0',
            'author' => 'Unknown',
            'description' => 'Custom theme'
        ];
        
        // Check for Shopify theme info
        if ($this->isShopifyTheme()) {
            $info['type'] = 'shopify';
            $configFile = $this->themePath . '/config/settings_schema.json';
            if (file_exists($configFile)) {
                $config = json_decode(file_get_contents($configFile), true);
                if (is_array($config) && isset($config[0])) {
                    $info = array_merge($info, $config[0]);
                }
            }
        }
        
        // Check for WooCommerce theme info
        if ($this->isWooCommerceTheme()) {
            $info['type'] = 'woocommerce';
            $styleFile = $this->themePath . '/style.css';
            if (file_exists($styleFile)) {
                $content = file_get_contents($styleFile);
                if (preg_match('/Theme Name:\s*(.+)/i', $content, $matches)) {
                    $info['name'] = trim($matches[1]);
                }
                if (preg_match('/Version:\s*(.+)/i', $content, $matches)) {
                    $info['version'] = trim($matches[1]);
                }
                if (preg_match('/Author:\s*(.+)/i', $content, $matches)) {
                    $info['author'] = trim($matches[1]);
                }
                if (preg_match('/Description:\s*(.+)/i', $content, $matches)) {
                    $info['description'] = trim($matches[1]);
                }
            }
        }
        
        return $info;
    }
    
    public function getAvailableTemplates(): array {
        $templates = [];
        
        // Check for Shopify templates
        if ($this->isShopifyTheme()) {
            $templateFiles = glob($this->themePath . '/templates/*.liquid');
            foreach ($templateFiles as $file) {
                $templates[] = basename($file, '.liquid');
            }
        }
        
        // Check for WooCommerce templates
        if ($this->isWooCommerceTheme()) {
            $templateFiles = glob($this->themePath . '/templates/*.php');
            foreach ($templateFiles as $file) {
                $templates[] = basename($file, '.php');
            }
        }
        
        // Check for custom templates
        foreach ($this->supportedFormats as $format) {
            $templateFiles = glob($this->themePath . "/templates/*.{$format}");
            foreach ($templateFiles as $file) {
                $templates[] = basename($file, ".{$format}");
            }
        }
        
        return array_unique($templates);
    }
}
