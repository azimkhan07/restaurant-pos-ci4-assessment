<?php

namespace App\Models;

use CodeIgniter\Model;

class ItemPriceModel extends Model
{
    protected $table         = 'item_prices';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['item_id', 'size', 'price'];
}
