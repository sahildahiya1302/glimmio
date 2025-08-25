<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /backend/auth/login.php');
    exit;
}

$pageTitle = 'Returns & Refunds';
require __DIR__ . '/../components/header.php';

// Fetch return requests
$returns = [];
try {
    $stmt = $pdo->query('SELECT id, order_id, product_name, reason, status, requested_at FROM returns ORDER BY requested_at DESC');
    $returns = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching return requests: " . $e->getMessage();
}

// Handle return status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['return_id'], $_POST['status'])) {
    $returnId = (int)$_POST['return_id'];
    $status = $_POST['status'];

    $updateStmt = $pdo->prepare('UPDATE returns SET status = :status WHERE id = :id');
    $updateStmt->execute(['status' => $status, 'id' => $returnId]);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
?>

<h1>Returns & Refunds</h1>
<p>Manage product returns and refund requests here.</p>

<?php if (isset($error)): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<table class="table-auto w-full border-collapse border border-gray-300">
    <thead>
        <tr class="bg-gray-200">
            <th class="border border-gray-300 px-4 py-2">Return ID</th>
            <th class="border border-gray-300 px-4 py-2">Order ID</th>
            <th class="border border-gray-300 px-4 py-2">Product Name</th>
            <th class="border border-gray-300 px-4 py-2">Reason</th>
            <th class="border border-gray-300 px-4 py-2">Status</th>
            <th class="border border-gray-300 px-4 py-2">Requested At</th>
            <th class="border border-gray-300 px-4 py-2">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($returns) === 0): ?>
            <tr>
                <td colspan="7" class="border border-gray-300 px-4 py-2 text-center">No return requests found.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($returns as $return): ?>
                <tr>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($return['id']) ?></td>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($return['order_id']) ?></td>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($return['product_name']) ?></td>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($return['reason']) ?></td>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($return['status']) ?></td>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($return['requested_at']) ?></td>
                    <td class="border border-gray-300 px-4 py-2">
                        <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
                            <input type="hidden" name="return_id" value="<?= htmlspecialchars($return['id']) ?>">
                            <select name="status" class="border border-gray-300 px-2 py-1">
                                <option value="Pending" <?= $return['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="Approved" <?= $return['status'] === 'Approved' ? 'selected' : '' ?>>Approved</option>
                                <option value="Rejected" <?= $return['status'] === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
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
