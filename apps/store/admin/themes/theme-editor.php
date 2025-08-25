<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';
require_once __DIR__ . '/../../src/ThemeEngine/ThemeDetector.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /backend/auth/login.php');
    exit;
}

$pageTitle = 'Theme Editor';

$themeId = isset($_GET['theme_id']) ? (int)$_GET['theme_id'] : THEME_ID;
$pageType = $_GET['page'] ?? 'index';

// Determine theme folder
$themeFolder = ($themeId === 1) ? 'default' : 'theme' . $themeId;
$themePath = __DIR__ . "/../../themes/{$themeFolder}";

// Initialize theme detector for multi-platform support
$detector = new ThemeDetector($themePath);

// Detect template type for current page
$templateType = $detector->detectThemeType($pageType);

// Load layout based on template type
$layout = [];
$settings = [];

// Load settings for all theme types
$settingsPaths = [
    'json' => $themePath . "/config/settings_data.json",
    'liquid' => $themePath . "/config/settings_data.json",
    'php' => $themePath . "/config/settings_data.json",
    'html' => $themePath . "/config/settings_data.json"
];

foreach ($settingsPaths as $path) {
    if (file_exists($path)) {
        $settingsJson = file_get_contents($path);
        $settings = json_decode($settingsJson, true) ?: [];
        break;
    }
}

// Load layout based on template type
switch ($templateType) {
    case 'json':
        $layoutJsonPath = $themePath . "/templates/{$pageType}.json";
        if (file_exists($layoutJsonPath)) {
            $layoutJson = file_get_contents($layoutJsonPath);
            $layout = json_decode($layoutJson, true) ?: [];
        }
        break;
        
    case 'liquid':
    case 'php':
    case 'html':
        // For non-JSON templates, create a basic layout structure
        $layout = [
            'template_type' => $templateType,
            'template_file' => "{$pageType}.{$templateType}",
            'sections' => [],
            'settings' => $settings
        ];
        break;
}

// Load available sections for all supported formats
$sectionsDir = $themePath . "/sections";
$sectionSchemas = [];

// Load JSON schemas
foreach (glob($sectionsDir . "/*.schema.json") as $schemaFile) {
    $schemaContent = file_get_contents($schemaFile);
    $schema = json_decode($schemaContent, true);
    if ($schema && isset($schema['name'])) {
        $sectionSchemas[basename($schemaFile, '.schema.json')] = $schema;
    }
}

// Load Liquid schemas
foreach (glob($sectionsDir . "/*.liquid") as $liquidFile) {
    $schema = parseLiquidSchema($liquidFile);
    if ($schema && isset($schema['name'])) {
        $sectionSchemas[basename($liquidFile, '.liquid')] = $schema;
    }
}

// Load PHP schemas
foreach (glob($sectionsDir . "/*.php") as $phpFile) {
    $schema = parsePhpSchema($phpFile);
    if ($schema && isset($schema['name'])) {
        $sectionSchemas[basename($phpFile, '.php')] = $schema;
    }
}

// Load HTML schemas
foreach (glob($sectionsDir . "/*.html") as $htmlFile) {
    $schema = parseHtmlSchema($htmlFile);
    if ($schema && isset($schema['name'])) {
        $sectionSchemas[basename($htmlFile, '.html')] = $schema;
    }
}

// Helper functions for parsing different schema types
function parseLiquidSchema($file) {
    $content = file_get_contents($file);
    if (preg_match('/\{\%\s*schema\s*\%\}(.*?)\{\%\s*endschema\s*\%\}/s', $content, $matches)) {
        $schema = json_decode($matches[1], true);
        return $schema ?: null;
    }
    return null;
}

function parsePhpSchema($file) {
    $content = file_get_contents($file);
    if (preg_match('/\/\*\s*schema\s*\*\/(.*?)\/\*\s*endschema\s*\*\//s', $content, $matches)) {
        $schema = json_decode($matches[1], true);
        return $schema ?: null;
    }
    return null;
}

function parseHtmlSchema($file) {
    $content = file_get_contents($file);
    if (preg_match('/<!--\s*schema\s*-->(.*?)<!--\s*endschema\s*-->/s', $content, $matches)) {
        $schema = json_decode($matches[1], true);
        return $schema ?: null;
    }
    return null;
}

