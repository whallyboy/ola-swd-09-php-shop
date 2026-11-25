<?php
require __DIR__ . '/app/bootstrap.php';
require __DIR__ . '/app/cart.php';
$title = 'Checkout';

$errors = [];
$success = false;
$lines = cart_items();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf'] ?? null)) {
        http_response_code(403);
        exit('Invalid CSRF token');
    }
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($name === '') $errors[] = 'Name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if ($phone === '') $errors[] = 'Phone is required.';
    if ($address === '') $errors[] = 'Address is required.';
    if (!$lines) $errors[] = 'Your cart is empty.';

    if (!$errors) {
        try {
            $pdo->beginTransaction();
            $total = 0.0;
            $locked = [];
            foreach ($lines as $l) {
                $stmt = $pdo->prepare('SELECT id, name, price, stock, is_active FROM products WHERE id = ? FOR UPDATE');
                $stmt->execute([(int)$l['id']]);
                $p = $stmt->fetch();
                if (!$p || !(int)$p['is_active']) {
                    throw new RuntimeException('A product is no longer available.');
                }
                if ((int)$p['stock'] < (int)$l['qty']) {
                    throw new RuntimeException('Insufficient stock for ' . $p['name'] . '.');
                }
                $locked[] = ['product' => $p, 'qty' => (int)$l['qty']];
                $total += (float)$p['price'] * (int)$l['qty'];
            }

            $ref = 'ORD-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));
            $insOrder = $pdo->prepare('INSERT INTO orders (reference, customer_name, email, phone, address, total) VALUES (?,?,?,?,?,?)');
            $insOrder->execute([$ref, $name, $email, $phone, $address, $total]);
            $orderId = (int)$pdo->lastInsertId();

            $insItem = $pdo->prepare('INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity) VALUES (?,?,?,?,?)');
            foreach ($locked as $row) {
                $p = $row['product'];
                $insItem->execute([$orderId, (int)$p['id'], $p['name'], (float)$p['price'], $row['qty']]);
            }

            foreach ($locked as $row) {
                $p = $row['product'];
                $upd = $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?');
                $upd->execute([$row['qty'], (int)$p['id'], $row['qty']]);
                if ($upd->rowCount() === 0) {
                    throw new RuntimeException('Stock changed during checkout for ' . $p['name'] . '.');
                }
            }

            $pdo->commit();
            cart_clear();
            header('Location: order-success.php?ref=' . urlencode($ref));
            exit;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errors[] = 'Checkout failed: ' . $e->getMessage();
        }
    }
}

require __DIR__ . '/partials/header.php';
?>
<h1>Checkout</h1>
<?php if ($errors): ?>
  <ul style="color:#dc2626">
    <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
  </ul>
<?php endif; ?>

<?php if ($lines): ?>
  <h3>Order summary</h3>
  <table style="width:100%;border-collapse:collapse;margin-bottom:16px">
    <thead><tr><th style="text-align:left">Product</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr></thead>
    <tbody>
      <?php foreach ($lines as $l): ?>
        <tr>
          <td><?= e($l['name']) ?></td>
          <td><?= (int)$l['qty'] ?></td>
          <td><?= money($l['price']) ?></td>
          <td><?= money($l['subtotal']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <p style="text-align:right;font-weight:700">Total: <?= money(cart_total()) ?></p>
<?php endif; ?>

<form method="post">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
  <div class="form-row"><label>Name</label><input name="name" value="<?= e($_POST['name'] ?? '') ?>" required></div>
  <div class="form-row"><label>Email</label><input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required></div>
  <div class="form-row"><label>Phone</label><input name="phone" value="<?= e($_POST['phone'] ?? '') ?>" required></div>
  <div class="form-row"><label>Delivery address</label><textarea name="address" rows="3" style="width:100%;padding:8px;border:1px solid #d1d5db;border-radius:6px" required><?= e($_POST['address'] ?? '') ?></textarea></div>
  <button class="btn" type="submit">Place order</button>
</form>
<?php require __DIR__ . '/partials/footer.php'; ?>
