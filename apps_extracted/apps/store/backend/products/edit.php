<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo 'Unauthorized';
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo 'Invalid product ID';
    exit;
}

$id = (int) $_GET['id'];

try {
    $stmt = db()->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if (!$product) {
        http_response_code(404);
        echo 'Product not found';
        exit;
    }

    $variants = db_query("SELECT * FROM product_variants WHERE product_id = ?", [$id])->fetchAll();

} catch (Throwable $e) {
    http_response_code(500);
    echo 'Error: ' . $e->getMessage();
    exit;
}
?>

<form id="edit-product-form" data-id="<?= $product['id'] ?>" enctype="multipart/form-data">
  <div class="mb-3">
    <label class="form-label">Title</label>
    <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($product['title']) ?>" required>
  </div>

  <div class="mb-3">
    <label class="form-label">Type</label>
    <input type="text" class="form-control" name="type" value="<?= htmlspecialchars($product['type']) ?>">
  </div>

  <div class="mb-3">
    <label class="form-label">Tags</label>
    <input type="text" class="form-control" name="tags" value="<?= htmlspecialchars($product['tags']) ?>">
  </div>

  <div class="mb-3">
    <label class="form-label">Description</label>
    <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($product['description']) ?></textarea>
  </div>

  <div class="mb-3">
    <label class="form-label">Vendor</label>
    <input type="text" class="form-control" name="vendor" value="<?= htmlspecialchars($product['vendor']) ?>">
  </div>

  <hr>
  <h5>Variants</h5>
  <div id="variant-list">
    <?php foreach ($variants as $index => $v): ?>
      <div class="variant-block border rounded p-3 mb-2 position-relative" data-index="<?= $index ?>">
        <input type="hidden" name="variants[<?= $index ?>][id]" value="<?= $v['id'] ?>">
        <div class="row g-2">
          <div class="col-md-3">
            <input type="text" class="form-control" name="variants[<?= $index ?>][sku]" value="<?= htmlspecialchars($v['sku']) ?>" placeholder="SKU">
          </div>
          <div class="col-md-2">
            <input type="number" step="0.01" class="form-control" name="variants[<?= $index ?>][price]" value="<?= htmlspecialchars($v['price']) ?>" placeholder="Price">
          </div>
          <div class="col-md-2">
            <input type="text" class="form-control" name="variants[<?= $index ?>][option_label]" value="<?= htmlspecialchars($v['option_label']) ?>" placeholder="Option">
          </div>
          <div class="col-md-2">
            <input type="number" class="form-control" name="variants[<?= $index ?>][inventory]" value="<?= (int)($v['inventory'] ?? 0) ?>" placeholder="Stock">
          </div>
          <div class="col-md-2">
            <input type="url" class="form-control" name="variants[<?= $index ?>][image_url]" value="<?= htmlspecialchars($v['image_url']) ?>" placeholder="Image URL">
          </div>
          <div class="col-md-1 d-flex align-items-center">
            <button type="button" class="btn btn-sm btn-outline-danger remove-variant">&times;</button>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <button type="button" class="btn btn-sm btn-secondary mb-3" id="add-variant">➕ Add Variant</button>

  <div class="mb-3">
    <label class="form-label">Shipping Charged?</label>
    <select name="shipping_required" class="form-select">
      <option value="1" <?= $product['shipping_required'] ? 'selected' : '' ?>>Yes</option>
      <option value="0" <?= !$product['shipping_required'] ? 'selected' : '' ?>>No</option>
    </select>
  </div>

  <div class="mb-3">
    <label class="form-label">Taxable?</label>
    <select name="taxable" class="form-select">
      <option value="1" <?= $product['taxable'] ? 'selected' : '' ?>>Yes</option>
      <option value="0" <?= !$product['taxable'] ? 'selected' : '' ?>>No</option>
    </select>
  </div>

  <button type="submit" class="btn btn-primary">💾 Save Changes</button>
</form>

<script>
document.getElementById('edit-product-form').addEventListener('submit', async function(e) {
  e.preventDefault();
  const id = this.getAttribute('data-id');
  const formData = new FormData(this);
  formData.append('id', id);

  try {
    const res = await fetch('/backend/products/update.php', {
      method: 'POST',
      body: formData
    });
    const json = await res.json();
    if (json.success) {
      alert('✅ Product updated');
      location.reload();
    } else {
      alert('❌ Update failed: ' + (json.message || 'Unknown error'));
    }
  } catch (err) {
    console.error('❌ Edit Submit Error:', err);
    alert('❌ Server error while updating');
  }
});

document.getElementById('add-variant').addEventListener('click', function () {
  const container = document.getElementById('variant-list');
  const index = container.querySelectorAll('.variant-block').length;

  const div = document.createElement('div');
  div.className = 'variant-block border rounded p-3 mb-2';
  div.innerHTML = `
    <div class="row g-2">
      <div class="col-md-3"><input type="text" class="form-control" name="variants[${index}][sku]" placeholder="SKU"></div>
      <div class="col-md-2"><input type="number" step="0.01" class="form-control" name="variants[${index}][price]" placeholder="Price"></div>
      <div class="col-md-2"><input type="text" class="form-control" name="variants[${index}][option_label]" placeholder="Option"></div>
      <div class="col-md-2"><input type="number" class="form-control" name="variants[${index}][inventory]" placeholder="Stock"></div>
      <div class="col-md-2"><input type="url" class="form-control" name="variants[${index}][image_url]" placeholder="Image URL"></div>
      <div class="col-md-1 d-flex align-items-center">
        <button type="button" class="btn btn-sm btn-outline-danger remove-variant">&times;</button>
      </div>
    </div>
  `;
  container.appendChild(div);
});

document.addEventListener('click', function (e) {
  if (e.target.classList.contains('remove-variant')) {
    e.target.closest('.variant-block').remove();
  }
});
</script>
