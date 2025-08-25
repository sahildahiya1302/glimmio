<?php
declare(strict_types=1);
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

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

$allowedTypes = ['image/jpeg', 'image/png'];
if (!in_array($file['type'], $allowedTypes)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid image type']);
    exit;
}

$sourcePath = $file['tmp_name'];
$destinationPath = __DIR__ . '/../../uploads/compressed/' . basename($file['name']);

try {
    if ($file['type'] === 'image/jpeg') {
        $image = imagecreatefromjpeg($sourcePath);
        imagejpeg($image, $destinationPath, 75);
        imagedestroy($image);
    } elseif ($file['type'] === 'image/png') {
        $image = imagecreatefrompng($sourcePath);
        imagepng($image, $destinationPath, 6);
        imagedestroy($image);
    }
    echo json_encode(['success' => true, 'message' => 'Image compressed', 'filename' => basename($file['name'])]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Image compression failed: ' . $e->getMessage()]);
}
