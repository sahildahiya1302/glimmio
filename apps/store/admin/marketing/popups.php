<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /backend/auth/login.php');
    exit;
}

$pageTitle = 'Popups & Triggers';
require __DIR__ . '/../components/header.php';

// Fetch popups and triggers
$popups = [];
try {
    $stmt = $pdo->query('SELECT id, name, trigger_event, status, created_at FROM popups ORDER BY created_at DESC');
    $popups = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching popups: " . $e->getMessage();
}

// Handle popup status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['popup_id'], $_POST['status'])) {
    $popupId = (int)$_POST['popup_id'];
    $status = $_POST['status'];

    $updateStmt = $pdo->prepare('UPDATE popups SET status = :status WHERE id = :id');
    $updateStmt->execute(['status' => $status, 'id' => $popupId]);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
?>

<h1>Popups & Triggers</h1>
<p>Manage on-site popups and trigger rules.</p>

<?php if (isset($error)): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<table class="table-auto w-full border-collapse border border-gray-300">
    <thead>
        <tr class="bg-gray-200">
            <th class="border border-gray-300 px-4 py-2">Popup ID</th>
            <th class="border border-gray-300 px-4 py-2">Name</th>
            <th class="border border-gray-300 px-4 py-2">Trigger Event</th>
            <th class="border border-gray-300 px-4 py-2">Status</th>
            <th class="border border-gray-300 px-4 py-2">Created At</th>
            <th class="border border-gray-300 px-4 py-2">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($popups) === 0): ?>
            <tr>
                <td colspan="6" class="border border-gray-300 px-4 py-2 text-center">No popups found.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($popups as $popup): ?>
                <tr>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($popup['id']) ?></td>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($popup['name']) ?></td>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($popup['trigger_event']) ?></td>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($popup['status']) ?></td>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($popup['created_at']) ?></td>
                    <td class="border border-gray-300 px-4 py-2">
                        <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
                            <input type="hidden" name="popup_id" value="<?= htmlspecialchars($popup['id']) ?>">
                            <select name="status" class="border border-gray-300 px-2 py-1">
                                <option value="Active" <?= $popup['status'] === 'Active' ? 'selected' : '' ?>>Active</option>
                                <option value="Inactive" <?= $popup['status'] === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                            <button type="submit" class="ml-2 bg-blue-600 text-white px-3 py-1 rounded">Update</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php require __DIR__ . '/../components/footer.php'; ?>
