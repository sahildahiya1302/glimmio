<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';



// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /backend/auth/login.php');
    exit;
}

$pageTitle = 'Orders';

// Handle form submissions for updating order status or deleting orders
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = $_POST['id'] ?? null;
    $status = $_POST['status'] ?? '';

    if ($action === 'update_status' && $id && $status) {
        db_query('UPDATE orders SET status = :status WHERE id = :id', [
            ':status' => $status,
            ':id' => $id,
        ]);
        $_SESSION['flash_message'] = 'Order status updated successfully.';
    } elseif ($action === 'delete' && $id) {
        db_query('DELETE FROM orders WHERE id = :id', [':id' => $id]);
        $_SESSION['flash_message'] = 'Order deleted successfully.';
    }
    header('Location: /admin/orders.php');
    exit;
}

// Fetch all orders with customer info
$orders = db_query('
    SELECT o.id, o.status, o.total_amount, o.created_at, c.first_name, c.last_name
    FROM orders o
    LEFT JOIN customers c ON o.customer_id = c.id
    ORDER BY o.created_at DESC
')->fetchAll();

require __DIR__ . '/../components/header.php';
?>

<h1>Orders</h1>

<?php if (!empty($_SESSION['flash_message'])): ?>
    <div class="flash-message"><?= htmlspecialchars($_SESSION['flash_message']) ?></div>
    <?php unset($_SESSION['flash_message']); ?>
<?php endif; ?>

<!-- Orders list -->
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Customer</th>
            <th>Total</th>
            <th>Status</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($orders as $order): ?>
        <tr>
            <td><?= $order['id'] ?></td>
            <td><?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></td>
            <td>$<?= number_format($order['total_amount'], 2) ?></td>
            <td><?= htmlspecialchars($order['status']) ?></td>
            <td><?= htmlspecialchars($order['created_at']) ?></td>
            <td>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="id" value="<?= $order['id'] ?>">
                    <select name="status" onchange="this.form.submit()">
                        <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                        <option value="completed" <?= $order['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </form>
                <form method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this order?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $order['id'] ?>">
                    <button type="submit">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php
require __DIR__ . '/../components/footer.php';
?>
