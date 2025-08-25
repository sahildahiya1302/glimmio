<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';


// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /backend/auth/login.php');
    exit;
}

$pageTitle = 'Settings';

// Handle form submission to update settings
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_delivery_settings'])) {
        $shiprocketApiKey = trim($_POST['shiprocket_api_key'] ?? '');
        $shiprocketApiSecret = trim($_POST['shiprocket_api_secret'] ?? '');
        $enableShiprocket = isset($_POST['enable_shiprocket']) ? 1 : 0;

        $delhiveryApiKey = trim($_POST['delhivery_api_key'] ?? '');
        $delhiveryApiSecret = trim($_POST['delhivery_api_secret'] ?? '');
        $enableDelhivery = isset($_POST['enable_delhivery']) ? 1 : 0;

        $ekartApiKey = trim($_POST['ekart_api_key'] ?? '');
        $ekartApiSecret = trim($_POST['ekart_api_secret'] ?? '');
        $enableEkart = isset($_POST['enable_ekart']) ? 1 : 0;

        db_query('UPDATE settings SET shiprocket_api_key = :shiprocket_api_key, shiprocket_api_secret = :shiprocket_api_secret, enable_shiprocket = :enable_shiprocket, delhivery_api_key = :delhivery_api_key, delhivery_api_secret = :delhivery_api_secret, enable_delhivery = :enable_delhivery, ekart_api_key = :ekart_api_key, ekart_api_secret = :ekart_api_secret, enable_ekart = :enable_ekart WHERE id = 1', [
            ':shiprocket_api_key' => $shiprocketApiKey,
            ':shiprocket_api_secret' => $shiprocketApiSecret,
            ':enable_shiprocket' => $enableShiprocket,
            ':delhivery_api_key' => $delhiveryApiKey,
            ':delhivery_api_secret' => $delhiveryApiSecret,
            ':enable_delhivery' => $enableDelhivery,
            ':ekart_api_key' => $ekartApiKey,
            ':ekart_api_secret' => $ekartApiSecret,
            ':enable_ekart' => $enableEkart,
        ]);
        $_SESSION['flash_message'] = 'Delivery partner settings updated successfully.';
        header('Location: /admin/settings.php');
        exit;
    }

    $siteName = trim($_POST['site_name'] ?? '');
    $siteEmail = trim($_POST['site_email'] ?? '');
    $storePassword = trim($_POST['store_password'] ?? '');
    $storePasswordEnabled = isset($_POST['store_password_enabled']) ? 1 : 0;
    $currency = trim($_POST['currency'] ?? '');
    $timezoneField = trim($_POST['timezone'] ?? '');

    $enableFacebookPixel = isset($_POST['enable_facebook_pixel']) ? 1 : 0;
    $facebookPixelId = trim($_POST['facebook_pixel_id'] ?? '');
    $enableGoogleAnalytics = isset($_POST['enable_google_analytics']) ? 1 : 0;
    $googleAnalyticsId = trim($_POST['google_analytics_id'] ?? '');
    $enableSnapchatPixel = isset($_POST['enable_snapchat_pixel']) ? 1 : 0;
    $snapchatPixelId = trim($_POST['snapchat_pixel_id'] ?? '');

    if ($siteName && filter_var($siteEmail, FILTER_VALIDATE_EMAIL)) {
        if ($storePassword !== '') {
            // Hash the password before storing
            $hashedPassword = password_hash($storePassword, PASSWORD_DEFAULT);
            db_query('UPDATE settings SET site_name = :site_name, site_email = :site_email, store_password = :store_password, store_password_enabled = :store_password_enabled, enable_facebook_pixel = :enable_facebook_pixel, facebook_pixel_id = :facebook_pixel_id, enable_google_analytics = :enable_google_analytics, google_analytics_id = :google_analytics_id, enable_snapchat_pixel = :enable_snapchat_pixel, snapchat_pixel_id = :snapchat_pixel_id, currency = :currency, timezone = :timezone WHERE id = 1', [
                ':site_name' => $siteName,
                ':site_email' => $siteEmail,
                ':store_password' => $hashedPassword,
                ':store_password_enabled' => $storePasswordEnabled,
                ':enable_facebook_pixel' => $enableFacebookPixel,
                ':facebook_pixel_id' => $facebookPixelId,
                ':enable_google_analytics' => $enableGoogleAnalytics,
                ':google_analytics_id' => $googleAnalyticsId,
                ':enable_snapchat_pixel' => $enableSnapchatPixel,
                ':snapchat_pixel_id' => $snapchatPixelId,
                ':currency' => $currency ?: STORE_CURRENCY,
                ':timezone' => $timezoneField ?: STORE_TIMEZONE,
            ]);
        } else {
            db_query('UPDATE settings SET site_name = :site_name, site_email = :site_email, store_password_enabled = :store_password_enabled, enable_facebook_pixel = :enable_facebook_pixel, facebook_pixel_id = :facebook_pixel_id, enable_google_analytics = :enable_google_analytics, google_analytics_id = :google_analytics_id, enable_snapchat_pixel = :enable_snapchat_pixel, snapchat_pixel_id = :snapchat_pixel_id, currency = :currency, timezone = :timezone WHERE id = 1', [
                ':site_name' => $siteName,
                ':site_email' => $siteEmail,
                ':store_password_enabled' => $storePasswordEnabled,
                ':enable_facebook_pixel' => $enableFacebookPixel,
                ':facebook_pixel_id' => $facebookPixelId,
                ':enable_google_analytics' => $enableGoogleAnalytics,
                ':google_analytics_id' => $googleAnalyticsId,
                ':enable_snapchat_pixel' => $enableSnapchatPixel,
                ':snapchat_pixel_id' => $snapchatPixelId,
                ':currency' => $currency ?: STORE_CURRENCY,
                ':timezone' => $timezoneField ?: STORE_TIMEZONE,
            ]);
        }
        $_SESSION['flash_message'] = 'Settings updated successfully.';
    } else {
        $_SESSION['flash_message'] = 'Please provide valid site name and email.';
    }
    header('Location: /admin/settings.php');
    exit;
}

