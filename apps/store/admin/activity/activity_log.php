<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';


if (!isset($_SESSION['user_id'])) {
    header('Location: /backend/auth/login.php');
    exit;
}





$logs = db_query('SELECT activity_logs.*, users.email FROM activity_logs LEFT JOIN users ON activity_logs.user_id = users.id ORDER BY activity_logs.created_at DESC LIMIT 100')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Activity Log</title>
    <link rel="stylesheet" href="/admin/assets/admin.css">
</head>
<body>
<?php include __DIR__ . '/../components/header.php'; ?>
<h1>Activity Log</h1>
<table class="admin-table">
    <thead>
        <tr>
            <th>Time</th>
            <th>User</th>
            <th>Action</th>
            <th>Metadata</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($logs as $log): ?>
        <tr>
            <td><?= htmlspecialchars($log['created_at']) ?></td>
            <td><?= htmlspecialchars($log['email'] ?? 'System') ?></td>
            <td><?= htmlspecialchars($log['action']) ?></td>
            <td><pre><?= htmlspecialchars($log['metadata']) ?></pre></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php include __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
