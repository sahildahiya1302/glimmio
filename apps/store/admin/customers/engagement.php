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

$pageTitle = 'Email/SMS Engagement';
require __DIR__ . '/../components/header.php';

// Fetch engagement data
$engagementData = [];
try {
    $stmt = $pdo->query('SELECT id, customer_name, email, last_engagement, engagement_score FROM customer_engagement ORDER BY last_engagement DESC');
    $engagementData = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching engagement data: " . $e->getMessage();
}
?>

<h1>Email/SMS Engagement</h1>
<p>View customer engagement metrics for email and SMS campaigns.</p>

<?php if (isset($error)): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<table class="table-auto w-full border-collapse border border-gray-300">
    <thead>
        <tr class="bg-gray-200">
            <th class="border border-gray-300 px-4 py-2">Customer ID</th>
            <th class="border border-gray-300 px-4 py-2">Customer Name</th>
            <th class="border border-gray-300 px-4 py-2">Email</th>
            <th class="border border-gray-300 px-4 py-2">Last Engagement</th>
            <th class="border border-gray-300 px-4 py-2">Engagement Score</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($engagementData) === 0): ?>
            <tr>
                <td colspan="5" class="border border-gray-300 px-4 py-2 text-center">No engagement data found.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($engagementData as $row): ?>
                <tr>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($row['id']) ?></td>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($row['customer_name']) ?></td>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($row['email']) ?></td>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($row['last_engagement']) ?></td>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($row['engagement_score']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php require __DIR__ . '/../components/footer.php'; ?>
