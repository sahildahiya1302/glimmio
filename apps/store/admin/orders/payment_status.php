<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /backend/auth/login.php');
    exit;
}

$pageTitle = 'Payment Status';
require __DIR__ . '/../components/header.php';

// Fetch orders with payment status
$orders = [];
try {
    $stmt = $pdo->query('SELECT id, customer_name, total_amount, payment_status, created_at FROM orders ORDER BY created_at DESC');
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching orders: " . $e->getMessage();
}

// Handle payment status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['payment_status'])) {
    $orderId = (int)$_POST['order_id'];
    $paymentStatus = $_POST['payment_status'];

    $updateStmt = $pdo->prepare('UPDATE orders SET payment_status = :payment_status WHERE id = :id');
    $updateStmt->execute(['payment_status' => $paymentStatus, 'id' => $orderId]);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
?>

<h1>Payment Status</h1>
<p>View and update payment status for orders.</p>

<?php if (isset($error)): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<table class="table-auto w-full border-collapse border border-gray-300">
    <thead>
        <tr class="bg-gray-200">
            <th class="border border-gray-300 px-4 py-2">Order ID</th>
            <th class="border border-gray-300 px-4 py-2">Customer Name</th>
            <th class="border border-gray-300 px-4 py-2">Total Amount</th>
            <th class="border border-gray-300 px-4 py-2">Payment Status</th>
            <th class="border border-gray-300 px-4 py-2">Created At</th>
            <th class="border border-gray-300 px-4 py-2">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($orders) === 0): ?>
            <tr>
                <td colspan="6" class="border border-gray-300 px-4 py-2 text-center">No orders found.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($order['id']) ?></td>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($order['customer_name']) ?></td>
                    <td class="border border-gray-300 px-4 py-2">$<?= htmlspecialchars(number_format($order['total_amount'], 2)) ?></td>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($order['payment_status']) ?></td>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($order['created_at']) ?></td>
                    <td class="border border-gray-300 px-4 py-2">
                        <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
                            <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['id']) ?>">
                            <select name="payment_status" class="border border-gray-300 px-2 py-1">
                                <option value="Pending" <?= $order['payment_status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="Completed" <?= $order['payment_status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="Failed" <?= $order['payment_status'] === 'Failed' ? 'selected' : '' ?>>Failed</option>
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
