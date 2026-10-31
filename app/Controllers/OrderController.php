<?php

namespace App\Controllers;

use App\Libraries\OrderReportService;
use CodeIgniter\Exceptions\PageNotFoundException;

class OrderController extends BaseController
{
    public function index(): string
    {
        $service = new OrderReportService();

        $status = trim((string) $this->request->getGet('status'));
        $search = trim((string) $this->request->getGet('search'));

        $result = $service->paginate([
            'page'     => 1,
            'per_page' => 100,
            'status'   => $status,
            'search'   => $search,
        ]);

        $orderValue = 0.0;
        $paidValue  = 0.0;
        $refunded   = 0.0;
        foreach ($result['data'] as $order) {
            $orderValue += (float) $order['order_total'];
            $paidValue += (float) $order['paid_total'];
            $refunded += (float) $order['refunded_total'];
        }

        return view('orders/index', [
            'orders'     => $result['data'],
            'meta'       => $result['meta'],
            'status'     => $status,
            'search'     => $search,
            'orderValue' => $orderValue,
            'paidValue'  => $paidValue,
            'refunded'   => $refunded,
        ]);
    }

    public function show(string $id): string
    {
        $service = new OrderReportService();
        $order   = $service->find((int) $id);

        if ($order === null) {
            throw PageNotFoundException::forPageNotFound('Order not found');
        }

        return view('orders/show', ['order' => $order]);
    }
}
