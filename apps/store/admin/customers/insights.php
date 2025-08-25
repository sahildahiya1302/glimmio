<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /backend/auth/login.php');
    exit;
}

$pageTitle = 'Customer Insights';
require __DIR__ . '/../components/header.php';

// Fetch customer insights data
$insights = [];
try {
    $stmt = $pdo->query('SELECT id, customer_name, total_orders, total_spent, last_order_date FROM customer_insights ORDER BY total_spent DESC');
    $insights = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching customer insights: " . $e->getMessage();
}
?>

<h1>Customer Insights</h1>
<p>Analyze customer purchase behavior and trends.</p>

<?php if (isset($error)): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<table class="table-auto w-full border-collapse border border-gray-300">
    <thead>
        <tr class="bg-gray-200">
            <th class="border border-gray-300 px-4 py-2">Customer ID</th>
            <th class="border border-gray-300 px-4 py-2">Customer Name</th>
            <th class="border border-gray-300 px-4 py-2">Total Orders</th>
            <th class="border border-gray-300 px-4 py-2">Total Spent</th>
            <th class="border border-gray-300 px-4 py-2">Last Order Date</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($insights) === 0): ?>
            <tr>
                <td colspan="5" class="border border-gray-300 px-4 py-2 text-center">No customer insights found.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($insights as $row): ?>
                <tr>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($row['id']) ?></td>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($row['customer_name']) ?></td>
                    <td class="border border-gray-300 px-4 py-2"><?= (int)$row['total_orders'] ?></td>
                    <td class="border border-gray-300 px-4 py-2">$<?= number_format($row['total_spent'], 2) ?></td>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($row['last_order_date']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php require __DIR__ . '/../components/footer.php'; ?>
