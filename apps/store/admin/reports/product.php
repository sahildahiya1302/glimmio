<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /backend/auth/login.php');
    exit;
}

$pageTitle = 'Product Performance Report';
require __DIR__ . '/../components/header.php';

// Fetch product performance data
$productData = [];
try {
    $stmt = $pdo->query('
        SELECT p.id, p.title, SUM(oi.quantity) AS total_sold, SUM(oi.price * oi.quantity) AS total_revenue
        FROM products p
        LEFT JOIN order_items oi ON p.id = oi.product_id
        GROUP BY p.id, p.title
        ORDER BY total_revenue DESC
    ');
    $productData = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching product data: " . $e->getMessage();
}
?>

<h1>Product Performance Report</h1>
<p>Analyze sales and revenue by product.</p>

<?php if (isset($error)): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<table class="table-auto w-full border-collapse border border-gray-300">
    <thead>
        <tr class="bg-gray-200">
            <th class="border border-gray-300 px-4 py-2">Product ID</th>
            <th class="border border-gray-300 px-4 py-2">Title</th>
            <th class="border border-gray-300 px-4 py-2">Total Sold</th>
            <th class="border border-gray-300 px-4 py-2">Total Revenue</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($productData) === 0): ?>
            <tr>
                <td colspan="4" class="border border-gray-300 px-4 py-2 text-center">No product data found.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($productData as $row): ?>
                <tr>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($row['id']) ?></td>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($row['title']) ?></td>
                    <td class="border border-gray-300 px-4 py-2"><?= (int)$row['total_sold'] ?></td>
                    <td class="border border-gray-300 px-4 py-2">$<?= number_format($row['total_revenue'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php require __DIR__ . '/../components/footer.php'; ?>
