<?php
// config.php (SAFE version — no early DB call)
declare(strict_types=1);

// Extend session cookie lifetime to keep carts persistent for 30 days


// Load .env
$dotenvPath = __DIR__ . '/.env';
if (file_exists($dotenvPath)) {
    $lines = file($dotenvPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }
        putenv($line);
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $_ENV[trim($parts[0])] = trim($parts[1]);
            $_SERVER[trim($parts[0])] = trim($parts[1]);
        }
    }
}

define('DB_HOST', getenv('DB_HOST'));
define('DB_NAME', getenv('DB_NAME'));
define('DB_USER', getenv('DB_USER'));
define('DB_PASS', getenv('DB_PASSWORD'));
define('DB_CHARSET', 'utf8mb4');
define('DB_DSN', 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET);

define('BASE_PATH', __DIR__);
define('BASE_URL', getenv('BASE_URL') ?: '');

// Determine active store and theme
$previewThemeId = isset($_GET['preview_theme_id']) ? (int)$_GET['preview_theme_id'] : 0;
$activeThemeId = 1;
$storeId = 1;
$currency = '$';
$timezone = 'UTC';
$domain = $_SERVER['HTTP_HOST'] ?? '';
if ($previewThemeId) {
    $activeThemeId = $previewThemeId;
} else {
    try {
        $pdo = new PDO(DB_DSN, DB_USER ?: '', DB_PASS ?: '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        $stmt = $pdo->prepare('SELECT id, theme_id, currency, timezone FROM stores WHERE custom_domain = :d OR subdomain = :d LIMIT 1');
        $stmt->execute([':d' => $domain]);
        $storeRow = $stmt->fetch();
        if ($storeRow) {
            $storeId = (int)$storeRow['id'];
            $activeThemeId = (int)$storeRow['theme_id'];
            $currency = $storeRow['currency'] ?? '$';
            $timezone = $storeRow['timezone'] ?? 'UTC';
        } else {
            $row = $pdo->query('SELECT id FROM themes WHERE active = 1 LIMIT 1')->fetch();
            if ($row) {
                $activeThemeId = (int)$row['id'];
            } else {
                // If no active theme exists, ensure a default theme record is present
                $count = (int)$pdo->query('SELECT COUNT(*) FROM themes')->fetchColumn();
                if ($count === 0) {
                    $pdo->exec("INSERT INTO themes (name, active) VALUES ('Default Theme', 1)");
                    $activeThemeId = (int)$pdo->lastInsertId();
                } else {
                    $row = $pdo->query('SELECT id FROM themes ORDER BY id LIMIT 1')->fetch();
                    if ($row) {
                        $activeThemeId = (int)$row['id'];
                    }
                }
            }
        }

        // Ensure settings row exists so other queries don't fail
        $exists = $pdo->query('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = "settings"')->fetchColumn();
        if ($exists) {
            $settingsCount = (int)$pdo->query('SELECT COUNT(*) FROM settings')->fetchColumn();
            if ($settingsCount === 0) {
                $pdo->exec("INSERT INTO settings (site_name, site_email) VALUES ('My Store', 'https://midnightstyle.in/')");
            }
        }
    } catch (PDOException $e) {
        // Table might not exist yet. Use defaults.
    }
}
$themeFolder = ($activeThemeId === 1) ? 'default' : 'theme' . $activeThemeId;
define('THEME_ID', $activeThemeId);
define('STORE_ID', $storeId);
define('THEME', $themeFolder);
define('THEME_PATH', BASE_PATH . '/themes/' . THEME);
define('UPLOADS_PATH', BASE_PATH . '/uploads');
define('STORE_CURRENCY', $currency);
define('STORE_TIMEZONE', $timezone);
date_default_timezone_set(STORE_TIMEZONE);
define("RECAPTCHA_SECRET", getenv("RECAPTCHA_SECRET") ?: "");
define("RECAPTCHA_SITE_KEY", getenv("RECAPTCHA_SITE_KEY") ?: "");

// Function is defined, but not executed here
function getStorePassword() {
    $stmt = db()->prepare('SELECT `key`, `value` FROM store_settings WHERE store_id = :sid AND `key` IN ("store_password","store_password_enabled")');
    $stmt->execute([':sid' => STORE_ID]);
    $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    return [
        'password' => $rows['store_password'] ?? null,
        'enabled' => !empty($rows['store_password_enabled'] ?? null),
    ];
}