<?php
$pageTitle = $title ?? APP_NAME;

// Which menu item is active? The first part of the address
$segment = is_string($_GET['url'] ?? null) ? strtolower(explode('/', trim($_GET['url'], '/'))[0]) : '';

// The menu only shows pages that exist. In later phases the Products and
// Categories links appear on their own once their controllers are created
$menu = [];
if (is_logged_in()) {
    $menu['dashboard'] = ['Dashboard', 'dashboard'];
    if (class_exists('ProductsController'))   { $menu['products']   = ['Products', 'products']; }
    if (class_exists('CategoriesController')) { $menu['categories'] = ['Categories', 'categories']; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?> | <?= e(APP_NAME) ?></title>
  <link rel="stylesheet" href="<?= url('assets/style.css') ?>">
</head>
<body>
<header class="topbar">
  <a class="brand" href="<?= url('') ?>"><span>S</span><?= e(APP_NAME) ?></a>

  <?php if (is_logged_in()): ?>
    <nav class="links">
      <?php foreach ($menu as $key => [$label, $path]): ?>
        <a href="<?= url($path) ?>" class="<?= $segment === $key ? 'active' : '' ?>"><?= $label ?></a>
      <?php endforeach; ?>
    </nav>
    <div class="userbox">
      <?= e($_SESSION['user']['name']) ?>
      <span class="badge <?= e($_SESSION['user']['role']) ?>"><?= e($_SESSION['user']['role']) ?></span>
      <a class="btn small light" href="<?= url('auth/logout') ?>">Logout</a>
    </div>
  <?php else: ?>
    <nav class="links">
      <a href="<?= url('auth/login') ?>" class="<?= $segment === 'auth' ? 'active' : '' ?>">Login</a>
      <a href="<?= url('auth/register') ?>">Register</a>
    </nav>
  <?php endif; ?>
</header>
<main>
<?php if (!empty($_SESSION['flash'])): $f = $_SESSION['flash']; unset($_SESSION['flash']); ?>
  <div class="alert <?= e($f['type']) ?>"><?= e($f['msg']) ?></div>
<?php endif; ?>