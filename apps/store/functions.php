<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/config.php';

if (!function_exists('render')) {
    function render(string $template, array $data = []): string
    {
        extract($data);
        ob_start();
        include BASE_PATH . '/' . ltrim($template, '/');
        return ob_get_clean();
    }
}

/**
 * Output a template directly.
 */
if (!function_exists('view')) {
    function view(string $template, array $data = []): void
    {
        echo render($template, $data);
    }
}

/**
 * Escape output for HTML.
 */
if (!function_exists('e')) {
    function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Send JSON response.
 */
if (!function_exists('jsonResponse')) {
    function jsonResponse($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}

/**
 * Get or generate a CSRF token stored in the session.
 */
if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
        }
        return $_SESSION['csrf_token'];
    }
}

/**
 * Verify a CSRF token from user input.
 */
if (!function_exists('verify_csrf')) {
    function verify_csrf(string $token): bool
    {
        return hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }
}


if (!function_exists('verify_recaptcha')) {
    function verify_recaptcha(string $token): bool
    {
        $secret = getenv('RECAPTCHA_SECRET');
        if (!$secret || !$token) {
            return true;
        }
        $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'secret' => $secret,
            'response' => $token,
            'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
        ]));
        $result = curl_exec($ch);
        curl_close($ch);
        if ($result === false) {
            return false;
        }
        $data = json_decode($result, true);
        return !empty($data['success']);
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }
}

/**
 * Generate an asset URL for the active theme.
 */
if (!function_exists('asset')) {
function asset(string $path): string
    {
        return '/themes/' . THEME . '/assets/' . ltrim($path, '/');
    }
}

if (!function_exists('themeLayoutFile')) {
    /**
     * Determine the theme layout wrapper file (PHP or Liquid) supporting
     * either `layouts/` or Shopify's `layout/` directory name.
     */
    function themeLayoutFile(): ?string
    {
        $dirs = ['layouts', 'layout'];
        foreach ($dirs as $dir) {
            $php = THEME_PATH . "/{$dir}/theme.php";
            if (is_file($php)) {
                return $php;
            }
            $liquid = THEME_PATH . "/{$dir}/theme.liquid";
            if (is_file($liquid)) {
                return $liquid;
            }
        }
        $alt = resolveThemeFile('layouts/theme');
        return $alt ?: null;
    }
}

if (!function_exists('passwordLayoutFile')) {
    /**
     * Locate the storefront password template, checking both directory styles.
     */
    function passwordLayoutFile(): ?string
    {
        $dirs = ['layouts', 'layout'];
        foreach ($dirs as $dir) {
            $php = THEME_PATH . "/{$dir}/password.php";
            if (is_file($php)) {
                return $php;
            }
            $liquid = THEME_PATH . "/{$dir}/password.liquid";
            if (is_file($liquid)) {
                return $liquid;
            }
        }
        $alt = resolveThemeFile('layouts/password');
        return $alt ?: null;
    }
}

if (!function_exists('includePasswordLayout')) {
    /**
     * Include the password page layout using PHP or Liquid.
     */
    function includePasswordLayout(array $vars = []): void
    {
        $file = passwordLayoutFile();
        if (!$file) {
            echo '<!-- password layout not found -->';
            return;
        }

        if (str_ends_with($file, '.liquid')) {
            $vars['content_for_layout'] = $vars['content'] ?? ($vars['content_for_layout'] ?? '');
            $vars['content_for_header'] = $vars['pageHeadCode'] ?? ($vars['content_for_header'] ?? '');
            echo renderLiquid($file, $vars);
        } else {
            extract($vars);
            include $file;
        }
    }
}

if (!function_exists('includeThemeLayout')) {
    /**
     * Include the theme wrapper file, supporting PHP or Liquid formats.
     *
     * @param array $vars Variables to expose to the layout.
     */
    function includeThemeLayout(array $vars = []): void
    {
        $platform = getThemePlatform();
        $file = themeLayoutFile();
        if (!$file) {
            $file = resolveThemeFile('layouts/theme');
        }

        if (!$file && $platform === 'woocommerce') {
            $header = THEME_PATH . '/header.php';
            $footer = THEME_PATH . '/footer.php';
            if (is_file($header) && is_file($footer)) {
                extract($vars);
                include $header;
                echo $content_for_layout ?? ($content ?? '');
                include $footer;
                return;
            }
        }

        if (!$file) {
            echo '<!-- theme layout not found -->';
            return;
        }

        if (str_ends_with($file, '.liquid')) {
            $vars['content_for_layout'] = $vars['content'] ?? ($vars['content_for_layout'] ?? '');
            $vars['content_for_header'] = $vars['pageHeadCode'] ?? ($vars['content_for_header'] ?? '');
            echo renderLiquid($file, $vars);
        } else {
            extract($vars);
            include $file;
        }
    }
}

if (!function_exists('renderLiquidString')) {
    function renderLiquidString(string $str, array $vars): string
    {
        return preg_replace_callback('/{{\s*(\w+)\s*}}/', function ($m) use ($vars) {
            $key = $m[1];
            return htmlspecialchars($vars[$key] ?? '');
        }, $str);
    }
}

