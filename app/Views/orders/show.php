<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="section-head">
    <div>
        <h1>Order #<?= esc((string) $order['order_number']) ?></h1>
        <p class="muted small" style="margin:4px 0 0">Full order, line-item and payment detail.</p>
    </div>
    <a class="btn btn-outline" href="<?= site_url('orders') ?>">Back to orders</a>
</div>

<?= view('orders/order_card', ['order' => $order]) ?>
<?= $this->endSection() ?>
