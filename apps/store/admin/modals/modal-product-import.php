<div class="modal fade" id="modal-import-product" tabindex="-1" aria-labelledby="modalImportProductLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <form id="product-import-form" enctype="multipart/form-data">
        <div class="modal-header bg-light">
          <h5 class="modal-title" id="modalImportProductLabel">📥 Import Products via CSV</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">

          <div class="mb-3">
            <label for="csvFile" class="form-label">Upload CSV File</label>
            <input class="form-control" type="file" id="csvFile" name="csv" accept=".csv" required />
            <div class="form-text">Accepted format: .csv with UTF-8 encoding</div>
          </div>

          <div id="mapping-section" class="d-none mt-4">
            <h6 class="mb-2">🧠 Column Mapping</h6>
            <p class="small text-muted">Map your CSV columns to product fields:</p>
            <div class="table-responsive">
              <table class="table table-bordered align-middle">
                <thead class="table-light">
                  <tr>
                    <th>Required Field</th>
                    <th>Your CSV Column</th>
                  </tr>
                </thead>
                <tbody id="mappingTable">
                  <!-- Will be populated by JS -->
                </tbody>
              </table>
            </div>
          </div>

        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Import Products</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>
