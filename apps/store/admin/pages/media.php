<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /backend/auth/login.php');
    exit;
}

$pageTitle = 'Media Manager';
require __DIR__ . '/../components/header.php';

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['media_file'])) {
    $uploadDir = __DIR__ . '/../../uploads/media/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $fileName = basename($_FILES['media_file']['name']);
    $targetFile = $uploadDir . $fileName;
    if (move_uploaded_file($_FILES['media_file']['tmp_name'], $targetFile)) {
        $_SESSION['flash_message'] = 'File uploaded successfully.';
    } else {
        $_SESSION['flash_message'] = 'Error uploading file.';
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch media files
$mediaFiles = [];
$mediaDir = __DIR__ . '/../../uploads/media/';
if (is_dir($mediaDir)) {
    $mediaFiles = array_diff(scandir($mediaDir), ['.', '..']);
}
?>

<h1>Media Manager</h1>

<?php if (!empty($_SESSION['flash_message'])): ?>
    <div class="flash-message"><?= htmlspecialchars($_SESSION['flash_message']) ?></div>
    <?php unset($_SESSION['flash_message']); ?>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
    <label for="media_file">Upload Media File:</label><br>
    <input type="file" name="media_file" id="media_file" required><br><br>
    <button type="submit">Upload</button>
</form>

<h2>Uploaded Media Files</h2>
<?php if (empty($mediaFiles)): ?>
    <p>No media files uploaded yet.</p>
<?php else: ?>
    <ul>
        <?php foreach ($mediaFiles as $file): ?>
            <li><a href="/uploads/media/<?= urlencode($file) ?>" target="_blank"><?= htmlspecialchars($file) ?></a></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php require __DIR__ . '/../components/footer.php'; ?>
