<article class="card order-card">
    <div class="order-head">
        <div>
            <h2><a href="<?= site_url('orders/' . $order['id']) ?>">Order #<?= esc((string) $order['order_number']) ?></a></h2>
            <div class="muted small">
                <?= esc($order['order_date']) ?> &middot;
                <?= esc((string) $order['line_count']) ?> lines &middot;
                <?= esc((string) $order['item_count']) ?> items &middot;
                Order status: <?= esc($order['order_status']) ?>
            </div>
        </div>
        <div>
            <?= payment_badge($order['payment_status']) ?>
        </div>
    </div>

    <div class="grid grid-2">
        <div>
            <h3>Items</h3>
            <table>
                <thead>
                <tr>
                    <th>Item</th>
                    <th>Size</th>
                    <th class="num">Unit</th>
                    <th class="num">Qty</th>
                    <th class="num">Price</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($order['items'] as $item): ?>
                    <tr>
                        <td>
                            <?= esc($item['item_name']) ?>
                            <div class="muted small"><?= esc($item['category_name']) ?> &middot; <?= esc($item['menu_name']) ?></div>
                        </td>
                        <td><?= $item['size'] !== null ? esc($item['size']) : '&mdash;' ?></td>
                        <td class="num"><?= money($item['unit_price']) ?></td>
                        <td class="num"><?= esc((string) $item['qty']) ?></td>
                        <td class="num"><?= money($item['line_total']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div>
            <h3>Payments</h3>
            <?php if ($order['payments'] === []): ?>
                <p class="muted small">No payment recorded.</p>
            <?php else: ?>
                <table>
                    <thead>
                    <tr>
                        <th>Ref</th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th class="num">Paid</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($order['payments'] as $payment): ?>
                        <tr>
                            <td class="mono"><?= esc($payment['transaction_ref']) ?></td>
                            <td><?= esc($payment['payment_date']) ?></td>
                            <td><?= esc($payment['payment_type']) ?></td>
                            <td><?= esc($payment['status']) ?></td>
                            <td class="num"><?= money($payment['total_paid']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <p class="muted small" style="margin-bottom:0">
                    Tips: <?= money($order['tips_total']) ?> &middot;
                    Discount: <?= money($order['discount_total']) ?> &middot;
                    Refunded: <?= money($order['refunded_total']) ?>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <div class="order-totals">
        <div class="stat">
            <span class="label">Order total</span>
            <span class="value"><?= money($order['order_total']) ?></span>
        </div>
        <div class="stat">
            <span class="label">Paid</span>
            <span class="value"><?= money($order['paid_total']) ?></span>
        </div>
        <div class="stat">
            <span class="label">Balance due</span>
            <span class="value"><?= money($order['balance_due']) ?></span>
        </div>
    </div>
</article>
