<?php
require __DIR__ . '/app/bootstrap.php';
$title = 'Order lookup';
$order = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ref = trim($_POST['reference'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $stmt = $pdo->prepare('SELECT reference, status, total, created_at FROM orders WHERE reference = ? AND email = ?');
    $stmt->execute([$ref, $email]);
    $order = $stmt->fetch();
    $notfound = !$order;
}
require __DIR__ . '/partials/header.php';
?>
<h1>Order lookup</h1>
<form method="post">
  <div class="form-row"><label>Order reference</label><input name="reference" required></div>
  <div class="form-row"><label>Email</label><input type="email" name="email" required></div>
  <button class="btn" type="submit">Check status</button>
</form>
<?php if (isset($notfound) && $notfound): ?>
  <p style="color:#dc2626">No matching order found.</p>
<?php elseif ($order): ?>
  <p>Reference: <?= e($order['reference']) ?></p>
  <p>Status: <?= e($order['status']) ?></p>
  <p>Total: <?= money((float)$order['total']) ?></p>
  <p>Placed: <?= e($order['created_at']) ?></p>
<?php endif; ?>
<?php require __DIR__ . '/partials/footer.php'; ?>
