<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /backend/auth/login.php');
    exit;
}

$pageTitle = 'Conversion Funnel Report';
require __DIR__ . '/../components/header.php';

// Fetch funnel data
$funnelData = [];
try {
    $stmt = $pdo->query('
        SELECT step, COUNT(*) AS count
        FROM conversion_funnel
        GROUP BY step
        ORDER BY step ASC
    ');
    $funnelData = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching funnel data: " . $e->getMessage();
}
?>

<h1>Conversion Funnel Report</h1>
<p>Analyze the conversion funnel steps and drop-offs.</p>

<?php if (isset($error)): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<table class="table-auto w-full border-collapse border border-gray-300">
    <thead>
        <tr class="bg-gray-200">
            <th class="border border-gray-300 px-4 py-2">Step</th>
            <th class="border border-gray-300 px-4 py-2">Count</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($funnelData) === 0): ?>
            <tr>
                <td colspan="2" class="border border-gray-300 px-4 py-2 text-center">No funnel data found.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($funnelData as $row): ?>
                <tr>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($row['step']) ?></td>
                    <td class="border border-gray-300 px-4 py-2"><?= (int)$row['count'] ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php require __DIR__ . '/../components/footer.php'; ?>
