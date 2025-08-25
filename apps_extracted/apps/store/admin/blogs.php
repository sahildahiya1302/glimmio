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

$pageTitle = 'Blogs';

// Handle form submissions for adding/editing/deleting blogs
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = $_POST['id'] ?? null;
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($action === 'add' && $title && $content) {
        db_query('INSERT INTO blogs (title, content, created_at) VALUES (:title, :content, NOW())', [
            ':title' => $title,
            ':content' => $content,
        ]);
        $_SESSION['flash_message'] = 'Blog added successfully.';
    } elseif ($action === 'edit' && $id && $title && $content) {
        db_query('UPDATE blogs SET title = :title, content = :content WHERE id = :id', [
            ':title' => $title,
            ':content' => $content,
            ':id' => $id,
        ]);
        $_SESSION['flash_message'] = 'Blog updated successfully.';
    } elseif ($action === 'delete' && $id) {
        db_query('DELETE FROM blogs WHERE id = :id', [':id' => $id]);
        $_SESSION['flash_message'] = 'Blog deleted successfully.';
    }
    header('Location: /admin/blogs.php');
    exit;
}

// Fetch all blogs
$blogs = db_query('SELECT id, title, content, created_at FROM blogs ORDER BY created_at DESC')->fetchAll();

require __DIR__ . '/../components/header.php';
?>

<h1>Blogs</h1>

<?php if (!empty($_SESSION['flash_message'])): ?>
    <div class="flash-message"><?= htmlspecialchars($_SESSION['flash_message']) ?></div>
    <?php unset($_SESSION['flash_message']); ?>
<?php endif; ?>

<!-- Blog list -->
<table>
    <thead>
        <tr>
            <th>Title</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($blogs as $blog): ?>
        <tr>
            <td><?= htmlspecialchars($blog['title']) ?></td>
            <td><?= htmlspecialchars($blog['created_at']) ?></td>
            <td>
                <a href="/admin/blogs.php?action=edit&id=<?= $blog['id'] ?>">Edit</a> |
                <form method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this blog?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $blog['id'] ?>">
                    <button type="submit">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Add/Edit form -->
<?php
$editBlog = null;
if ($_GET['action'] === 'edit' && isset($_GET['id'])) {
    $editBlog = db_query('SELECT id, title, content FROM blogs WHERE id = :id', [':id' => $_GET['id']])->fetch();
}
?>

<h2><?= $editBlog ? 'Edit Blog' : 'Add New Blog' ?></h2>
<form method="post">
    <input type="hidden" name="action" value="<?= $editBlog ? 'edit' : 'add' ?>">
    <?php if ($editBlog): ?>
        <input type="hidden" name="id" value="<?= $editBlog['id'] ?>">
    <?php endif; ?>
    <label for="title">Title:</label><br>
    <input type="text" id="title" name="title" required value="<?= $editBlog ? htmlspecialchars($editBlog['title']) : '' ?>"><br><br>
    <label for="content">Content:</label><br>
    <textarea id="content" name="content" rows="10" required><?= $editBlog ? htmlspecialchars($editBlog['content']) : '' ?></textarea><br><br>
    <button type="submit"><?= $editBlog ? 'Update' : 'Add' ?> Blog</button>
</form>

<?php
require __DIR__ . '/../components/footer.php';
?>
