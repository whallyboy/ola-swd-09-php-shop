<?php
require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/config.php';
$title = 'Admin login';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (password_verify($_POST['password'] ?? '', ADMIN_PASSWORD_HASH)) {
        $_SESSION['admin'] = true;
        header('Location: index.php');
        exit;
    }
    $error = 'Invalid password.';
}
require __DIR__ . '/../partials/header.php';
?>
<h1>Admin login</h1>
<?php if (!empty($error)): ?><p style="color:#dc2626"><?= e($error) ?></p><?php endif; ?>
<form method="post">
  <div class="form-row"><label>Password</label><input type="password" name="password" required></div>
  <button class="btn" type="submit">Login</button>
</form>
<?php require __DIR__ . '/../partials/footer.php'; ?>
