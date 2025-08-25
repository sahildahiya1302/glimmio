<?php
// API endpoint to get and save global theme settings

require_once __DIR__ . '/../../config.php';

$method = $_SERVER['REQUEST_METHOD'];

$settingsFile = THEME_PATH . "/config/settings_data.json";

if ($method === 'GET') {
    $settings = [];
    if (file_exists($settingsFile)) {
        $settings = json_decode(file_get_contents($settingsFile), true) ?? [];
    }
    // merge DB values
    try {
        $rows = db_query('SELECT `key`, `value` FROM theme_settings WHERE theme_id = :tid', [':tid' => THEME_ID])->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $settings[$row['key']] = $row['value'];
        }
    } catch (Throwable $e) {
        // ignore
    }
    header('Content-Type: application/json');
    echo json_encode($settings);
    exit;
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON']);
        exit;
    }
    if (!is_writable(dirname($settingsFile))) {
        http_response_code(500);
        echo json_encode(['error' => 'Cannot write settings file']);
        exit;
    }
    file_put_contents($settingsFile, json_encode($data, JSON_PRETTY_PRINT));
    // persist to DB
    try {
        db_query('DELETE FROM theme_settings WHERE theme_id = :tid', [':tid' => THEME_ID]);
        $stmt = db()->prepare('INSERT INTO theme_settings (theme_id, `key`, `value`) VALUES (:tid, :k, :v)');
        foreach ($data as $k => $v) {
            $stmt->execute([':tid' => THEME_ID, ':k' => $k, ':v' => (string)$v]);
        }
    } catch (Throwable $e) {
        // ignore errors
    }
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
exit;
