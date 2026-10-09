<?php

namespace App\Http\Controllers;

use App\Models\PackageOrder;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $orders = PackageOrder::query();

        return view('admin.dashboard', [
            'totalOrders' => (clone $orders)->count(),
            'pendingOrders' => (clone $orders)->where('status', 'pending')->count(),
            'submittedOrders' => (clone $orders)->where('status', 'submitted')->count(),
            'paidOrders' => (clone $orders)->where('status', 'paid')->count(),
            'collected' => (clone $orders)->where('status', 'paid')->sum('paid_amount'),
            'outstanding' => (clone $orders)->whereIn('status', ['pending', 'submitted'])->sum('total_amount'),
            'totalUsers' => User::count(),
            'recentOrders' => PackageOrder::with('user')->latest()->take(8)->get(),
        ]);
    }

    public function orders(Request $request)
    {
        $query = PackageOrder::with('user')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('package')) {
            $query->where('package', $request->string('package'));
        }

        if ($request->filled('search')) {
            $search = '%'.$request->string('search').'%';
            $query->where(fn ($q) => $q
                ->where('reference', 'like', $search)
                ->orWhere('business_name', 'like', $search)
                ->orWhere('phone', 'like', $search));
        }

        return view('admin.orders', [
            'orders' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only(['status', 'package', 'search']),
            'packages' => array_keys(config('packages.packages')),
        ]);
    }

    public function markPaid(PackageOrder $order)
    {
        $order->update([
            'status' => 'paid',
            'paid_amount' => $order->due_today,
            'paid_at' => now(),
            'payment_method' => $order->payment_method === 'pending' ? 'momo_manual' : $order->payment_method,
        ]);

        return back()->with('status', "Order {$order->reference} marked as paid.");
    }

    public function markHandedOver(PackageOrder $order)
    {
        $order->update([
            'credentials_handed_over' => true,
            'handed_over_at' => now(),
        ]);

        return back()->with('status', "cPanel and credentials for {$order->reference} marked as handed over.");
    }

    public function payments()
    {
        $query = PackageOrder::with('user')->whereIn('status', ['paid', 'submitted'])->latest();

        return view('admin.payments', [
            'orders' => $query->paginate(15),
            'collected' => PackageOrder::where('status', 'paid')->sum('paid_amount'),
            'awaiting' => PackageOrder::where('status', 'submitted')->sum('due_today'),
        ]);
    }

    public function users(Request $request)
    {
        $query = User::withCount('packageOrders')->withSum('packageOrders', 'total_amount')->latest();

        if ($request->filled('search')) {
            $search = '%'.$request->string('search').'%';
            $query->where(fn ($q) => $q
                ->where('name', 'like', $search)
                ->orWhere('email', 'like', $search));
        }

        return view('admin.users', [
            'users' => $query->paginate(15)->withQueryString(),
            'search' => (string) $request->string('search'),
        ]);
    }

    public function toggleAdmin(User $user)
    {
        abort_if($user->id === request()->user()->id, 422, 'You cannot change your own administrator status.');

        $user->forceFill(['is_admin' => ! $user->is_admin])->save();

        return back()->with('status', "{$user->email} ".($user->is_admin ? 'is now' : 'is no longer').' an administrator.');
    }

    public function packages()
    {
        $packages = config('packages.packages');
        $stats = [];

        foreach ($packages as $name => $data) {
            $orders = PackageOrder::where('package', $name);
            $stats[$name] = [
                'data' => $data,
                'orders' => (clone $orders)->count(),
                'revenue' => (clone $orders)->sum('total_amount'),
                'collected' => (clone $orders)->where('status', 'paid')->sum('paid_amount'),
                'active' => (clone $orders)->where('status', 'paid')->count(),
            ];
        }

        return view('admin.packages', ['stats' => $stats]);
    }
}
