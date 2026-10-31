<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Restaurant POS Assessment') ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/app.css') ?>">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="<?= site_url('/') ?>">Restaurant POS <span>Assessment</span></a>
        <nav class="nav">
            <a href="<?= site_url('orders') ?>">Orders</a>
            <a href="<?= site_url('cart') ?>">Cart</a>
            <a href="<?= site_url('api/orders') ?>">API</a>
            <a href="<?= site_url('api/data-quality') ?>">Data findings</a>
        </nav>
    </div>
</header>

<main class="container main">
    <?php if (session()->getFlashdata('message')): ?>
        <div class="flash flash-success"><?= esc(session()->getFlashdata('message')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="flash flash-error"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <?= $this->renderSection('content') ?>
</main>

<footer class="site-footer">
    <div class="container">
        <span>CodeIgniter 4 assessment</span>
        <span>Task 1 data notes &middot; Task 2 orders API &middot; Task 3 cart</span>
    </div>
</footer>
</body>
</html>
