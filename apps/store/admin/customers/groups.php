<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';

$pdo = db();

if (!isset($_SESSION['user_id'])) {
    header('Location: /backend/auth/login.php');
    exit;
}

$pageTitle = 'Customer Groups & Segments';
require __DIR__ . '/../components/header.php';

// Fetch customer groups
$groups = [];
try {
    $stmt = $pdo->query('SELECT id, name, description, created_at FROM customer_groups ORDER BY created_at DESC');
    $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching customer groups: " . $e->getMessage();
}
?>

<h1>Customer Groups & Segments</h1>
<p>Manage customer groups and segmentation.</p>

<?php if (isset($error)): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<table class="table-auto w-full border-collapse border border-gray-300">
    <thead>
        <tr class="bg-gray-200">
            <th class="border border-gray-300 px-4 py-2">Group ID</th>
            <th class="border border-gray-300 px-4 py-2">Name</th>
            <th class="border border-gray-300 px-4 py-2">Description</th>
            <th class="border border-gray-300 px-4 py-2">Created At</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($groups) === 0): ?>
            <tr>
                <td colspan="4" class="border border-gray-300 px-4 py-2 text-center">No customer groups found.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($groups as $group): ?>
                <tr>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($group['id']) ?></td>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($group['name']) ?></td>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($group['description']) ?></td>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($group['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php require __DIR__ . '/../components/footer.php'; ?>
