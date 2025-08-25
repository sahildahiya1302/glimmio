document.addEventListener('DOMContentLoaded', () => {
  const inputs = document.querySelectorAll('.setting-input');
  const groups = document.querySelectorAll('.settings-group');
  const links = document.querySelectorAll('.settings-menu .menu-link');
  const saveBtn = document.getElementById('save-settings');

  // Init section tabs
  function initTabs() {
    if (!groups.length || !links.length) return;

    groups.forEach((g, i) => g.classList.toggle('active', i === 0));
    links.forEach((link, index) => {
      link.classList.toggle('active', index === 0);
      link.addEventListener('click', e => {
        e.preventDefault();
        const targetID = link.getAttribute('href').replace('#', '');

        groups.forEach(group => group.classList.toggle('active', group.id === targetID));
        links.forEach(l => l.classList.remove('active'));
        link.classList.add('active');
      });
    });
  }

  // Gather data from inputs
  function collectSettings() {
    const data = {};
    inputs.forEach(input => {
      const key = input.dataset.key;
      if (!key) return;
      if (input.type === 'checkbox') {
        data[key] = input.checked ? '1' : '0';
      } else {
        data[key] = input.value;
      }
    });
    return data;
  }

  // Save button click handler
  async function saveSettings() {
    if (!saveBtn) return;
    const data = collectSettings();

    saveBtn.disabled = true;
    const originalText = saveBtn.textContent;
    saveBtn.textContent = 'Saving...';

    try {
      const res = await fetch(`/admin/api/theme-settings.php?preview_theme_id=${CURRENT_THEME_ID}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      });

      if (!res.ok) throw new Error('Failed to save');

      saveBtn.textContent = 'Saved';
      showToast('Settings saved successfully', 'success');
    } catch (err) {
      saveBtn.textContent = 'Error';
      showToast('Failed to save settings', 'error');
    } finally {
      setTimeout(() => {
        saveBtn.textContent = originalText;
        saveBtn.disabled = false;
      }, 1200);
    }
  }

  // Show toast message
  function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
  }

  // Attach listeners
  if (saveBtn) saveBtn.addEventListener('click', saveSettings);

  initTabs();
});
