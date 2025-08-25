<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';


// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /backend/auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['plugin_file'])) {
    $file = $_FILES['plugin_file'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        $tmpName = $file['tmp_name'];
        $zip = new ZipArchive();
        if ($zip->open($tmpName) === true) {
            // Extract to plugins directory
            $pluginsDir = __DIR__ . '/../plugins/';
            if (!is_dir($pluginsDir)) {
                mkdir($pluginsDir, 0755, true);
            }

            // Extract to a temp folder to read plugin info
            $tempDir = sys_get_temp_dir() . '/plugin_' . uniqid();
            mkdir($tempDir, 0755, true);
            $zip->extractTo($tempDir);
            $zip->close();

            // Read plugin info from plugin.json
            $pluginJsonPath = $tempDir . '/plugin.json';
            if (file_exists($pluginJsonPath)) {
                $pluginInfo = json_decode(file_get_contents($pluginJsonPath), true);
                if ($pluginInfo && isset($pluginInfo['name'])) {
                    $pluginName = $pluginInfo['name'];
                    $pluginDir = $pluginsDir . $pluginName;

                    // Move extracted files to plugins directory
                    if (is_dir($pluginDir)) {
                        // Remove existing plugin directory
                        $it = new RecursiveDirectoryIterator($pluginDir, RecursiveDirectoryIterator::SKIP_DOTS);
                        $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
                        foreach ($files as $file) {
                            if ($file->isDir()) {
                                rmdir($file->getRealPath());
                            } else {
                                unlink($file->getRealPath());
                            }
                        }
                        rmdir($pluginDir);
                    }
                    rename($tempDir, $pluginDir);

                    // Insert plugin info into database
                    db_query('INSERT INTO plugins (name, description, version, enabled) VALUES (:name, :description, :version, 0)', [
                        ':name' => $pluginName,
                        ':description' => $pluginInfo['description'] ?? '',
                        ':version' => $pluginInfo['version'] ?? '1.0.0',
                    ]);

                    $_SESSION['flash_message'] = 'Plugin installed successfully.';
                } else {
                    $_SESSION['flash_message'] = 'Invalid plugin.json file.';
                    // Cleanup temp dir
                    rmdir($tempDir);
                }
            } else {
                $_SESSION['flash_message'] = 'plugin.json file not found in ZIP.';
                // Cleanup temp dir
                rmdir($tempDir);
            }
        } else {
            $_SESSION['flash_message'] = 'Failed to open ZIP file.';
        }
    } else {
        $_SESSION['flash_message'] = 'File upload error.';
    }
} else {
    $_SESSION['flash_message'] = 'Invalid request.';
}

header('Location: /admin/plugins.php');
exit;
?>
