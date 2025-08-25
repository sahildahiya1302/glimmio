<?php
if (!defined('THEME')) define('THEME', 'default');
if (!defined('THEME_PATH')) define('THEME_PATH', realpath(__DIR__ . '/../'));


// ✅ Load shared functions
require_once __DIR__ . '/../../../functions.php';

// ✅ Locale
$locale = 'en';
$localeFile = THEME_PATH . "locales/{$locale}.json";
$localeStrings = file_exists($localeFile) ? json_decode(file_get_contents($localeFile), true) : [];

// ✅ Page dynamic variables
$pageTitle = isset($pageTitle) ? $pageTitle : getSetting('page_title', 'My Store');
$pageDescription = $pageDescription ?? getSetting('page_description', 'Welcome to My Store');
$pageHeadCode = $pageHeadCode ?? '';
$pageBodyStartCode = $pageBodyStartCode ?? '';
$pageBodyEndCode = $pageBodyEndCode ?? '';
$pageCssCode = $pageCssCode ?? '';
$pageJsCode = $pageJsCode ?? '';
$backgroundColor = getSetting('background_color', '#ffffff');
$themeConf = getThemeConfig();
$vars = [];

if (isset($themeConf['settings']) && is_array($themeConf['settings'])) {
    foreach ($themeConf['settings'] as $item) {
        if (!empty($item['id']) && isset($item['default'])) {
            $vars[$item['id']] = $item['default'];
        }
    }
}

$ogImage = asset('images/logo.png');
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($locale) ?>">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= e($pageTitle) ?></title>
  <meta name="description" content="<?= e($pageDescription) ?>" />
  <meta property="og:title" content="<?= e($pageTitle) ?>" />
  <meta property="og:description" content="<?= e($pageDescription) ?>" />
  <meta property="og:image" content="<?= e($ogImage) ?>" />
  <link rel="icon" href="<?= asset('images/favicon.ico') ?>" type="image/x-icon" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet" />

  <link rel="preload" href="<?= asset('reset.css') ?>" as="style" onload="this.rel='stylesheet'" />
  <link rel="preload" href="<?= asset('grid.css') ?>" as="style" onload="this.rel='stylesheet'" />
  <link rel="preload" href="<?= asset('theme.css') ?>" as="style" onload="this.rel='stylesheet'" />
  <link rel="preload" href="<?= asset('components/buttons.css') ?>" as="style" onload="this.rel='stylesheet'" />
  <link rel="preload" href="<?= asset('components/forms.css') ?>" as="style" onload="this.rel='stylesheet'" />
  <noscript>
    <link rel="stylesheet" href="<?= asset('reset.css') ?>" />
    <link rel="stylesheet" href="<?= asset('grid.css') ?>" />
    <link rel="stylesheet" href="<?= asset('theme.css') ?>" />
    <link rel="stylesheet" href="<?= asset('components/buttons.css') ?>" />
    <link rel="stylesheet" href="<?= asset('components/forms.css') ?>" />
  </noscript>
  <style>
  :root {
    <?php foreach ($vars as $k => $v): ?>
      --<?= e($k) ?>: <?= e($v) ?>;
    <?php endforeach; ?>
    <?php foreach (getColorScheme() as $ck => $cv): ?>
      --color-<?= e($ck) ?>: <?= e($cv) ?>;
    <?php endforeach; ?>
  }
  </style>
  <?php if (!empty($pageCssCode)): ?>
    <style><?= $pageCssCode ?></style>
  <?php endif; ?>
  <?php $customCss = getSetting('custom_css', ''); if ($customCss): ?>
    <style><?= $customCss ?></style>
  <?php endif; ?>
  <script defer src="<?= asset('theme.js') ?>"></script>
  <script defer src="<?= asset('custom.js') ?>"></script>
  <script defer src="<?= asset('performance.js') ?>"></script>
  <?php if (!empty($pageHeadCode)) echo $pageHeadCode; ?>

  <?php if (getSetting('enable_analytics', false)): ?>
    <?php
      $settings = json_decode(file_get_contents(THEME_PATH . '/config/settings_data.json'), true);
      $facebookPixelId = $settings['facebook_pixel_id'] ?? '';
      $googleAnalyticsId = $settings['google_analytics_id'] ?? '';
      $snapchatPixelId = $settings['snapchat_pixel_id'] ?? '';
    ?>
    <?php if ($facebookPixelId): ?>
      <!-- Facebook Pixel -->
      <script>
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '<?= e($facebookPixelId) ?>');
        fbq('track', 'PageView');
      </script>
      <noscript>
        <img height="1" width="1" style="display:none"
             src="https://www.facebook.com/tr?id=<?= e($facebookPixelId) ?>&ev=PageView&noscript=1"/>
      </noscript>
    <?php endif; ?>

    <?php if ($googleAnalyticsId): ?>
      <!-- Google Analytics -->
      <script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($googleAnalyticsId) ?>"></script>
      <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '<?= e($googleAnalyticsId) ?>');
      </script>
    <?php endif; ?>

    <?php if ($snapchatPixelId): ?>
      <!-- Snapchat Pixel -->
      <script type="text/javascript">
        (function(e,t,n){if(e.snaptr)return;var a=e.snaptr=function(){
        a.handleRequest?a.handleRequest.apply(a,arguments):a.queue.push(arguments)};
        a.queue=[];var s='script';r=t.createElement(s);r.async=!0;
        r.src=n;var u=t.getElementsByTagName(s)[0];
        u.parentNode.insertBefore(r,u);})(window,document,
        'https://sc-static.net/scevent.min.js');
        snaptr('init', '<?= e($snapchatPixelId) ?>');
        snaptr('track', 'PAGE_VIEW');
      </script>
    <?php endif; ?>
  <?php endif; ?>
</head>
<body style="background-color: <?= htmlspecialchars($backgroundColor) ?>;">
<?php if (!empty($pageBodyStartCode)) echo $pageBodyStartCode; ?>

<?php
// Sections like announcement bar and header are now defined in each page layout
// via the theme JSON templates.
?>

<!-- ✅ Main page content -->
<div id="page-content" class="page-content container">
  <?php $content_to_show = $content ?? ($content_for_layout ?? ''); ?>
  <?php if (!empty($content_to_show)): ?>
    <?= $content_to_show ?>
  <?php else: ?>
    <div class="container">
      <p>No content available. Please check your page layout or theme editor settings.</p>
    </div>
  <?php endif; ?>

</div>

<?php
// Footer sections are also loaded from the page layout JSON.
?>

<?php if (!empty($pageJsCode)): ?>
  <script><?= $pageJsCode ?></script>
<?php endif; ?>
<?php if (!empty($pageBodyEndCode)) echo $pageBodyEndCode; ?>
</body>
</html>
