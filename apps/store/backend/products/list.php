<?php
declare(strict_types=1);
require_once __DIR__ . '/../../db.php';
header('Content-Type: application/json');

try {
    $conditions = [];
    $params = [];
    $joins = '';
    $sort = $_GET['sort'] ?? 'created_at_desc';
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 0;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

    if (!empty($_GET['q'])) {
        $conditions[] = '(p.title LIKE :q OR p.tags LIKE :q OR p.type LIKE :q)';
        $params['q'] = '%' . $_GET['q'] . '%';
    }

    if (!empty($_GET['collection_id'])) {
        $joins .= ' JOIN collection_products cp ON cp.product_id = p.id';
        $conditions[] = 'cp.collection_id = :collection_id';
        $params['collection_id'] = (int)$_GET['collection_id'];
    }

    if (!empty($_GET['type'])) {
        $conditions[] = 'p.type = :type';
        $params['type'] = $_GET['type'];
    }

    if (!empty($_GET['vendor'])) {
        $conditions[] = 'p.vendor = :vendor';
        $params['vendor'] = $_GET['vendor'];
    }

    if (!empty($_GET['tags'])) {
        $conditions[] = 'p.tags LIKE :tags';
        $params['tags'] = '%' . $_GET['tags'] . '%';
    }

    if (!empty($_GET['min_price']) || !empty($_GET['max_price']) || isset($_GET['on_sale'])) {
        $joins .= ' JOIN product_variants v ON v.product_id = p.id';
        if ($_GET['min_price'] !== '') {
            $conditions[] = 'v.price >= :min_price';
            $params['min_price'] = (float)$_GET['min_price'];
        }
        if ($_GET['max_price'] !== '') {
            $conditions[] = 'v.price <= :max_price';
            $params['max_price'] = (float)$_GET['max_price'];
        }
        if (isset($_GET['on_sale']) && $_GET['on_sale'] === '1') {
            $conditions[] = 'v.compare_price > v.price';
        }
    }

    $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
    $order = 'p.created_at DESC';
    if ($sort === 'price_asc') $order = 'min_price ASC';
    elseif ($sort === 'price_desc') $order = 'min_price DESC';

    $sql = "SELECT p.*,
            (SELECT image_url FROM product_variants WHERE product_id = p.id AND image_url IS NOT NULL AND image_url != '' ORDER BY id ASC LIMIT 1) AS image_url,
            MIN(v.price) AS min_price, MAX(v.price) AS max_price,
            (SELECT COUNT(*) FROM product_variants WHERE product_id = p.id) AS variant_count
            FROM products p LEFT JOIN product_variants v ON v.product_id = p.id
            $joins $where GROUP BY p.id ORDER BY $order";
    if ($limit > 0) {
        $sql .= " LIMIT $limit";
        if ($offset > 0) $sql .= " OFFSET $offset";
    }

    // Get total count for pagination
    $countSql = "SELECT COUNT(DISTINCT p.id) FROM products p $joins $where";
    $countStmt = db()->prepare($countSql);
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'products' => $products, 'total' => $total]);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Error loading products: ' . $e->getMessage()]);
}