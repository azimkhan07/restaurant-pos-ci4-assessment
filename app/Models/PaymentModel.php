<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentModel extends Model
{
    protected $table         = 'payments';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'payment_date', 'transaction_ref', 'order_id', 'amount_due',
        'tips', 'discount', 'total_paid', 'payment_type', 'status',
    ];
}
