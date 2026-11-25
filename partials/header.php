<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($title ?? 'Shop') ?></title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="site-header">
  <div class="container header-inner">
    <a class="brand" href="index.php">NovaShop</a>
    <nav>
      <a href="index.php">Shop</a>
      <a class="cart-link" href="cart.php">Cart <span class="badge"><?= cart_count() ?></span></a>
    </nav>
  </div>
</header>
<main class="container">
