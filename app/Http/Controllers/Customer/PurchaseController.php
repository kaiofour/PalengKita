<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PurchaseController extends Controller
{
    public function index()
    {
        return view('customer.purchases.index');
    }

    public function getProducts()
    {
        $products = Cache::get('products.latest', []);
        return response()->json($products);
    }
}
