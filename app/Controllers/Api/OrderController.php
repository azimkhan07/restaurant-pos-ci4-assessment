<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\OrderReportService;

class OrderController extends BaseController
{
    public function index()
    {
        $page    = $this->request->getGet('page');
        $perPage = $this->request->getGet('per_page');
        $status  = trim((string) $this->request->getGet('status'));
        $search  = trim((string) $this->request->getGet('search'));

        if ($page !== null && (! ctype_digit((string) $page) || (int) $page < 1)) {
            return $this->validationError('page must be a positive integer.');
        }

        if ($perPage !== null && (! ctype_digit((string) $perPage) || (int) $perPage < 1 || (int) $perPage > 100)) {
            return $this->validationError('per_page must be between 1 and 100.');
        }

        if ($status !== '' && ! preg_match('/^[A-Za-z ]{1,20}$/', $status)) {
            return $this->validationError('status contains invalid characters.');
        }

        if (mb_strlen($search) > 50) {
            return $this->validationError('search must be 50 characters or fewer.');
        }

        $service = new OrderReportService();
        $result  = $service->paginate([
            'page'     => $page ?? 1,
            'per_page' => $perPage ?? 20,
            'status'   => $status,
            'search'   => $search,
        ]);

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $result['data'],
            'meta'   => $result['meta'],
        ]);
    }

    public function show(string $id)
    {
        if (! ctype_digit($id) || (int) $id < 1) {
            return $this->validationError('Order id must be a positive integer.');
        }

        $service = new OrderReportService();
        $order   = $service->find((int) $id);

        if ($order === null) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 'error',
                'message' => 'Order not found.',
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $order,
        ]);
    }

    private function validationError(string $message)
    {
        return $this->response->setStatusCode(422)->setJSON([
            'status'  => 'error',
            'message' => $message,
        ]);
    }
}