if (!function_exists('resolveLiquidVar')) {
    /**
     * Resolve a dotted variable path from the provided array.
     */
    function resolveLiquidVar(string $expr, array $vars)
    {
        $parts = explode('.', trim($expr));
        $value = $vars;
        foreach ($parts as $p) {
            if (is_array($value) && array_key_exists($p, $value)) {
                $value = $value[$p];
            } else {
                return null;
            }
        }
        return $value;
    }
}

if (!function_exists('renderLiquid')) {
    /**
     * Extremely simplified Liquid parser. This only handles variable output,
     * basic if/for blocks and {% render %} includes so Shopify templates show
     * some content instead of a blank page.
     */
    function renderLiquid(string $file, array $vars = []): string
    {
        $content = file_get_contents($file);

        // Include or render other snippets
        $content = preg_replace_callback('/{%\s*(?:include|render)\s+[\'" ]([^\'" ]+)[\'" ]\s*%}/',
            function ($m) use ($vars) {
                return renderPartial($m[1], $vars);
            },
            $content);

        // Insert sections
        $content = preg_replace_callback('/{%\s*section\s+[\'" ]([^\'" ]+)[\'" ]\s*%}/',
            function ($m) use ($vars) {
                ob_start();
                includeSection($m[1]);
                return ob_get_clean();
            },
            $content);

        // Remove comments
        $content = preg_replace('/{%\s*comment\s*%}.*?{%\s*endcomment\s*%}/s', '', $content);

        // Handle if/endif
        $content = preg_replace_callback('/{%\s*if\s+([^%]+)\s*%}(.*?){%\s*endif\s*%}/s',
            function ($m) use ($vars) {
                $val = resolveLiquidVar($m[1], $vars);
                return $val ? $m[2] : '';
            },
            $content);

        // Handle simple for loops
        $content = preg_replace_callback('/{%\s*for\s+(\w+)\s+in\s+([^%]+)\s*%}(.*?){%\s*endfor\s*%}/s',
            function ($m) use ($vars) {
                $list = resolveLiquidVar($m[2], $vars);
                if (!is_array($list)) return '';
                $out = '';
                foreach ($list as $item) {
                    $local = array_merge($vars, [$m[1] => $item]);
                    $out .= renderLiquidString($m[3], $local);
                }
                return $out;
            },
            $content);

        // Variable interpolation with simple filter handling
        $content = preg_replace_callback('/{{\s*([^{}]+)\s*}}/', function ($m) use ($vars) {
            $expr = trim($m[1]);
            $parts = preg_split('/\s*\|\s*/', $expr);
            $value = resolveLiquidVar(array_shift($parts), $vars);
            if ($value === null) $value = '';
            foreach ($parts as $filter) {
                $filter = trim($filter);
                if ($filter === 'asset_url') {
                    $value = asset((string)$value);
                } elseif ($filter === 'stylesheet_tag') {
                    $value = '<link rel="stylesheet" href="' . asset((string)$value) . '">';
                } elseif ($filter === 'script_tag') {
                    $value = '<script src="' . asset((string)$value) . '"></script>';
                } elseif ($filter === 'img_tag' || $filter === 'image_tag') {
                    $value = '<img src="' . asset((string)$value) . '" alt="">';
                }
            }
            return $value;
        }, $content);

        // Strip any remaining Liquid tags
        $content = preg_replace('/{%[^%]*%}/', '', $content);

        return $content;
    }
}

if (!function_exists('parseLiquidSchema')) {
    function parseLiquidSchema(string $file): ?array
    {
        $content = file_get_contents($file);
        if (preg_match('/{%\s*schema\s*%}(.*?){%\s*endschema\s*%}/s', $content, $m)) {
            $json = trim($m[1]);
            $data = json_decode($json, true);
            return is_array($data) ? $data : null;
        }
        return null;
    }
}

if (!function_exists('deepMerge')) {
    function deepMerge(array $defaults, array $overrides): array
    {
        foreach ($overrides as $key => $value) {
            if (is_array($value) && isset($defaults[$key]) && is_array($defaults[$key])) {
                $defaults[$key] = deepMerge($defaults[$key], $value);
            } else {
                $defaults[$key] = $value;
            }
        }
        return $defaults;
    }
}

/**
 * Include a theme section file if it exists.
 */
