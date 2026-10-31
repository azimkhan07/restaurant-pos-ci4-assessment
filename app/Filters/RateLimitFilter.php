<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RateLimitFilter implements FilterInterface
{
    private const CAPACITY = 120;
    private const SECONDS  = 60;

    public function before(RequestInterface $request, $arguments = null)
    {
        $key = 'api_' . md5($request->getIPAddress());

        if (! service('throttler')->check($key, self::CAPACITY, self::SECONDS)) {
            return service('response')
                ->setStatusCode(429)
                ->setHeader('Retry-After', (string) self::SECONDS)
                ->setJSON([
                    'status'  => 'error',
                    'message' => 'Too many requests. Please retry later.',
                ]);
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
