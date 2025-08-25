<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /backend/auth/login.php');
    exit;
}

$themeId = THEME_ID;
$schemaFile = THEME_PATH . '/settings-schema.json';
$schema = is_file($schemaFile) ? json_decode(file_get_contents($schemaFile), true) : [];

$pageTitle = 'Theme Settings';
$modal = isset($_GET['modal']);
if (!$modal) {
    require __DIR__ . '/../components/header.php';
    echo '<link rel="stylesheet" href="/admin/assets/admin.css">';
    echo '<h1>Theme Settings</h1>';
} else {
    echo '<link rel="stylesheet" href="/admin/assets/admin.css">';
}
?>
<?php if(!$modal): ?><div class="settings-wrapper"><?php endif; ?>
  <aside class="settings-menu">
    <ul>
      <?php foreach ($schema as $group => $fields): ?>
        <li><a href="#<?= htmlspecialchars($group) ?>" class="menu-link"><?= htmlspecialchars($group) ?></a></li>
      <?php endforeach; ?>
    </ul>
  </aside>
  <div class="settings-content">
    <?php foreach ($schema as $group => $fields): ?>
      <section id="<?= htmlspecialchars($group) ?>" class="settings-group">
        <h2><?= htmlspecialchars($group) ?></h2>
        <?php foreach ($fields as $key => $field):
            $val = getSetting($key, $field['default'] ?? ''); ?>
          <div class="setting-row">
            <label><?= htmlspecialchars($field['label']) ?></label>
            <?php switch($field['type']){
              case 'color': ?>
                <input type="color" class="setting-input" data-key="<?= $key ?>" value="<?= htmlspecialchars($val) ?>">
              <?php break; case 'range': ?>
                <input type="range" min="<?= $field['min'] ?>" max="<?= $field['max'] ?>" class="setting-input" data-key="<?= $key ?>" value="<?= htmlspecialchars($val) ?>">
              <?php break; case 'checkbox': ?>
                <input type="checkbox" class="setting-input" data-key="<?= $key ?>" <?= $val ? 'checked' : '' ?>>
              <?php break; default: ?>
                <input type="text" class="setting-input" data-key="<?= $key ?>" value="<?= htmlspecialchars($val) ?>">
            <?php } ?>
          </div>
        <?php endforeach; ?>
      </section>
    <?php endforeach; ?>
  </div>
  <?php if($modal): ?>
    <div class="settings-actions">
      <button id="save-settings" class="btn">Save</button>
    </div>
  <?php endif; ?>
<?php if(!$modal): ?></div><?php endif; ?>
<script>const CURRENT_THEME_ID = <?= (int)$themeId ?>;</script>
<script src="/admin/assets/theme-settings.js"></script>
<?php if(!$modal) require __DIR__ . '/../components/footer.php'; ?>