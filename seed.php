<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI only');
}
require __DIR__ . '/app/bootstrap.php';

$categories = [
    ['Phones', 'phones'],
    ['Laptops', 'laptops'],
    ['Accessories', 'accessories'],
];

$catIds = [];
$stmt = $pdo->prepare('INSERT INTO categories (name, slug) VALUES (?, ?) ON DUPLICATE KEY UPDATE name = VALUES(name)');
foreach ($categories as [$name, $slug]) {
    $stmt->execute([$name, $slug]);
    $catIds[$slug] = (int)$pdo->lastInsertId() ?: (int)$pdo->query("SELECT id FROM categories WHERE slug = " . $pdo->quote($slug))->fetchColumn();
}

$products = [
    ['iPhone 15 Pro', 'iphone-15-pro', 'phones', 'Flagship smartphone with A17 Pro chip.', 1199.99, 12],
    ['Samsung Galaxy S24', 'samsung-galaxy-s24', 'phones', 'Bright AMOLED display and AI features.', 899.00, 8],
    ['Google Pixel 8', 'google-pixel-8', 'phones', 'Excellent camera and clean Android.', 699.00, 0],
    ['MacBook Air M3', 'macbook-air-m3', 'laptops', 'Thin and light with M3 performance.', 1299.00, 5],
    ['Dell XPS 13', 'dell-xps-13', 'laptops', 'Premium ultrabook with OLED option.', 1099.00, 6],
    ['Lenovo ThinkPad X1', 'lenovo-thinkpad-x1', 'laptops', 'Business laptop with great keyboard.', 1399.00, 3],
    ['HP Pavilion 15', 'hp-pavilion-15', 'laptops', 'Affordable everyday laptop.', 649.00, 10],
    ['USB-C Cable', 'usb-c-cable', 'accessories', 'Durable 2m charging cable.', 9.99, 50],
    ['Wireless Mouse', 'wireless-mouse', 'accessories', 'Ergonomic 2.4GHz mouse.', 24.99, 30],
    ['Mechanical Keyboard', 'mechanical-keyboard', 'accessories', 'RGB backlit mechanical keyboard.', 79.99, 15],
    ['Laptop Stand', 'laptop-stand', 'accessories', 'Aluminum adjustable stand.', 34.99, 20],
    ['Phone Case', 'phone-case', 'accessories', 'Shockproof protective case.', 14.99, 40],
    ['Earbuds Pro', 'earbuds-pro', 'accessories', 'Active noise cancelling earbuds.', 129.99, 18],
    ['Power Bank 20000mAh', 'power-bank-20000', 'accessories', 'Fast charging power bank.', 39.99, 25],
    ['Webcam HD', 'webcam-hd', 'accessories', '1080p webcam with mic.', 49.99, 0],
];

$ins = $pdo->prepare('INSERT INTO products (category_id, name, slug, description, price, stock, image_url, is_active) VALUES (?,?,?,?,?,?,?,1)
    ON DUPLICATE KEY UPDATE name=VALUES(name), description=VALUES(description), price=VALUES(price), stock=VALUES(stock), image_url=VALUES(image_url)');

foreach ($products as [$name, $slug, $catSlug, $desc, $price, $stock]) {
    $img = 'https://placehold.co/600x400?text=' . urlencode($name);
    $ins->execute([$catIds[$catSlug], $name, $slug, $desc, $price, $stock, $img]);
}

echo "Seeded " . count($products) . " products.\n";
