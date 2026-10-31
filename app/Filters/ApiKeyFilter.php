<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ApiKeyFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $configuredKey = (string) env('api.key', '');

        if ($configuredKey === '') {
            return null;
        }

        $providedKey = $request->getHeaderLine('X-API-Key');

        if ($providedKey === '' || ! hash_equals($configuredKey, $providedKey)) {
            return service('response')->setStatusCode(401)->setJSON([
                'status'  => 'error',
                'message' => 'Unauthorized. Send a valid X-API-Key header.',
            ]);
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
