<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /backend/auth/login.php');
    exit;
}

$pageTitle = 'Traffic Analytics Report';
require __DIR__ . '/../components/header.php';

// Fetch traffic data
$trafficData = [];
try {
    $stmt = $pdo->query('
        SELECT DATE(visit_date) AS visit_day, COUNT(*) AS visits
        FROM traffic
        GROUP BY visit_day
        ORDER BY visit_day DESC
    ');
    $trafficData = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching traffic data: " . $e->getMessage();
}
?>

<h1>Traffic Analytics Report</h1>
<p>View website traffic analytics.</p>

<?php if (isset($error)): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<table class="table-auto w-full border-collapse border border-gray-300">
    <thead>
        <tr class="bg-gray-200">
            <th class="border border-gray-300 px-4 py-2">Date</th>
            <th class="border border-gray-300 px-4 py-2">Visits</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($trafficData) === 0): ?>
            <tr>
                <td colspan="2" class="border border-gray-300 px-4 py-2 text-center">No traffic data found.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($trafficData as $row): ?>
                <tr>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($row['visit_day']) ?></td>
                    <td class="border border-gray-300 px-4 py-2"><?= (int)$row['visits'] ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php require __DIR__ . '/../components/footer.php'; ?>
