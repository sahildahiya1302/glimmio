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

$data = json_decode(file_get_contents('php://input'), true);

$code = trim($data['code'] ?? '');
$description = trim($data['description'] ?? '');
$amount = floatval($data['amount'] ?? 0);
$active = boolval($data['active'] ?? false);

if (!$code) {
    http_response_code(400);
    echo json_encode(['error' => 'Code is required']);
    exit;
}

if ($amount <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Amount must be greater than zero']);
    exit;
}

try {
    db_query('INSERT INTO discounts (code, description, amount, active, created_at) VALUES (:code, :description, :amount, :active, NOW())', [
        ':code' => $code,
        ':description' => $description,
        ':amount' => $amount,
        ':active' => $active ? 1 : 0,
    ]);
    echo json_encode(['success' => true, 'message' => 'Discount created']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
