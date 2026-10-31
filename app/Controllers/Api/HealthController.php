<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

class HealthController extends BaseController
{
    public function index()
    {
        return $this->response->setJSON([
            'status'      => 'ok',
            'service'     => 'restaurant-pos-assessment',
            'environment' => ENVIRONMENT,
            'php'         => PHP_VERSION,
            'time'        => date(DATE_ATOM),
        ]);
    }
}
