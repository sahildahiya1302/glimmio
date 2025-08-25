<?php
session_start();

$data = json_decode(file_get_contents("php://input"), true);

$page = $data['page'] ?? '';
$layout = $data['layout'] ?? [];

if (strpos($page, '..') !== false || !preg_match('/^[A-Za-z0-9._\/-]+$/', $page)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid page']);
    exit;
}

$_SESSION['live_preview'][$page] = $layout;

echo json_encode(['success' => true]);
