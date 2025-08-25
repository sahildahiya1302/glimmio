<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /backend/auth/login.php');
    exit;
}

$page_title = "Products";
$cache_bust = filemtime(__DIR__ . '/../../admin/assets/js/products.js');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($page_title) ?></title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="/admin/assets/admin.css?v=<?= $cache_bust ?>" rel="stylesheet" onerror="console.warn('admin.css not found')">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
  <script src="/admin/assets/js/products.js?v=<?= $cache_bust ?>" defer></script>
</head>
<body>
  <?php include '../components/header.php'; ?>

<div class="container py-4 d-flex flex-column gap-3">
  <!-- Section Title -->
  <div class="mb-3 d-flex justify-content-between align-items-center">
    <h2 class="fw-bold">🛍️ Product Manager</h2>
    <div>
      <button id="csvExportBtn" class="btn btn-outline-primary me-2" disabled>Export CSV</button>
      <button class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#modal-import-product">
        📥 Import Catalog
      </button>
      <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modal-product">
        ➕ Add Product
      </button>
    </div>
  </div>

  <!-- Filters -->
  <form id="product-filter" class="row row-cols-lg-auto g-2 align-items-end mb-3">
    <div class="col-12">
      <input type="text" name="q" class="form-control" placeholder="Search title or tags">
    </div>
    <div class="col-12">
      <input type="text" name="vendor" class="form-control" placeholder="Vendor">
    </div>
    <div class="col-12">
      <input type="text" name="type" class="form-control" placeholder="Type">
    </div>
    <div class="col-12">
      <input type="number" name="min_price" class="form-control" step="0.01" placeholder="Min Price">
    </div>
    <div class="col-12">
      <input type="number" name="max_price" class="form-control" step="0.01" placeholder="Max Price">
    </div>
    <div class="col-12">
      <button type="submit" class="btn btn-outline-primary">Filter</button>
    </div>
  </form>

  <!-- Product Table -->
  <div id="productTable" class="table-responsive"></div>
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div id="productCount" class="text-muted"></div>
    <div id="perPageOptions" class="btn-group btn-group-sm" role="group">
      <button type="button" class="btn btn-link per-page-option active" data-per="10">10</button>
      <button type="button" class="btn btn-link per-page-option" data-per="25">25</button>
      <button type="button" class="btn btn-link per-page-option" data-per="50">50</button>
      <button type="button" class="btn btn-link per-page-option" data-per="100">100</button>
    </div>
  </div>
  <nav id="productPagination" class="mt-2"></nav>
</div>

  <!-- Product Modals -->
  <?php include '../modals/modal-product.php'; ?>
  <?php include '../modals/modal-product-edit.php'; ?>

  <!-- Import Modal -->
  <div class="modal fade" id="modal-import-product" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <form id="product-import-form">
          <div class="modal-header">
            <h5 class="modal-title">📥 Import Product Catalog</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="csvFile" class="form-label">Upload CSV File</label>
              <input type="file" name="csv" id="csvFile" class="form-control" accept=".csv" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Import Mode</label>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="mode" value="append" id="mode-append" checked>
                <label class="form-check-label" for="mode-append">Append – Keep existing and add new</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="mode" value="overwrite" id="mode-overwrite">
                <label class="form-check-label" for="mode-overwrite">Overwrite – Update existing SKUs if matched</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="mode" value="replace" id="mode-replace">
                <label class="form-check-label text-danger" for="mode-replace">Replace – Delete everything and upload fresh</label>
              </div>
            </div>

            <div class="d-none" id="mapping-section">
              <h6>Map Your CSV Columns</h6>
              <table class="table table-sm table-bordered mt-2" id="mappingTable"></table>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">📥 Import Now</button>
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  
  <?php include '../modals/modal-collection.php'; ?>
  <?php include '../components/footer.php'; ?>
</body>
</html>