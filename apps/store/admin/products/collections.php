<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: /backend/auth/login.php');
  exit;
}

$page_title = "Collections";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($page_title) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="/admin/assets/css/admin.css" rel="stylesheet" onerror="console.warn('admin.css not found')">
  <script src="/admin/assets/js/collections.js" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
</head>
<body>
  <?php include '../components/header.php'; ?>

   <div class="container py-4 d-flex flex-column gap-3">
  <!-- Each child will stack vertically with spacing -->
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2>Collections</h2>
      <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modal-collection">+ Create Collection</button>
    </div>
    <div id="collectionsTable">Loading...</div>
  </div>

  <?php include '../modals/modal-collection.php'; ?>
  <?php include '../components/footer.php'; ?>
</body>
</html>
