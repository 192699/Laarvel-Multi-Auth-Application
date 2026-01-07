<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth('customer')->user();
        
        // Get featured products (latest 6 products)
        $featuredProducts = Product::latest()->take(6)->get();
        
        // Get total products count
        $totalProducts = Product::count();
        
        return view('customer.dashboard', compact('user', 'featuredProducts', 'totalProducts'));
    }
}

