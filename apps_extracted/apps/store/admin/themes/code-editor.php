<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /backend/auth/login.php');
    exit;
}

$pageTitle = 'Theme Code Editor';
$themeId = $_GET['theme_id'] ?? ($_SESSION['active_theme'] ?? 'default');

// Security: Ensure theme_id is valid
$allowedThemes = ['default', 'theme11', 'theme12', 'theme13'];
if (!in_array($themeId, $allowedThemes)) {
    $themeId = 'default';
}

$themePath = __DIR__ . '/../../themes/' . $themeId;
$file = $_GET['file'] ?? '';
$action = $_POST['action'] ?? '';

// Handle file operations
if ($action === 'save' && isset($_POST['content']) && isset($_POST['file'])) {
    $fileToSave = realpath($themePath . '/' . $_POST['file']);
    
    // Security check: ensure file is within theme directory
    if ($fileToSave && strpos($fileToSave, realpath($themePath)) === 0) {
        file_put_contents($fileToSave, $_POST['content']);
        $message = 'File saved successfully!';
    } else {
        $error = 'Invalid file path';
    }
}

// Get all theme files
function getThemeFiles($dir, $basePath = '') {
    $files = [];
    if (!is_dir($dir)) return $files;
    
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        
        $path = $dir . '/' . $item;
        $relativePath = $basePath ? $basePath . '/' . $item : $item;
        
        if (is_dir($path)) {
            $files = array_merge($files, getThemeFiles($path, $relativePath));
        } else {
            $files[] = $relativePath;
        }
    }
    
    return $files;
}

$themeFiles = getThemeFiles($themePath);

// Get file content for editing
$fileContent = '';
if ($file && in_array($file, $themeFiles)) {
    $filePath = $themePath . '/' . $file;
    if (file_exists($filePath)) {
        $fileContent = file_get_contents($filePath);
    }
}

