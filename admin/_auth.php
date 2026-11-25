<?php
require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/config.php';
if (empty($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}