if (!function_exists('includeSection')) {
function includeSection(string $name, array $data = []): void
{
        $platform = getThemePlatform();
        $paths = [THEME_PATH . '/sections/' . $name];
        if ($platform === 'woocommerce') {
            $paths[] = THEME_PATH . '/template-parts/' . $name;
        }

        $phpFile = '';
        $liquidFile = '';
        foreach ($paths as $base) {
            if (is_file($base . '.php')) {
                $phpFile = $base . '.php';
                break;
            }
            if (is_file($base . '.liquid')) {
                $liquidFile = $base . '.liquid';
                break;
            }
        }

        if (!$phpFile && !$liquidFile) {
            echo "<!-- Section file '{$name}' not found -->";
            return;
        }

        // Generate a unique id if none provided
        $id = $data['id'] ?? ($name . '-' . uniqid());
        $blocks = $data['blocks'] ?? [];

        // Remove non-setting keys before merging with defaults
        $settingData = $data;
        unset($settingData['id'], $settingData['blocks']);

        // Load defaults from schema file or embedded Liquid schema
        $schemaFile = THEME_PATH . '/sections/' . $name . '.schema.json';
        $defaults = [];
        $schema = null;
        if (is_file($schemaFile)) {
            $schema = json_decode(file_get_contents($schemaFile), true);
        } elseif (is_file($liquidFile)) {
            $schema = parseLiquidSchema($liquidFile);
        }

        if ($schema) {
            foreach (($schema['settings'] ?? []) as $setting) {
                if (isset($setting['id'])) {
                    $defaults[$setting['id']] = $setting['default'] ?? null;
                }
            }

            // Apply defaults from the primary preset so new schema values
            // propagate even when a section already exists in the layout
            if (isset($schema['presets']['default']['settings'])) {
                $defaults = array_merge($defaults, $schema['presets']['default']['settings']);
            }

            // Collect block-level defaults
            $blockDefaults = [];
            foreach (($schema['blocks'] ?? []) as $blockSchema) {
                if (isset($blockSchema['type'])) {
                    foreach ($blockSchema['settings'] ?? [] as $bs) {
                        if (isset($bs['id'])) {
                            $blockDefaults[$blockSchema['type']][$bs['id']] = $bs['default'] ?? null;
                        }
                    }
                }
            }

            if (empty($blocks) && isset($schema['presets']['default']['blocks'])) {
                $blocks = $schema['presets']['default']['blocks'];
            }
            // Merge block defaults
            foreach ($blocks as $i => $block) {
                $bType = $block['type'] ?? '';
                $blockSettings = $block['settings'] ?? [];
                $defaultsForType = $blockDefaults[$bType] ?? [];
                $blocks[$i]['settings'] = deepMerge($defaultsForType, $blockSettings);
            }
        }

        $settings = deepMerge($defaults, $settingData);

        // Render the section template
        ob_start();
        if (is_file($phpFile)) {
            $file = $phpFile;
            include $file;
        } else {
            $vars = array_merge(
                ['section' => ['id' => $id, 'settings' => $settings, 'blocks' => $blocks]],
                $settings
            );
            echo renderLiquid($liquidFile, $vars);
        }
        $html = ob_get_clean();

        // Ensure the element can be located for scrolling in the editor
        if (strpos($html, "id=\"$id\"") === false && strpos($html, "id='$id'") === false) {
            $html = "<div id=\"{$id}\" data-section=\"{$name}\">{$html}</div>";
        }

        echo $html;
    }
}

/**
 * Include a theme snippet file with optional settings.
 */
if (!function_exists('includeSnippet')) {
function includeSnippet(string $name, array $settings = []): void
{
        $file = resolveThemeFile('snippets/' . $name);
        if (!$file) {
            echo "<!-- Snippet file '{$name}' not found -->";
            return;
        }

        $schemaFile = THEME_PATH . '/snippets/' . $name . '.schema.json';
        $defaults = [];
        if (is_file($schemaFile)) {
            $schema = json_decode(file_get_contents($schemaFile), true);
            foreach (($schema['settings'] ?? []) as $setting) {
                if (isset($setting['id'])) {
                    $defaults[$setting['id']] = $setting['default'] ?? null;
                }
            }
        }

        $settings = deepMerge($defaults, $settings);
        $snippet = ['settings' => $settings];
        include $file;
    }
}

/**
 * Load theme settings from settings_data.json.
 */
if (!function_exists('getSetting')) {
function getSetting(string $key, $default = null)
    {
        static $settings;
        if ($settings === null) {
            $file = THEME_PATH . '/config/settings_data.json';
            $settings = is_file($file) ? json_decode(file_get_contents($file), true) : [];

            try {
                $themeId = db_query('SELECT id FROM themes WHERE active = 1 LIMIT 1')->fetchColumn();
                if ($themeId) {
                    $rows = db_query('SELECT `key`, `value` FROM theme_settings WHERE theme_id = :tid', [':tid' => $themeId])->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($rows as $row) {
                        $settings[$row['key']] = $row['value'];
                    }
                }
            } catch (Throwable $e) {
                // ignore if table missing
            }
        }
        return $settings[$key] ?? $default;
    }
}

if (!function_exists('storeSetting')) {
    /**
     * Retrieve a store-specific setting.
     */
function storeSetting(string $key, $default = null)
    {
        static $cache = [];
        if (!isset($cache[STORE_ID])) {
            try {
                $rows = db_query('SELECT `key`, `value` FROM store_settings WHERE store_id = :sid', [':sid' => STORE_ID])->fetchAll(PDO::FETCH_ASSOC);
                $cache[STORE_ID] = [];
                foreach ($rows as $row) {
                    $cache[STORE_ID][$row['key']] = $row['value'];
                }
            } catch (Throwable $e) {
                $cache[STORE_ID] = [];
            }
        }
        return $cache[STORE_ID][$key] ?? $default;
    }
}

