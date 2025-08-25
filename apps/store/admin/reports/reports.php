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

$pageTitle = 'Reports';

// Example report: Sales by product
$salesReport = db_query('
    SELECT p.title, SUM(oi.quantity) AS total_quantity, SUM(oi.price * oi.quantity) AS total_sales
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    GROUP BY p.title
    ORDER BY total_sales DESC
')->fetchAll();

require __DIR__ . '/../components/header.php';
?>

<h1>Reports</h1>

<h2>Sales by Product</h2>
<table>
    <thead>
        <tr>
            <th>Product</th>
            <th>Total Quantity Sold</th>
            <th>Total Sales</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($salesReport as $row): ?>
        <tr>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><?= (int)$row['total_quantity'] ?></td>
            <td>$<?= number_format($row['total_sales'], 2) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php
require __DIR__ . '/../components/footer.php';
?>
