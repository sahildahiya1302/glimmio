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

$pageTitle = 'Pages';

// Handle form submissions for adding/editing/deleting pages
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = $_POST['id'] ?? null;
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $layout  = trim($_POST['layout'] ?? '{}');
    $headCode = trim($_POST['head_code'] ?? '');
    $bodyStartCode = trim($_POST['body_start_code'] ?? '');
    $bodyEndCode = trim($_POST['body_end_code'] ?? '');
    $cssCode = trim($_POST['css_code'] ?? '');
    $jsCode = trim($_POST['js_code'] ?? '');
    $userId = $_SESSION['user_id'];

    if ($action === 'add' && $title && $slug) {
        db_query('INSERT INTO pages (title, slug, content, layout, layout_draft, layout_published, head_code, body_start_code, body_end_code, css_code, js_code, is_published, version, created_at) VALUES (:title, :slug, :content, :layout, :layout, :layout, :head, :bstart, :bend, :css, :js, 1, 1, NOW())', [
            ':title' => $title,
            ':slug' => $slug,
            ':content' => $content,
            ':layout' => $layout,
            ':head' => $headCode,
            ':bstart' => $bodyStartCode,
            ':bend' => $bodyEndCode,
            ':css' => $cssCode,
            ':js' => $jsCode,
        ]);
        $pageId = (int)db()->lastInsertId();
        db_query('INSERT INTO page_versions (page_id, version, saved_by, layout_snapshot) VALUES (:pid, 1, :uid, :snap)', [
            ':pid' => $pageId,
            ':uid' => $userId,
            ':snap' => $layout,
        ]);
        $_SESSION['flash_message'] = 'Page added successfully.';
    } elseif ($action === 'edit' && $id && $title && $slug) {
        $page = db_query('SELECT version FROM pages WHERE id = :id', [':id' => $id])->fetch();
        $newVersion = ($page['version'] ?? 0) + 1;
        db_query('UPDATE pages SET title = :title, slug = :slug, content = :content, layout_draft = :layout, layout_published = :layout, head_code = :head, body_start_code = :bstart, body_end_code = :bend, css_code = :css, js_code = :js, is_published = 1, version = :ver WHERE id = :id', [
            ':title' => $title,
            ':slug' => $slug,
            ':content' => $content,
            ':layout' => $layout,
            ':head' => $headCode,
            ':bstart' => $bodyStartCode,
            ':bend' => $bodyEndCode,
            ':css' => $cssCode,
            ':js' => $jsCode,
            ':ver' => $newVersion,
            ':id' => $id,
        ]);
        db_query('INSERT INTO page_versions (page_id, version, saved_by, layout_snapshot) VALUES (:pid, :ver, :uid, :snap)', [
            ':pid' => $id,
            ':ver' => $newVersion,
            ':uid' => $userId,
            ':snap' => $layout,
        ]);
        $_SESSION['flash_message'] = 'Page updated successfully.';
    } elseif ($action === 'delete' && $id) {
        db_query('DELETE FROM pages WHERE id = :id', [':id' => $id]);
        $_SESSION['flash_message'] = 'Page deleted successfully.';
    }
    header('Location: /admin/pages/pages.php');
    exit;
}

// Fetch all pages
$pages = db_query('SELECT id, title, slug, created_at, version, is_published FROM pages ORDER BY created_at DESC')->fetchAll();

require __DIR__ . '/../components/header.php';
?>

<h1>Pages</h1>

<?php if (!empty($_SESSION['flash_message'])): ?>
    <div class="flash-message"><?= htmlspecialchars($_SESSION['flash_message']) ?></div>
    <?php unset($_SESSION['flash_message']); ?>
<?php endif; ?>

<!-- Pages list -->
<table>
    <thead>
        <tr>
            <th>Title</th>
            <th>Slug</th>
            <th>Version</th>
            <th>Status</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($pages as $page): ?>
        <tr>
            <td><?= htmlspecialchars($page['title']) ?></td>
            <td><?= htmlspecialchars($page['slug']) ?></td>
            <td><?= (int)$page['version'] ?></td>
            <td><?= $page['is_published'] ? 'Published' : 'Draft' ?></td>
            <td><?= htmlspecialchars($page['created_at']) ?></td>
            <td>
                <a href="/admin/pages/pages.php?action=edit&id=<?= $page['id'] ?>">Edit</a> |
                <form method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this page?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $page['id'] ?>">
                    <button type="submit">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Add/Edit form -->
<?php
$editPage = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $editPage = db_query('SELECT id, title, slug, content, layout_draft, layout_published, head_code, body_start_code, body_end_code, css_code, js_code, version FROM pages WHERE id = :id', [':id' => $_GET['id']])->fetch();
}
?>

<h2><?= $editPage ? 'Edit Page' : 'Add New Page' ?></h2>
<form method="post">
    <input type="hidden" name="action" value="<?= $editPage ? 'edit' : 'add' ?>">
    <?php if ($editPage): ?>
        <input type="hidden" name="id" value="<?= $editPage['id'] ?>">
    <?php endif; ?>
    <label for="title">Title:</label><br>
    <input type="text" id="title" name="title" required value="<?= $editPage ? htmlspecialchars($editPage['title']) : '' ?>"><br><br>
    <label for="slug">Slug:</label><br>
    <input type="text" id="slug" name="slug" required value="<?= $editPage ? htmlspecialchars($editPage['slug']) : '' ?>"><br><br>
    <label for="content">Content:</label><br>
    <textarea id="content" name="content" rows="6"><?= $editPage ? htmlspecialchars($editPage['content']) : '' ?></textarea><br><br>
    <label for="layout">Layout JSON:</label><br>
    <textarea id="layout" name="layout" rows="10"><?= $editPage ? htmlspecialchars($editPage['layout_draft'] ?? $editPage['layout_published']) : '{}' ?></textarea><br><br>
    <label for="head_code">Head Code:</label><br>
    <textarea id="head_code" name="head_code" rows="4"><?= $editPage ? htmlspecialchars($editPage['head_code'] ?? '') : '' ?></textarea><br><br>
    <label for="body_start_code">Body Start Code:</label><br>
    <textarea id="body_start_code" name="body_start_code" rows="4"><?= $editPage ? htmlspecialchars($editPage['body_start_code'] ?? '') : '' ?></textarea><br><br>
    <label for="body_end_code">Body End Code:</label><br>
    <textarea id="body_end_code" name="body_end_code" rows="4"><?= $editPage ? htmlspecialchars($editPage['body_end_code'] ?? '') : '' ?></textarea><br><br>
    <label for="css_code">Page CSS:</label><br>
    <textarea id="css_code" name="css_code" rows="4"><?= $editPage ? htmlspecialchars($editPage['css_code'] ?? '') : '' ?></textarea><br><br>
    <label for="js_code">Page JS:</label><br>
    <textarea id="js_code" name="js_code" rows="4"><?= $editPage ? htmlspecialchars($editPage['js_code'] ?? '') : '' ?></textarea><br><br>
    <button type="submit"><?= $editPage ? 'Update' : 'Add' ?> Page</button>
</form>

<h3>Preview</h3>
<?php if ($editPage): ?>
<iframe src="/page/<?= htmlspecialchars($editPage['slug']) ?>" style="width:100%;height:400px;border:1px solid #ccc;"></iframe>
<?php endif; ?>

<?php
require __DIR__ . '/components/footer.php';
?>
