<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /backend/auth/login.php');
    exit;
}

$pageTitle = 'Invoices';
require __DIR__ . '/../components/header.php';

// Fetch invoices from database
$invoices = [];
try {
    $stmt = $pdo->query('SELECT id, order_id, amount, status, created_at FROM invoices ORDER BY created_at DESC');
    $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching invoices: " . $e->getMessage();
}
?>

<h1>Invoices</h1>
<p>Generate and manage order invoices.</p>

<?php if (isset($error)): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<table class="table-auto w-full border-collapse border border-gray-300">
    <thead>
        <tr class="bg-gray-200">
            <th class="border border-gray-300 px-4 py-2">Invoice ID</th>
            <th class="border border-gray-300 px-4 py-2">Order ID</th>
            <th class="border border-gray-300 px-4 py-2">Amount</th>
            <th class="border border-gray-300 px-4 py-2">Status</th>
            <th class="border border-gray-300 px-4 py-2">Created At</th>
            <th class="border border-gray-300 px-4 py-2">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($invoices) === 0): ?>
            <tr>
                <td colspan="6" class="border border-gray-300 px-4 py-2 text-center">No invoices found.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($invoices as $invoice): ?>
                <tr>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($invoice['id']) ?></td>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($invoice['order_id']) ?></td>
                    <td class="border border-gray-300 px-4 py-2">$<?= htmlspecialchars(number_format($invoice['amount'], 2)) ?></td>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($invoice['status']) ?></td>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($invoice['created_at']) ?></td>
                    <td class="border border-gray-300 px-4 py-2">
                        <a href="view_invoice.php?id=<?= urlencode($invoice['id']) ?>" class="text-blue-600 hover:underline">View</a>
                        |
                        <a href="edit_invoice.php?id=<?= urlencode($invoice['id']) ?>" class="text-green-600 hover:underline">Edit</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php require __DIR__ . '/../components/footer.php'; ?>
