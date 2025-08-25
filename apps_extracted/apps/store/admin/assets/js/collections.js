document.addEventListener('DOMContentLoaded', () => {
  if (document.getElementById('collectionsTable')) {
    initCollectionsPage();
  }
});

let collectionsData = [];
let currentPage = 1;
const pageSize = 10;
let currentSort = { field: 'created_at', direction: 'desc' };
let currentFilter = '';

function initCollectionsPage() {
  renderControls();
  loadCollectionsTable();
}

function renderControls() {
  const container = document.querySelector('.container.py-4');
  const controlsHtml = `
    <div class="d-flex justify-content-between align-items-center mb-3 gap-2 flex-wrap">
      <div class="d-flex gap-2 align-items-center flex-wrap">
        <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Search collections...">
        <select id="sortSelect" class="form-select form-select-sm">
          <option value="title_asc">Sort by Name (A-Z)</option>
          <option value="title_desc">Sort by Name (Z-A)</option>
          <option value="created_at_asc">Sort by Created Date (Oldest)</option>
          <option value="created_at_desc" selected>Sort by Created Date (Newest)</option>
        </select>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <button id="bulkDeleteBtn" class="btn btn-danger btn-sm" disabled>Bulk Delete</button>
        <button id="bulkExportBtn" class="btn btn-secondary btn-sm" disabled>Bulk Export</button>
        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modal-collection">+ Create Collection</button>
      </div>
    </div>
    <div id="alertContainer"></div>
    <div id="collectionsTable">Loading...</div>
  `;
  container.innerHTML = controlsHtml;

  document.getElementById('searchInput').addEventListener('input', (e) => {
    currentFilter = e.target.value.toLowerCase();
    currentPage = 1;
    renderCollectionsTable();
  });

  document.getElementById('sortSelect').addEventListener('change', (e) => {
    const [field, direction] = e.target.value.split('_');
    currentSort = { field, direction };
    renderCollectionsTable();
  });

  document.getElementById('bulkDeleteBtn').addEventListener('click', bulkDeleteCollections);
  document.getElementById('bulkExportBtn').addEventListener('click', bulkExportCollections);
}

async function loadCollectionsTable() {
  showSkeletonLoader();
  try {
    const res = await fetch('/backend/collections/list.php');
    const json = await res.json();

    if (!json.success) {
      showAlert('Failed to load collections.', 'danger');
      document.getElementById('collectionsTable').innerHTML = '';
      return;
    }

    collectionsData = json.collections;
    currentPage = 1;
    renderCollectionsTable();
  } catch (err) {
    console.error('❌ Error loading collections:', err);
    showAlert('Error loading collections.', 'danger');
    document.getElementById('collectionsTable').innerHTML = '';
  }
}

function showSkeletonLoader() {
  const skeletonHtml = `
    <table class="table table-bordered">
      <thead><tr><th>Title</th><th>Slug</th><th>Type</th><th>Products</th><th>Actions</th></tr></thead>
      <tbody>
        ${Array(5).fill('<tr><td colspan="5" class="p-3"><div class="skeleton-loader"></div></td></tr>').join('')}
      </tbody>
    </table>
  `;
  document.getElementById('collectionsTable').innerHTML = skeletonHtml;
}

