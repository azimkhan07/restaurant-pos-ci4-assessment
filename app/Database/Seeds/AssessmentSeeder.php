<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AssessmentSeeder extends Seeder
{
    public function run(): void
    {
        $path = __DIR__ . DIRECTORY_SEPARATOR . 'assessment_data.json';
        $json = file_get_contents($path);

        if ($json === false) {
            throw new \RuntimeException('Unable to read assessment data file.');
        }

        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        $this->db->disableForeignKeyChecks();

        foreach (['payments', 'order_items', 'orders', 'item_prices', 'items', 'categories', 'menus'] as $table) {
            $this->db->table($table)->truncate();
        }

        $this->db->table('menus')->insertBatch($data['menus']);
        $this->db->table('categories')->insertBatch($data['categories']);
        $this->db->table('items')->insertBatch($data['items']);
        $this->db->table('item_prices')->insertBatch($data['item_prices']);
        $this->db->table('orders')->insertBatch($data['orders']);
        $this->db->table('order_items')->insertBatch($data['order_items']);
        $this->db->table('payments')->insertBatch($data['payments']);

        $this->db->enableForeignKeyChecks();
    }
}