$settings = db_query('SELECT site_name, site_email, store_password, store_password_enabled, enable_facebook_pixel, facebook_pixel_id, enable_google_analytics, google_analytics_id, enable_snapchat_pixel, snapchat_pixel_id, currency, timezone FROM settings WHERE id = 1')->fetch();

require __DIR__ . '/../components/header.php';
?>

<h2>Delivery Partner Integrations</h2>
<form method="post" action="settings.php">
    <h3>Shiprocket</h3>
    <label for="shiprocket_api_key">API Key:</label><br>
    <input type="text" id="shiprocket_api_key" name="shiprocket_api_key" value="<?= htmlspecialchars($settings['shiprocket_api_key'] ?? '') ?>"><br>
    <label for="shiprocket_api_secret">API Secret:</label><br>
    <input type="text" id="shiprocket_api_secret" name="shiprocket_api_secret" value="<?= htmlspecialchars($settings['shiprocket_api_secret'] ?? '') ?>"><br>
    <label><input type="checkbox" name="enable_shiprocket" <?= !empty($settings['enable_shiprocket']) ? 'checked' : '' ?>> Enable Shiprocket</label><br>

    <h3>Delhivery</h3>
    <label for="delhivery_api_key">API Key:</label><br>
    <input type="text" id="delhivery_api_key" name="delhivery_api_key" value="<?= htmlspecialchars($settings['delhivery_api_key'] ?? '') ?>"><br>
    <label for="delhivery_api_secret">API Secret:</label><br>
    <input type="text" id="delhivery_api_secret" name="delhivery_api_secret" value="<?= htmlspecialchars($settings['delhivery_api_secret'] ?? '') ?>"><br>
    <label><input type="checkbox" name="enable_delhivery" <?= !empty($settings['enable_delhivery']) ? 'checked' : '' ?>> Enable Delhivery</label><br>

    <h3>Ekart</h3>
    <label for="ekart_api_key">API Key:</label><br>
    <input type="text" id="ekart_api_key" name="ekart_api_key" value="<?= htmlspecialchars($settings['ekart_api_key'] ?? '') ?>"><br>
    <label for="ekart_api_secret">API Secret:</label><br>
    <input type="text" id="ekart_api_secret" name="ekart_api_secret" value="<?= htmlspecialchars($settings['ekart_api_secret'] ?? '') ?>"><br>
    <label><input type="checkbox" name="enable_ekart" <?= !empty($settings['enable_ekart']) ? 'checked' : '' ?>> Enable Ekart</label><br>

    <button type="submit" name="save_delivery_settings">Save Delivery Partner Settings</button>