function renderCollectionsTable() {
  let filtered = collectionsData.filter(c =>
    c.title.toLowerCase().includes(currentFilter) ||
    c.slug.toLowerCase().includes(currentFilter) ||
    (c.handle && c.handle.toLowerCase().includes(currentFilter))
  );

  filtered.sort((a, b) => {
    let valA = a[currentSort.field] || '';
    let valB = b[currentSort.field] || '';
    if (currentSort.field === 'created_at') {
      valA = new Date(valA);
      valB = new Date(valB);
    } else {
      valA = valA.toString().toLowerCase();
      valB = valB.toString().toLowerCase();
    }
    if (valA < valB) return currentSort.direction === 'asc' ? -1 : 1;
    if (valA > valB) return currentSort.direction === 'asc' ? 1 : -1;
    return 0;
  });

  const totalPages = Math.ceil(filtered.length / pageSize);
  if (currentPage > totalPages) currentPage = totalPages || 1;

  const pageData = filtered.slice((currentPage - 1) * pageSize, currentPage * pageSize);

  let html = `<table class="table table-bordered">
    <thead>
      <tr>
        <th><input type="checkbox" id="selectAllCheckbox"></th>
        <th>Image</th>
        <th>Title</th>
        <th>Handle</th>
        <th>Slug</th>
        <th>Type</th>
        <th>Products</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>`;

  for (const c of pageData) {
    html += `<tr data-id="${c.id}">
      <td><input type="checkbox" class="selectCheckbox"></td>
      <td>${c.image ? `<img src="${c.image}" alt="${c.title}" style="width:40px; height:40px; object-fit:cover; border-radius:4px;">` : ''}</td>
      <td class="editable" data-field="title">${escapeHtml(c.title)}</td>
      <td>${escapeHtml(c.handle || '')}</td>
      <td>${escapeHtml(c.slug)}</td>
      <td>${c.rule_based ? 'Rule-Based' : 'Manual'}</td>
      <td>${c.product_count}</td>
      
      
      <td class="text-nowrap">
  <button class="btn btn-link p-0 me-2" onclick="openEditCollection(${c.id})"><i class="bi bi-pencil-square"></i></button>
  <button class="btn btn-link p-0 text-danger" onclick="deleteCollection(${c.id})"><i class="bi bi-trash"></i></button>
</td>

      
    </tr>`;
  }

  html += `</tbody></table>`;

  // Pagination controls
  html += `<nav aria-label="Page navigation">
    <ul class="pagination justify-content-center">
      <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="changePage(${currentPage - 1}); return false;">Previous</a>
      </li>`;

  for (let i = 1; i <= totalPages; i++) {
    html += `<li class="page-item ${currentPage === i ? 'active' : ''}">
      <a class="page-link" href="#" onclick="changePage(${i}); return false;">${i}</a>
    </li>`;
  }

  html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="changePage(${currentPage + 1}); return false;">Next</a>
      </li>
    </ul>
  </nav>`;

  document.getElementById('collectionsTable').innerHTML = html;

  // Event listeners
  document.getElementById('selectAllCheckbox').addEventListener('change', toggleSelectAll);
  document.querySelectorAll('.selectCheckbox').forEach(cb => cb.addEventListener('change', updateBulkButtons));
  document.querySelectorAll('.editable').forEach(td => td.addEventListener('click', enableInlineEdit));
}

function escapeHtml(text) {
  return text.replace(/[&<>"']/g, function (m) {
    return {'&':'&amp;', '<':'<', '>':'>', '"':'"', "'":'&#39;'}[m];
  });
}

function toggleSelectAll(e) {
  const checked = e.target.checked;
  document.querySelectorAll('.selectCheckbox').forEach(cb => cb.checked = checked);
  updateBulkButtons();
}

function updateBulkButtons() {
  const anyChecked = Array.from(document.querySelectorAll('.selectCheckbox')).some(cb => cb.checked);
  document.getElementById('bulkDeleteBtn').disabled = !anyChecked;
  document.getElementById('bulkExportBtn').disabled = !anyChecked;
}

function changePage(page) {
  currentPage = page;
  renderCollectionsTable();
}

function enableInlineEdit(e) {
  const td = e.target;
  if (td.querySelector('input')) return; // already editing

  const originalText = td.textContent;
  const id = td.closest('tr').dataset.id;

  const input = document.createElement('input');
  input.type = 'text';
  input.value = originalText;
  input.className = 'form-control form-control-sm';
  td.textContent = '';
  td.appendChild(input);
  input.focus();

  input.addEventListener('blur', async () => {
    const newValue = input.value.trim();
    if (newValue && newValue !== originalText) {
      const success = await updateCollectionField(id, 'title', newValue);
      if (success) {
        td.textContent = newValue;
        showAlert('Collection name updated successfully.', 'success');
        // Update local data
        const col = collectionsData.find(c => c.id == id);
        if (col) col.title = newValue;
      } else {
        td.textContent = originalText;
        showAlert('Failed to update collection name.', 'danger');
      }
    } else {
      td.textContent = originalText;
    }
  });

  input.addEventListener('keydown', (ev) => {
    if (ev.key === 'Enter') {
      input.blur();
    } else if (ev.key === 'Escape') {
      td.textContent = originalText;
    }
  });
}

async function updateCollectionField(id, field, value) {
  try {
    const form = new FormData();
    form.append('id', id);
    form.append(field, value);

    const res = await fetch('/backend/collections/update.php', {
      method: 'POST',
      body: form
    });
    const json = await res.json();
    return json.success;
  } catch (err) {
    console.error('Error updating collection field:', err);
    return false;
  }
}

function showAlert(message, type = 'info') {
  const alertContainer = document.getElementById('alertContainer');
  if (!alertContainer) return;
  alertContainer.innerHTML = `
    <div class="alert alert-${type} alert-dismissible fade show" role="alert">
      ${escapeHtml(message)}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  `;
}

function openEditCollection(id) {
  const collection = collectionsData.find(c => c.id === id);
  if (!collection) {
    showAlert('Collection not found.', 'danger');
    return;
  }

  const modal = new bootstrap.Modal(document.getElementById('modal-collection'));
  const form = document.getElementById('collection-form');

  form.reset();

  form.elements['title'].value = collection.title || '';
  form.elements['slug'].value = collection.slug || '';
  form.elements['description'].value = collection.description || '';
  form.elements['seo_title'].value = collection.seo_title || '';
  form.elements['seo_description'].value = collection.seo_description || '';
  form.elements['rule_based'].checked = collection.rule_based ? true : false;

  // Clear rules and manual products
  document.getElementById('rule-list').innerHTML = '';
  const manualSelect = document.getElementById('manual-products-select');
  manualSelect.selectedIndex = -1;

  if (collection.rule_based && collection.rules) {
    try {
      const rules = JSON.parse(collection.rules);
      rules.forEach(rule => {
        addRuleRow(rule.field, rule.operator, rule.value);
      });
    } catch {
      // ignore parse errors
    }
  } else if (!collection.rule_based && collection.manual_products) {
    try {
      const manualProducts = JSON.parse(collection.manual_products);
      for (const option of manualSelect.options) {
        option.selected = manualProducts.includes(parseInt(option.value));
      }
    } catch {
      // ignore parse errors
    }
  }

  // Set hidden id field or add it to form
  if (!form.elements['id']) {
    const idInput = document.createElement('input');
    idInput.type = 'hidden';
    idInput.name = 'id';
    form.appendChild(idInput);
  }
  form.elements['id'].value = collection.id;

  modal.show();
}

async function deleteCollection(id) {
  if (!confirm('Are you sure you want to delete this collection?')) return;

  try {
    const form = new FormData();
    form.append('id', id);

    const res = await fetch('/backend/collections/delete.php', {
      method: 'POST',
      body: form
    });
    const json = await res.json();

    if (json.success) {
      showAlert('Collection deleted successfully.', 'success');
      // Remove from local data and re-render
      collectionsData = collectionsData.filter(c => c.id !== id);
      renderCollectionsTable();
    } else {
      showAlert('Failed to delete collection: ' + json.message, 'danger');
    }
  } catch (err) {
    console.error('Error deleting collection:', err);
    showAlert('Server error deleting collection.', 'danger');
  }
}

function bulkDeleteCollections() {
  const selectedIds = Array.from(document.querySelectorAll('.selectCheckbox:checked'))
    .map(cb => parseInt(cb.closest('tr').dataset.id));

  if (selectedIds.length === 0) return;

  if (!confirm(`Are you sure you want to delete ${selectedIds.length} collections?`)) return;

  Promise.all(selectedIds.map(id => {
    const form = new FormData();
    form.append('id', id);
    return fetch('/backend/collections/delete.php', { method: 'POST', body: form })
      .then(res => res.json());
  })).then(results => {
    const failed = results.filter(r => !r.success);
    if (failed.length === 0) {
      showAlert('Selected collections deleted successfully.', 'success');
    } else {
      showAlert(`${failed.length} collections failed to delete.`, 'danger');
    }
    collectionsData = collectionsData.filter(c => !selectedIds.includes(c.id));
    renderCollectionsTable();
  }).catch(() => {
    showAlert('Server error during bulk delete.', 'danger');
  });
}

function bulkExportCollections() {
  const selectedIds = Array.from(document.querySelectorAll('.selectCheckbox:checked'))
    .map(cb => parseInt(cb.closest('tr').dataset.id));

  if (selectedIds.length === 0) return;

  // For demo, export JSON of selected collections
  const exportData = collectionsData.filter(c => selectedIds.includes(c.id));
  const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(exportData, null, 2));
  const dlAnchor = document.createElement('a');
  dlAnchor.setAttribute("href", dataStr);
  dlAnchor.setAttribute("download", "collections_export.json");
  document.body.appendChild(dlAnchor);
  dlAnchor.click();
  dlAnchor.remove();
}

// Manual product search filter
document.addEventListener('DOMContentLoaded', () => {
  const searchInput = document.getElementById('manual-product-search');
  const select = document.getElementById('manual-products-select');
  if (searchInput && select) {
    searchInput.addEventListener('input', () => {
      const filter = searchInput.value.toLowerCase();
      for (const option of select.options) {
        const text = option.text.toLowerCase();
        option.style.display = text.includes(filter) ? '' : 'none';
      }
    });
  }
});

// Advanced Rules Modal Logic
const advancedRuleFields = [
  { value: 'title', label: 'Title', type: 'text' },
  { value: 'price', label: 'Price', type: 'number' },
  { value: 'type', label: 'Product Type', type: 'text' },
  { value: 'vendor', label: 'Vendor', type: 'text' },
  { value: 'tags', label: 'Tags', type: 'text' },
  { value: 'inventory_qty', label: 'Inventory Quantity', type: 'number' }
];

const advancedRuleOperators = {
  text: ['equals', 'contains', 'starts_with', 'ends_with', 'not_equals', 'not_contains'],
  number: ['equals', 'not_equals', 'greater_than', 'less_than', 'between']
};

function addAdvancedRuleRow(field = '', operator = '', value1 = '', value2 = '') {
  const container = document.getElementById('advanced-rule-list');
  const row = document.createElement('div');
  row.className = 'd-flex gap-2 mb-2 align-items-center advanced-rule-row';

  const fieldSelect = document.createElement('select');
  fieldSelect.className = 'form-select form-select-sm';
  fieldSelect.name = 'advanced_rule_field[]';
  advancedRuleFields.forEach(f => {
    const opt = document.createElement('option');
    opt.value = f.value;
    opt.textContent = f.label;
    if (f.value === field) opt.selected = true;
    fieldSelect.appendChild(opt);
  });

  const operatorSelect = document.createElement('select');
  operatorSelect.className = 'form-select form-select-sm';
  operatorSelect.name = 'advanced_rule_operator[]';

  function updateOperators() {
    const selectedField = fieldSelect.value;
    const fieldType = advancedRuleFields.find(f => f.value === selectedField)?.type || 'text';
    operatorSelect.innerHTML = '';
    advancedRuleOperators[fieldType].forEach(op => {
      const opt = document.createElement('option');
      opt.value = op;
      opt.textContent = op.replace(/_/g, ' ');
      if (op === operator) opt.selected = true;
      operatorSelect.appendChild(opt);
    });
    updateValueInputs();
  }

  const valueInput1 = document.createElement('input');
  valueInput1.className = 'form-control form-control-sm';
  valueInput1.name = 'advanced_rule_value1[]';

  const valueInput2 = document.createElement('input');
  valueInput2.className = 'form-control form-control-sm d-none';
  valueInput2.name = 'advanced_rule_value2[]';

  function updateValueInputs() {
    const op = operatorSelect.value;
    if (op === 'between') {
      valueInput2.classList.remove('d-none');
      valueInput1.type = 'number';
      valueInput2.type = 'number';
    } else {
      valueInput2.classList.add('d-none');
      valueInput1.type = advancedRuleFields.find(f => f.value === fieldSelect.value)?.type || 'text';
    }
  }

  fieldSelect.addEventListener('change', () => {
    updateOperators();
  });

  operatorSelect.addEventListener('change', () => {
    updateValueInputs();
  });

  valueInput1.value = value1;
  valueInput2.value = value2;

  updateOperators();

  const removeBtn = document.createElement('button');
  removeBtn.type = 'button';
  removeBtn.className = 'btn btn-sm btn-danger';
  removeBtn.textContent = '✕';
  removeBtn.addEventListener('click', () => {
    row.remove();
  });

  row.appendChild(fieldSelect);
  row.appendChild(operatorSelect);
  row.appendChild(valueInput1);
  row.appendChild(valueInput2);
  row.appendChild(removeBtn);

  container.appendChild(row);
}

function applyAdvancedRules() {
  const form = document.getElementById('advanced-rules-form');
  const fields = Array.from(form.querySelectorAll('select[name="advanced_rule_field[]"]')).map(el => el.value);
  const operators = Array.from(form.querySelectorAll('select[name="advanced_rule_operator[]"]')).map(el => el.value);
  const values1 = Array.from(form.querySelectorAll('input[name="advanced_rule_value1[]"]')).map(el => el.value);
  const values2 = Array.from(form.querySelectorAll('input[name="advanced_rule_value2[]"]')).map(el => el.value);
  const logic = form.querySelector('select[name="advanced_logic"]').value;

  // Clear existing rules in main rule list
  const ruleList = document.getElementById('rule-list');
  ruleList.innerHTML = '';

  for (let i = 0; i < fields.length; i++) {
    let value = values1[i];
    if (operators[i] === 'between') {
      value = `${values1[i]} and ${values2[i]}`;
    }
    addRuleRow(fields[i], operators[i], value);
  }

  // Set matching logic
  const logicSelect = document.querySelector('select[name="logic"]');
  if (logicSelect) {
    logicSelect.value = logic;
  }

  // Close modal
  const modal = bootstrap.Modal.getInstance(document.getElementById('modal-advanced-rules'));
  if (modal) modal.hide();
}