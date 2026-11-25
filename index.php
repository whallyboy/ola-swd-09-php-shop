<?php
require __DIR__ . '/app/bootstrap.php';
$title = 'Shop';

$category = $_GET['category'] ?? '';
$q = trim($_GET['q'] ?? '');
$sort = $_GET['sort'] ?? 'newest';

$where = ['is_active = 1'];
$params = [];
if ($category !== '') {
    $where[] = 'c.slug = ?';
    $params[] = $category;
}
if ($q !== '') {
    $where[] = '(p.name LIKE ? OR p.description LIKE ?)';
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
}
$orderBy = match ($sort) {
    'price_asc'  => 'p.price ASC',
    'price_desc' => 'p.price DESC',
    default      => 'p.created_at DESC',
};

$sql = 'SELECT p.*, c.name AS category_name, c.slug AS category_slug
        FROM products p JOIN categories c ON c.id = p.category_id
        WHERE ' . implode(' AND ', $where) . ' ORDER BY ' . $orderBy;
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();

require __DIR__ . '/partials/header.php';
?>
<h1>Shop</h1>
<form class="filters" method="get">
  <input type="text" name="q" placeholder="Search products..." value="<?= e($q) ?>">
  <select name="category">
    <option value="">All categories</option>
    <?php foreach ($categories as $c): ?>
      <option value="<?= e($c['slug']) ?>" <?= $category === $c['slug'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
    <?php endforeach; ?>
  </select>
  <select name="sort">
    <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest</option>
    <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
    <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
  </select>
  <button class="btn" type="submit">Filter</button>
</form>

<div class="grid">
  <?php foreach ($products as $p): ?>
    <div class="card">
      <a href="product.php?slug=<?= urlencode($p['slug']) ?>"><img src="<?= e($p['image_url']) ?>" alt="<?= e($p['name']) ?>"></a>
      <div class="card-body">
        <span class="muted"><?= e($p['category_name']) ?></span>
        <h3><a href="product.php?slug=<?= urlencode($p['slug']) ?>" style="text-decoration:none;color:inherit"><?= e($p['name']) ?></a></h3>
        <div class="price"><?= money((float)$p['price']) ?></div>
        <?php if ((int)$p['stock'] <= 0): ?>
          <span class="badge-out">Out of stock</span>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (!$products): ?>
    <p>No products found.</p>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
