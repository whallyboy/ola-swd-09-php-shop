<?php
require __DIR__ . '/app/bootstrap.php';
$title = 'Order placed';
$ref = $_GET['ref'] ?? '';
$stmt = $pdo->prepare('SELECT * FROM orders WHERE reference = ?');
$stmt->execute([$ref]);
$order = $stmt->fetch();
require __DIR__ . '/partials/header.php';
?>
<?php if ($order): ?>
  <h1>Thank you!</h1>
  <p>Your order <strong><?= e($order['reference']) ?></strong> has been placed.</p>
  <p>Total: <?= money((float)$order['total']) ?></p>
  <p><a class="btn" href="index.php">Continue shopping</a></p>
<?php else: ?>
  <p>Order not found.</p>
<?php endif; ?>
<?php require __DIR__ . '/partials/footer.php'; ?>
