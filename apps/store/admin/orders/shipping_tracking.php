<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /backend/auth/login.php');
    exit;
}

$pageTitle = 'Shipping & Tracking';
require __DIR__ . '/../components/header.php';

// Fetch shipments
$shipments = [];
try {
    $stmt = $pdo->query('SELECT id, order_id, carrier, tracking_number, status, shipped_at FROM shipments ORDER BY shipped_at DESC');
    $shipments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching shipments: " . $e->getMessage();
}

// Handle shipment status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['shipment_id'], $_POST['status'])) {
    $shipmentId = (int)$_POST['shipment_id'];
    $status = $_POST['status'];

    $updateStmt = $pdo->prepare('UPDATE shipments SET status = :status WHERE id = :id');
    $updateStmt->execute(['status' => $status, 'id' => $shipmentId]);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
?>

<h1>Shipping & Tracking</h1>
<p>Track shipments using connected delivery partners.</p>

<?php if (isset($error)): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<table class="table-auto w-full border-collapse border border-gray-300">
    <thead>
        <tr class="bg-gray-200">
            <th class="border border-gray-300 px-4 py-2">Shipment ID</th>
            <th class="border border-gray-300 px-4 py-2">Order ID</th>
            <th class="border border-gray-300 px-4 py-2">Carrier</th>
            <th class="border border-gray-300 px-4 py-2">Tracking Number</th>
            <th class="border border-gray-300 px-4 py-2">Status</th>
            <th class="border border-gray-300 px-4 py-2">Shipped At</th>
            <th class="border border-gray-300 px-4 py-2">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($shipments) === 0): ?>
            <tr>
                <td colspan="7" class="border border-gray-300 px-4 py-2 text-center">No shipments found.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($shipments as $shipment): ?>
                <tr>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($shipment['id']) ?></td>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($shipment['order_id']) ?></td>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($shipment['carrier']) ?></td>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($shipment['tracking_number']) ?></td>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($shipment['status']) ?></td>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($shipment['shipped_at']) ?></td>
                    <td class="border border-gray-300 px-4 py-2">
                        <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
                            <input type="hidden" name="shipment_id" value="<?= htmlspecialchars($shipment['id']) ?>">
                            <select name="status" class="border border-gray-300 px-2 py-1">
                                <option value="Pending" <?= $shipment['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="In Transit" <?= $shipment['status'] === 'In Transit' ? 'selected' : '' ?>>In Transit</option>
                                <option value="Delivered" <?= $shipment['status'] === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                                <option value="Cancelled" <?= $shipment['status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
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
