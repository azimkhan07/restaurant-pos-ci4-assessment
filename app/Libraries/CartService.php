<?php

namespace App\Libraries;

use Config\Cart as CartConfig;

class CartService
{
    private const SESSION_KEY = 'cart';

    private CartConfig $config;

    public function __construct(?CartConfig $config = null)
    {
        $this->config = $config ?? config(CartConfig::class);
    }

    public function menu(): array
    {
        $items = [];
        foreach ($this->config->items as $item) {
            $items[] = [
                'id'          => $item['id'],
                'name'        => $item['name'],
                'price_pence' => $item['price_pence'],
                'price'       => $this->formatMoney($item['price_pence']),
            ];
        }

        return $items;
    }

    public function add(int $itemId, int $qty = 1): bool
    {
        if ($this->findItem($itemId) === null || $qty < 1) {
            return false;
        }

        $cart    = $this->cart();
        $current = $cart[$itemId] ?? 0;
        $cart[$itemId] = min($this->config->maxQtyPerItem, $current + $qty);
        $this->save($cart);

        return true;
    }

    public function increment(int $itemId): bool
    {
        return $this->add($itemId, 1);
    }

    public function decrement(int $itemId): bool
    {
        $cart = $this->cart();
        if (! isset($cart[$itemId])) {
            return false;
        }

        if ($cart[$itemId] <= 1) {
            unset($cart[$itemId]);
        } else {
            $cart[$itemId]--;
        }

        $this->save($cart);

        return true;
    }

    public function remove(int $itemId): void
    {
        $cart = $this->cart();
        unset($cart[$itemId]);
        $this->save($cart);
    }

    public function clear(): void
    {
        session()->remove(self::SESSION_KEY);
    }

    public function setSample(): void
    {
        $this->save([1 => 1, 2 => 2, 4 => 2]);
    }

    public function summary(): array
    {
        $cart    = $this->cart();
        $lines   = [];
        $total   = 0;

        foreach ($this->config->items as $item) {
            $qty = $cart[$item['id']] ?? 0;
            if ($qty < 1) {
                continue;
            }

            $lineTotal = $item['price_pence'] * $qty;
            $total += $lineTotal;

            $lines[] = [
                'id'          => $item['id'],
                'name'        => $item['name'],
                'unit_pence'  => $item['price_pence'],
                'unit_price'  => $this->formatMoney($item['price_pence']),
                'qty'         => $qty,
                'line_pence'  => $lineTotal,
                'line_total'  => $this->formatMoney($lineTotal),
            ];
        }

        // Inclusive tax: total * 12.5 / 112.5 === total / 9, rounded to nearest penny.
        $tax = intdiv($total + 4, 9);
        $net = $total - $tax;

        return [
            'items'          => $lines,
            'item_count'     => array_sum(array_column($lines, 'qty')),
            'total_pence'    => $total,
            'tax_pence'      => $tax,
            'net_pence'      => $net,
            'total'          => $this->formatMoney($total),
            'tax'            => $this->formatMoney($tax),
            'net'            => $this->formatMoney($net),
            'tax_rate'       => $this->config->taxRatePercent,
            'total_plain'    => number_format($total / 100, 2, '.', ''),
            'tax_plain'      => number_format($tax / 100, 2, '.', ''),
            'net_plain'      => number_format($net / 100, 2, '.', ''),
        ];
    }

    private function cart(): array
    {
        $cart = session()->get(self::SESSION_KEY);

        if (! is_array($cart)) {
            return [];
        }

        $clean = [];
        foreach ($cart as $id => $qty) {
            $id  = (int) $id;
            $qty = (int) $qty;
            if ($this->findItem($id) !== null && $qty > 0) {
                $clean[$id] = min($this->config->maxQtyPerItem, $qty);
            }
        }

        return $clean;
    }

    private function save(array $cart): void
    {
        session()->set(self::SESSION_KEY, $cart);
    }

    private function findItem(int $itemId): ?array
    {
        foreach ($this->config->items as $item) {
            if ($item['id'] === $itemId) {
                return $item;
            }
        }

        return null;
    }

    private function formatMoney(int $pence): string
    {
        return '£' . number_format($pence / 100, 2);
    }
}
