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

// Handle stock update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = intval($_POST['product_id'] ?? 0);
    $newQuantity = intval($_POST['quantity'] ?? 0);

    if ($productId > 0) {
        db_query('UPDATE products SET quantity = ? WHERE id = ?', [$newQuantity, $productId]);
    }
}

// Fetch products with inventory info
$products = db_query('SELECT id, sku, name, quantity FROM products ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/../components/header.php';
?>

<h1>Product Inventory</h1>

<table>
    <thead>
        <tr>
            <th>SKU</th>
            <th>Name</th>
            <th>Quantity</th>
            <th>Update Stock</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($products as $product): ?>
        <tr>
            <td><?= htmlspecialchars($product['sku']) ?></td>
            <td><?= htmlspecialchars($product['name']) ?></td>
            <td><?= (int)$product['quantity'] ?></td>
            <td>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                    <input type="number" name="quantity" value="<?= (int)$product['quantity'] ?>" min="0" required>
                    <button type="submit">Update</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php
require __DIR__ . '/../components/footer.php';
?>