</form>


<h1>Settings</h1>

<?php if (!empty($_SESSION['flash_message'])): ?>
    <div class="flash-message"><?= htmlspecialchars($_SESSION['flash_message']) ?></div>
    <?php unset($_SESSION['flash_message']); ?>
<?php endif; ?>

<form method="post">
    <label for="site_name">Site Name:</label><br>
    <input type="text" id="site_name" name="site_name" required value="<?= htmlspecialchars($settings['site_name'] ?? '') ?>"><br><br>
    <label for="site_email">Site Email:</label><br>
    <input type="email" id="site_email" name="site_email" required value="<?= htmlspecialchars($settings['site_email'] ?? '') ?>"><br><br>
    <label for="store_password">Store Password (leave blank to keep current):</label><br>
    <input type="password" id="store_password" name="store_password" autocomplete="new-password"><br><br>
    <label for="store_password_enabled">
        <input type="checkbox" id="store_password_enabled" name="store_password_enabled" value="1" <?= !empty($settings['store_password_enabled']) ? 'checked' : '' ?>>
        Enable Store Password Protection
    </label><br><br>

    <label for="currency">Currency Symbol:</label><br>
    <input type="text" id="currency" name="currency" value="<?= htmlspecialchars($settings['currency'] ?? STORE_CURRENCY) ?>"><br><br>

    <label for="timezone">Store Timezone:</label><br>
    <input type="text" id="timezone" name="timezone" value="<?= htmlspecialchars($settings['timezone'] ?? STORE_TIMEZONE) ?>"><br><br>

    <h2>Pixel Integrations</h2>

    <label for="enable_facebook_pixel">
        <input type="checkbox" id="enable_facebook_pixel" name="enable_facebook_pixel" value="1" <?= !empty($settings['enable_facebook_pixel']) ? 'checked' : '' ?>>
        Enable Facebook Pixel
    </label><br>

    <label for="facebook_pixel_id">Facebook Pixel ID:</label><br>
    <input type="text" id="facebook_pixel_id" name="facebook_pixel_id" value="<?= htmlspecialchars($settings['facebook_pixel_id'] ?? '') ?>"><br><br>

    <label for="enable_google_analytics">
        <input type="checkbox" id="enable_google_analytics" name="enable_google_analytics" value="1" <?= !empty($settings['enable_google_analytics']) ? 'checked' : '' ?>>
        Enable Google Analytics
    </label><br>

    <label for="google_analytics_id">Google Analytics Tracking ID:</label><br>
    <input type="text" id="google_analytics_id" name="google_analytics_id" value="<?= htmlspecialchars($settings['google_analytics_id'] ?? '') ?>"><br><br>

    <label for="enable_snapchat_pixel">
        <input type="checkbox" id="enable_snapchat_pixel" name="enable_snapchat_pixel" value="1" <?= !empty($settings['enable_snapchat_pixel']) ? 'checked' : '' ?>>
        Enable Snapchat Pixel
    </label><br>

    <label for="snapchat_pixel_id">Snapchat Pixel ID:</label><br>
    <input type="text" id="snapchat_pixel_id" name="snapchat_pixel_id" value="<?= htmlspecialchars($settings['snapchat_pixel_id'] ?? '') ?>"><br><br>

    <button type="submit">Update Settings</button>
</form>

<?php
require __DIR__ . '/../components/footer.php';
?>
