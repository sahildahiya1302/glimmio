<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = intval($_POST['order_id'] ?? 0);
    $status = trim($_POST['status'] ?? '');
    $deliveryPartner = trim($_POST['delivery_partner'] ?? '');
    $trackingNumber = trim($_POST['tracking_number'] ?? '');
    $trackingStatus = trim($_POST['tracking_status'] ?? '');

    if ($orderId > 0 && $status !== '') {
        db_query('UPDATE orders SET status = :status, delivery_partner = :delivery_partner, tracking_number = :tracking_number, tracking_status = :tracking_status, tracking_last_updated = NOW() WHERE id = :id', [
            ':status' => $status,
            ':delivery_partner' => $deliveryPartner,
            ':tracking_number' => $trackingNumber,
            ':tracking_status' => $trackingStatus,
            ':id' => $orderId,
        ]);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid input']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
