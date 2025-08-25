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

$pageTitle = 'Discounts and Sales';

// Handle form submissions for adding/editing/deleting discounts and sales
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = $_POST['id'] ?? null;
    $code = trim($_POST['code'] ?? '');
    $discount_percent = floatval($_POST['discount_percent'] ?? 0);
    $active = isset($_POST['active']) ? 1 : 0;
    $apply_to = $_POST['apply_to'] ?? 'all'; // all, products, sets, collections
    $product_ids = $_POST['product_ids'] ?? [];
    $set_ids = $_POST['set_ids'] ?? [];
    $collection_ids = $_POST['collection_ids'] ?? [];
    $is_sale = isset($_POST['is_sale']) ? 1 : 0; // New: Check if it's a sale

    if ($action === 'add' && $code && $discount_percent > 0) {
        db_query('INSERT INTO discounts (code, discount_percent, active, apply_to, is_sale, created_at) VALUES (:code, :discount_percent, :active, :apply_to, :is_sale, NOW())', [
            ':code' => $code,
            ':discount_percent' => $discount_percent,
            ':active' => $active,
            ':apply_to' => $apply_to,
            ':is_sale' => $is_sale, // New: Save is_sale status
        ]);
        $discount_id = db_last_insert_id();

        // Apply discount/sale to selected products, sets, or collections
        if ($apply_to === 'products') {
            foreach ($product_ids as $product_id) {
                db_query('INSERT INTO product_discounts (product_id, discount_id) VALUES (:product_id, :discount_id)', [
                    ':product_id' => $product_id,
                    ':discount_id' => $discount_id,
                ]);
            }
        } elseif ($apply_to === 'sets') {
            foreach ($set_ids as $set_id) {
                // Assuming you have a table called product_set_products
                $product_ids = db_query('SELECT product_id FROM product_set_products WHERE set_id = :set_id', [':set_id' => $set_id])->fetchAll(PDO::FETCH_COLUMN);
                foreach ($product_ids as $product_id) {
                     db_query('INSERT INTO product_discounts (product_id, discount_id) VALUES (:product_id, :discount_id)', [
                        ':product_id' => $product_id,
                        ':discount_id' => $discount_id,
                    ]);
                }
            }
        } elseif ($apply_to === 'collections') {
            foreach ($collection_ids as $collection_id) {
                $product_ids = db_query('SELECT product_id FROM collection_products WHERE collection_id = :collection_id', [':collection_id' => $collection_id])->fetchAll(PDO::FETCH_COLUMN);
                foreach ($product_ids as $product_id) {
                    db_query('INSERT INTO product_discounts (product_id, discount_id) VALUES (:product_id, :discount_id)', [
                        ':product_id' => $product_id,
                        ':discount_id' => $discount_id,
                    ]);
                }
            }
        }

        $_SESSION['flash_message'] = $is_sale ? 'Sale added successfully.' : 'Discount added successfully.';
    } elseif ($action === 'edit' && $id && $code && $discount_percent > 0) {
        // Update discount/sale details
        db_query('UPDATE discounts SET code = :code, discount_percent = :discount_percent, active = :active, apply_to = :apply_to, is_sale = :is_sale WHERE id = :id', [
            ':code' => $code,
            ':discount_percent' => $discount_percent,
            ':active' => $active,
            ':apply_to' => $apply_to,
            ':is_sale' => $is_sale, // New: Update is_sale status
            ':id' => $id,
        ]);

        // Clear existing product_discounts for this discount/sale
         db_query('DELETE FROM product_discounts WHERE discount_id = :discount_id', [':discount_id' => $id]);

        if ($apply_to === 'products') {
            foreach ($product_ids as $product_id) {
                db_query('INSERT INTO product_discounts (product_id, discount_id) VALUES (:product_id, :discount_id)', [
                    ':product_id' => $product_id,
                    ':discount_id' => $id,
                ]);
            }
        } elseif ($apply_to === 'sets') {
            foreach ($set_ids as $set_id) {
                // Assuming you have a table called product_set_products
                $product_ids = db_query('SELECT product_id FROM product_set_products WHERE set_id = :set_id', [':set_id' => $set_id])->fetchAll(PDO::FETCH_COLUMN);
                foreach ($product_ids as $product_id) {
                     db_query('INSERT INTO product_discounts (product_id, discount_id) VALUES (:product_id, :discount_id)', [
                        ':product_id' => $product_id,
                        ':discount_id' => $id,
                    ]);
                }
            }
        } elseif ($apply_to === 'collections') {
            foreach ($collection_ids as $collection_id) {
                $product_ids = db_query('SELECT product_id FROM collection_products WHERE collection_id = :collection_id', [':collection_id' => $collection_id])->fetchAll(PDO::FETCH_COLUMN);
                foreach ($product_ids as $product_id) {
                    db_query('INSERT INTO product_discounts (product_id, discount_id) VALUES (:product_id, :discount_id)', [
                        ':product_id' => $product_id,
                        ':discount_id' => $id,
                    ]);
                }
            }
        }
        $_SESSION['flash_message'] = $is_sale ? 'Sale updated successfully.' : 'Discount updated successfully.';
    } elseif ($action === 'delete' && $id) {
        db_query('DELETE FROM discounts WHERE id = :id', [':id' => $id]);
        $_SESSION['flash_message'] = 'Discount/Sale deleted successfully.';
    }
    header('Location: /admin/discounts.php');
    exit;
}