if (!function_exists('storeCurrency')) {
    function storeCurrency(): string
    {
        return STORE_CURRENCY;
    }
}

if (!function_exists('storeTimezone')) {
    function storeTimezone(): string
    {
        return STORE_TIMEZONE;
    }
}

/**
 * Get the active color scheme values.
 */
function getColorScheme(): array
{
    $schemeName = getSetting('color_scheme', 'default');
    $file = THEME_PATH . '/config/color_schemes.json';
    $schemes = is_file($file) ? json_decode(file_get_contents($file), true) : [];
    return $schemes[$schemeName] ?? ($schemes['default'] ?? []);
}

/**
 * Retrieve a menu by handle from theme settings.
 *
 * Menus are stored in settings_data.json under the "menus" key
 * as an associative array of menu handles to item arrays.
 */
if (!function_exists('getMenu')) {
    function getMenu(string $handle): array
    {
        $menus = getSetting('menus', []);
        return $menus[$handle] ?? [];
    }
}

/**
 * Render a snippet from the theme snippets directory.
 */
if (!function_exists('renderPartial')) {
    function renderPartial(string $name, array $data = []): string
    {
        // Backwards compatible snippet loader
        $paths = [
            THEME_PATH . '/blocks/' . $name,
            THEME_PATH . '/snippets/' . $name,
        ];

        $phpFile = '';
        $liquidFile = '';

        foreach ($paths as $base) {
            if (is_file($base . '.php')) {
                $phpFile = $base . '.php';
                break;
            }
            if (is_file($base . '.liquid')) {
                $liquidFile = $base . '.liquid';
                break;
            }
        }

        if (!$phpFile && !$liquidFile) {
            return '';
        }

        extract($data);
        ob_start();
        if ($phpFile) {
            include $phpFile;
        } else {
            echo renderLiquid($liquidFile, $data);
        }
        return ob_get_clean();
    }
}

// New alias using ecommerce naming
if (!function_exists('renderBlock')) {
    function renderBlock(string $name, array $data = []): string
    {
        return renderPartial($name, $data);
    }
}

/**
 * Send a 404 response and render the 404 template.
 */
if (!function_exists('notFound')) {
    function notFound(): void
    {
        http_response_code(404);
        $template = THEME_PATH . '/templates/404.php';
        if (is_file($template)) {
            $content = render($template);
            includeThemeLayout(get_defined_vars());
            exit;
        } else {
            echo "404 Not Found";
            exit;
        }
    }
}

/**
 * Load locale strings for the given code.
 */
if (!function_exists('loadLocale')) {
    function loadLocale(string $code = 'en'): array
    {
        static $cache = [];
        if (!isset($cache[$code])) {
            $file = THEME_PATH . '/locales/' . $code . '.json';
            $cache[$code] = is_file($file) ? json_decode(file_get_contents($file), true) : [];
        }
        return $cache[$code];
    }
}

/**
 * Translate key using loaded locale strings.
 */
if (!function_exists('t')) {
    function t(string $key, array $locale): string
    {
        return $locale[$key] ?? $key;
    }
}

if (!function_exists('getCurrentPage')) {
    function getCurrentPage(): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
        return trim($uri, '/') ?: 'index';
    }
}

/**
 * Fetch a single product by ID.
 */
if (!function_exists('getProduct')) {
    function getProduct(int $id): ?array
    {
        $stmt = db_query('SELECT * FROM products WHERE id = :id AND store_id = :sid', [':id' => $id, ':sid' => STORE_ID]);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }
}

if (!function_exists('listThemeSections')) {
function listThemeSections(string $theme = THEME): array
    {
        $path = __DIR__ . "/themes/{$theme}/sections";
        if (!is_dir($path)) {
            return [];
        }
        $files = array_merge(glob($path . '/*.php'), glob($path . '/*.liquid'));
        $names = [];
        foreach ($files as $file) {
            $names[] = preg_replace('/\.(php|liquid)$/', '', basename($file));
        }
        sort($names);
        return $names;
    }
}


