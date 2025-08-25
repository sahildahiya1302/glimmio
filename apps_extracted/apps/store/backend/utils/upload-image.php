<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_FILES['product_file']) || $_FILES['product_file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
    exit;
}

$uploadDir = __DIR__ . '/../../uploads/products/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$fileTmpPath = $_FILES['product_file']['tmp_name'];
$fileName = basename($_FILES['product_file']['name']);
$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
$allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

if (!in_array($fileExt, $allowedExts)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid file type']);
    exit;
}

$newFileName = uniqid('prod_img_') . '.' . $fileExt;
$destPath = $uploadDir . $newFileName;

if (!move_uploaded_file($fileTmpPath, $destPath)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
    exit;
}

// Return the public URL of the uploaded image
$publicUrl = '/uploads/products/' . $newFileName;

echo json_encode(['success' => true, 'url' => $publicUrl]);
?>
