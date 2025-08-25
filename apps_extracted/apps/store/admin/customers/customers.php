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

$pageTitle = 'Customers';

// Handle form submissions for adding/editing/deleting customers
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = $_POST['id'] ?? null;
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($action === 'add' && $name && $email) {
        db_query('INSERT INTO customers (name, email, created_at) VALUES (:name, :email, NOW())', [
            ':name' => $name,
            ':email' => $email,
        ]);
        $_SESSION['flash_message'] = 'Customer added successfully.';
    } elseif ($action === 'edit' && $id && $name && $email) {
        db_query('UPDATE customers SET name = :name, email = :email WHERE id = :id', [
            ':name' => $name,
            ':email' => $email,
            ':id' => $id,
        ]);
        $_SESSION['flash_message'] = 'Customer updated successfully.';
    } elseif ($action === 'delete' && $id) {
        db_query('DELETE FROM customers WHERE id = :id', [':id' => $id]);
        $_SESSION['flash_message'] = 'Customer deleted successfully.';
    }
    header('Location: /admin/customers.php');
    exit;
}

// Fetch all customers
$customers = db_query('SELECT id, first_name, last_name, email, created_at FROM customers ORDER BY created_at DESC')->fetchAll();

require __DIR__ . '/../components/header.php';
?>

<h1>Customers</h1>

<?php if (!empty($_SESSION['flash_message'])): ?>
    <div class="flash-message"><?= htmlspecialchars($_SESSION['flash_message']) ?></div>
    <?php unset($_SESSION['flash_message']); ?>
<?php endif; ?>

<!-- Customer list -->
<table>
    <thead>
        <tr>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Email</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($customers as $customer): ?>
        <tr>
            <td><?= htmlspecialchars($customer['first_name']) ?></td>
            <td><?= htmlspecialchars($customer['last_name']) ?></td>
            <td><?= htmlspecialchars($customer['email']) ?></td>
            <td><?= htmlspecialchars($customer['created_at']) ?></td>
            <td>
                <a href="/admin/customers.php?action=edit&id=<?= $customer['id'] ?>">Edit</a> |
                <form method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this customer?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $customer['id'] ?>">
                    <button type="submit">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Add/Edit form -->
<?php
$editCustomer = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $editCustomer = db_query('SELECT id, first_name, last_name, email FROM customers WHERE id = :id', [':id' => $_GET['id']])->fetch();
}
?>

<h2><?= $editCustomer ? 'Edit Customer' : 'Add New Customer' ?></h2>
<form method="post">
    <input type="hidden" name="action" value="<?= $editCustomer ? 'edit' : 'add' ?>">
    <?php if ($editCustomer): ?>
        <input type="hidden" name="id" value="<?= $editCustomer['id'] ?>">
    <?php endif; ?>
    <label for="first_name">First Name:</label><br>
    <input type="text" id="first_name" name="first_name" required value="<?= $editCustomer ? htmlspecialchars($editCustomer['first_name']) : '' ?>"><br><br>
    <label for="last_name">Last Name:</label><br>
    <input type="text" id="last_name" name="last_name" required value="<?= $editCustomer ? htmlspecialchars($editCustomer['last_name']) : '' ?>"><br><br>
    <label for="email">Email:</label><br>
    <input type="email" id="email" name="email" required value="<?= $editCustomer ? htmlspecialchars($editCustomer['email']) : '' ?>"><br><br>
    <button type="submit"><?= $editCustomer ? 'Update' : 'Add' ?> Customer</button>
</form>

<?php
require __DIR__ . '/../components/footer.php';
?>
