<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /backend/auth/login.php');
    exit;
}

$pageTitle = 'Customer LTV & Segments';
require __DIR__ . '/../components/header.php';

// Fetch customer LTV and segments
$ltvData = [];
try {
    $stmt = $pdo->query('
        SELECT customer_id, segment, lifetime_value
        FROM customer_ltv
        ORDER BY lifetime_value DESC
    ');
    $ltvData = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching LTV data: " . $e->getMessage();
}
?>

<h1>Customer LTV & Segments</h1>
<p>View customer lifetime value and segmentation data.</p>

<?php if (isset($error)): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<table class="table-auto w-full border-collapse border border-gray-300">
    <thead>
        <tr class="bg-gray-200">
            <th class="border border-gray-300 px-4 py-2">Customer ID</th>
            <th class="border border-gray-300 px-4 py-2">Segment</th>
            <th class="border border-gray-300 px-4 py-2">Lifetime Value</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($ltvData) === 0): ?>
            <tr>
                <td colspan="3" class="border border-gray-300 px-4 py-2 text-center">No LTV data found.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($ltvData as $row): ?>
                <tr>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($row['customer_id']) ?></td>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($row['segment']) ?></td>
                    <td class="border border-gray-300 px-4 py-2">$<?= number_format($row['lifetime_value'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php require __DIR__ . '/../components/footer.php'; ?>
