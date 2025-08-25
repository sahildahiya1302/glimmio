<?php
$id = $id ?? 'faq-' . uniqid();
$heading = $settings['heading'] ?? 'Frequently Asked Questions';
$faqs = $settings['faqs'] ?? [];
$textAlign = $settings['text_align'] ?? 'left';
$background = $settings['background'] ?? '#ffffff';
?>

<style>
#<?= $id ?> {
  padding: 60px 20px;
  max-width: 800px;
  margin: 0 auto;
  background: <?= escape_html($background) ?>;
  text-align: <?= escape_html($textAlign) ?>;
}

#<?= $id ?> .faq-item {
  margin-bottom: 20px;
  border: 1px solid #ddd;
  border-radius: 8px;
  overflow: hidden;
}

#<?= $id ?> .faq-question {
  background: #f5f5f5;
  padding: 15px 20px;
  cursor: pointer;
  font-weight: bold;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

#<?= $id ?> .faq-question:hover {
  background: #e9e9e9;
}

#<?= $id ?> .faq-answer {
  padding: 20px;
  background: white;
  display: none;
}

#<?= $id ?> .faq-answer.active {
  display: block;
}

#<?= $id ?> .toggle-icon {
  font-size: 18px;
  transition: transform 0.3s;
}

#<?= $id ?> .faq-item.active .toggle-icon {
  transform: rotate(45deg);
}
</style>

<section id="<?= $id ?>">
  <h2><?= htmlspecialchars($heading) ?></h2>
  
  <div class="faq-list">
    <?php foreach ($faqs as $index => $faq): ?>
      <div class="faq-item">
        <div class="faq-question" onclick="toggleFaq(this)">
          <?= htmlspecialchars($faq['question'] ?? '') ?>
          <span class="toggle-icon">+</span>
        </div>
        <div class="faq-answer">
          <?= htmlspecialchars($faq['answer'] ?? '') ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<script>
function toggleFaq(element) {
  const faqItem = element.parentElement;
  const answer = element.nextElementSibling;
  const isActive = faqItem.classList.contains('active');
  
  // Close all other FAQs
  document.querySelectorAll('.faq-item').forEach(item => {
    item.classList.remove('active');
    item.querySelector('.faq-answer').style.display = 'none';
  });
  
  // Toggle current FAQ
  if (!isActive) {
    faqItem.classList.add('active');
    answer.style.display = 'block';
  }
}
</script>
