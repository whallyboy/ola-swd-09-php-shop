<?php
require __DIR__ . '/../app/bootstrap.php';
unset($_SESSION['admin']);
header('Location: login.php');
exit;