if (!function_exists('saveThemeLayout')) {
    function saveThemeLayout(string $page, array $layout, string $theme = THEME): bool
    {
        $file = __DIR__ . "/themes/{$theme}/templates/{$page}.json";
        $json = json_encode($layout, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        return file_put_contents($file, $json) !== false;
    }
}

if (!function_exists('getThemeConfig')) {
    function getThemeConfig(): array
    {
        static $themeConfig;
        if ($themeConfig === null) {
            $file = THEME_PATH . '/theme.json';
            $themeConfig = is_file($file) ? json_decode(file_get_contents($file), true) : [];
        }
        return $themeConfig;
    }
}

if (!function_exists('listAvailableSections')) {
    /**
     * Return section names from the current theme combined with defaults.
     */
    function listAvailableSections(string $theme = THEME): array
    {
        $sections = listThemeSections($theme);
        if ($theme !== 'default' && is_dir(__DIR__ . '/themes/default/sections')) {
            $sections = array_unique(array_merge($sections, listThemeSections('default')));
            sort($sections);
        }
        return $sections;
    }
}

if (!function_exists('loadSectionSchema')) {
    /**
     * Load a section's schema for the given theme.
     */
    function loadSectionSchema(string $name, string $theme = THEME): array
    {
        $schemaPath = __DIR__ . "/themes/{$theme}/sections/{$name}.schema.json";
        if (is_file($schemaPath)) {
            $json = json_decode(file_get_contents($schemaPath), true);
            return is_array($json) ? $json : [];
        }

        $liquidFile = __DIR__ . "/themes/{$theme}/sections/{$name}.liquid";
        if (is_file($liquidFile)) {
            $data = parseLiquidSchema($liquidFile);
            return $data ?? [];
        }

        return [];
    }
}

if (!function_exists('getThemePlatform')) {
    /**
     * Return the platform this theme originated from (e.g. native, shopify, woocommerce).
     */
    function getThemePlatform(): string
    {
        $conf = getThemeConfig();
        return $conf['platform'] ?? 'native';
    }
}

if (!function_exists('resolveThemeFile')) {
    /**
     * Locate a theme file supporting alternative directory structures.
     */
    function resolveThemeFile(string $relative, array $extensions = ['php', 'liquid']): ?string
    {
        $platform = getThemePlatform();
        $candidates = [THEME_PATH . '/' . ltrim($relative, '/')];

        if ($platform === 'woocommerce') {
            $candidates[] = THEME_PATH . '/template-parts/' . ltrim($relative, '/');
            $candidates[] = THEME_PATH . '/woocommerce/' . ltrim($relative, '/');
        }

        foreach ($candidates as $base) {
            foreach ($extensions as $ext) {
                $file = preg_replace('/\.' . preg_quote($ext) . '$/', '', $base) . '.' . $ext;
                if (is_file($file)) {
                    return $file;
                }
            }
        }
        return null;
    }
}

if (!function_exists('getCheckoutSettings')) {
    function getCheckoutSettings(): array
    {
        static $settings;
        if ($settings === null) {
            $file = THEME_PATH . '/config/checkout_settings.json';
            $settings = is_file($file) ? json_decode(file_get_contents($file), true) : [];
        }
        return $settings;
    }
}

if (!function_exists('renderLayoutArray')) {
    /**
     * Render a page layout from a layout array (with sections and order).
     */
function renderLayoutArray(array $layout): string
{
    $output = '';

    if (isset($layout['template_file'])) {
        $file = $layout['template_file'];
        if (str_ends_with($file, '.liquid')) {
            $output .= renderLiquid($file);
        } elseif (is_file($file)) {
            ob_start();
            include $file;
            $output .= ob_get_clean();
        }
        return $output;
    }

    if (!isset($layout['order']) || !is_array($layout['order'])) return $output;
    if (!isset($layout['sections']) || !is_array($layout['sections'])) return $output;

    foreach ($layout['order'] as $sectionId) {
        $section = $layout['sections'][$sectionId] ?? null;
        if (!$section || !is_array($section) || !array_key_exists('type', $section)) continue;

        $type = $section['type'];
        $settings = $section['settings'] ?? [];
        $blocks = $section['blocks'] ?? [];
        $id = $sectionId;

        ob_start();
        includeSection($type, [
            'id' => $id,
            'settings' => $settings,
            'blocks' => $blocks
        ]);
        $output .= ob_get_clean();
    }

    return $output;
}
}


function loadThemeLayout(string $page, string $theme = THEME): array {

    $slug = $page;

    // Check session for live preview data
    if (isset($_GET['preview']) && isset($_SESSION['live_preview'][$slug])) {
        return convertEditorLayoutToCompiled($_SESSION['live_preview'][$slug]);
    }

    $file = __DIR__ . "/themes/{$theme}/templates/{$slug}.json";
    if (!is_file($file)) {
        $alt = resolveThemeFile("templates/{$slug}");
        if (!$alt) {
            $alt = resolveThemeFile($slug);
        }
        if ($alt) {
            return ['sections' => [], 'order' => [], 'template_file' => $alt];
        }
        return ['sections' => [], 'order' => []];
    }

    $json = file_get_contents($file);
    $data = json_decode($json, true);
    return is_array($data) ? $data : ['sections' => [], 'order' => []];
}
function convertEditorLayoutToCompiled(array $layout): array {
    $compiled = ['sections' => [], 'order' => []];
    foreach ($layout as $section) {
        $id = $section['id'] ?? uniqid($section['type'] . '-');
        $compiled['sections'][$id] = [
            'type' => $section['type'],
            'settings' => $section['settings'] ?? [],
            'blocks' => $section['blocks'] ?? [],
        ];
        $compiled['order'][] = $id;
    }
    return $compiled;
}

function convertCompiledLayoutToEditor(array $compiled): array {
    $result = [];
    foreach ($compiled['order'] ?? [] as $id) {
        if (!isset($compiled['sections'][$id])) {
            continue;
        }
        $sec = $compiled['sections'][$id];
        $result[] = [
            'id' => $id,
            'type' => $sec['type'] ?? '',
            'settings' => $sec['settings'] ?? [],
            'blocks' => $sec['blocks'] ?? []
        ];
    }
    return $result;
}




if (!function_exists('getProductByHandle')) {
    function getProductByHandle(string $handle): ?array
    {
        $stmt = db_query('SELECT * FROM products WHERE handle = :handle AND store_id = :sid LIMIT 1', [':handle' => $handle, ':sid' => STORE_ID]);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }
}

if (!function_exists('getCollectionBySlug')) {
    function getCollectionBySlug(string $slug): ?array
    {
        $stmt = db_query('SELECT * FROM collections WHERE slug = :slug AND store_id = :sid LIMIT 1', [':slug' => $slug, ':sid' => STORE_ID]);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }
}

if (!function_exists('getPageBySlug')) {
    function getPageBySlug(string $slug): ?array
    {
        $stmt = db_query('SELECT * FROM pages WHERE slug = :slug LIMIT 1', [':slug' => $slug]);
        $row = $stmt->fetch();
        if ($row === false) {
            return null;
        }
        // Fallback for older schema
        if (!isset($row['layout_published'])) {
            $row['layout_published'] = $row['layout'] ?? '{}';
        }
        return $row;
    }
}

/**
 * Fetch multiple products by ID list, preserving input order.
 */
if (!function_exists('getProducts')) {
function getProducts(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = db_query('SELECT * FROM products WHERE id IN (' . $placeholders . ') AND store_id = ' . STORE_ID, $ids);
        $rows = $stmt->fetchAll();
        // index by id for easier ordering
        $indexed = [];
        foreach ($rows as $row) {
            $indexed[$row['id']] = $row;
        }
        $ordered = [];
        foreach ($ids as $id) {
            if (isset($indexed[$id])) {
                $ordered[] = $indexed[$id];
            }
        }
        return $ordered;
    }
}

if (!function_exists('getProductsByCollection')) {
    /**
     * Fetch products belonging to a collection with optional limit.
     */
function getProductsByCollection($collectionId, $limit = 8) {
    $limit = intval($limit); // Ensure it's a valid number
    $sql = "
      SELECT p.*, 
             COALESCE(pv.price, 0) as price,
             COALESCE(pv.compare_at_price, 0) as compare_price,
             COALESCE(pv.image_url, '') as image
      FROM products p
      JOIN collection_product cp ON cp.product_id = p.id
      LEFT JOIN product_variants pv ON pv.product_id = p.id
      WHERE cp.collection_id = :collection_id AND p.store_id = :sid
      GROUP BY p.id
      LIMIT $limit
    ";
    $stmt = db_query($sql, [
        'collection_id' => $collectionId,
        'sid' => STORE_ID
    ]);
    return $stmt->fetchAll();
}

function getProductReviews($productId) {
    $sql = "SELECT rating FROM product_reviews WHERE product_id = ?";
    return db_query($sql, [$productId]);
}

function getCollectionsForProduct(int $productId): array {
    $sql = "SELECT c.* FROM collections c JOIN collection_product cp ON cp.collection_id = c.id WHERE cp.product_id = :pid";
    $stmt = db_query($sql, [':pid' => $productId]);
    return $stmt->fetchAll();
}

function getFirstCollectionIdForProduct(int $productId): ?int {
    $sql = "SELECT collection_id FROM collection_product WHERE product_id = :pid ORDER BY collection_id ASC LIMIT 1";
    $stmt = db_query($sql, [':pid' => $productId]);
    $row = $stmt->fetch();
    return $row ? (int)$row['collection_id'] : null;
}

if (!function_exists('getProductsBySet')) {
    /**
     * Fetch products belonging to a product set with optional limit.
     */
    function getProductsBySet($setId, $limit = 8) {
        $limit = intval($limit);
        $sql = "
          SELECT p.*
          FROM products p
          JOIN product_set_products sp ON sp.product_id = p.id
          WHERE sp.set_id = :sid AND p.store_id = :sid_store
          LIMIT $limit
        ";
        $stmt = db_query($sql, ['sid' => $setId, 'sid_store' => STORE_ID]);
        return $stmt->fetchAll();
    }
}



}

if (!function_exists('generateHandle')) {
    function generateHandle(string $title): string
    {
        return slugify($title);
    }
}

if (!function_exists('db_last_insert_id')) {
    function db_last_insert_id(): string
    {
        return db()->lastInsertId();  // ✅ Use the db() helper from db.php
    }
}

if (!function_exists('getPrimaryProductImage')) {
    function getPrimaryProductImage(int $productId): ?string
    {
        $stmt = db_query('SELECT src FROM product_images WHERE product_id = :pid ORDER BY id ASC LIMIT 1', [':pid' => $productId]);
        $row = $stmt->fetch();
        return $row['src'] ?? null;
    }
}


// -------------------------
// Product-related Functions
// -------------------------

function getRelatedProductsByCollection($collectionId, $excludeProductId, $limit = 6) {
    $limit = intval($limit);
    $sql = "
      SELECT p.*
      FROM products p
      JOIN collection_product cp ON cp.product_id = p.id
      WHERE cp.collection_id = :collection_id
        AND p.id != :exclude_id
      ORDER BY RAND()
      LIMIT $limit
    ";
    return db_query($sql, [
        'collection_id' => $collectionId,
        'exclude_id' => $excludeProductId
    ]);
}

function getFallbackRecommendedProducts($limit = 6) {
    $limit = intval($limit);
    $sql = "SELECT * FROM products ORDER BY RAND() LIMIT $limit";
    return db_query($sql);
}


// ---------- Wishlist Helpers ----------
if (!function_exists('addToWishlist')) {
    function addToWishlist(int $productId): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['wishlist'] = $_SESSION['wishlist'] ?? [];
        if (!in_array($productId, $_SESSION['wishlist'], true)) {
            $_SESSION['wishlist'][] = $productId;
        }
    }
}

