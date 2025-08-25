<div class="modal fade" id="modal-collection" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <form id="collection-form">
        <div class="modal-header">
          <h5 class="modal-title">Create/Edit Collection</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Collection Title</label>
              <input type="text" class="form-control" name="title" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Slug (URL handle)</label>
              <input type="text" class="form-control" name="slug" placeholder="auto-generated if blank">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" rows="3"></textarea>
          </div>

          <div class="row mb-4">
            <div class="col-md-6">
              <label class="form-label">SEO Title</label>
              <input type="text" class="form-control" name="seo_title">
            </div>
            <div class="col-md-6">
              <label class="form-label">SEO Description</label>
              <input type="text" class="form-control" name="seo_description">
            </div>
          </div>

          <div class="form-check form-switch mb-4">
            <input class="form-check-input" type="checkbox" id="toggle-rule-based" name="rule_based">
            <label class="form-check-label" for="toggle-rule-based">Rule-based collection</label>
          </div>

          <!-- Rule Builder -->
          <div id="rule-builder" class="d-none">
            <h6 class="mb-2">Rules</h6>
            <button type="button" class="btn btn-sm btn-outline-primary mb-2" data-bs-toggle="modal" data-bs-target="#modal-advanced-rules">Advanced Rules</button>
            <div id="rule-list"></div>
            <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="addRuleRow()">+ Add Rule</button>

            <div class="mt-4">
              <label class="form-label">Matching Logic</label>
              <select class="form-select" name="logic">
                <option value="AND">Match ALL conditions (AND)</option>
                <option value="OR">Match ANY condition (OR)</option>
              </select>
            </div>
          </div>

          <!-- Manual Selector -->
          <div id="manual-selector">
            <h6 class="mb-2">Select Products</h6>
            <input type="text" id="manual-product-search" class="form-control form-control-sm mb-2" placeholder="Search products...">
            <select class="form-select" name="manual_products[]" multiple id="manual-products-select" size="10"></select>
          </div>

          <!-- Advanced Rules Modal -->
          <div class="modal fade" id="modal-advanced-rules" tabindex="-1" aria-labelledby="modalAdvancedRulesLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="modalAdvancedRulesLabel">Advanced Product Rules</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <form id="advanced-rules-form">
                    <div id="advanced-rule-list"></div>
                    <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="addAdvancedRuleRow()">+ Add Rule</button>
                    <div class="mt-4">
                      <label class="form-label">Matching Logic</label>
                      <select class="form-select" name="advanced_logic" id="advanced-logic">
                        <option value="AND">Match ALL conditions (AND)</option>
                        <option value="OR">Match ANY condition (OR)</option>
                      </select>
                    </div>
                  </form>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-primary" onclick="applyAdvancedRules()">Apply Rules</button>
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Save Collection</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
const ruleFields = ['title', 'type', 'vendor', 'tags'];
const ruleOperators = ['equals', 'contains', 'starts_with', 'ends_with', 'not_equals', 'not_contains'];

function addRuleRow(field = '', operator = '', value = '') {
  const row = document.createElement('div');
  row.className = 'd-flex gap-2 mb-2 align-items-center rule-row';
  row.innerHTML = `
    <select class="form-select form-select-sm" name="rule_field[]">
      ${ruleFields.map(f => `<option value="${f}" ${f === field ? 'selected' : ''}>${f}</option>`).join('')}
    </select>
    <select class="form-select form-select-sm" name="rule_operator[]">
      ${ruleOperators.map(op => `<option value="${op}" ${op === operator ? 'selected' : ''}>${op.replace(/_/g, ' ')}</option>`).join('')}
    </select>
    <input type="text" class="form-control form-control-sm" name="rule_value[]" value="${value}" required>
    <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove()">✕</button>
  `;
  document.getElementById('rule-list').appendChild(row);
}

document.getElementById('toggle-rule-based').addEventListener('change', function () {
  const ruleSection = document.getElementById('rule-builder');
  const manualSection = document.getElementById('manual-selector');
  ruleSection.classList.toggle('d-none', !this.checked);
  manualSection.classList.toggle('d-none', this.checked);
});

