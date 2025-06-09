<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the user's shopping cart
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to view your cart');
        }
        
        $cart = $this->getUserCart($request->user());
        return view('cart', compact('cart'));
    }

    /**
     * Get or create a cart for the user
     */
    private function getUserCart($user)
    {
        $cart = $user->cart;

        if (!$cart) {
            $cart = Cart::create([
                'user_id' => $user->id,
                'total_amount' => 0,
            ]);
        }
        
        $cart->load('items.product');

        return $cart;
    }
} 