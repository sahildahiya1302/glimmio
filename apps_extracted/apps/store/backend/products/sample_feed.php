<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename=sample_product_feed.csv');

echo "Handle,Title,Body (HTML),Vendor,Type,Tags,Option1 Name,Option1 Value,Option2 Name,Option2 Value,SKU,Price / India,Image Src\n";

$products = [
    [
        'handle' => 'classic-white-shirt',
        'title' => 'Classic White Shirt',
        'body' => '<p>Perfect fit</p>',
        'vendor' => 'Vendor',
        'type' => 'Fashion',
        'tags' => 'white|shirt',
        'price' => 999,
        'image' => 'https://cdn.example.com/p1.jpg'
    ],
    [
        'handle' => 'beaded-bangle-set',
        'title' => 'Beaded Bangle Set',
        'body' => '<p>Handcrafted set of 3</p>',
        'vendor' => 'Vendor',
        'type' => 'Jewelry',
        'tags' => 'bangle|handmade',
        'price' => 499,
        'image' => 'https://cdn.example.com/b1.jpg'
    ],
    [
        'handle' => 'marble-plate',
        'title' => 'Decorative Marble Plate',
        'body' => '<p>Carved floral design</p>',
        'vendor' => 'Vendor',
        'type' => 'Home Decor',
        'tags' => 'marble|plate',
        'price' => 1299,
        'image' => 'https://cdn.example.com/m1.jpg'
    ]
];

$sizes = ['Small', 'Medium', 'Large'];
$colors = ['Red', 'Black', 'White'];

foreach ($products as $product) {
    foreach ($sizes as $size) {
        foreach ($colors as $color) {
            $sku = strtoupper(substr($product['handle'], 0, 6)) . '-' . strtoupper($size[0]) . strtoupper($color[0]);
            echo "{$product['handle']},"
                . "{$product['title']},"
                . "{$product['body']},"
                . "{$product['vendor']},"
                . "{$product['type']},"
                . "{$product['tags']},"
                . "Size,"
                . "{$size},"
                . "Color,"
                . "{$color},"
                . "{$sku},"
                . "{$product['price']},"
                . "{$product['image']}\n";
        }
    }
}
