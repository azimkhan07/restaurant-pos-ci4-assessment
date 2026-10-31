<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRestaurantTables extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'   => ['type' => 'INTEGER', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => false],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('menus', true);

        $this->forge->addField([
            'id'      => ['type' => 'INTEGER', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'    => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => false],
            'menu_id' => ['type' => 'INTEGER', 'constraint' => 11, 'unsigned' => true, 'null' => false],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('menu_id');
        $this->forge->addForeignKey('menu_id', 'menus', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('categories', true);

        $this->forge->addField([
            'id'          => ['type' => 'INTEGER', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => false],
            'category_id' => ['type' => 'INTEGER', 'constraint' => 11, 'unsigned' => true, 'null' => false],
            'menu_id'     => ['type' => 'INTEGER', 'constraint' => 11, 'unsigned' => true, 'null' => false],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('category_id');
        $this->forge->addKey('menu_id');
        $this->forge->addForeignKey('category_id', 'categories', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('menu_id', 'menus', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('items', true);

        $this->forge->addField([
            'id'      => ['type' => 'INTEGER', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'item_id' => ['type' => 'INTEGER', 'constraint' => 11, 'unsigned' => true, 'null' => false],
            'size'    => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'price'   => ['type' => 'DECIMAL', 'constraint' => '14,5', 'null' => false],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('item_id');
        $this->forge->addForeignKey('item_id', 'items', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('item_prices', true);

        $this->forge->addField([
            'id'           => ['type' => 'INTEGER', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'order_number' => ['type' => 'INTEGER', 'constraint' => 11, 'unsigned' => true, 'null' => false],
            'order_date'   => ['type' => 'DATE', 'null' => false],
            'status'       => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => false, 'default' => 'Completed'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('order_number', false, true);
        $this->forge->addKey('order_date');
        $this->forge->addKey('status');
        $this->forge->createTable('orders', true);

        $this->forge->addField([
            'id'         => ['type' => 'INTEGER', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'order_id'   => ['type' => 'INTEGER', 'constraint' => 11, 'unsigned' => true, 'null' => false],
            'item_id'    => ['type' => 'INTEGER', 'constraint' => 11, 'unsigned' => true, 'null' => false],
            'size'       => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'unit_price' => ['type' => 'DECIMAL', 'constraint' => '14,5', 'null' => false],
            'qty'        => ['type' => 'INTEGER', 'constraint' => 11, 'unsigned' => true, 'null' => false],
            'line_total' => ['type' => 'DECIMAL', 'constraint' => '16,5', 'null' => false],
            'status'     => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => false, 'default' => 'Completed'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('order_id');
        $this->forge->addKey('item_id');
        $this->forge->addForeignKey('order_id', 'orders', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('item_id', 'items', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('order_items', true);

        $this->forge->addField([
            'id'              => ['type' => 'INTEGER', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'payment_date'    => ['type' => 'DATE', 'null' => false],
            'transaction_ref' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => false],
            'order_id'        => ['type' => 'INTEGER', 'constraint' => 11, 'unsigned' => true, 'null' => false],
            'amount_due'      => ['type' => 'DECIMAL', 'constraint' => '16,5', 'null' => false],
            'tips'            => ['type' => 'DECIMAL', 'constraint' => '14,2', 'null' => false, 'default' => '0'],
            'discount'        => ['type' => 'DECIMAL', 'constraint' => '14,2', 'null' => false, 'default' => '0'],
            'total_paid'      => ['type' => 'DECIMAL', 'constraint' => '16,5', 'null' => false],
            'payment_type'    => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => false],
            'status'          => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => false, 'default' => 'Completed'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('order_id');
        $this->forge->addKey('status');
        $this->forge->addKey('transaction_ref', false, true);
        $this->forge->addForeignKey('order_id', 'orders', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('payments', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('payments', true);
        $this->forge->dropTable('order_items', true);
        $this->forge->dropTable('orders', true);
        $this->forge->dropTable('item_prices', true);
        $this->forge->dropTable('items', true);
        $this->forge->dropTable('categories', true);
        $this->forge->dropTable('menus', true);
    }
}
