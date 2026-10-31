<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\OrderReportService;

class MenuController extends BaseController
{
    public function index()
    {
        $service = new OrderReportService();

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $service->menu(),
        ]);
    }
}
