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

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    // Fetch plugin info
    $plugin = db_query('SELECT * FROM plugins WHERE id = :id', [':id' => $id])->fetch();

    if ($plugin) {
        // Delete plugin files - assuming plugins are stored in /plugins/{plugin_name}
        $pluginDir = __DIR__ . '/../plugins/' . $plugin['name'];
        if (is_dir($pluginDir)) {
            // Recursively delete directory
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

        // Delete plugin record from database
        db_query('DELETE FROM plugins WHERE id = :id', [':id' => $id]);
    }
}

header('Location: /admin/plugins.php');
exit;
?>
