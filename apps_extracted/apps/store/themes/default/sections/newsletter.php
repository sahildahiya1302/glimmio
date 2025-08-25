<?php
$id = $id ?? 'newsletter-' . uniqid();
$heading = $settings['heading'] ?? 'Subscribe to our Newsletter';
$subheading = $settings['subheading'] ?? 'Get special offers, updates, and more.';
$textAlign = $settings['text_align'] ?? 'center';
$placeholder = $settings['placeholder'] ?? 'Enter your email';
$buttonText = $settings['button_text'] ?? 'Subscribe';
$actionUrl = $settings['action_url'] ?? '/subscribe-newsletter.php';
$backgroundColor = $settings['background_color'] ?? '#f8f8f8';
$textColor = $settings['text_color'] ?? '#000';
$buttonBg = $settings['button_bg'] ?? '#000';
$buttonColor = $settings['button_color'] ?? '#fff';
$successMessage = $settings['success_message'] ?? 'Thanks for subscribing!';

if (!function_exists('escape_html')) {
  function escape_html($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
  }
}
?>

<style>
#<?= $id ?> {
  padding: 50px 20px;
  background: <?= escape_html($backgroundColor) ?>;
  color: <?= escape_html($textColor) ?>;
  font-family: Arial, sans-serif;
  text-align: <?= escape_html($textAlign) ?>;
}

#<?= $id ?> form {
  max-width: 500px;
  margin: 20px auto 0;
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

#<?= $id ?> input[type="email"] {
  flex: 1;
  padding: 12px;
  border-radius: 4px;
  border: 1px solid #ccc;
}

#<?= $id ?> button {
  padding: 12px 20px;
  background: <?= escape_html($buttonBg) ?>;
  color: <?= escape_html($buttonColor) ?>;
  border: none;
  border-radius: 4px;
  font-weight: bold;
}
</style>

<section id="<?= $id ?>">
  <h2><?= escape_html($heading) ?></h2>
  <p><?= escape_html($subheading) ?></p>
  <form id="<?= $id ?>-form" method="POST" action="<?= escape_html($actionUrl) ?>">
    <input type="email" name="email" placeholder="<?= escape_html($placeholder) ?>" required />
    <button type="submit"><?= escape_html($buttonText) ?></button>
  </form>
  <div class="success-message" style="display:none;margin-top:1rem;">
    <?= escape_html($successMessage) ?>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded',function(){
  const form=document.getElementById('<?= $id ?>-form');
  const success=form.nextElementSibling;
  form.addEventListener('submit',function(e){
    e.preventDefault();
    success.style.display='block';
    form.style.display='none';
  });
});
</script>
