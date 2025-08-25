<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /backend/auth/login.php');
    exit;
}

$pageTitle = 'Plugin Management';

// Fetch installed plugins from database or config
$plugins = db_query('SELECT * FROM plugins')->fetchAll();

require __DIR__ . '/../components/header.php';
?>

<h1>Plugin Management</h1>

<table border="1" cellpadding="5" cellspacing="0" style="width: 100%;">
    <thead>
        <tr>
            <th>Name</th>
            <th>Description</th>
            <th>Version</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($plugins as $plugin): ?>
        <tr>
            <td><?= htmlspecialchars($plugin['name']) ?></td>
            <td><?= htmlspecialchars($plugin['description']) ?></td>
            <td><?= htmlspecialchars($plugin['version']) ?></td>
            <td><?= $plugin['enabled'] ? 'Enabled' : 'Disabled' ?></td>
            <td>
                <?php if ($plugin['enabled']): ?>
                    <a href="plugins_toggle.php?id=<?= $plugin['id'] ?>&action=disable">Disable</a>
                <?php else: ?>
                    <a href="plugins_toggle.php?id=<?= $plugin['id'] ?>&action=enable">Enable</a>
                <?php endif; ?>
                | <a href="plugins_delete.php?id=<?= $plugin['id'] ?>" onclick="return confirm('Are you sure you want to delete this plugin?');">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<h2>Add New Plugin</h2>
<form method="post" action="plugins_add.php" enctype="multipart/form-data">
    <label for="plugin_file">Plugin ZIP File:</label><br>
    <input type="file" id="plugin_file" name="plugin_file" accept=".zip" required><br><br>
    <button type="submit">Upload and Install</button>
</form>

<?php
require __DIR__ . '/../components/footer.php';
?>
