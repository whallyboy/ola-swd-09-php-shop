<?php
require_once __DIR__ . '/bootstrap.php';

function cart_add(int $id, int $qty): void
{
    if ($id <= 0 || $qty <= 0) {
        return;
    }
    $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + $qty;
}

function cart_update(int $id, int $qty): void
{
    if ($id <= 0) {
        return;
    }
    if ($qty <= 0) {
        unset($_SESSION['cart'][$id]);
        return;
    }
    $_SESSION['cart'][$id] = $qty;
}

function cart_remove(int $id): void
{
    unset($_SESSION['cart'][$id]);
}

function cart_clear(): void
{
    $_SESSION['cart'] = [];
}

function cart_items(): array
{
    global $pdo;
    $cart = $_SESSION['cart'] ?? [];
    if (!$cart) {
        return [];
    }
    $ids = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT id, name, slug, price, stock, image_url, is_active FROM products WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $rows = $stmt->fetchAll();
    $byId = [];
    foreach ($rows as $r) {
        $byId[(int)$r['id']] = $r;
    }
    $lines = [];
    foreach ($cart as $id => $qty) {
        $id = (int)$id;
        if (!isset($byId[$id])) {
            continue;
        }
        $p = $byId[$id];
        if (!(int)$p['is_active']) {
            continue;
        }
        $qty = (int)$qty;
        $stock = (int)$p['stock'];
        $adjusted = false;
        if ($qty > $stock) {
            $qty = $stock;
            $adjusted = true;
            if ($qty <= 0) {
                unset($_SESSION['cart'][$id]);
                continue;
            }
            $_SESSION['cart'][$id] = $qty;
        }
        $price = (float)$p['price'];
        $lines[] = [
            'product'  => $p,
            'id'       => $id,
            'name'     => $p['name'],
            'slug'     => $p['slug'],
            'price'    => $price,
            'qty'      => $qty,
            'stock'    => $stock,
            'subtotal' => $price * $qty,
            'adjusted' => $adjusted,
        ];
    }
    return $lines;
}

function cart_total(): float
{
    $total = 0.0;
    foreach (cart_items() as $line) {
        $total += $line['subtotal'];
    }
    return $total;
}
