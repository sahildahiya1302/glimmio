<?php
$id = $id ?? 'countdown-' . uniqid();
$heading = $settings['heading'] ?? 'Limited Time Offer!';
$endTime = $settings['end_time'] ?? date('Y-m-d\TH:i:s', strtotime('+2 days'));
$background = $settings['background'] ?? '#f2f2f2';
$textColor = $settings['text_color'] ?? '#000';
$textAlign = $settings['text_align'] ?? 'center';
$fontSize = $settings['font_size'] ?? '2rem';
$expiredMessage = $settings['expired_message'] ?? 'Expired';
?>

<style>
#<?= $id ?> {
  background-color: <?= $background ?>;
  color: <?= $textColor ?>;
  text-align: <?= escape_html($textAlign) ?>;
  padding: 40px 20px;
  font-family: Arial, sans-serif;
}

#<?= $id ?> .countdown {
  font-size: <?= escape_html($fontSize) ?>;
  font-weight: bold;
  margin-top: 10px;
}
</style>

<section id="<?= $id ?>">
  <h2><?= escape_html($heading) ?></h2>
  <div class="countdown" id="<?= $id ?>-clock">00:00:00</div>
</section>

<script>
(function(){
  const end = new Date("<?= $endTime ?>").getTime();
  const el = document.getElementById("<?= $id ?>-clock");
  const timer = setInterval(function() {
    const now = new Date().getTime();
    const diff = end - now;
    if (diff < 0) {
      el.innerText = <?= json_encode($expiredMessage) ?>;
      clearInterval(timer);
      return;
    }
    const h = String(Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)).toString().padStart(2, '0'));
    const m = String(Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60)).toString().padStart(2, '0'));
    const s = String(Math.floor((diff % (1000 * 60)) / 1000).toString().padStart(2, '0'));
    el.innerText = `${h}:${m}:${s}`;
  }, 1000);
})();
</script>