// Fetch all discounts and sales
$discounts = db_query('SELECT id, code, discount_percent, active, apply_to, is_sale, created_at FROM discounts ORDER BY created_at DESC')->fetchAll();

// Fetch all products, product sets, and collections for the forms
$products = db_query('SELECT id, title, price FROM products')->fetchAll();
$product_sets = db_query('SELECT id, name FROM product_sets')->fetchAll();
$collections = db_query('SELECT id, title FROM collections')->fetchAll();

require __DIR__ . '/../components/header.php';
?>

<h1>Discounts and Sales</h1>

<?php if (!empty($_SESSION['flash_message'])): ?>
    <div class="flash-message"><?= htmlspecialchars($_SESSION['flash_message']) ?></div>
    <?php unset($_SESSION['flash_message']); ?>
<?php endif; ?>

<!-- Discount/Sale list -->
<table>
    <thead>
        <tr>
            <th>Code</th>
            <th>Discount Percent</th>
            <th>Active</th>
            <th>Apply To</th>
            <th>Type</th> <!-- New: Discount or Sale -->
            <th>Created At</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($discounts as $discount): ?>
        <tr>
            <td><?= htmlspecialchars($discount['code']) ?></td>
            <td><?= number_format((float)$discount['discount_percent'], 2) ?></td>
            <td><?= $discount['active'] ? 'Yes' : 'No' ?></td>
            <td><?= htmlspecialchars($discount['apply_to']) ?></td>
            <td><?= $discount['is_sale'] ? 'Sale' : 'Discount' ?></td> <!-- New: Display type -->
            <td><?= htmlspecialchars($discount['created_at']) ?></td>
            <td>
                <a href="/admin/discounts.php?action=edit&id=<?= $discount['id'] ?>">Edit</a> |
                <form method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this discount/sale?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $discount['id'] ?>">
                    <button type="submit">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Add/Edit form -->
<?php
$editDiscount = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $editDiscount = db_query('SELECT id, code, discount_percent, active, apply_to, is_sale FROM discounts WHERE id = :id', [':id' => $_GET['id']])->fetch();
}
?>

