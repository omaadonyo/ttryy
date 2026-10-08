<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $orders = $request->user()->packageOrders()->latest()->get();

        return view('dashboard', [
            'orders' => $orders,
            'recentOrders' => $orders->take(3),
            'totalOrders' => $orders->count(),
            'pendingOrders' => $orders->where('status', 'pending')->count(),
            'totalCommitted' => $orders->sum('total_amount'),
        ]);
    }

    public function scraper()
    {
        return view('scraper');
    }
}
