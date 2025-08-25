<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');
$stmt = $pdo->query('SELECT * FROM page_templates ORDER BY created_at DESC');
$templates = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($templates);
