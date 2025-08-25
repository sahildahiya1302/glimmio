<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /backend/auth/login.php');
    exit;
}

$pageTitle = 'Section Presets';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'delete') {
        $id = intval($_POST['id']);
        $stmt = $pdo->prepare('DELETE FROM section_presets WHERE id = ?');
        $stmt->execute([$id]);
    }
}

$presets = $pdo->query('SELECT * FROM section_presets ORDER BY created_at DESC')->fetchAll(PDO::FETCH_ASSOC);
include __DIR__ . '/components/header.php';
?>
<h1>Section Presets</h1>
<table class="table">
    <tr><th>ID</th><th>Name</th><th>Type</th><th>Actions</th></tr>
    <?php foreach ($presets as $preset): ?>
    <tr>
        <td><?= htmlspecialchars($preset['id']) ?></td>
        <td><?= htmlspecialchars($preset['name']) ?></td>
        <td><?= htmlspecialchars($preset['type']) ?></td>
        <td>
            <form method="post" style="display:inline">
                <input type="hidden" name="id" value="<?= $preset['id'] ?>">
                <input type="hidden" name="action" value="delete">
                <button class="btn btn-sm btn-danger" onclick="return confirm('Delete preset?')">Delete</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php include __DIR__ . '/components/footer.php'; ?>
