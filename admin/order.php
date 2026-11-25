<?php
require __DIR__ . '/_auth.php';
$title = 'Order detail';
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ?');
$stmt->execute([$id]);
$order = $stmt->fetch();
if (!$order) { http_response_code(404); exit('Not found'); }
$items = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ?');
$items->execute([$id]);
$items = $items->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify($_POST['csrf'] ?? null)) {
    $newStatus = $_POST['status'] ?? $order['status'];
    $allowed = ['pending','paid','shipped','delivered','cancelled'];
    if (in_array($newStatus, $allowed, true)) {
        try {
            $pdo->beginTransaction();
            if ($newStatus === 'cancelled' && $order['status'] !== 'cancelled') {
                $rest = $pdo->prepare('UPDATE products p JOIN order_items oi ON oi.product_id=p.id SET p.stock = p.stock + oi.quantity WHERE oi.order_id = ?');
                $rest->execute([$id]);
            }
            $upd = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
            $upd->execute([$newStatus, $id]);
            $pdo->commit();
            header('Location: order.php?id=' . $id);
            exit;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = $e->getMessage();
        }
    }
}
require __DIR__ . '/../partials/header.php';
?>
<h1>Order <?= e($order['reference']) ?></h1>
<p>Status: <?= e($order['status']) ?> | Total: <?= money((float)$order['total']) ?></p>
<p><?= e($order['customer_name']) ?> | <?= e($order['email']) ?> | <?= e($order['phone']) ?></p>
<p><?= nl2br(e($order['address'])) ?></p>
<table style="width:100%;border-collapse:collapse">
  <thead><tr><th>Product</th><th>Qty</th><th>Unit</th><th>Subtotal</th></tr></thead>
  <tbody>
    <?php foreach ($items as $it): ?>
      <tr>
        <td><?= e($it['product_name']) ?></td>
        <td><?= (int)$it['quantity'] ?></td>
        <td><?= money((float)$it['unit_price']) ?></td>
        <td><?= money((float)$it['unit_price'] * (int)$it['quantity']) ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<form method="post" style="margin-top:16px">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
  <select name="status">
    <?php foreach (['pending','paid','shipped','delivered','cancelled'] as $s): ?>
      <option value="<?= $s ?>" <?= $order['status']===$s?'selected':'' ?>><?= $s ?></option>
    <?php endforeach; ?>
  </select>
  <button class="btn" type="submit">Update status</button>
</form>
<?php if (!empty($error)): ?><p style="color:#dc2626"><?= e($error) ?></p><?php endif; ?>
<p><a href="orders.php">Back</a></p>
<?php require __DIR__ . '/../partials/footer.php'; ?>
