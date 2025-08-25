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

$query = trim($_GET['q'] ?? '');

if (!$query) {
    echo json_encode(['results' => []]);
    exit;
}

$results = [];

// Search products
$products = db_query('SELECT id, title FROM products WHERE title LIKE :query LIMIT 10', [
        ':query' => "%$query%",
])->fetchAll();
foreach ($products as $product) {
    $results[] = ['type' => 'product', 'id' => $product['id'], 'title' => $product['title']];
}

// Search collections
$collections = db_query('SELECT id, title FROM collections WHERE title LIKE :query LIMIT 10', [
        ':query' => "%$query%",
])->fetchAll();
foreach ($collections as $collection) {
    $results[] = ['type' => 'collection', 'id' => $collection['id'], 'title' => $collection['title']];
}

// Search customers
$customers = db_query('SELECT id, first_name, last_name FROM customers WHERE first_name LIKE :query OR last_name LIKE :query LIMIT 10', [
        ':query' => "%$query%",
])->fetchAll();
foreach ($customers as $customer) {
    $results[] = ['type' => 'customer', 'id' => $customer['id'], 'name' => $customer['first_name'] . ' ' . $customer['last_name']];
}

echo json_encode(['results' => $results]);