if (!function_exists('removeFromWishlist')) {
    function removeFromWishlist(int $productId): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['wishlist'] = $_SESSION['wishlist'] ?? [];
        $_SESSION['wishlist'] = array_values(array_filter(
            $_SESSION['wishlist'],
            fn($id) => (int)$id !== $productId
        ));
    }
}

if (!function_exists('getWishlist')) {
    function getWishlist(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return array_map('intval', $_SESSION['wishlist'] ?? []);
    }
}

// ---------- Compare Helpers ----------
if (!function_exists('addToCompare')) {
    function addToCompare(int $productId): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['compare'] = $_SESSION['compare'] ?? [];
        if (!in_array($productId, $_SESSION['compare'], true)) {
            $_SESSION['compare'][] = $productId;
        }
    }
}

if (!function_exists('removeFromCompare')) {
    function removeFromCompare(int $productId): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['compare'] = $_SESSION['compare'] ?? [];
        $_SESSION['compare'] = array_values(array_filter(
            $_SESSION['compare'],
            fn($id) => (int)$id !== $productId
        ));
    }
}

if (!function_exists('getCompareList')) {
    function getCompareList(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return array_map('intval', $_SESSION['compare'] ?? []);
    }
}

// ---------- Recently Viewed Helpers ----------
if (!function_exists('addToRecentlyViewed')) {
    function addToRecentlyViewed(int $productId): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['recently_viewed'] = $_SESSION['recently_viewed'] ?? [];
        $_SESSION['recently_viewed'] = array_values(array_filter(
            array_unique(array_merge([$productId], $_SESSION['recently_viewed'])),
            fn($id) => $id > 0
        ));
        $_SESSION['recently_viewed'] = array_slice($_SESSION['recently_viewed'], 0, 10);
    }
}