document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('collection-form');
  if (form) {
    form.addEventListener('submit', async function (e) {
      e.preventDefault();
      console.log('Collection form submit triggered');
      const formData = new FormData(this);

      const id = formData.get('id');
      const url = id ? '/backend/collections/update.php' : '/backend/collections/create.php';

      try {
        const res = await fetch(url, {
          method: 'POST',
          body: formData
        });
        const json = await res.json();
        if (json.success) {
          showAlert('Collection saved successfully.', 'success');
          // Refresh collections list
          if (window.loadCollectionsTable) {
            window.loadCollectionsTable();
          }
          // Hide modal
          const modalEl = document.getElementById('modal-collection');
          const modal = bootstrap.Modal.getInstance(modalEl);
          if (modal) modal.hide();
        } else {
          showAlert('Error saving collection: ' + json.message, 'danger');
        }
      } catch (err) {
        console.error('Error saving collection:', err);
        showAlert('Server error. See console.', 'danger');
      }
    });
  }

  // Advanced Rules: Show matching products and count
  const advancedRulesForm = document.getElementById('advanced-rules-form');
  const advancedRuleList = document.getElementById('advanced-rule-list');
  const modalAdvancedRules = document.getElementById('modal-advanced-rules');

  // Container to show matching products count and list
  let matchInfoContainer = document.createElement('div');
  matchInfoContainer.id = 'match-info-container';
  matchInfoContainer.style.maxHeight = '200px';
  matchInfoContainer.style.overflowY = 'auto';
  matchInfoContainer.style.marginTop = '10px';
  matchInfoContainer.style.border = '1px solid #ddd';
  matchInfoContainer.style.padding = '10px';
  matchInfoContainer.style.borderRadius = '4px';
  matchInfoContainer.style.backgroundColor = '#f9f9f9';
  advancedRulesForm.appendChild(matchInfoContainer);

  // Fetch all products once for filtering
  let allProducts = [];
  async function fetchAllProducts() {
    try {
      const res = await fetch('/admin/api/get-products.php?limit=1000');
      const json = await res.json();
      if (json.success) {
        allProducts = json.products;
      }
    } catch {
      allProducts = [];
    }
  }
  fetchAllProducts();

  // Function to filter products based on rules
  function filterProductsByRules() {
    const fields = Array.from(advancedRulesForm.querySelectorAll('select[name="advanced_rule_field[]"]')).map(el => el.value);
    const operators = Array.from(advancedRulesForm.querySelectorAll('select[name="advanced_rule_operator[]"]')).map(el => el.value);
    const values1 = Array.from(advancedRulesForm.querySelectorAll('input[name="advanced_rule_value1[]"]')).map(el => el.value.toLowerCase());
    const values2 = Array.from(advancedRulesForm.querySelectorAll('input[name="advanced_rule_value2[]"]')).map(el => el.value.toLowerCase());
    const logic = advancedRulesForm.querySelector('select[name="advanced_logic"]').value;

    if (fields.length === 0) {
      matchInfoContainer.innerHTML = '<em>No rules defined.</em>';
      return;
    }

    let filtered = allProducts.filter(product => {
      let results = fields.map((field, i) => {
        const op = operators[i];
        const val1 = values1[i];
        const val2 = values2[i];
        const prodValRaw = product[field] || '';
        const prodVal = typeof prodValRaw === 'string' ? prodValRaw.toLowerCase() : prodValRaw;

        switch (op) {
          case 'equals':
            return prodVal == val1;
          case 'not_equals':
            return prodVal != val1;
          case 'contains':
            return prodVal.includes(val1);
          case 'not_contains':
            return !prodVal.includes(val1);
          case 'starts_with':
            return prodVal.startsWith(val1);
          case 'ends_with':
            return prodVal.endsWith(val1);
          case 'greater_than':
            return parseFloat(prodVal) > parseFloat(val1);
          case 'less_than':
            return parseFloat(prodVal) < parseFloat(val1);
          case 'between':
            return parseFloat(prodVal) >= parseFloat(val1) && parseFloat(prodVal) <= parseFloat(val2);
          default:
            return false;
        }
      });

      if (logic === 'AND') {
        return results.every(r => r);
      } else {
        return results.some(r => r);
      }
    });

    matchInfoContainer.innerHTML = `<strong>${filtered.length}</strong> product(s) match the rules.<br>` +
      filtered.slice(0, 20).map(p => `<div>${p.title}</div>`).join('');
  }

  // Add event listeners to update matching products on rule changes
  function addAdvancedRuleListeners() {
    advancedRulesForm.addEventListener('input', () => {
      filterProductsByRules();
    });
  }
  addAdvancedRuleListeners();

  // Also update matching products when modal is shown
  modalAdvancedRules.addEventListener('shown.bs.modal', () => {
    filterProductsByRules();
  });
});

// Populate manual product list on modal open
document.getElementById('modal-collection').addEventListener('show.bs.modal', async function () {
  const select = document.getElementById('manual-products-select');
  if (select.options.length > 0) return;

  try {
    const res = await fetch('/admin/api/get-products.php?limit=1000');
    const json = await res.json();
    if (json.success) {
      for (const p of json.products) {
        const opt = document.createElement('option');
        opt.value = p.id;
        opt.textContent = p.title;
        select.appendChild(opt);
      }
    }
  } catch (err) {
    console.warn('Could not load product list');
  }
});
</script>
