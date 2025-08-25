<?php
declare(strict_types=1);
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$uploadDir = __DIR__ . '/../../uploads/themes/images/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if (!isset($_FILES['image'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No image uploaded']);
    exit;
}

$file = $_FILES['image'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'Image upload error']);
    exit;
}

$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
if (!in_array($file['type'], $allowedTypes)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid image type']);
    exit;
}

$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid('img_', true) . ($ext ? '.' . $ext : '');
$targetPath = $uploadDir . $filename;

if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to move uploaded image']);
    exit;
}

echo json_encode([
    'success'  => true,
    'message'  => 'Image uploaded',
    'filename' => $filename
]);