// Get available themes for theme switching
$availableThemes = [];
$themesDir = __DIR__ . "/../../themes";
foreach (glob($themesDir . "/*", GLOB_ONLYDIR) as $themeDir) {
    $themeName = basename($themeDir);
    $themeConfig = $themeDir . "/config/settings_data.json";
    $themeData = ['name' => $themeName, 'id' => $themeName];
    
    if (file_exists($themeConfig)) {
        $config = json_decode(file_get_contents($themeConfig), true);
        $themeData['title'] = $config['name'] ?? $themeName;
        $themeData['version'] = $config['version'] ?? '1.0';
    }
    
    $availableThemes[] = $themeData;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Theme Editor - Pages Tab</title>
<link rel="stylesheet" href="/admin/assets/theme-editor.css" />
<link rel="stylesheet" href="/admin/assets/admin.css" />
</head>
  <!-- New Sidebar with 3 Icons -->
  <div class="editor-sidebar">
    <div class="sidebar-icon active" id="sections-icon" title="Sections">
      <i class="bi bi-grid-3x3-gap"></i>
    </div>
    <div class="sidebar-icon" id="theme-settings-icon" title="Theme Settings">
      <i class="bi bi-gear settings-link" title="Theme settings"></i>
      
    </div>
    <div class="sidebar-icon" id="code-icon" title="Code Editor">
      <i class="bi bi-code-slash"></i>
    </div>
  </div>

  <div class="main-content">
  <div class="editor-header">
    <h1>Theme Editor</h1>
    <div class="header-actions">
      <select id="page-select" aria-label="Select page to edit">
        <option value="">-- Select Page --</option>
      </select>
      <button id="save-layout-btn">Save Draft</button>
      <button id="publish-layout-btn">Publish</button>
      <a href="/admin/themes/code-editor.php?theme_id=<?php echo $themeId; ?>" target="_blank" class="code-link">Edit Code</a>
      <div class="device-toggle">
        <button type="button" data-width="100%" class="active" id="device-desktop">Desktop</button>
        <button type="button" data-width="768px" id="device-tablet">Tablet</button>
        <button type="button" data-width="375px" id="device-mobile">Mobile</button>
      </div>
    </div>
    <span class="version-badge">v1.2</span>
  </div>

  <!-- Add Section Modal -->
  <div id="add-section-modal" class="modal">
    <div class="modal-content">
      <button type="button" class="close-modal" aria-label="Close">&times;</button>
      <input type="text" id="section-search" placeholder="Search sections" />
      <div id="section-card-list">
        <?php foreach ($sectionSchemas as $key => $schema): ?>
          <div class="section-card" data-type="<?php echo htmlspecialchars($key); ?>">
            <img src="/themes/<?php echo $themeFolder; ?>/sections/<?php echo htmlspecialchars($key); ?>.png" alt="" onerror="this.style.display='none'" />
            <h4><?php echo htmlspecialchars($schema['name']); ?></h4>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <div class="admin-layout">
  <div class="sidebar">
      <h2>Page Sections (<span id="page-type-label"><?php echo htmlspecialchars($pageType); ?></span>)</h2>
      <div class="sidebar-search">
        <input type="text" id="section-filter" placeholder="Search sections and blocks..." />
      </div>
      <div id="section-list" aria-live="polite" aria-relevant="additions removals">
        <!-- Sections will be loaded here -->
      </div>
    </div>
    <div id="theme-settings-panel" class="sidebar" style="display:none;">
      <div id="theme-settings-content"></div>
    </div>

    <div class="content">
      <h2>Live Preview</h2>
      <iframe id="preview-frame" src="/?preview_theme_id=<?php echo $themeId; ?>&page=<?php echo htmlspecialchars($pageType); ?>"></iframe>
    </div>
  </div>
</div>

<script>
    const themeId = <?php echo (int)$themeId; ?>;
    let selectedSectionIndex = null;
    let layoutDirty = false;
  const sectionSchemas = <?php echo json_encode($sectionSchemas); ?>;
  const pageSelect = document.getElementById('page-select');
  const pageTypeLabel = document.getElementById('page-type-label');
  const sectionList = document.getElementById('section-list');
  const modal = document.getElementById('add-section-modal');
  const sectionSearch = document.getElementById('section-search');
  const searchInput = document.getElementById('section-filter');
  const previewFrame = document.getElementById('preview-frame');
  const deviceButtons = document.querySelectorAll('.device-toggle button');
  const settingsContent = document.getElementById('theme-settings-content');

  let addTargetGroup = 'template';
  let addTargetIndex = null;

  let currentLayout = [];
  let currentPage = '<?php echo htmlspecialchars($pageType); ?>';

  // Load pages for page selector
  async function loadPages() {
    const response = await fetch(`/admin/api/get-pages.php?preview_theme_id=${themeId}`);
    if (!response.ok) return;
    const pages = await response.json();
    pageSelect.innerHTML = '<option value="">-- Select Page --</option>';
    pages.forEach(page => {
      const option = document.createElement('option');
      option.value = page;
      option.textContent = (page === 'index') ? 'Home' : (page.charAt(0).toUpperCase() + page.slice(1));
      if (page === currentPage) option.selected = true;
      pageSelect.appendChild(option);
    });
  }

  // Load layout for selected page
  async function loadLayout(page) {
    if (!page) return;
    
let fileSlug = page;

    const response = await fetch(`/admin/api/get-page-layout.php?page=${fileSlug}&preview_theme_id=${themeId}`);
    if (!response.ok) {
      alert('Failed to load page layout');
      return;
    }
    const layout = await response.json();
    console.log('Loaded layout:', layout);
    if (layout.liquid) {
      alert('This template is in Liquid format and cannot be edited visually. Use Edit Code.');
      currentLayout = [];
    } else if (!layout || layout.error) {
      alert(layout?.error || 'Invalid layout format received');
      currentLayout = [];
    } else if (Array.isArray(layout.sections)) {
      currentLayout = layout.sections;
    } else if (layout.sections && typeof layout.sections === 'object' && Array.isArray(layout.order)) {
      // Convert old format to array and preserve IDs
      currentLayout = layout.order.map(key => ({ ...layout.sections[key], id: key })).filter(Boolean);
    } else {
      alert('Invalid layout format received');
      currentLayout = [];
    }
    // Ensure preset blocks exist for sections that define them
    currentLayout.forEach(sec => {
      const schema = sectionSchemas[sec.type];
      if (!schema) return;
      if (!Array.isArray(sec.blocks)) sec.blocks = [];
      if (sec.blocks.length === 0 && Array.isArray(schema.blocks)) {
        sec.blocks = schema.blocks.map(b => {
          const bs = {};
          (b.settings || []).forEach(s => { bs[s.id] = s.default ?? ''; });
          return { type: b.type, settings: bs, visible: true };
        });
      }
    });
    currentPage = page; // Move this before updatePreview
    pageTypeLabel.textContent = page.charAt(0).toUpperCase() + page.slice(1);
    renderSections();
    updatePreview();
  }

  // Categorize sections into groups
  function categorizeSections() {
    const groups = { header: [], template: [], footer: [] };
    currentLayout.forEach((sec, idx) => {
      if (sec.type.includes('header') || sec.type.includes('announcement')) {
        groups.header.push({ section: sec, index: idx });
      } else if (sec.type.includes('footer')) {
        groups.footer.push({ section: sec, index: idx });
      } else {
        groups.template.push({ section: sec, index: idx });
      }
    });
    return groups;
  }

  // Build single list item
  function buildSectionItem(section, index) {
    const li = document.createElement('li');
    li.className = 'section-item';
    if (selectedSectionIndex === index) li.classList.add('active');
    li.draggable = true;
    li.dataset.index = index;

    const row = document.createElement('div');
    row.className = 'section-row';

    const nameSpan = document.createElement('span');
    nameSpan.className = 'item-name';
    nameSpan.textContent = section.type;

    const actions = document.createElement('span');
    actions.className = 'actions';

    const visBtn = document.createElement('button');
    const setVisIcon = () => { visBtn.textContent = section.visible === false ? '🙈' : '👁'; };
    setVisIcon();
    visBtn.title = 'Toggle visibility';
    visBtn.addEventListener('click', e => {
      e.stopPropagation();
      section.visible = section.visible === false ? true : false;
      setVisIcon();
      layoutDirty = true;
      updatePreview();
    });
    actions.appendChild(visBtn);

    const delBtn = document.createElement('button');
    delBtn.textContent = '🗑';
    delBtn.title = 'Delete';
    delBtn.addEventListener('click', e => {
      e.stopPropagation();
      if (confirm('Are you sure you want to delete this section?')) deleteSection(index);
    });
    actions.appendChild(delBtn);


    let toggleBtn = null;
    let bUl = null;
    if (!Array.isArray(section.blocks)) section.blocks = [];
    const hasBlockSchema = sectionSchemas[section.type]?.blocks?.length;
    if (hasBlockSchema) {
      toggleBtn = document.createElement('span');
      toggleBtn.className = 'blocks-toggle';
      toggleBtn.textContent = '▸';
      bUl = document.createElement('ul');
      bUl.className = 'block-list';
      bUl.style.display = 'none';
    } else {
      toggleBtn = document.createElement('span');
      toggleBtn.className = 'blocks-toggle placeholder';
    }

    row.appendChild(toggleBtn);
    row.appendChild(nameSpan);
    row.appendChild(actions);
    li.appendChild(row);

    if (hasBlockSchema) {
      const buildBlockItem = (block, bIndex) => {
        const bi = document.createElement('li');
        bi.className = 'block-item';

        const labelText = block.settings.title || block.settings.text || `Tile ${bIndex + 1}`;
        const nameSpan = document.createElement('span');
        nameSpan.className = 'item-name';
        nameSpan.textContent = section.type;
        const labelSpan = document.createElement('span');
        labelSpan.className = 'block-label';
        labelSpan.textContent = ` - ${labelText}`;
        bi.appendChild(nameSpan);
        bi.appendChild(labelSpan);

        const actions = document.createElement('span');
        actions.className = 'actions';

        const visBtn = document.createElement('button');
        const setVisIcon = () => { visBtn.textContent = block.visible === false ? '🙈' : '👁'; };
        setVisIcon();
        visBtn.title = 'Toggle visibility';
        visBtn.addEventListener('click', e => {
          e.stopPropagation();
          block.visible = block.visible === false ? true : false;
          setVisIcon();
          layoutDirty = true;
          updatePreview();
        });
        actions.appendChild(visBtn);

        const delBtn = document.createElement('button');
        delBtn.textContent = '🗑';
        delBtn.title = 'Delete';
        delBtn.addEventListener('click', e => {
          e.stopPropagation();
          section.blocks.splice(bIndex, 1);
          layoutDirty = true;
          renderSections();
          updatePreview();
        });
        actions.appendChild(delBtn);

        bi.addEventListener('click', e => {
          e.stopPropagation();
          sectionList.style.display = 'none';
          showCustomizer(index, bIndex);
          scrollToSection(index);
        });

        bi.appendChild(actions);
        return bi;
      };

      const buildAddBlockLine = (pos) => {
        const liLine = document.createElement('li');
        liLine.className = 'add-block-line';
        const icon = document.createElement('span');
        icon.className = 'plus-icon';
        icon.textContent = '+';
        liLine.appendChild(icon);
        liLine.addEventListener('click', async e => {
          e.stopPropagation();
          const res = await fetch(`/admin/api/get-section-schema.php?section=${section.type}&preview_theme_id=${themeId}`);
          const schema = await res.json();
          const bSchema = (schema.blocks || [])[0];
          if (!bSchema) return;
          const settings = {};
          (bSchema.settings || []).forEach(s => { settings[s.id] = s.default ?? '' });
          if (!Array.isArray(section.blocks)) section.blocks = [];
          section.blocks.splice(pos, 0, {type: bSchema.type, settings, visible: true});
          section.open = true;
          layoutDirty = true;
          renderSections();
          updatePreview();
        });
        return liLine;
      };

      bUl.appendChild(buildAddBlockLine(0));
      (section.blocks || []).forEach((b, bi) => {
        bUl.appendChild(buildBlockItem(b, bi));
        bUl.appendChild(buildAddBlockLine(bi + 1));
      });

      toggleBtn.addEventListener('click', e => {
        e.stopPropagation();
        const show = bUl.style.display === 'none';
        bUl.style.display = show ? 'block' : 'none';
        toggleBtn.textContent = show ? '▾' : '▸';
        if (show) {
          li.classList.add('blocks-open');
        } else {
          li.classList.remove('blocks-open');
        }
        section.open = show;
      });
    }

    if (section.open && bUl) {
      bUl.style.display = 'block';
      toggleBtn.textContent = '▾';
      li.classList.add('blocks-open');
    }

    if (bUl) li.appendChild(bUl);

    li.addEventListener('click', () => {
      sectionList.style.display = 'none';
      selectedSectionIndex = index;
      renderSections();
      showCustomizer(index);
      scrollToSection(index);
    });

    return li;
  }

  function buildAddLine(pos, group) {
    const li = document.createElement('li');
    li.className = 'add-section-line';
    li.dataset.index = pos;
    li.dataset.group = group;
    const icon = document.createElement('span');
    icon.className = 'plus-icon';
    icon.textContent = '+';
    li.appendChild(icon);
    li.addEventListener('click', e => {
      e.stopPropagation();
      addTargetIndex = parseInt(li.dataset.index, 10);
      addTargetGroup = li.dataset.group;
      modal.style.display = 'flex';
      sectionSearch.value = '';
      filterCards('');
    });
    return li;
  }

  function showCustomizer(index, blockIndex = null) {
    let customizer = document.getElementById('section-customizer');
    if (!customizer) {
      customizer = document.createElement('div');
      customizer.id = 'section-customizer';
      customizer.style.flexGrow = '1';
      customizer.style.display = 'block';
      customizer.style.padding = '10px';
      customizer.style.borderTop = '1px solid #ccc';
      customizer.style.overflowY = 'auto';
      customizer.style.flexBasis = '100%';
      document.querySelector('.sidebar').appendChild(customizer);
    } else {
      customizer.style.display = 'block';
    }

    let form = document.getElementById('customizer-form');
    if (!form) {
      form = document.createElement('form');
      form.id = 'customizer-form';
      form.style.display = 'flex';
      form.style.flexDirection = 'column';
      form.style.gap = '10px';
      customizer.appendChild(form);
    }

    let backBtn = document.getElementById('back-to-sections-btn');
    if (!backBtn) {
      backBtn = document.createElement('button');
      backBtn.id = 'back-to-sections-btn';
      backBtn.textContent = 'Back to Sections';
      backBtn.style.marginBottom = '10px';
      backBtn.style.fontWeight = 'bold';
      backBtn.addEventListener('click', () => {
        sectionList.style.display = 'block';
        customizer.style.display = 'none';
        const form = document.getElementById('customizer-form');
        if (form) form.innerHTML = '';
        selectedSectionIndex = null;
        backBtn.remove();
        renderSections();
      });
      customizer.insertBefore(backBtn, customizer.firstChild);
    }

    if (blockIndex === null) {
      openCustomizerForSection(index);
    } else {
      openCustomizerForBlock(index, blockIndex);
    }
  }

  function renderSections() {
    sectionList.innerHTML = '';
    const groups = categorizeSections();
    const filter = (searchInput.value || '').toLowerCase();
    ['header','template','footer'].forEach(g => {
      const arr = groups[g];
      const wrapper = document.createElement('div');
      wrapper.className = 'section-group';
      const header = document.createElement('div');
      header.className = 'section-group-header';
      header.innerHTML = `<span>${g.toUpperCase()}</span><span class="chevron">▾</span>`;
      header.addEventListener('click', () => wrapper.classList.toggle('collapsed'));
      wrapper.appendChild(header);
      const ul = document.createElement('ul');
      ul.className = 'section-group-list';
      arr.forEach((item, idx) => {
        if (idx === 0) ul.appendChild(buildAddLine(item.index, g));
        if (filter && !item.section.type.toLowerCase().includes(filter)) {
          return ul.appendChild(buildAddLine(item.index + 1, g));
        }
        ul.appendChild(buildSectionItem(item.section, item.index));
        ul.appendChild(buildAddLine(item.index + 1, g));
      });
      wrapper.appendChild(ul);
      sectionList.appendChild(wrapper);
    });
    addDragAndDropHandlers();
  }

  // Duplicate section function
  function duplicateSection(index) {
    if (index < 0 || index >= currentLayout.length) return;
    const sectionToDuplicate = currentLayout[index];
    const newId = `${sectionToDuplicate.type}-${Date.now()}`;
    const duplicatedSection = JSON.parse(JSON.stringify(sectionToDuplicate));
    duplicatedSection.id = newId;
    currentLayout.splice(index + 1, 0, duplicatedSection);
    renderSections();
    layoutDirty = true;
    updatePreview();
  }

  // Delete section function
  function deleteSection(index) {
    if (index < 0 || index >= currentLayout.length) return;
    currentLayout.splice(index, 1);
    renderSections();
    layoutDirty = true;
    updatePreview();
  }
  // Open customization panel for a section
  async function openCustomizerForSection(index) {
    selectedSectionIndex = index;
    const section = currentLayout[index];
    if (!section) return;
    scrollToSection(index);
    let form = document.getElementById("customizer-form");
    let customizer = document.getElementById('section-customizer');
    if (!customizer) {
      customizer = document.createElement('div');
      customizer.id = 'section-customizer';
      customizer.style.flexGrow = '1';
      customizer.style.display = 'block';
      customizer.style.padding = '10px';
      customizer.style.borderTop = '1px solid #ccc';
      customizer.style.overflowY = 'auto';
      customizer.style.flexBasis = '100%';
      document.querySelector('.sidebar').appendChild(customizer);
    }
    if (!form) {
      form = document.createElement("form");
      form.id = "customizer-form";
      form.style.display = "flex";
      form.style.flexDirection = "column";
      form.style.gap = "10px";
      customizer.appendChild(form);
    }
    form.innerHTML = "";
    const response = await fetch(`/admin/api/get-section-schema.php?section=${section.type}&preview_theme_id=${themeId}`);
    if (!response.ok) {
      form.innerHTML = "<p>Failed to load section schema.</p>";
      return;
    }
    const schema = await response.json();
    if (!schema || !schema.settings) {
      form.innerHTML = "<p>No customization available.</p>";
      return;
    }
    schema.settings.forEach(setting => {
      const wrapper = document.createElement("div");
      wrapper.className = "setting-item";
      const label = document.createElement("label");
      label.textContent = setting.label || setting.id;
      label.htmlFor = setting.id;
      wrapper.appendChild(label);
      let input;
      switch (setting.type) {
        case "textarea":
          input = document.createElement("textarea");
          break;
        case "select":
          input = document.createElement("select");
          (setting.options || []).forEach(opt => {
            const optEl = document.createElement("option");
            optEl.value = opt.value;
            optEl.textContent = opt.label;
            if (section.settings[setting.id] === opt.value) optEl.selected = true;
            input.appendChild(optEl);
          });
          break;
        case "image":
          input = document.createElement("input");
          input.type = "file";
          input.accept = "image/*";
          let preview = null;
          if (section.settings[setting.id]) {
            preview = document.createElement('img');
            preview.src = section.settings[setting.id];
            preview.style.maxWidth = '100%';
            wrapper.appendChild(preview);
          }
          input.addEventListener('change', async () => {
            if (!input.files.length) return;
            const fd = new FormData();
            fd.append('image', input.files[0]);
            try {
              const r = await fetch('/backend/themes/upload-image.php', { method: 'POST', body: fd });
              const d = await r.json();
              if (d.success) {
                section.settings[setting.id] = '/uploads/themes/images/' + d.filename;
                if (!preview) {
                  preview = document.createElement('img');
                  preview.style.maxWidth = '100%';
                  wrapper.insertBefore(preview, input);
                }
                preview.src = section.settings[setting.id];
                layoutDirty = true;
                updatePreview();
              } else {
                alert(d.error || 'Upload failed');
              }
            } catch (e) {
              console.error('Image upload failed', e);
              alert('Image upload failed');
            }
          });
          break;
        case "collection_select":
          input = document.createElement("select");
          input.innerHTML = '<option value="">Loading...</option>';
          fetch('/admin/api/get-collections.php')
            .then(r => r.json())
            .then(d => {
              input.innerHTML = '<option value="">-- Select Collection --</option>';
              if (d.success) {
                d.collections.forEach(c => {
                  const opt = document.createElement('option');
                  opt.value = c.id;
                  opt.textContent = c.title || c.name;
                  if (section.settings[setting.id] == c.id) opt.selected = true;
                  input.appendChild(opt);
                });
              }
            });
          break;
        case "product_select":
        case "product":
          input = document.createElement("select");
          input.innerHTML = '<option value="">Loading...</option>';
          fetch('/admin/api/get-products.php')
            .then(r => r.json())
            .then(d => {
              input.innerHTML = '<option value="">-- Select Product --</option>';
              if (d.success) {
                d.products.forEach(p => {
                  const opt = document.createElement('option');
                  opt.value = p.id;
                  opt.textContent = p.title;
                  if (section.settings[setting.id] == p.id) opt.selected = true;
                  input.appendChild(opt);
                });
              }
            });
          break;
        case "product_set_select":
          input = document.createElement("select");
          input.innerHTML = '<option value="">Loading...</option>';
          fetch('/admin/api/get-product-sets.php')
            .then(r => r.json())
            .then(d => {
              input.innerHTML = '<option value="">-- Select Set --</option>';
              if (d.success) {
                d.sets.forEach(s => {
                  const opt = document.createElement('option');
                  opt.value = s.id;
                  opt.textContent = s.title;
                  if (section.settings[setting.id] == s.id) opt.selected = true;
                  input.appendChild(opt);
                });
              }
            });
          break;
        case "checkbox":
          input = document.createElement("input");
          input.type = "checkbox";
          input.checked = !!section.settings[setting.id];
          break;
        default:
          input = document.createElement("input");
          input.type = setting.type || "text";
          input.value = section.settings[setting.id] || "";
      }
      input.id = setting.id;
      input.name = setting.id;
      const onChange = () => {
        if (setting.type === "checkbox") {
          section.settings[setting.id] = input.checked;
        } else {
          section.settings[setting.id] = input.value;
        }
        layoutDirty = true;
        updatePreview();
      };
      input.addEventListener("input", onChange);
      input.addEventListener("change", onChange);
      wrapper.appendChild(input);
      form.appendChild(wrapper);
    });

    // Add style presets selector
    const stylePresets = ['default', 'light', 'dark', 'full-width'];
    const presetWrapper = document.createElement("div");
    presetWrapper.className = "setting-item";
    const presetLabel = document.createElement("label");
    presetLabel.textContent = "Style Preset";
    presetLabel.htmlFor = "style-preset-select";
    presetWrapper.appendChild(presetLabel);

    const presetSelect = document.createElement("select");
    presetSelect.id = "style-preset-select";
    presetSelect.name = "style-preset-select";

    stylePresets.forEach(preset => {
      const option = document.createElement("option");
      option.value = preset;
      option.textContent = preset.charAt(0).toUpperCase() + preset.slice(1);
      if (section.settings.stylePreset === preset) option.selected = true;
      presetSelect.appendChild(option);
    });

    presetSelect.addEventListener("change", () => {
      section.settings.stylePreset = presetSelect.value;
      layoutDirty = true;
      updatePreview();
    });

    presetWrapper.appendChild(presetSelect);
    form.appendChild(presetWrapper);

    // Add device visibility toggles
    const visibilityWrapper = document.createElement("div");
    visibilityWrapper.className = "setting-item";

    const visibilityLabel = document.createElement("label");
    visibilityLabel.textContent = "Visibility";
    visibilityWrapper.appendChild(visibilityLabel);

    const mobileCheckbox = document.createElement("input");
    mobileCheckbox.type = "checkbox";
    mobileCheckbox.id = "visibility-mobile";
    mobileCheckbox.name = "visibility-mobile";
    mobileCheckbox.checked = section.settings.visibility?.mobile !== false; // default true
    const mobileLabel = document.createElement("label");
    mobileLabel.htmlFor = "visibility-mobile";
    mobileLabel.textContent = "Show on Mobile";

    const desktopCheckbox = document.createElement("input");
    desktopCheckbox.type = "checkbox";
    desktopCheckbox.id = "visibility-desktop";
    desktopCheckbox.name = "visibility-desktop";
    desktopCheckbox.checked = section.settings.visibility?.desktop !== false; // default true
    const desktopLabel = document.createElement("label");
    desktopLabel.htmlFor = "visibility-desktop";
    desktopLabel.textContent = "Show on Desktop";

    mobileCheckbox.addEventListener("change", () => {
      if (!section.settings.visibility) section.settings.visibility = {};
      section.settings.visibility.mobile = mobileCheckbox.checked;
      layoutDirty = true;
      updatePreview();
    });

    desktopCheckbox.addEventListener("change", () => {
      if (!section.settings.visibility) section.settings.visibility = {};
      section.settings.visibility.desktop = desktopCheckbox.checked;
      layoutDirty = true;
      updatePreview();
    });

    visibilityWrapper.appendChild(mobileCheckbox);
    visibilityWrapper.appendChild(mobileLabel);
    visibilityWrapper.appendChild(document.createElement("br"));
    visibilityWrapper.appendChild(desktopCheckbox);
    visibilityWrapper.appendChild(desktopLabel);

    form.appendChild(visibilityWrapper);
  }

  async function openCustomizerForBlock(sectionIndex, blockIndex) {
    const section = currentLayout[sectionIndex];
    if (!section || !section.blocks || !section.blocks[blockIndex]) return;
    scrollToSection(sectionIndex);
    const block = section.blocks[blockIndex];
    const form = document.getElementById('customizer-form');
    if (!form) return;
    form.innerHTML = '';
    const resp = await fetch(`/admin/api/get-section-schema.php?section=${section.type}&preview_theme_id=${themeId}`);
    const schema = await resp.json();
    const bSchema = (schema.blocks || []).find(b => b.type === block.type);
    if (!bSchema) { form.innerHTML = '<p>No settings available.</p>'; return; }
    const title = document.createElement('h3');
    title.textContent = block.type;
    form.appendChild(title);
    (bSchema.settings || []).forEach(s => {
      const wrap = document.createElement('div');
      wrap.className = 'setting-item';
      const lbl = document.createElement('label');
      lbl.textContent = s.label || s.id;
      lbl.htmlFor = s.id;
      wrap.appendChild(lbl);
      let inp;
      if (s.type === 'textarea') {
        inp = document.createElement('textarea');
        inp.value = block.settings[s.id] || '';
      } else if (s.type === 'image') {
        inp = document.createElement('input');
        inp.type = 'file';
        inp.accept = 'image/*';
        let preview = null;
        if (block.settings[s.id]) {
          preview = document.createElement('img');
          preview.src = block.settings[s.id];
          preview.style.maxWidth = '100%';
          wrap.appendChild(preview);
        }
        inp.addEventListener('change', async () => {
          if (!inp.files.length) return;
          const fd = new FormData();
          fd.append('image', inp.files[0]);
          const r = await fetch('/backend/themes/upload-image.php', { method: 'POST', body: fd });
          const d = await r.json();
          if (d.success) {
            block.settings[s.id] = '/uploads/themes/images/' + d.filename;
            if (!preview) {
              preview = document.createElement('img');
              preview.style.maxWidth = '100%';
              wrap.insertBefore(preview, inp);
            }
            preview.src = block.settings[s.id];
            layoutDirty = true;
            updatePreview();
          } else {
            alert(d.error || 'Upload failed');
          }
        });
      } else {
        inp = document.createElement('input');
        inp.type = s.type || 'text';
        inp.value = block.settings[s.id] || '';
      }
      inp.id = s.id;
      inp.addEventListener('input', () => {
        block.settings[s.id] = s.type === 'checkbox' ? inp.checked : inp.value;
        layoutDirty = true;
        updatePreview();
      });
      wrap.appendChild(inp);
      form.appendChild(wrap);
    });
  }

  // Save button click handler
  document.getElementById('save-layout-btn').addEventListener('click', async () => {
    if (!layoutDirty) {
      alert('No changes to save.');
      return;
    }

    try {
      await saveLayout();
      alert('Layout and section settings saved successfully!');
      layoutDirty = false;
      updatePreview();


    } catch (error) {
      console.error('Error while saving:', error);
      alert('Something went wrong while saving. Please try again.');
    }
  });



  // Add drag and drop handlers
  function addDragAndDropHandlers() {
    let dragSrcEl = null;

    function handleDragStart(e) {
      dragSrcEl = this;
      e.dataTransfer.effectAllowed = 'move';
      e.dataTransfer.setData('text/plain', this.dataset.index);
      this.style.opacity = '0.4';
    }

    function handleDragOver(e) {
      if (e.preventDefault) e.preventDefault();
      e.dataTransfer.dropEffect = 'move';
      return false;
    }

    function handleDrop(e) {
      if (e.stopPropagation) e.stopPropagation();
      const srcIndex = parseInt(e.dataTransfer.getData('text/plain'), 10);
      const targetIndex = parseInt(this.dataset.index, 10);
      if (srcIndex !== targetIndex) {
        const moved = currentLayout.splice(srcIndex, 1)[0];
        currentLayout.splice(targetIndex, 0, moved);
        renderSections();
        layoutDirty = true;
        updatePreview();
      }
      return false;
    }

    function handleDragEnd(e) {
      this.style.opacity = '1';
    }

    const items = document.querySelectorAll('.section-item');
    items.forEach(item => {
      item.addEventListener('dragstart', handleDragStart, false);
      item.addEventListener('dragover', handleDragOver, false);
      item.addEventListener('drop', handleDrop, false);
      item.addEventListener('dragend', handleDragEnd, false);
    });
  }


  function addSectionOfType(sectionType, group) {
    fetch(`/admin/api/get-section-schema.php?section=${sectionType}&preview_theme_id=${themeId}`)
      .then(r => r.json())
      .then(schema => {
        const uniqueId = `${sectionType}-${Date.now().toString(36)}-${Math.random().toString(36).slice(2)}`;
        const presetSettings = schema?.presets?.default?.settings || {};
        const defaultSettings = {};
        (schema.settings || []).forEach(s => { defaultSettings[s.id] = presetSettings[s.id] ?? s.default ?? '' });
        let blocks;
        const presetBlocks = schema?.presets?.default?.blocks;
        if (Array.isArray(presetBlocks) && presetBlocks.length) {
          blocks = presetBlocks.map(pb => {
            const bSchema = (schema.blocks || []).find(b => b.type === pb.type) || {};
            const bs = {};
            (bSchema.settings || []).forEach(s => {
              bs[s.id] = pb.settings?.[s.id] ?? s.default ?? '';
            });
            return { type: pb.type, settings: bs, visible: true };
          });
        } else {
          blocks = (schema.blocks || []).map(b => {
            const bs = {};
            (b.settings || []).forEach(s => { bs[s.id] = s.default ?? ''; });
            return { type: b.type, settings: bs, visible: true };
          });
        }
        let insertIndex = addTargetIndex !== null ? addTargetIndex : currentLayout.length;
        if (insertIndex < 0 || insertIndex > currentLayout.length) insertIndex = currentLayout.length;
        if (addTargetIndex === null) {
          if (group === 'header') {
            insertIndex = currentLayout.findIndex(s => !(s.type.includes('header') || s.type.includes('announcement')));
            if (insertIndex === -1) insertIndex = 0;
          } else if (group === 'template') {
            insertIndex = currentLayout.findIndex(s => s.type.includes('footer'));
            if (insertIndex === -1) insertIndex = currentLayout.length;
          }
        }
        const newSec = { id: uniqueId, type: sectionType, settings: defaultSettings, blocks, visible: true };
        currentLayout.splice(insertIndex, 0, newSec);
        renderSections(); layoutDirty = true; updatePreview();
        addTargetIndex = null;
      }).catch(err => {
        console.error('Failed adding section', err);
        alert('Could not add section');
      });
  }


  // Publish button simply calls save then reloads preview
  document.getElementById('publish-layout-btn').addEventListener('click', async () => {
    await document.getElementById('save-layout-btn').click();
    if (!layoutDirty) {
      alert('Published successfully.');
      updatePreview();
    }
  });

  document.querySelector('#add-section-modal .close-modal').addEventListener('click', () => {
    modal.style.display = 'none';
  });

  function filterCards(q) {
    document.querySelectorAll('#section-card-list .section-card').forEach(card => {
      card.style.display = card.querySelector('h4').textContent.toLowerCase().includes(q.toLowerCase()) ? 'flex' : 'none';
    });
  }
  sectionSearch.addEventListener('input', e => filterCards(e.target.value));
  searchInput.addEventListener('input', renderSections);

  document.querySelectorAll('#section-card-list .section-card').forEach(card => {
    card.addEventListener('click', () => {
      modal.style.display = 'none';
      addSectionOfType(card.dataset.type, addTargetGroup);
      addTargetIndex = null;
    });
  });

  function scrollToSection(index) {
    const sec = currentLayout[index];
    if (!sec) return;
    const id = sec.settings?.custom_id || sec.id;
    try {
      const el = previewFrame.contentWindow.document.getElementById(id);
      if (el) el.scrollIntoView({behavior: 'smooth', block: 'start'});
    } catch (e) {}
  }

  function bindPreviewClicks() {
    try {
      const doc = previewFrame.contentWindow.document;
      currentLayout.forEach((sec, idx) => {
        const id = sec.settings?.custom_id || sec.id;
        const el = doc.getElementById(id);
        if (el) {
          el.addEventListener('click', () => {
            sectionList.style.display = 'none';
            selectedSectionIndex = idx;
            renderSections();
            showCustomizer(idx);
            scrollToSection(idx);
          });
        }
      });
    } catch (e) {}
  }

  previewFrame.addEventListener('load', bindPreviewClicks);


// Update live preview iframe with session layout override
function updatePreview() {
  const visibleLayout = currentLayout
    .filter(sec => sec.visible !== false)
    .map(sec => ({
      ...sec,
      blocks: (sec.blocks || []).filter(b => b.visible !== false)
    }));

  // Send current layout to session for preview rendering
  fetch('/admin/api/set-live-preview.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      page: currentPage,
      layout: visibleLayout
    })
  }).then(() => {
    // After storing in session, reload preview iframe
    const livePage = currentPage;
    previewFrame.src = `/?preview=1&page=${livePage}&t=${Date.now()}`;
  }).catch((err) => {
    console.error('Failed to update preview:', err);
    alert('Preview update failed.');
  });
}

