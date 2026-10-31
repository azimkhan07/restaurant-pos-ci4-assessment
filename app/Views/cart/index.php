<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="section-head">
    <div>
        <h1>Cart</h1>
        <p class="muted small" style="margin:4px 0 0">
            Prices are inclusive of 12.5% tax. Click an item to add it, then use + / &minus; to change quantity.
        </p>
    </div>
    <div style="display:flex; gap:8px; flex-wrap:wrap">
        <form method="post" action="<?= site_url('cart/sample') ?>">
            <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
            <button class="btn btn-outline" type="submit">Load example</button>
        </form>
        <form method="post" action="<?= site_url('cart/clear') ?>">
            <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
            <button class="btn btn-danger" type="submit">Clear cart</button>
        </form>
    </div>
</div>

<div class="grid grid-5" style="margin-bottom:22px">
    <?php foreach ($menu as $item): ?>
        <div class="card cart-item">
            <div>
                <h3 style="margin:0 0 4px"><?= esc($item['name']) ?></h3>
                <div class="muted small">12.5% tax included</div>
            </div>
            <div class="price"><?= esc($item['price']) ?></div>
            <form method="post" action="<?= site_url('cart/add/' . $item['id']) ?>">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
                <button class="btn" type="submit">Add to cart</button>
            </form>
        </div>
    <?php endforeach; ?>
</div>

<div class="grid" style="grid-template-columns:minmax(0,1.7fr) minmax(280px,1fr)">
    <section class="card">
        <h2>Your items</h2>

        <?php if ($summary['items'] === []): ?>
            <div class="empty">
                Cart is empty. Add one of the five items above or load the example.
            </div>
        <?php else: ?>
            <table>
                <thead>
                <tr>
                    <th>Item</th>
                    <th class="num">Unit price</th>
                    <th class="num">Qty</th>
                    <th class="num">Price</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($summary['items'] as $line): ?>
                    <tr>
                        <td><strong><?= esc($line['name']) ?></strong></td>
                        <td class="num"><?= esc($line['unit_price']) ?></td>
                        <td class="num">
                            <div class="qty-form">
                                <form method="post" action="<?= site_url('cart/decrement/' . $line['id']) ?>">
                                    <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
                                    <button class="btn btn-qty btn-sm" type="submit" aria-label="Decrease quantity">&minus;</button>
                                </form>
                                <span class="qty-value"><?= esc((string) $line['qty']) ?></span>
                                <form method="post" action="<?= site_url('cart/increment/' . $line['id']) ?>">
                                    <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
                                    <button class="btn btn-qty btn-sm" type="submit" aria-label="Increase quantity">+</button>
                                </form>
                            </div>
                        </td>
                        <td class="num"><?= esc($line['line_total']) ?></td>
                        <td class="num">
                            <form method="post" action="<?= site_url('cart/remove/' . $line['id']) ?>">
                                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
                                <button class="btn btn-outline btn-sm" type="submit">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <aside class="card cart-summary">
        <h2>Totals</h2>
        <div class="summary-row">
            <span>Items</span>
            <span><?= esc((string) $summary['item_count']) ?></span>
        </div>
        <div class="summary-row tax">
            <span>Net (excl. tax)</span>
            <span><?= esc($summary['net']) ?></span>
        </div>
        <div class="summary-row tax">
            <span>Tax (<?= esc((string) $summary['tax_rate']) ?>%)</span>
            <span><?= esc($summary['tax']) ?></span>
        </div>
        <div class="summary-row total">
            <span>Total (incl. tax)</span>
            <span><?= esc($summary['total']) ?></span>
        </div>
        <p class="muted small" style="margin-bottom:0">
            Tax is calculated from the inclusive total: total &times; 12.5 &divide; 112.5.
            Money is stored in pence, so quantity changes never introduce floating-point drift.
        </p>
    </aside>
</div>

<section class="card" style="margin-top:16px">
    <h3>Example from the task</h3>
    <p class="muted small" style="margin-top:0">
        Item 1 &times; 1 = £10.00, Item 2 &times; 2 = £15.00, Item 4 &times; 2 = £5.00.
        Total incl. tax = <strong>£30.00</strong>, tax = <strong>£3.33</strong>, net = <strong>£26.67</strong>.
        Use <em>Load example</em> above to reproduce it.
    </p>
</section>
<?= $this->endSection() ?>
