<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Cart extends BaseConfig
{
    /**
     * Task 3 menu. Prices are stored in pence so money maths never uses floats.
     *
     * @var list<array{id: int, name: string, price_pence: int}>
     */
    public array $items = [
        ['id' => 1, 'name' => 'Item 1', 'price_pence' => 1000],
        ['id' => 2, 'name' => 'Item 2', 'price_pence' => 750],
        ['id' => 3, 'name' => 'Item 3', 'price_pence' => 500],
        ['id' => 4, 'name' => 'Item 4', 'price_pence' => 250],
        ['id' => 5, 'name' => 'Item 5', 'price_pence' => 300],
    ];

    /**
     * Prices already include this tax rate.
     */
    public float $taxRatePercent = 12.5;

    public int $maxQtyPerItem = 99;
}