if (!function_exists('getRecentlyViewed')) {
    function getRecentlyViewed(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return array_map('intval', $_SESSION['recently_viewed'] ?? []);
    }
}

if (!function_exists('slugify')) {
    function slugify(string $string): string
    {
        $string = strtolower(trim($string));
        $string = preg_replace('/[^a-z0-9]+/i', '-', $string);
        return trim($string, '-');
    }
}

if (!function_exists('captureUTMParams')) {
    function captureUTMParams(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $params = ['utm_source','utm_medium','utm_campaign','utm_term','utm_content'];
        foreach ($params as $p) {
            if (!empty($_GET[$p])) {
                $_SESSION['utm'][$p] = $_GET[$p];
            }
        }
    }
}

if (!function_exists('getUTMParams')) {
    function getUTMParams(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['utm'] ?? [];
    }
}

if (!function_exists('isMobile')) {
    function isMobile(): bool
    {
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        return preg_match('/(android|iphone|ipod|blackberry|mobile)/i', $ua) === 1;
    }
}

if (!function_exists('isTablet')) {
    function isTablet(): bool
    {
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        return preg_match('/(ipad|tablet)/i', $ua) === 1;
    }
}

if (!function_exists('currentDevice')) {
    function currentDevice(): string
    {
        if (isMobile()) {
            return 'mobile';
        }
        if (isTablet()) {
            return 'tablet';
        }
        return 'desktop';
    }
}

if (!function_exists('applyResponsiveSettings')) {
    function applyResponsiveSettings(array $settings): array
    {
        $device = currentDevice();
        foreach ($settings as $k => $v) {
            if (is_array($v) && isset($v['desktop']) ) {
                $settings[$k] = $v[$device] ?? ($v['desktop'] ?? null);
            } elseif (is_array($v)) {
                $settings[$k] = applyResponsiveSettings($v);
            }
        }
        return $settings;
    }
}

if (!function_exists('setSecurityHeaders')) {
    /**
     * Send common security headers to harden the application.
     */
    function setSecurityHeaders(): void
    {
        header('X-Frame-Options: SAMEORIGIN');
        header('X-Content-Type-Options: nosniff');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: no-referrer-when-downgrade');
        header('X-Permitted-Cross-Domain-Policies: none');
        header("Permissions-Policy: camera=(), microphone=(), geolocation=()");
        if (isset($_SERVER['HTTPS'])) {
            header('Strict-Transport-Security: max-age=63072000; includeSubDomains; preload');
        }
        header("Content-Security-Policy: default-src 'self' https://www.googletagmanager.com https://connect.facebook.net; img-src 'self' data: https:; script-src 'self' https://www.googletagmanager.com https://connect.facebook.net; style-src 'self' 'unsafe-inline'");
    }
}

