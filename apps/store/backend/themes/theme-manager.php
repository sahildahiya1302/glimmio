<?php
declare(strict_types=1);

require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';

class ThemeManager {
    private $db;

    public function __construct() {
        $this->db = db();
    }

  public function getActiveTheme(): string {
    $stmt = $this->db->prepare("SELECT `value` FROM settings WHERE `key` = 'active_theme' LIMIT 1");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['value'] ?? 'default';
}


public function setActiveTheme(string $themeName): bool {
    $stmt = $this->db->prepare("
        INSERT INTO settings (`key`, `value`) 
        VALUES ('active_theme', ?) 
        ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)
    ");
    return $stmt->execute([$themeName]);
}



    public function getThemeSettings(string $themeName): array {
        $stmt = $this->db->prepare("SELECT settings FROM theme_settings WHERE theme_name = ?");
        $stmt->execute([$themeName]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? json_decode($result['settings'], true) : [];
    }

    public function saveThemeSettings(string $themeName, array $settings): bool {
        $stmt = $this->db->prepare("INSERT INTO theme_settings (theme_name, settings) VALUES (?, ?) ON DUPLICATE KEY UPDATE settings = ?");
        return $stmt->execute([$themeName, json_encode($settings), json_encode($settings)]);
    }

    public function getAllThemes(): array {
        $themes = [];
        $themesDir = __DIR__ . '/../../themes';

        foreach (glob($themesDir . '/*', GLOB_ONLYDIR) as $themeDir) {
            $themeName = basename($themeDir);
            $configPath = $themeDir . '/config/settings_data.json';

            $theme = [
                'name' => $themeName,
                'id' => $themeName,
                'path' => $themeDir,
                'active' => $this->getActiveTheme() === $themeName,
                'preview' => file_exists($themeDir . '/preview.png') ? '/themes/' . $themeName . '/preview.png' : null,
                'templates' => [],
                'settings' => []
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

    public function getThemeFiles(string $themeName): array {
        $themePath = __DIR__ . '/../../themes/' . $themeName;
        return $this->scanDirectory($themePath);
    }

    private function scanDirectory(string $dir, string $basePath = ''): array {
        $files = [];
        if (!is_dir($dir)) return $files;

        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;

            $path = $dir . '/' . $item;
            $relativePath = $basePath ? $basePath . '/' . $item : $item;

            if (is_dir($path)) {
                $files = array_merge($files, $this->scanDirectory($path, $relativePath));
            } else {
                $files[] = [
                    'path' => $relativePath,
                    'name' => $item,
                    'size' => filesize($path),
                    'modified' => filemtime($path)
                ];
            }
        }

        return $files;
    }

    public function saveFile(string $themeName, string $filePath, string $content): bool {
        $fullPath = __DIR__ . '/../../themes/' . $themeName . '/' . $filePath;

        // Security check: ensure file is within theme directory
        $realPath = realpath($fullPath);
        $themeRealPath = realpath(__DIR__ . '/../../themes/' . $themeName);

        if (!$realPath || strpos($realPath, $themeRealPath) !== 0) {
            return false;
        }

        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return file_put_contents($fullPath, $content) !== false;
    }

    public function getFileContent(string $themeName, string $filePath): ?string {
        $fullPath = __DIR__ . '/../../themes/' . $themeName . '/' . $filePath;

        // Security check
        $realPath = realpath($fullPath);
        $themeRealPath = realpath(__DIR__ . '/../../themes/' . $themeName);

        if (!$realPath || strpos($realPath, $themeRealPath) !== 0 || !file_exists($fullPath)) {
            return null;
        }

        return file_get_contents($fullPath);
    }

    public function getPerformanceMetrics(string $themeName): array {
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
                'score' => 'N/A'
            ];
        }

        $audits = $data['lighthouseResult']['audits'];
        $score = round(($data['lighthouseResult']['categories']['performance']['score'] ?? 0) * 100);

        return [
            'fcp' => $audits['first-contentful-paint']['displayValue']    ?? 'N/A',
            'lcp' => $audits['largest-contentful-paint']['displayValue']   ?? 'N/A',
            'tbt' => $audits['total-blocking-time']['displayValue']        ?? 'N/A',
            'cls' => $audits['cumulative-layout-shift']['displayValue']    ?? 'N/A',
            'si'  => $audits['speed-index']['displayValue']                ?? 'N/A',
            'score' => $score
        ];
    }
}