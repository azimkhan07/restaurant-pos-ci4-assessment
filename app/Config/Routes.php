<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

$routes->get('orders', 'OrderController::index');
$routes->get('orders/(:num)', 'OrderController::show/$1');

$routes->group('cart', ['filter' => 'csrf'], static function (RouteCollection $routes): void {
    $routes->get('/', 'CartController::index');
    $routes->get('state', 'CartController::state');
    $routes->post('add/(:num)', 'CartController::add/$1');
    $routes->post('increment/(:num)', 'CartController::increment/$1');
    $routes->post('decrement/(:num)', 'CartController::decrement/$1');
    $routes->post('remove/(:num)', 'CartController::remove/$1');
    $routes->post('clear', 'CartController::clear');
    $routes->post('sample', 'CartController::sample');
});

$routes->group('api', [
    'namespace' => 'App\Controllers\Api',
    'filter'    => ['apikey', 'ratelimit'],
], static function (RouteCollection $routes): void {
    $routes->get('health', 'HealthController::index');
    $routes->get('orders', 'OrderController::index');
    $routes->get('orders/(:num)', 'OrderController::show/$1');
    $routes->get('menu', 'MenuController::index');
    $routes->get('data-quality', 'DataQualityController::index');
});
