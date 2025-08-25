<?php
declare(strict_types=1);
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$phone = trim($data['phone'] ?? '');
$message = trim($data['message'] ?? '');

if (!$phone) {
    http_response_code(400);
    echo json_encode(['error' => 'Phone number is required']);
    exit;
}

if (!$message) {
    http_response_code(400);
    echo json_encode(['error' => 'Message is required']);
    exit;
}

// Placeholder for SMS sending logic, e.g., using Twilio or other service
// For now, simulate success

echo json_encode(['success' => true, 'message' => 'SMS sent to ' . $phone]);
