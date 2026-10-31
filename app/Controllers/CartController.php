<?php

namespace App\Controllers;

use App\Libraries\CartService;

class CartController extends BaseController
{
    private CartService $cart;

    public function __construct()
    {
        $this->cart = new CartService();
    }

    public function index(): string
    {
        return view('cart/index', [
            'menu'    => $this->cart->menu(),
            'summary' => $this->cart->summary(),
        ]);
    }

    public function add(string $id)
    {
        $ok = $this->cart->add((int) $id);

        return redirect()->to(site_url('cart'))->with(
            $ok ? 'message' : 'error',
            $ok ? 'Item added to cart.' : 'Invalid item.'
        );
    }

    public function increment(string $id)
    {
        $this->cart->increment((int) $id);

        return redirect()->to(site_url('cart'));
    }

    public function decrement(string $id)
    {
        $this->cart->decrement((int) $id);

        return redirect()->to(site_url('cart'));
    }

    public function remove(string $id)
    {
        $this->cart->remove((int) $id);

        return redirect()->to(site_url('cart'))->with('message', 'Item removed.');
    }

    public function clear()
    {
        $this->cart->clear();

        return redirect()->to(site_url('cart'))->with('message', 'Cart cleared.');
    }

    public function sample()
    {
        $this->cart->setSample();

        return redirect()->to(site_url('cart'))->with('message', 'Sample cart loaded (Item 1 x1, Item 2 x2, Item 4 x2).');
    }

    public function state()
    {
        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $this->cart->summary(),
        ]);
    }
}
