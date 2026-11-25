<?php
require __DIR__ . '/_auth.php';
$title = 'Admin dashboard';
$today = date('Y-m-d');
$todaySales = (float)$pdo->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE DATE(created_at)='$today' AND status<>'cancelled'")->fetchColumn();
$byStatus = $pdo->query("SELECT status, COUNT(*) c FROM orders GROUP BY status")->fetchAll();
$top = $pdo->query("SELECT oi.product_name, SUM(oi.quantity) q FROM order_items oi JOIN orders o ON o.id=oi.order_id WHERE o.status<>'cancelled' GROUP BY oi.product_name ORDER BY q DESC LIMIT 5")->fetchAll();
require __DIR__ . '/../partials/header.php';
?>
<h1>Dashboard</h1>
<p>Today's sales: <strong><?= money($todaySales) ?></strong></p>
<h3>Orders by status</h3>
<ul><?php foreach ($byStatus as $r): ?><li><?= e($r['status']) ?>: <?= (int)$r['c'] ?></li><?php endforeach; ?></ul>
<h3>Top 5 products</h3>
<ol><?php foreach ($top as $r): ?><li><?= e($r['product_name']) ?> (<?= (int)$r['q'] ?>)</li><?php endforeach; ?></ol>
<p><a href="orders.php">All orders</a> | <a href="logout.php">Logout</a></p>
<?php require __DIR__ . '/../partials/footer.php'; ?>
