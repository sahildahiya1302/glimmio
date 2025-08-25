<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /backend/auth/login.php');
    exit;
}

$message = '';

// Handle create, update, delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $funnelId = intval($_POST['funnel_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $active = isset($_POST['active']) ? 1 : 0;

    if ($action === 'create' && $name !== '') {
        db_query('INSERT INTO marketing_funnels (name, active) VALUES (?, ?)', [$name, $active]);
        $message = 'Funnel created successfully.';
    } elseif ($action === 'update' && $funnelId > 0 && $name !== '') {
        db_query('UPDATE marketing_funnels SET name = ?, active = ? WHERE id = ?', [$name, $active, $funnelId]);
        $message = 'Funnel updated successfully.';
    } elseif ($action === 'delete' && $funnelId > 0) {
        db_query('DELETE FROM marketing_funnels WHERE id = ?', [$funnelId]);
        $message = 'Funnel deleted successfully.';
    }
}

// Fetch all funnels
$funnels = db_query('SELECT id, name, active FROM marketing_funnels ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/../components/header.php';
?>

<h1>UTM & Funnel Tracking</h1>

<?php if ($message): ?>
    <p><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<h2>Create New Funnel</h2>
<form method="post">
    <input type="hidden" name="action" value="create">
    <input type="text" name="name" placeholder="Funnel Name" required>
    <label>
        <input type="checkbox" name="active" checked> Active
    </label>
    <button type="submit">Create</button>
</form>

<h2>Existing Funnels</h2>
<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Active</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($funnels as $funnel): ?>
        <tr>
            <form method="post">
                <td>
                    <input type="text" name="name" value="<?= htmlspecialchars($funnel['name']) ?>" required>
                    <input type="hidden" name="funnel_id" value="<?= (int)$funnel['id'] ?>">
                </td>
                <td>
                    <input type="checkbox" name="active" <?= $funnel['active'] ? 'checked' : '' ?>>
                </td>
                <td>
                    <button type="submit" name="action" value="update">Update</button>
                    <button type="submit" name="action" value="delete" onclick="return confirm('Are you sure?')">Delete</button>
                </td>
            </form>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php
require __DIR__ . '/../components/footer.php';
?>
