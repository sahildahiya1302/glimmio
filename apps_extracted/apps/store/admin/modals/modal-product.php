<div class="modal fade" id="modal-product" tabindex="-1" aria-labelledby="modalProductLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <form id="manual-product-form">
        <div class="modal-header">
          <h5 class="modal-title fw-bold" id="modalProductLabel">🛍️ Create New Product</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">

          <div class="mb-3">
            <label for="productTitle" class="form-label">Product Title</label>
            <input type="text" class="form-control" id="productTitle" name="title" required>
          </div>

          <div class="mb-3">
            <label for="productDescription" class="form-label">Description</label>
            <textarea class="form-control" id="productDescription" name="description" rows="4"></textarea>
          </div>

          <div class="row g-3">
            <div class="col-md-4">
              <label for="productType" class="form-label">Product Type</label>
              <input type="text" class="form-control" id="productType" name="type">
            </div>
            <div class="col-md-4">
              <label for="productVendor" class="form-label">Vendor</label>
              <input type="text" class="form-control" id="productVendor" name="vendor">
            </div>
            <div class="col-md-4">
              <label for="productTags" class="form-label">Tags (comma-separated)</label>
              <input type="text" class="form-control" id="productTags" name="tags">
            </div>
          </div>

          <hr class="my-4">

          <div class="row g-3">
            <div class="col-md-4">
              <label for="productPrice" class="form-label">Price</label>
              <input type="number" class="form-control" id="productPrice" name="price" step="0.01">
            </div>
            <div class="col-md-4">
              <label for="productSKU" class="form-label">SKU</label>
              <input type="text" class="form-control" id="productSKU" name="sku">
            </div>
          <div class="col-md-4">
            <label for="productImage" class="form-label">Image URL</label>
            <input type="text" class="form-control" id="productImage" name="image">
          </div>
          <div class="col-md-4">
            <label for="productFile" class="form-label">Upload Image</label>
            <input type="file" class="form-control" id="productFile" name="product_file" accept="image/*">
          </div>
          </div>

          <hr class="my-4">

          <div class="row g-3">
            <div class="col-md-6">
              <label for="shippingCharge" class="form-label">Shipping Charge</label>
              <input type="number" class="form-control" id="shippingCharge" name="shipping_charge" step="0.01">
            </div>
            <div class="col-md-6">
              <label for="taxAmount" class="form-label">Tax (%)</label>
              <input type="number" class="form-control" id="taxAmount" name="tax" step="0.01">
            </div>
          </div>

          <div class="form-check form-switch mt-2">
            <input class="form-check-input" type="checkbox" id="requiresShipping" name="requires_shipping" checked>
            <label class="form-check-label" for="requiresShipping">Requires Shipping</label>
          </div>
          <div class="form-check form-switch mb-4">
            <input class="form-check-input" type="checkbox" id="isTaxable" name="taxable" checked>
            <label class="form-check-label" for="isTaxable">Taxable</label>
          </div>

          <hr class="my-4">

          <div class="row g-3">
            <div class="col-md-4">
              <label for="weight" class="form-label">Weight (kg)</label>
              <input type="number" class="form-control" id="weight" name="weight" step="0.01">
            </div>
            <div class="col-md-4">
              <label for="seoTitle" class="form-label">SEO Title</label>
              <input type="text" class="form-control" id="seoTitle" name="seo_title">
            </div>
            <div class="col-md-4">
              <label for="seoDesc" class="form-label">SEO Description</label>
              <input type="text" class="form-control" id="seoDesc" name="seo_description">
            </div>
          </div>

          <hr class="my-4">

          <h6 class="fw-semibold">Variants</h6>
          <div id="variantContainer" class="mt-2"></div>
          <button type="button" class="btn btn-outline-primary btn-sm mt-3" onclick="addVariant()">
            ➕ Add Variant
          </button>

        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Create Product</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function addVariant() {
  const container = document.getElementById('variantContainer');
  const index = container.querySelectorAll('.variant-row').length;

  const variantHTML = `
    <div class="border rounded p-3 mb-3 position-relative variant-row bg-light">
      <button type="button" class="btn-close position-absolute end-0 top-0" onclick="this.parentElement.remove()" title="Remove Variant"></button>
      <div class="row g-3">
        <div class="col-md-3">
          <label class="form-label">Option Name</label>
          <input type="text" name="variants[${index}][option_name]" class="form-control">
        </div>
        <div class="col-md-3">
          <label class="form-label">Option Value</label>
          <input type="text" name="variants[${index}][option_value]" class="form-control">
        </div>
        <div class="col-md-2">
          <label class="form-label">SKU</label>
          <input type="text" name="variants[${index}][sku]" class="form-control">
        </div>
        <div class="col-md-2">
          <label class="form-label">Price</label>
          <input type="number" name="variants[${index}][price]" step="0.01" class="form-control">
        </div>
        <div class="col-md-2">
          <label class="form-label">Compare</label>
          <input type="number" name="variants[${index}][compare_price]" step="0.01" class="form-control">
        </div>
        <div class="col-md-2">
          <label class="form-label">Inventory</label>
          <input type="number" name="variants[${index}][inventory]" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">Image URL</label>
          <input type="text" name="variants[${index}][image]" class="form-control">
        </div>
      </div>
    </div>`;
  container.insertAdjacentHTML('beforeend', variantHTML);
}
</script>
