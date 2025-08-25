<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$customerId = intval($data['customer_id'] ?? 0);
$totalAmount = floatval($data['total_amount'] ?? 0);
$status = trim($data['status'] ?? '');

if ($totalAmount <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Total amount must be greater than zero']);
    exit;
}

if (!$status) {
    http_response_code(400);
    echo json_encode(['error' => 'Status is required']);
    exit;
}

try {
    $utm = getUTMParams();
    db_query('INSERT INTO orders (customer_id, total_amount, status, created_at, utm_source, utm_medium, utm_campaign, utm_term, utm_content) VALUES (:customer_id, :total_amount, :status, NOW(), :utm_source, :utm_medium, :utm_campaign, :utm_term, :utm_content)', [
        ':customer_id' => $customerId > 0 ? $customerId : null,
        ':total_amount' => $totalAmount,
        ':status' => $status,
        ':utm_source' => $utm['utm_source'] ?? null,
        ':utm_medium' => $utm['utm_medium'] ?? null,
        ':utm_campaign' => $utm['utm_campaign'] ?? null,
        ':utm_term' => $utm['utm_term'] ?? null,
        ':utm_content' => $utm['utm_content'] ?? null
    ]);
    
    // Send order confirmation email
    $customerEmail = '';
    if ($customerId > 0) {
        $customer = db_query('SELECT email FROM customers WHERE id = :id', [':id' => $customerId])->fetch();
        if ($customer) {
            $customerEmail = $customer['email'];
        }
    }
    if ($customerEmail) {
        $subject = 'Order Confirmation';
        $message = "Thank you for your order. Your order has been successfully placed.";
        send_mail($customerEmail, $subject, $message, 'no-reply@example.com');
    }
    
    echo json_encode(['success' => true, 'message' => 'Order created']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
