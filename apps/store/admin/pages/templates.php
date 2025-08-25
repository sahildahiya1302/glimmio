<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /backend/auth/login.php');
    exit;
}

$pageTitle = 'Page Templates';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'delete') {
        $id = intval($_POST['id']);
        $stmt = $pdo->prepare('DELETE FROM page_templates WHERE id = ?');
        $stmt->execute([$id]);
    }
}

$templates = $pdo->query('SELECT * FROM page_templates ORDER BY created_at DESC')->fetchAll(PDO::FETCH_ASSOC);
include __DIR__ . '/components/header.php';
?>
<h1>Page Templates</h1>
<table class="table">
    <tr><th>ID</th><th>Name</th><th>Type</th><th>Actions</th></tr>
    <?php foreach ($templates as $tpl): ?>
    <tr>
        <td><?= htmlspecialchars($tpl['id']) ?></td>
        <td><?= htmlspecialchars($tpl['name']) ?></td>
        <td><?= htmlspecialchars($tpl['type']) ?></td>
        <td>
            <form method="post" style="display:inline">
                <input type="hidden" name="id" value="<?= $tpl['id'] ?>">
                <input type="hidden" name="action" value="delete">
                <button class="btn btn-sm btn-danger" onclick="return confirm('Delete template?')">Delete</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php include __DIR__ . '/components/footer.php'; ?>
