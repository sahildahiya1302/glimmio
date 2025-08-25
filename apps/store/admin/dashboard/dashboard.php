<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /backend/auth/login.php');
    exit;
}

$pageTitle = 'Dashboard';

$totalCustomers = db_query('SELECT COUNT(*) FROM customers')->fetchColumn();
$totalOrders = db_query('SELECT COUNT(*) FROM orders')->fetchColumn();
$totalProducts = db_query('SELECT COUNT(*) FROM products')->fetchColumn();
$totalRevenue = db_query('SELECT SUM(total_amount) FROM orders WHERE status = "completed"')->fetchColumn();

require __DIR__ . '/../components/header.php';
?>
<h1>Dashboard</h1>
<section class="dash-section">
  <h2>Sales Overview</h2>
  <p>Total Revenue: $<?= number_format((float)$totalRevenue, 2) ?></p>
  <p>Total Orders: <?= (int)$totalOrders ?></p>
  <p>Average Order Value: <?= $totalOrders ? number_format((float)$totalRevenue/$totalOrders, 2) : 0 ?></p>
</section>
<section class="dash-section">
  <h2>Orders Overview</h2>
  <p>Pending Orders: <!-- placeholder --></p>
</section>
<section class="dash-section">
  <h2>Customers Overview</h2>
  <p>Total Customers: <?= (int)$totalCustomers ?></p>
</section>
<section class="dash-section">
  <h2>Traffic &amp; Conversion</h2>
  <p><!-- placeholder --></p>
</section>
<section class="dash-section">
  <h2>Product Performance</h2>
  <p>Total Products: <?= (int)$totalProducts ?></p>
</section>
<section class="dash-section">
  <h2>Campaign Highlights</h2>
  <p><!-- placeholder --></p>
</section>
<section class="dash-section">
  <h2>Live Notifications / Tasks</h2>
  <p><!-- placeholder --></p>
</section>
<section class="dash-section">
  <h2>Revenue Heatmap</h2>
  <p><!-- placeholder --></p>
</section>
<?php require __DIR__ . '/../components/footer.php'; ?>