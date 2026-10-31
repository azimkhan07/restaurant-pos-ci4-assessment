<?php

namespace App\Libraries;

use Config\Database;

class OrderReportService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function paginate(array $filters): array
    {
        $page    = max(1, (int) ($filters['page'] ?? 1));
        $perPage = min(100, max(1, (int) ($filters['per_page'] ?? 20)));
        $status  = trim((string) ($filters['status'] ?? ''));
        $search  = trim((string) ($filters['search'] ?? ''));

        $countBuilder = $this->db->table('orders');
        $this->applyFilters($countBuilder, $status, $search);
        $total = $countBuilder->countAllResults();

        $builder = $this->db->table('orders')
            ->select('id, order_number, order_date, status');
        $this->applyFilters($builder, $status, $search);

        $orders = $builder
            ->orderBy('order_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()
            ->getResultArray();

        $ids             = array_map('intval', array_column($orders, 'id'));
        $itemsByOrder    = $this->itemsForOrders($ids);
        $paymentsByOrder = $this->paymentsForOrders($ids);

        $data = [];
        foreach ($orders as $order) {
            $orderId = (int) $order['id'];
            $data[]  = $this->buildOrder(
                $order,
                $itemsByOrder[$orderId] ?? [],
                $paymentsByOrder[$orderId] ?? []
            );
        }

        return [
            'data' => $data,
            'meta' => [
                'page'        => $page,
                'per_page'    => $perPage,
                'total'       => $total,
                'total_pages' => (int) ceil($total / $perPage),
                'filters'     => [
                    'status' => $status !== '' ? $status : null,
                    'search' => $search !== '' ? $search : null,
                ],
            ],
        ];
    }

    public function find(int $orderId): ?array
    {
        if ($orderId < 1) {
            return null;
        }

        $order = $this->db->table('orders')
            ->select('id, order_number, order_date, status')
            ->where('id', $orderId)
            ->get()
            ->getRowArray();

        if ($order === null) {
            return null;
        }

        return $this->buildOrder(
            $order,
            $this->itemsForOrders([$orderId])[$orderId] ?? [],
            $this->paymentsForOrders([$orderId])[$orderId] ?? []
        );
    }

    public function menu(): array
    {
        $menus      = $this->db->table('menus')->orderBy('id')->get()->getResultArray();
        $categories = $this->db->table('categories')->orderBy('id')->get()->getResultArray();
        $items      = $this->db->table('items')->orderBy('id')->get()->getResultArray();
        $prices     = $this->db->table('item_prices')->orderBy('item_id')->orderBy('id')->get()->getResultArray();

        $pricesByItem = [];
        foreach ($prices as $price) {
            $pricesByItem[(int) $price['item_id']][] = [
                'size'  => $price['size'],
                'price' => $this->decimal($price['price'], 2),
            ];
        }

        $itemsByCategory = [];
        foreach ($items as $item) {
            $itemId = (int) $item['id'];
            $itemsByCategory[(int) $item['category_id']][] = [
                'id'     => $itemId,
                'name'   => $item['name'],
                'prices' => $pricesByItem[$itemId] ?? [],
            ];
        }

        $categoriesByMenu = [];
        foreach ($categories as $category) {
            $categoryId = (int) $category['id'];
            $categoriesByMenu[(int) $category['menu_id']][] = [
                'id'    => $categoryId,
                'name'  => $category['name'],
                'items' => $itemsByCategory[$categoryId] ?? [],
            ];
        }

        $result = [];
        foreach ($menus as $menu) {
            $menuId   = (int) $menu['id'];
            $result[] = [
                'id'         => $menuId,
                'name'       => $menu['name'],
                'categories' => $categoriesByMenu[$menuId] ?? [],
            ];
        }

        return $result;
    }

    public function dataQuality(): array
    {
        $orderCount   = $this->db->table('orders')->countAllResults();
        $itemLineCount = $this->db->table('order_items')->countAllResults();
        $paymentCount = $this->db->table('payments')->countAllResults();

        $orderTotal = $this->db->table('order_items')->selectSum('line_total')->get()->getRowArray();
        $paidTotal  = $this->db->table('payments')->selectSum('total_paid')->where('status', 'Completed')->get()->getRowArray();
        $refundTotal = $this->db->table('payments')->selectSum('total_paid')->where('status', 'Refunded')->get()->getRowArray();

        $mismatchSql = <<<'SQL'
            SELECT o.id AS order_id, o.order_number,
                   ROUND(SUM(oi.line_total), 5) AS calculated_total,
                   p.amount_due AS payment_amount_due,
                   ROUND(SUM(oi.line_total) - p.amount_due, 5) AS difference
            FROM orders o
            JOIN order_items oi ON oi.order_id = o.id
            JOIN (
                SELECT order_id, MAX(amount_due) AS amount_due
                FROM payments
                GROUP BY order_id
            ) p ON p.order_id = o.id
            GROUP BY o.id, o.order_number, p.amount_due
            HAVING ABS(SUM(oi.line_total) - p.amount_due) > 0.00001
            ORDER BY o.id
            SQL;

        $priceMismatchSql = <<<'SQL'
            SELECT oi.id AS order_item_id, oi.order_id, oi.item_id, oi.size,
                   oi.unit_price, ip.price AS current_menu_price
            FROM order_items oi
            JOIN item_prices ip
              ON ip.item_id = oi.item_id
             AND ((ip.size IS NULL AND oi.size IS NULL) OR ip.size = oi.size)
            WHERE ABS(oi.unit_price - ip.price) > 0.00001
            ORDER BY oi.id
            SQL;

        $refundedCompletedSql = <<<'SQL'
            SELECT DISTINCT o.id AS order_id, o.order_number, o.status AS order_status
            FROM orders o
            JOIN payments p ON p.order_id = o.id
            WHERE p.status = 'Refunded' AND o.status = 'Completed'
            ORDER BY o.id
            SQL;

        $missingSizeSql = <<<'SQL'
            SELECT oi.id AS order_item_id, oi.order_id, oi.item_id
            FROM order_items oi
            WHERE oi.size IS NULL
              AND EXISTS (
                  SELECT 1 FROM item_prices ip
                  WHERE ip.item_id = oi.item_id AND ip.size IS NOT NULL
              )
            ORDER BY oi.id
            SQL;

        $lineIds = $this->db->table('order_items')->select('id')->orderBy('id')->get()->getResultArray();
        $lineIdList = array_map(static fn ($row) => (int) $row['id'], $lineIds);
        $missingLineIds = [];
        if ($lineIdList !== []) {
            $expected = range(min($lineIdList), max($lineIdList));
            $missingLineIds = array_values(array_diff($expected, $lineIdList));
        }

        $refs = $this->db->table('payments')->select('transaction_ref')->orderBy('transaction_ref')->get()->getResultArray();
        $refList = array_map(static fn ($row) => (int) $row['transaction_ref'], $refs);
        $missingPaymentRefs = [];
        if ($refList !== []) {
            $expectedRefs = range(min($refList), max($refList));
            $missingPaymentRefs = array_values(array_diff($expectedRefs, $refList));
        }

        $categoryMenuConflictSql = <<<'SQL'
            SELECT i.id AS item_id, i.name AS item_name,
                   i.menu_id AS item_menu_id, c.id AS category_id,
                   c.name AS category_name, c.menu_id AS category_menu_id
            FROM items i
            JOIN categories c ON c.id = i.category_id
            WHERE i.menu_id <> c.menu_id
            ORDER BY i.id
            SQL;

        return [
            'counts' => [
                'orders'      => $orderCount,
                'order_lines' => $itemLineCount,
                'payments'    => $paymentCount,
            ],
            'totals' => [
                'order_value'     => $this->decimal($orderTotal['line_total'] ?? 0, 5),
                'paid_completed'  => $this->decimal($paidTotal['total_paid'] ?? 0, 5),
                'refunded'        => $this->decimal($refundTotal['total_paid'] ?? 0, 5),
            ],
            'findings' => [
                'order_total_vs_payment_amount_mismatches' => $this->db->query($mismatchSql)->getResultArray(),
                'historical_price_vs_current_menu_mismatch_count' => count($this->db->query($priceMismatchSql)->getResultArray()),
                'refunded_but_completed_orders' => $this->db->query($refundedCompletedSql)->getResultArray(),
                'variant_items_missing_size' => $this->db->query($missingSizeSql)->getResultArray(),
                'missing_order_item_ids' => $missingLineIds,
                'missing_payment_refs' => $missingPaymentRefs,
                'category_menu_conflicts' => $this->db->query($categoryMenuConflictSql)->getResultArray(),
            ],
        ];
    }

    private function applyFilters($builder, string $status, string $search): void
    {
        if ($status !== '') {
            $builder->where('status', $status);
        }

        if ($search !== '') {
            $builder->groupStart();
            if (ctype_digit($search)) {
                $builder->where('order_number', (int) $search);
            } else {
                $builder->like('status', $search);
            }
            $builder->groupEnd();
        }
    }

    private function itemsForOrders(array $orderIds): array
    {
        if ($orderIds === []) {
            return [];
        }

        $rows = $this->db->table('order_items oi')
            ->select('oi.id, oi.order_id, oi.item_id, oi.size, oi.unit_price, oi.qty, oi.line_total, oi.status')
            ->select('i.name AS item_name, c.name AS category_name, m.name AS menu_name')
            ->join('items i', 'i.id = oi.item_id', 'left')
            ->join('categories c', 'c.id = i.category_id', 'left')
            ->join('menus m', 'm.id = i.menu_id', 'left')
            ->whereIn('oi.order_id', $orderIds)
            ->orderBy('oi.id', 'ASC')
            ->get()
            ->getResultArray();

        $grouped = [];
        foreach ($rows as $row) {
            $grouped[(int) $row['order_id']][] = [
                'id'            => (int) $row['id'],
                'item_id'       => (int) $row['item_id'],
                'item_name'     => $row['item_name'],
                'category_name' => $row['category_name'],
                'menu_name'     => $row['menu_name'],
                'size'          => $row['size'],
                'unit_price'    => $this->decimal($row['unit_price'], 5),
                'qty'           => (int) $row['qty'],
                'line_total'    => $this->decimal($row['line_total'], 5),
                'status'        => $row['status'],
            ];
        }

        return $grouped;
    }

    private function paymentsForOrders(array $orderIds): array
    {
        if ($orderIds === []) {
            return [];
        }

        $rows = $this->db->table('payments')
            ->select('id, payment_date, transaction_ref, order_id, amount_due, tips, discount, total_paid, payment_type, status')
            ->whereIn('order_id', $orderIds)
            ->orderBy('payment_date', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        $grouped = [];
        foreach ($rows as $row) {
            $grouped[(int) $row['order_id']][] = [
                'id'              => (int) $row['id'],
                'payment_date'    => $row['payment_date'],
                'transaction_ref' => $row['transaction_ref'],
                'amount_due'      => $this->decimal($row['amount_due'], 5),
                'tips'            => $this->decimal($row['tips'], 2),
                'discount'        => $this->decimal($row['discount'], 2),
                'total_paid'      => $this->decimal($row['total_paid'], 5),
                'payment_type'    => $row['payment_type'],
                'status'          => $row['status'],
            ];
        }

        return $grouped;
    }

    private function buildOrder(array $order, array $items, array $payments): array
    {
        $orderTotal = '0.00000';
        $totalQty   = 0;
        foreach ($items as $item) {
            $orderTotal = bcadd($orderTotal, $item['line_total'], 5);
            $totalQty += $item['qty'];
        }

        $tips       = '0.00';
        $discount   = '0.00';
        $paid       = '0.00000';
        $refunded   = '0.00000';

        foreach ($payments as $payment) {
            $tips     = bcadd($tips, $payment['tips'], 2);
            $discount = bcadd($discount, $payment['discount'], 2);

            if ($payment['status'] === 'Refunded') {
                $refunded = bcadd($refunded, $payment['total_paid'], 5);
            } else {
                $paid = bcadd($paid, $payment['total_paid'], 5);
            }
        }

        $expected = bcsub(bcadd($orderTotal, $tips, 5), $discount, 5);
        $netPaid  = bcsub($paid, $refunded, 5);
        $balance  = bcsub($expected, $netPaid, 5);
        if (bccomp($balance, '0', 5) <= 0) {
            $balance = '0.00000';
        }

        if (bccomp($refunded, '0', 5) > 0 && bccomp($netPaid, '0', 5) <= 0) {
            $paymentStatus = 'Refunded';
        } elseif (bccomp($balance, '0', 5) === 0) {
            $paymentStatus = 'Paid';
        } elseif (bccomp($netPaid, '0', 5) > 0) {
            $paymentStatus = 'Partially Paid';
        } else {
            $paymentStatus = 'Unpaid';
        }

        return [
            'id'             => (int) $order['id'],
            'order_number'   => (int) $order['order_number'],
            'order_date'     => $order['order_date'],
            'order_status'   => $order['status'],
            'item_count'     => $totalQty,
            'line_count'     => count($items),
            'order_total'    => $this->decimal($orderTotal, 5),
            'tips_total'     => $this->decimal($tips, 2),
            'discount_total' => $this->decimal($discount, 2),
            'paid_total'     => $this->decimal($paid, 5),
            'refunded_total' => $this->decimal($refunded, 5),
            'net_paid'       => $this->decimal($netPaid, 5),
            'balance_due'    => $this->decimal($balance, 5),
            'payment_status' => $paymentStatus,
            'items'          => $items,
            'payments'       => $payments,
        ];
    }

    private function decimal($value, int $scale): string
    {
        return number_format((float) ($value ?? 0), $scale, '.', '');
    }
}
