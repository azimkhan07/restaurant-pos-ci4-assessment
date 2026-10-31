<?php

if (! function_exists('money')) {
    function money($value, int $decimals = 2): string
    {
        return '£' . number_format((float) ($value ?? 0), $decimals, '.', ',');
    }
}

if (! function_exists('payment_badge')) {
    function payment_badge(string $status): string
    {
        $class = match ($status) {
            'Paid'           => 'badge-paid',
            'Partially Paid' => 'badge-partial',
            'Refunded'       => 'badge-refunded',
            default          => 'badge-unpaid',
        };

        return '<span class="badge ' . $class . '">' . esc($status) . '</span>';
    }
}