<h2><?= $editDiscount ? 'Edit Discount/Sale' : 'Add New Discount/Sale' ?></h2>
<form method="post">
    <input type="hidden" name="action" value="<?= $editDiscount ? 'edit' : 'add' ?>">
    <?php if ($editDiscount): ?>
        <input type="hidden" name="id" value="<?= $editDiscount['id'] ?>">
    <?php endif; ?>

    <label for="code">Code:</label><br>
    <input type="text" id="code" name="code" required value="<?= $editDiscount ? htmlspecialchars($editDiscount['code']) : '' ?>"><br><br>

    <label for="discount_percent">Discount Percent:</label><br>
    <input type="number" step="0.01" id="discount_percent" name="discount_percent" required value="<?= $editDiscount ? htmlspecialchars($editDiscount['discount_percent']) : '' ?>"><br><br>

    <label for="active">Active:</label>
    <input type="checkbox" id="active" name="active" <?= $editDiscount && $editDiscount['active'] ? 'checked' : '' ?>><br><br>

    <label for="apply_to">Apply To:</label><br>
    <select id="apply_to" name="apply_to">
        <option value="all" <?= ($editDiscount && $editDiscount['apply_to'] === 'all') ? 'selected' : '' ?>>All Products</option>
        <option value="products" <?= ($editDiscount && $editDiscount['apply_to'] === 'products') ? 'selected' : '' ?>>Specific Products</option>
        <option value="sets" <?= ($editDiscount && $editDiscount['apply_to'] === 'sets') ? 'selected' : '' ?>>Product Sets</option>
        <option value="collections" <?= ($editDiscount && $editDiscount['apply_to'] === 'collections') ? 'selected' : '' ?>>Collections</option>
    </select><br><br>

    <label for="is_sale">Is Sale:</label> <!-- New: Is Sale option -->
    <input type="checkbox" id="is_sale" name="is_sale" <?= $editDiscount && $editDiscount['is_sale'] ? 'checked' : '' ?>><br><br>

    <?php // Product selection ?>
    <div id="product_selection" style="display:<?= ($editDiscount && $editDiscount['apply_to'] === 'products') || (!$editDiscount) ? 'block' : 'none' ?>;">
        <label>Select Products:</label><br>
        <?php foreach ($products as $product): ?>
            <input type="checkbox" name="product_ids[]" value="<?= $product['id'] ?>" >
            <?= htmlspecialchars($product['title']) ?><br>
        <?php endforeach; ?>
    </div>

    <?php // Product set selection ?>
    <div id="set_selection" style="display:<?= ($editDiscount && $editDiscount['apply_to'] === 'sets') ? 'block' : 'none' ?>;">
        <label>Select Product Sets:</label><br>
        <?php foreach ($product_sets as $set): ?>
            <input type="checkbox" name="set_ids[]" value="<?= $set['id'] ?>">
            <?= htmlspecialchars($set['name']) ?><br>
        <?php endforeach; ?>
    </div>

    <?php // Collection selection ?>
    <div id="collection_selection" style="display:<?= ($editDiscount && $editDiscount['apply_to'] === 'collections') ? 'block' : 'none' ?>;">
        <label>Select Collections:</label><br>
        <?php foreach ($collections as $collection): ?>
            <input type="checkbox" name="collection_ids[]" value="<?= $collection['id'] ?>">
            <?= htmlspecialchars($collection['title']) ?><br>
        <?php endforeach; ?>
    </div>

    <button type="submit"><?= $editDiscount ? 'Update' : 'Add' ?> Discount/Sale</button>
</form>

<script>
const applyToSelect = document.getElementById('apply_to');
const productSelection = document.getElementById('product_selection');
const setSelection = document.getElementById('set_selection');
const collectionSelection = document.getElementById('collection_selection');

applyToSelect.addEventListener('change', function() {
    productSelection.style.display = (this.value === 'products') ? 'block' : 'none';
    setSelection.style.display = (this.value === 'sets') ? 'block' : 'none';
    collectionSelection.style.display = (this.value === 'collections') ? 'block' : 'none';
});
</script>

<?php
require __DIR__ . '/../components/footer.php';
?>
