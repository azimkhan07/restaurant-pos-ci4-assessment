<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<section class="hero">
    <h1>Restaurant POS assessment</h1>
    <p>
        CodeIgniter 4 implementation of the three interview tasks: data findings, an orders + payments API,
        and a session-based cart with 12.5% inclusive tax.
    </p>
    <div class="actions">
        <a class="btn btn-light" href="<?= site_url('orders') ?>">View orders</a>
        <a class="btn" style="background:#115e59" href="<?= site_url('cart') ?>">Open cart</a>
        <a class="btn btn-outline" style="background:#fff" href="<?= site_url('api/orders') ?>">Orders API</a>
    </div>
</section>

<div class="grid grid-3">
    <section class="card">
        <h2>Task 1 &middot; Data findings</h2>
        <p class="muted small">
            Normalised menu, orders, order lines and payments. Key findings are documented in
            <span class="mono">docs/DATA_FINDINGS.md</span> and exposed live through the data-quality endpoint.
        </p>
        <a class="btn btn-outline" href="<?= site_url('api/data-quality') ?>">Open data findings API</a>
    </section>

    <section class="card">
        <h2>Task 2 &middot; Orders API</h2>
        <p class="muted small">
            Paginated order list with item, price and payment details. Single-order endpoint includes a complete
            payment reconciliation (paid, refunded, balance due).
        </p>
        <a class="btn btn-outline" href="<?= site_url('api/orders') ?>">GET /api/orders</a>
    </section>

    <section class="card">
        <h2>Task 3 &middot; Cart</h2>
        <p class="muted small">
            Click any item to add it. Quantity controls recalculate line totals, total incl. tax and the tax amount.
            Prices are inclusive of 12.5% tax.
        </p>
        <a class="btn btn-outline" href="<?= site_url('cart') ?>">Open cart</a>
    </section>
</div>

<section class="card" style="margin-top:16px">
    <h2>Quick API examples</h2>
    <div class="code-block">
GET <?= site_url('api/orders') ?><br>
GET <?= site_url('api/orders?status=Completed&amp;per_page=5') ?><br>
GET <?= site_url('api/orders/10') ?><br>
GET <?= site_url('api/menu') ?><br>
GET <?= site_url('api/data-quality') ?>
    </div>
    <p class="muted small" style="margin-bottom:0">
        Optional API key: set <span class="mono">api.key</span> in <span class="mono">.env</span> and send
        <span class="mono">X-API-Key</span>. The API is rate-limited to 120 requests per minute per IP.
    </p>
</section>
<?= $this->endSection() ?>
