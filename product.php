<?php
require __DIR__ . '/app/bootstrap.php';
require __DIR__ . '/app/cart.php';

$slug = $_GET['slug'] ?? '';
$stmt = $pdo->prepare('SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON c.id = p.category_id WHERE p.slug = ? AND p.is_active = 1');
$stmt->execute([$slug]);
$product = $stmt->fetch();

if (!$product) {
    http_response_code(404);
    $title = 'Not found';
    require __DIR__ . '/partials/header.php';
    echo '<p>Product not found.</p>';
    require __DIR__ . '/partials/footer.php';
    exit;
}

$title = $product['name'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf'] ?? null;
    if (!csrf_verify($token)) {
        http_response_code(403);
        exit('Invalid CSRF token');
    }
    $qty = max(1, (int)($_POST['qty'] ?? 1));
    if ($qty > (int)$product['stock']) {
        $qty = (int)$product['stock'];
    }
    if ($qty < 1) {
        $qty = 1;
    }
    cart_add((int)$product['id'], $qty);
    header('Location: product.php?slug=' . urlencode($slug) . '&added=1');
    exit;
}

require __DIR__ . '/partials/header.php';
?>
<div class="detail">
  <img src="<?= e($product['image_url']) ?>" alt="<?= e($product['name']) ?>">
  <div>
    <span class="muted"><?= e($product['category_name']) ?></span>
    <h1><?= e($product['name']) ?></h1>
    <div class="price"><?= money((float)$product['price']) ?></div>
    <p><?= nl2br(e($product['description'])) ?></p>
    <?php if (isset($_GET['added'])): ?>
      <p style="color:green">Added to cart.</p>
    <?php endif; ?>
    <?php if ((int)$product['stock'] > 0): ?>
      <form method="post">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <div class="form-row">
          <label>Quantity (max <?= (int)$product['stock'] ?>)</label>
          <input type="number" name="qty" min="1" max="<?= (int)$product['stock'] ?>" value="1" required>
        </div>
        <button class="btn" type="submit">Add to cart</button>
      </form>
    <?php else: ?>
      <span class="badge-out">Out of stock</span>
    <?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
