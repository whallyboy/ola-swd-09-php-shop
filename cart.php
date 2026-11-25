<?php
require __DIR__ . '/app/bootstrap.php';
require __DIR__ . '/app/cart.php';
$title = 'Cart';

$notice = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf'] ?? null)) {
        http_response_code(403);
        exit('Invalid CSRF token');
    }
    $action = $_POST['action'] ?? '';
    if ($action === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        $qty = (int)($_POST['qty'] ?? 0);
        cart_update($id, $qty);
    } elseif ($action === 'remove') {
        cart_remove((int)($_POST['id'] ?? 0));
    } elseif ($action === 'clear') {
        cart_clear();
    }
    header('Location: cart.php');
    exit;
}

$lines = cart_items();
$adjusted = false;
foreach ($lines as $l) {
    if ($l['adjusted']) {
        $adjusted = true;
    }
}
if ($adjusted) {
    $notice = 'Some quantities were adjusted to available stock.';
}

require __DIR__ . '/partials/header.php';
?>
<h1>Cart</h1>
<?php if ($notice): ?>
  <p style="color:#b45309"><?= e($notice) ?></p>
<?php endif; ?>

<?php if (!$lines): ?>
  <p>Your cart is empty. <a href="index.php">Continue shopping</a></p>
<?php else: ?>
  <table style="width:100%;border-collapse:collapse">
    <thead>
      <tr><th style="text-align:left">Product</th><th>Price</th><th>Qty</th><th>Subtotal</th><th></th></tr>
    </thead>
    <tbody>
      <?php foreach ($lines as $l): ?>
        <tr>
          <td><?= e($l['name']) ?></td>
          <td><?= money($l['price']) ?></td>
          <td>
            <form method="post" style="display:inline-flex;gap:4px">
              <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
              <input type="hidden" name="action" value="update">
              <input type="hidden" name="id" value="<?= (int)$l['id'] ?>">
              <input type="number" name="qty" min="1" max="<?= (int)$l['stock'] ?>" value="<?= (int)$l['qty'] ?>" style="width:60px">
              <button class="btn" type="submit">Update</button>
            </form>
          </td>
          <td><?= money($l['subtotal']) ?></td>
          <td>
            <form method="post" style="display:inline">
              <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
              <input type="hidden" name="action" value="remove">
              <input type="hidden" name="id" value="<?= (int)$l['id'] ?>">
              <button class="btn" type="submit" style="background:#dc2626">Remove</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <p style="text-align:right;font-weight:700;margin-top:12px">Total: <?= money(cart_total()) ?></p>
  <form method="post" style="text-align:right">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="action" value="clear">
    <button class="btn" type="submit" style="background:#6b7280">Clear cart</button>
  </form>
  <div style="text-align:right;margin-top:12px">
    <a class="btn" href="checkout.php">Proceed to checkout</a>
  </div>
<?php endif; ?>
<?php require __DIR__ . '/partials/footer.php'; ?>
