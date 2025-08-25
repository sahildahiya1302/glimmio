<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /backend/auth/login.php');
    exit;
}

$pageTitle = 'Sales Performance Report';
require __DIR__ . '/../components/header.php';

// Fetch sales data
$salesData = [];
try {
    $stmt = $pdo->query('
        SELECT DATE(o.created_at) AS sale_date, SUM(oi.price * oi.quantity) AS total_sales
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        GROUP BY sale_date
        ORDER BY sale_date DESC
    ');
    $salesData = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching sales data: " . $e->getMessage();
}
?>

<h1>Sales Performance Report</h1>
<p>View daily sales performance.</p>

<?php if (isset($error)): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<table class="table-auto w-full border-collapse border border-gray-300">
    <thead>
        <tr class="bg-gray-200">
            <th class="border border-gray-300 px-4 py-2">Date</th>
            <th class="border border-gray-300 px-4 py-2">Total Sales</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($salesData) === 0): ?>
            <tr>
                <td colspan="2" class="border border-gray-300 px-4 py-2 text-center">No sales data found.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($salesData as $row): ?>
                <tr>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($row['sale_date']) ?></td>
                    <td class="border border-gray-300 px-4 py-2">$<?= number_format($row['total_sales'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php require __DIR__ . '/../components/footer.php'; ?>
