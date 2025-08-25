<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /backend/auth/login.php');
    exit;
}

$message = '';

// Handle create, update, delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $emailId = intval($_POST['email_id'] ?? 0);
    $subject = trim($_POST['subject'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $active = isset($_POST['active']) ? 1 : 0;

    if ($action === 'create' && $subject !== '' && $content !== '') {
        db_query('INSERT INTO marketing_emails (subject, content, active) VALUES (?, ?, ?)', [$subject, $content, $active]);
        $message = 'Email campaign created successfully.';
    } elseif ($action === 'update' && $emailId > 0 && $subject !== '' && $content !== '') {
        db_query('UPDATE marketing_emails SET subject = ?, content = ?, active = ? WHERE id = ?', [$subject, $content, $active, $emailId]);
        $message = 'Email campaign updated successfully.';
    } elseif ($action === 'delete' && $emailId > 0) {
        db_query('DELETE FROM marketing_emails WHERE id = ?', [$emailId]);
        $message = 'Email campaign deleted successfully.';
    }
}

// Fetch all email campaigns
$emails = db_query('SELECT id, subject, content, active FROM marketing_emails ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/../components/header.php';
?>

<h1>Email Campaigns</h1>

<?php if ($message): ?>
    <p><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<h2>Create New Email Campaign</h2>
<form method="post">
    <input type="hidden" name="action" value="create">
    <input type="text" name="subject" placeholder="Email Subject" required>
    <textarea name="content" placeholder="Email Content" required></textarea>
    <label>
        <input type="checkbox" name="active" checked> Active
    </label>
    <button type="submit">Create</button>
</form>

<h2>Existing Email Campaigns</h2>
<table>
    <thead>
        <tr>
            <th>Subject</th>
            <th>Content</th>
            <th>Active</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($emails as $email): ?>
        <tr>
            <form method="post">
                <td>
                    <input type="text" name="subject" value="<?= htmlspecialchars($email['subject']) ?>" required>
                    <input type="hidden" name="email_id" value="<?= (int)$email['id'] ?>">
                </td>
                <td>
                    <textarea name="content" required><?= htmlspecialchars($email['content']) ?></textarea>
                </td>
                <td>
                    <input type="checkbox" name="active" <?= $email['active'] ? 'checked' : '' ?>>
                </td>
                <td>
                    <button type="submit" name="action" value="update">Update</button>
                    <button type="submit" name="action" value="delete" onclick="return confirm('Are you sure?')">Delete</button>
                </td>
            </form>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php
require __DIR__ . '/../components/footer.php';
?>
