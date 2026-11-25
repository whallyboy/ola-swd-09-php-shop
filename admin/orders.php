<?php
require __DIR__ . '/_auth.php';
$title = 'Orders';
$status = $_GET['status'] ?? '';
$date = $_GET['date'] ?? '';
$where = ['1=1'];
$params = [];
if ($status !== '') { $where[] = 'o.status = ?'; $params[] = $status; }
if ($date !== '') { $where[] = 'DATE(o.created_at) = ?'; $params[] = $date; }
$sql = "SELECT o.*, COUNT(oi.product_id) items, COALESCE(SUM(oi.quantity),0) qty FROM orders o LEFT JOIN order_items oi ON oi.order_id=o.id WHERE " . implode(' AND ', $where) . " GROUP BY o.id ORDER BY o.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();
require __DIR__ . '/../partials/header.php';
?>
<h1>Orders</h1>
<form method="get" class="filters">
  <select name="status">
    <option value="">All</option>
    <?php foreach (['pending','paid','shipped','delivered','cancelled'] as $s): ?>
      <option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= $s ?></option>
    <?php endforeach; ?>
  </select>
  <input type="date" name="date" value="<?= e($date) ?>">
  <button class="btn" type="submit">Filter</button>
</form>
<table style="width:100%;border-collapse:collapse">
  <thead><tr><th>ID</th><th>Ref</th><th>Customer</th><th>Status</th><th>Items</th><th>Total</th><th>Date</th><th></th></tr></thead>
  <tbody>
    <?php foreach ($orders as $o): ?>
      <tr>
        <td><?= (int)$o['id'] ?></td>
        <td><?= e($o['reference']) ?></td>
        <td><?= e($o['customer_name']) ?></td>
        <td><?= e($o['status']) ?></td>
        <td><?= (int)$o['items'] ?> (<?= (int)$o['qty'] ?>)</td>
        <td><?= money((float)$o['total']) ?></td>
        <td><?= e($o['created_at']) ?></td>
        <td><a href="order.php?id=<?= (int)$o['id'] ?>">View</a></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php require __DIR__ . '/../partials/footer.php'; ?>
