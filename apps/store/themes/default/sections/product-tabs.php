<?php
$id = $id ?? 'product-tabs-' . uniqid();
$tabs = [];
if (!empty($blocks)) {
    foreach ($blocks as $block) {
        if (($block['type'] ?? '') === 'tab') {
            $tabs[] = [
                'label' => $block['settings']['tab_title'] ?? '',
                'content' => $block['settings']['content'] ?? ''
            ];
        }
    }
}
if (empty($tabs)) {
    $tabs = $settings['tabs'] ?? [];
}
$defaultTab = intval($settings['default_tab'] ?? 0);
$tabColor = $settings['tab_color'] ?? '#333';
$tabActiveColor = $settings['tab_active_color'] ?? '#000';
$tabBg = $settings['tab_bg'] ?? 'transparent';

if (!function_exists('escape_html')) {
  function escape_html($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
  }
}
?>

<style>
#<?= $id ?> {
  padding: 40px 20px;
  font-family: Arial, sans-serif;
  max-width: 1000px;
  margin: 0 auto;
}

#<?= $id ?> .tab-header {
  display: flex;
  gap: 20px;
  justify-content: center;
  flex-wrap: wrap;
  border-bottom: 2px solid #eee;
  margin-bottom: 20px;
}

#<?= $id ?> .tab-btn {
  padding: 12px 20px;
  cursor: pointer;
  background: <?= htmlspecialchars($tabBg) ?>;
  border: none;
  border-bottom: 3px solid transparent;
  font-weight: bold;
  font-size: 1rem;
  color: <?= htmlspecialchars($tabColor) ?>;
}

#<?= $id ?> .tab-btn.active {
  border-bottom-color: <?= htmlspecialchars($tabActiveColor) ?>;
  color: <?= htmlspecialchars($tabActiveColor) ?>;
}

#<?= $id ?> .tab-content {
  display: none;
  animation: fadeIn 0.3s ease-in-out;
}

#<?= $id ?> .tab-content.active {
  display: block;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to   { opacity: 1; transform: translateY(0); }
}
</style>

<section id="<?= $id ?>">
  <div class="tab-header" id="<?= $id ?>-tabs">
    <?php foreach ($tabs as $i => $tab): ?>
      <button class="tab-btn <?= $i === $defaultTab ? 'active' : '' ?>" data-tab="<?= $id ?>-tab-<?= $i ?>">
        <?= escape_html($tab['label']) ?>
      </button>
    <?php endforeach; ?>
  </div>

  <?php foreach ($tabs as $i => $tab): ?>
    <div class="tab-content <?= $i === $defaultTab ? 'active' : '' ?>" id="<?= $id ?>-tab-<?= $i ?>">
      <?= $tab['content'] ?>
    </div>
  <?php endforeach; ?>
</section>

<script>
document.querySelectorAll('#<?= $id ?> .tab-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const tabId = btn.getAttribute('data-tab');

    // Remove active from all buttons
    document.querySelectorAll('#<?= $id ?> .tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    // Hide all tabs
    document.querySelectorAll('#<?= $id ?> .tab-content').forEach(tab => tab.classList.remove('active'));

    // Show selected
    document.getElementById(tabId).classList.add('active');
  });
});
</script>