if (!function_exists('send_mail')) {
    function send_mail(string $to, string $subject, string $message, string $from = 'no-reply@example.com'): bool
    {
        $headers = "From: {$from}\r\n" .
                   "Reply-To: {$from}\r\n" .
                   "X-Mailer: PHP/" . phpversion();
        $sent = mail($to, $subject, $message, $headers);
        $status = $sent ? 'sent' : 'failed';
        db_query('INSERT INTO email_logs (to_email, subject, status, error_message) VALUES (:to_email, :subject, :status, :error)', [
            ':to_email' => $to,
            ':subject' => $subject,
            ':status' => $status,
            ':error' => $sent ? null : 'mail() failed'
        ]);
        return $sent;
    }
}

if (!function_exists('track_event')) {
    function track_event(?int $userId, string $eventType, array $eventData = []): void
    {
        $sessionId = session_id();
        db_query('INSERT INTO user_events (user_id, session_id, event_type, event_data) VALUES (:uid, :sid, :type, :data)', [
            ':uid' => $userId,
            ':sid' => $sessionId,
            ':type' => $eventType,
            ':data' => json_encode($eventData)
        ]);
    }
}

if (!function_exists('trackCategoryEvent')) {
    /**
     * Convenience wrapper to log category-based events from the editor.
     */
    function trackCategoryEvent(?int $userId, int $categoryId, string $action): void
    {
        track_event($userId, 'category_' . $action, ['category_id' => $categoryId]);
    }
}


if (!function_exists('getPostOrderOffers')) {
    function getPostOrderOffers(array $productIds): array
    {
        if (empty($productIds)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        $stmt = db()->prepare("SELECT * FROM post_order_offers WHERE product_id IN ($placeholders)");
        $stmt->execute($productIds);
        return $stmt->fetchAll();
    }
}

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
        $score = round(($data['lighthouseResult']['categories']['performance']['score'] ?? 0) * 100);
        $screenshot = $data['lighthouseResult']['audits']['final-screenshot']['details']['data'] ?? '';
        $tips = [];
        foreach ($data['lighthouseResult']['audits'] as $audit) {
            if (($audit['score'] ?? 1) < 0.9 && isset($audit['title'])) {
                $tips[] = $audit['title'];
                if (count($tips) >= 3) break;
            }
        }
        return [
            'score' => $score,
            'screenshot' => $screenshot,
            'tips' => $tips
        ];
    }
}



if (!function_exists("log_activity")) {
    /**
     * Record an admin action in the activity_logs table.
     */
    function log_activity(?int $userId, string $action, array $metadata = []): void
    {
        db_query("INSERT INTO activity_logs (user_id, action, metadata) VALUES (:uid, :action, :meta)", [
            ":uid" => $userId,
            ":action" => $action,
            ":meta" => json_encode($metadata)
        ]);
    }

};


function check_admin_logged_in(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
        header("Location: /backend/auth/login.php");
        exit;
    }
}

// ---- Additional E-commerce Helpers ----
if (!function_exists('getTrendingProducts')) {
    /**
     * Return products with highest sales over the last 30 days.
     */
    function getTrendingProducts(int $limit = 5): array
    {
        $sql = "SELECT p.*, SUM(oi.quantity) AS qty
                FROM order_items oi
                JOIN orders o ON o.id = oi.order_id
                JOIN products p ON p.id = oi.product_id
                WHERE o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY p.id
                ORDER BY qty DESC
                LIMIT :limit";
        $stmt = db()->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

if (!function_exists('getCrossSellProducts')) {
    /**
     * Recommend products frequently bought with the given product.
     */
    function getCrossSellProducts(int $productId, int $limit = 4): array
    {
        $sql = "SELECT p2.*, COUNT(*) as freq
                FROM order_items oi1
                JOIN order_items oi2 ON oi1.order_id = oi2.order_id AND oi2.product_id != oi1.product_id
                JOIN products p2 ON p2.id = oi2.product_id
                WHERE oi1.product_id = :pid
                GROUP BY p2.id
                ORDER BY freq DESC
                LIMIT :limit";
        $stmt = db()->prepare($sql);
        $stmt->bindValue(':pid', $productId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

if (!function_exists('currentStore')) {
    /**
     * Retrieve the store record for the current domain.
     */
    function currentStore(): array
    {
        static $store;
        if ($store !== null) {
            return $store;
        }
        try {
            $host = $_SERVER['HTTP_HOST'] ?? '';
            $stmt = db()->prepare('SELECT * FROM stores WHERE custom_domain = :d OR subdomain = :d LIMIT 1');
            $stmt->execute([':d' => $host]);
            $store = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['id' => STORE_ID, 'custom_domain' => $host, 'theme_id' => THEME_ID];
        } catch (Throwable $e) {
            $store = ['id' => STORE_ID, 'custom_domain' => '', 'theme_id' => THEME_ID];
        }
        return $store;
    }
}
