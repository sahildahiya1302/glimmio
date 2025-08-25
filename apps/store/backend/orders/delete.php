<?php
declare(strict_types=1);
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
    return;
}

$data = json_decode(file_get_contents('php://input'), true) ?? [];
if (!isset($data['id'])) {
    jsonResponse(['error' => 'Invalid payload'], 400);
    return;
}

db_query('DELETE FROM orders WHERE id = :id', [':id' => $data['id']]);
jsonResponse(['success' => true]);
