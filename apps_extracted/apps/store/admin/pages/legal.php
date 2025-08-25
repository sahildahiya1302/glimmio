<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /backend/auth/login.php');
    exit;
}

$pageTitle = 'Legal Pages';

$policies = [
    'shipping-policy' => 'Shipping Policy',
    'privacy-policy' => 'Privacy Policy',
    'return-refund-policy' => 'Return and Refund Policy',
    'data-usage' => 'Data Usage',
    'terms-of-service' => 'Terms of Service',
    'cancellation-policy' => 'Cancellation Policy'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($policies as $slug => $title) {
        $content = trim($_POST[$slug] ?? '');
        $page = db_query('SELECT id FROM pages WHERE slug = :slug', [':slug' => $slug])->fetch();
        if ($page) {
            db_query('UPDATE pages SET title = :title, content = :content, is_published = 1 WHERE slug = :slug', [
                ':title' => $title,
                ':content' => $content,
                ':slug' => $slug
            ]);
        } else {
            db_query('INSERT INTO pages (title, slug, content, layout, layout_draft, layout_published, is_published, version) VALUES (:title, :slug, :content, "{}", "{}", "{}", 1, 1)', [
                ':title' => $title,
                ':slug' => $slug,
                ':content' => $content
            ]);
        }
    }
    $_SESSION['flash_message'] = 'Policies updated.';
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

$policyContent = [];
foreach ($policies as $slug => $title) {
    $row = db_query('SELECT content FROM pages WHERE slug = :slug', [':slug' => $slug])->fetch();
    $policyContent[$slug] = $row['content'] ?? '';
}

require __DIR__ . '/../components/header.php';
?>
<h1>Legal Pages</h1>
<?php if (!empty($_SESSION['flash_message'])): ?>
    <div class="flash-message"><?= htmlspecialchars($_SESSION['flash_message']) ?></div>
    <?php unset($_SESSION['flash_message']); ?>
<?php endif; ?>
<form method="post">
<?php foreach ($policies as $slug => $title): ?>
    <h3><?= htmlspecialchars($title) ?></h3>
    <textarea name="<?= htmlspecialchars($slug) ?>" rows="6" style="width:100%;"><?= htmlspecialchars($policyContent[$slug]) ?></textarea>
    <br><br>
<?php endforeach; ?>
    <button type="submit">Save Policies</button>
</form>
<?php require __DIR__ . '/../components/footer.php'; ?>
