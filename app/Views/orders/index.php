<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="section-head">
    <div>
        <h1>Order history</h1>
        <p class="muted small" style="margin:4px 0 0">
            All orders with their line items, prices and payments. This page is rendered from the same data as the API.
        </p>
    </div>
    <div class="filters">
        <form method="get" action="<?= site_url('orders') ?>" class="filters">
            <div class="field">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">All</option>
                    <?php foreach (['Completed', 'Refunded', 'Cancelled', 'Pending'] as $option): ?>
                        <option value="<?= esc($option) ?>" <?= $status === $option ? 'selected' : '' ?>><?= esc($option) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="search">Order #</label>
                <input id="search" type="search" name="search" value="<?= esc($search) ?>" placeholder="e.g. 10">
            </div>
            <button class="btn" type="submit">Filter</button>
            <?php if ($status !== '' || $search !== ''): ?>
                <a class="btn btn-outline" href="<?= site_url('orders') ?>">Reset</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="grid grid-3" style="margin-bottom:18px">
    <div class="card stat">
        <span class="label">Orders</span>
        <span class="value"><?= esc((string) $meta['total']) ?></span>
    </div>
    <div class="card stat">
        <span class="label">Order value</span>
        <span class="value"><?= money($orderValue) ?></span>
    </div>
    <div class="card stat">
        <span class="label">Paid / refunded</span>
        <span class="value"><?= money($paidValue) ?> <span class="muted" style="font-size:14px">/ <?= money($refunded) ?></span></span>
    </div>
</div>

<?php if ($orders === []): ?>
    <div class="card empty">No orders match this filter.</div>
<?php else: ?>
    <?php foreach ($orders as $order): ?>
        <?= view('orders/order_card', ['order' => $order]) ?>
    <?php endforeach; ?>
<?php endif; ?>
<?= $this->endSection() ?>
