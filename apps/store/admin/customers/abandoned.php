<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';

$pdo = db();

if (!isset($_SESSION['user_id'])) {
    header('Location: /backend/auth/login.php');
    exit;
}

$pageTitle = 'Abandoned Checkouts';
require __DIR__ . '/../components/header.php';

// Fetch abandoned checkouts
$abandonedCheckouts = [];
try {
    $stmt = $pdo->query('SELECT id, customer_name, email, cart_contents, abandoned_at FROM abandoned_checkouts ORDER BY abandoned_at DESC');
    $abandonedCheckouts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching abandoned checkouts: " . $e->getMessage();
}
?>

<h1>Abandoned Checkouts</h1>
<p>Manage and analyze abandoned checkout sessions.</p>

<?php if (isset($error)): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<table class="table-auto w-full border-collapse border border-gray-300">
    <thead>
        <tr class="bg-gray-200">
            <th class="border border-gray-300 px-4 py-2">Checkout ID</th>
            <th class="border border-gray-300 px-4 py-2">Customer Name</th>
            <th class="border border-gray-300 px-4 py-2">Email</th>
            <th class="border border-gray-300 px-4 py-2">Cart Contents</th>
            <th class="border border-gray-300 px-4 py-2">Abandoned At</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($abandonedCheckouts) === 0): ?>
            <tr>
                <td colspan="5" class="border border-gray-300 px-4 py-2 text-center">No abandoned checkouts found.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($abandonedCheckouts as $checkout): ?>
                <tr>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($checkout['id']) ?></td>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($checkout['customer_name']) ?></td>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($checkout['email']) ?></td>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($checkout['cart_contents']) ?></td>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($checkout['abandoned_at']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php require __DIR__ . '/../components/footer.php'; ?>