// Get available themes
$themes = [];
$themesDir = __DIR__ . '/../../themes';
foreach (glob($themesDir . '/*', GLOB_ONLYDIR) as $themeDir) {
    $themeName = basename($themeDir);
    $themes[] = $themeName;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/monokai.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #1e1e1e;
            color: #d4d4d4;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .header {
            background: #252526;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #3e3e42;
        }
        
        .header h1 {
            font-size: 18px;
            color: #cccccc;
        }
        
        .header-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .theme-selector {
            padding: 8px 12px;
            background: #3c3c3c;
            color: #cccccc;
            border: 1px solid #5a5a5a;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }
        
        .btn-primary {
            background: #0e639c;
            color: white;
        }
        
        .btn-primary:hover {
            background: #1177bb;
        }
        
        .btn-success {
            background: #16825d;
            color: white;
        }
        
        .btn-success:hover {
            background: #1e9e6f;
        }
        
        .main-container {
            display: flex;
            flex: 1;
            overflow: hidden;
        }
        
        .sidebar {
            width: 250px;
            background: #252526;
            border-right: 1px solid #3e3e42;
            display: flex;
            flex-direction: column;
        }
        
        .sidebar-header {
            padding: 15px;
            border-bottom: 1px solid #3e3e42;
            font-weight: 600;
            color: #cccccc;
        }
        
        .file-tree {
            flex: 1;
            overflow-y: auto;
            padding: 10px;
        }
        
        .file-item {
            padding: 5px 10px;
            cursor: pointer;
            font-size: 13px;
            color: #cccccc;
            display: flex;
            align-items: center;
            gap: 5px;
            border-radius: 3px;
            margin-bottom: 2px;
        }
        
        .file-item:hover {
            background: #2a2d2e;
        }
        
        .file-item.active {
            background: #094771;
        }
        
        .file-item i {
            font-size: 12px;
            width: 16px;
        }
        
        .editor-container {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .editor-header {
            padding: 10px 15px;
            background: #252526;
            border-bottom: 1px solid #3e3e42;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .file-path {
            font-size: 14px;
            color: #cccccc;
        }
        
        .editor-actions {
            display: flex;
            gap: 10px;
        }
        
        .editor-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        #code-editor {
            flex: 1;
            font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
            font-size: 14px;
            line-height: 1.5;
        }
        
        .CodeMirror {
            height: 100% !important;
            font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
            font-size: 14px;
        }
        
        .message {
            padding: 10px 15px;
            margin: 10px;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .message.success {
            background: #16825d;
            color: white;
        }
        
        .message.error {
            background: #f14c4c;
            color: white;
        }
        
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #cccccc;
            font-size: 16px;
        }
        
        .empty-state i {
            font-size: 48px;
            margin-bottom: 10px;
            color: #5a5a5a;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><i class="fas fa-code"></i> Theme Code Editor</h1>
        <div class="header-actions">
            <select class="theme-selector" onchange="switchTheme(this.value)">
                <?php foreach ($themes as $theme): ?>
                    <option value="<?php echo htmlspecialchars($theme); ?>" <?php echo $theme === $themeId ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($theme); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-success" onclick="saveFile()">
                <i class="fas fa-save"></i> Save
            </button>
            <button class="btn btn-primary" onclick="previewTheme()">
                <i class="fas fa-eye"></i> Preview
            </button>
        </div>
    </div>

    <div class="main-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-folder"></i> Files
            </div>
            <div class="file-tree">
                <?php foreach ($themeFiles as $file): ?>
                    <div class="file-item <?php echo $file === $file ? 'active' : ''; ?>" 
                         onclick="loadFile('<?php echo htmlspecialchars($file); ?>')">
                        <i class="fas fa-file-code"></i>
                        <?php echo htmlspecialchars($file); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="editor-container">
            <div class="editor-header">
                <div class="file-path">
                    <?php echo $file ? htmlspecialchars($file) : 'No file selected'; ?>
                </div>
                <div class="editor-actions">
                    <span id="save-status"></span>
                </div>
            </div>
            <div class="editor-content">
                <?php if ($message): ?>
                    <div class="message success"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="message error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <?php if ($file): ?>
                    <textarea id="code-editor"><?php echo htmlspecialchars($fileContent); ?></textarea>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-file-code"></i>
                        <p>Select a file to edit</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/xml/xml.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/javascript/javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/css/css.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/php/php.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/htmlmixed/htmlmixed.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/liquid/liquid.min.js"></script>

    <script>
        let editor;
        let currentFile = '<?php echo addslashes($file); ?>';
        let currentTheme = '<?php echo addslashes($themeId); ?>';

        // Initialize CodeMirror
        function initEditor() {
            if (editor) {
                editor.toTextArea();
            }
            
            const textarea = document.getElementById('code-editor');
            if (!textarea) return;

            const mode = getModeFromFile(currentFile);
            
            editor = CodeMirror.fromTextArea(textarea, {
                mode: mode,
                theme: 'monokai',
                lineNumbers: true,
                lineWrapping: true,
                autoCloseTags: true,
                autoCloseBrackets: true,
                matchBrackets: true,
                indentUnit: 2,
                tabSize: 2,
                indentWithTabs: false
            });
        }

        function getModeFromFile(filename) {
            const ext = filename.split('.').pop().toLowerCase();
            const modeMap = {
                'html': 'htmlmixed',
                'htm': 'htmlmixed',
                'php': 'php',
                'js': 'javascript',
                'css': 'css',
                'json': 'javascript',
                'liquid': 'liquid',
                'xml': 'xml'
            };
            return modeMap[ext] || 'htmlmixed';
        }

        function loadFile(file) {
            currentFile = file;
            window.location.href = `?theme_id=${currentTheme}&file=${encodeURIComponent(file)}`;
        }

        function switchTheme(theme) {
            currentTheme = theme;
            window.location.href = `?theme_id=${theme}`;
        }

        function saveFile() {
            if (!editor || !currentFile) return;

            const content = editor.getValue();
            
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=save&file=${encodeURIComponent(currentFile)}&content=${encodeURIComponent(content)}`
            })
            .then(response => response.text())
            .then(() => {
                document.getElementById('save-status').innerHTML = '<i class="fas fa-check" style="color: #28a745;"></i> Saved';
                setTimeout(() => {
                    document.getElementById('save-status').innerHTML = '';
                }, 2000);
            })
            .catch(error => {
                document.getElementById('save-status').innerHTML = '<i class="fas fa-times" style="color: #dc3545;"></i> Error';
            });
        }

        function previewTheme() {
            window.open(`/?theme=${currentTheme}`, '_blank');
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('code-editor')) {
                initEditor();
            }
        });
    </script>
</body>
</html>