// Page selector change handler
pageSelect.addEventListener('change', () => {
  const selectedPage = pageSelect.value;
  if (selectedPage) {
    loadLayout(selectedPage).then(() => {
      updatePreview(); // ✅ force reload preview
    });
  }
});

// Initial load
loadPages().then(() => {
  pageSelect.value = currentPage;
  pageSelect.dispatchEvent(new Event('change'));
});

  deviceButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      deviceButtons.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      previewFrame.style.width = btn.dataset.width;
    });
  });



  // Define the missing saveLayout function
async function saveLayout() {
  const layoutObj = { sections: {}, order: [] };

  currentLayout.forEach(sec => {
    const id = sec.id || `${sec.type}-${Date.now().toString(36)}-${Math.random().toString(36).slice(2)}`;
    sec.id = id; // Ensure ID is assigned and consistent
    layoutObj.sections[id] = {
      type: sec.type,
      settings: sec.settings || {},
      blocks: sec.blocks || []
    };
    layoutObj.order.push(id);
  });

  const payload = {
    page: currentPage,
    layout: layoutObj
  };

  // Validate JSON before sending
  try {
    JSON.parse(JSON.stringify(payload));
  } catch (e) {
    alert('Layout data is invalid and could not be saved.');
    return;
  }

  try {
    const response = await fetch(`/admin/api/save-layout.php?preview_theme_id=${themeId}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });

    const result = await response.json();
    if (!result.success) throw new Error(result.error || 'Failed to save layout');

    layoutDirty = false;
    console.log('Layout saved successfully!');
  } catch (err) {
    console.error('Error saving layout:', err);
    alert('Failed to save layout. Please try again.');
  }
}
// Sidebar icon switching logic
const sectionsIcon = document.getElementById('sections-icon');
const settingsIcon = document.getElementById('theme-settings-icon');
const codeIcon = document.getElementById('code-icon');

const sectionsSidebar = document.querySelector('.admin-layout .sidebar');
const settingsPanel = document.getElementById('theme-settings-panel');

// Fix: Remove all 'active' classes
function setActiveIcon(icon) {
  document.querySelectorAll('.sidebar-icon').forEach(el => el.classList.remove('active'));
  icon.classList.add('active');
}

// Switch between views
sectionsIcon.addEventListener('click', () => {
  setActiveIcon(sectionsIcon);
  sectionsSidebar.style.display = 'flex';
  settingsPanel.style.display = 'none';
});

settingsIcon.addEventListener('click', () => {
  setActiveIcon(settingsIcon);
  sectionsSidebar.style.display = 'none';
  settingsPanel.style.display = 'flex';

  // Load theme settings if not loaded
  if (!settingsContent.innerHTML.trim()) {
    fetch(`/admin/editor/theme-settings.php?modal=1&preview_theme_id=${themeId}`)
      .then(r => r.text())
      .then(html => {
        const cleaned = html.replace(/<link[^>]*>/i, '');
        settingsContent.insertAdjacentHTML('beforeend', cleaned);
        const script = document.createElement('script');
        script.src = '/admin/assets/theme-settings.js';
        settingsContent.appendChild(script);
      });
  }
});

codeIcon.addEventListener('click', () => {
  setActiveIcon(codeIcon);
  window.open(`/admin/themes/code-editor.php?theme_id=${themeId}`, '_blank');
});


</script>
</body>
</html>