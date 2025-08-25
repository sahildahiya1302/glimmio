<?php
$id = $id ?? 'contact-form-' . uniqid();
$heading = $settings['heading'] ?? 'Get in Touch';
$subheading = $settings['subheading'] ?? 'We usually respond within 24 hours.';
$textAlign = $settings['text_align'] ?? 'center';
$buttonText = $settings['button_text'] ?? 'Send Message';
$backgroundColor = $settings['background_color'] ?? '#f8f8f8';
$textColor = $settings['text_color'] ?? '#000';
$buttonBg = $settings['button_bg'] ?? '#000';
$buttonColor = $settings['button_color'] ?? '#fff';
$padding = $settings['padding'] ?? '50px 20px';
$borderRadius = $settings['border_radius'] ?? '8px';
$successMessage = $settings['success_message'] ?? 'Thanks for reaching out!';
$submitUrl = $settings['submit_url'] ?? '/submit-contact.php';

if (!function_exists('escape_html')) {
  function escape_html($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
  }
}
?>

<style>
#<?= $id ?> {
  padding: <?= escape_html($padding) ?>;
  text-align: <?= escape_html($textAlign) ?>;
  font-family: Arial, sans-serif;
  background-color: <?= escape_html($backgroundColor) ?>;
  color: <?= escape_html($textColor) ?>;
  border-radius: <?= escape_html($borderRadius) ?>;
}

#<?= $id ?> form {
  max-width: 600px;
  margin: 0 auto;
  text-align: left;
}

#<?= $id ?> input, #<?= $id ?> textarea {
  width: 100%;
  padding: 10px;
  margin-bottom: 1rem;
  border: 1px solid #ccc;
  border-radius: 4px;
}

#<?= $id ?> button {
  background-color: <?= escape_html($buttonBg) ?>;
  color: <?= escape_html($buttonColor) ?>;
  padding: 12px 24px;
  border: none;
  border-radius: 30px;
  cursor: pointer;
  font-weight: bold;
}

#<?= $id ?> button:hover {
  opacity: 0.85;
}
</style>

<section id="<?= $id ?>">
  <h2><?= escape_html($heading) ?></h2>
  <p><?= escape_html($subheading) ?></p>
  <form id="<?= $id ?>-form" method="post" action="<?= escape_html($submitUrl) ?>">
    <input type="text" name="name" placeholder="Your Name" required />
    <input type="email" name="email" placeholder="Email Address" required />
    <textarea name="message" placeholder="Your Message" rows="5" required></textarea>
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
