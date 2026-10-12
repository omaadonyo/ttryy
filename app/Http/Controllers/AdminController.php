<?php

namespace App\Http\Controllers;

use App\Models\PackageOrder;
use App\Models\User;
use App\Notifications\SiteAlert;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $orders = PackageOrder::query();
        $months = collect(range(5, 0))->map(fn ($i) => now()->subMonths($i));

        $emailSent = (int) \App\Models\EmailCampaign::sum('sent');
        $emailOpened = (int) \App\Models\CampaignRecipient::whereNotNull('opened_at')->count();

        return view('admin.dashboard', [
            'totalOrders' => (clone $orders)->count(),
            'pendingOrders' => (clone $orders)->where('status', 'pending')->count(),
            'submittedOrders' => (clone $orders)->where('status', 'submitted')->count(),
            'paidOrders' => (clone $orders)->where('status', 'paid')->count(),
            'collected' => (clone $orders)->where('status', 'paid')->sum('paid_amount'),
            'outstanding' => (clone $orders)->whereIn('status', ['pending', 'submitted'])->sum('total_amount'),
            'totalUsers' => User::count(),
            'recentOrders' => PackageOrder::with('user')->latest()->take(8)->get(),
            'revenueChart' => [
                'labels' => $months->map(fn ($m) => $m->format('M'))->all(),
                'revenue' => $months->map(fn ($m) => (int) PackageOrder::where('status', 'paid')
                    ->whereYear('paid_at', $m->year)->whereMonth('paid_at', $m->month)->sum('paid_amount'))->all(),
                'orders' => $months->map(fn ($m) => PackageOrder::whereYear('created_at', $m->year)
                    ->whereMonth('created_at', $m->month)->count())->all(),
            ],
            'usage' => [
                'signups' => $months->map(fn ($m) => User::whereYear('created_at', $m->year)->whereMonth('created_at', $m->month)->count())->all(),
                'signupLabels' => $months->map(fn ($m) => $m->format('M'))->all(),
                'tokensSold' => (int) \App\Models\TokenTopup::where('status', 'paid')->sum('tokens'),
                'tokensUsed' => (int) abs(\App\Models\TokenTransaction::where('type', 'debit')->sum('amount')),
                'tokensHeld' => (int) User::sum('token_balance'),
                'topupRevenue' => (int) \App\Models\TokenTopup::where('status', 'paid')->sum('amount_ugx'),
                'campaigns' => \App\Models\EmailCampaign::count(),
                'emailSent' => $emailSent,
                'emailOpened' => $emailOpened,
                'emailRate' => $emailSent > 0 ? (int) round($emailOpened / $emailSent * 100) : 0,
                'prospectsIndexed' => \App\Models\ScrapedProspect::count(),
                'contactsSaved' => \App\Models\SavedContact::count(),
                'groupsListed' => \App\Models\WhatsappGroup::where('is_active', true)->count(),
            ],
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
            'summary' => [
                'pending' => PackageOrder::where('status', 'pending')->count(),
                'submitted' => PackageOrder::where('status', 'submitted')->count(),
                'submittedValue' => (int) PackageOrder::where('status', 'submitted')->sum('due_today'),
                'paid' => PackageOrder::where('status', 'paid')->count(),
            ],
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

        $order->user->notify(new SiteAlert(
            "Payment confirmed for {$order->reference}",
            'UGX '.number_format($order->paid_amount).' received. Website work is underway.',
            route('packages.index'),
            'payment'
        ));

        return back()->with('status', "Order {$order->reference} marked as paid.");
    }

    public function markHandedOver(PackageOrder $order)
    {
        $order->update([
            'credentials_handed_over' => true,
            'handed_over_at' => now(),
        ]);

        $order->user->notify(new SiteAlert(
            "Website {$order->reference} handed over",
            'cPanel and credentials are with you — the website is fully yours.',
            route('packages.index'),
            'order'
        ));

        return back()->with('status', "cPanel and credentials for {$order->reference} marked as handed over.");
    }

    public function payments()
    {
        $query = PackageOrder::with('user')->whereIn('status', ['paid', 'submitted'])->latest();

        return view('admin.payments', [
            'orders' => $query->paginate(15)->withQueryString(),
            'collected' => PackageOrder::where('status', 'paid')->sum('paid_amount'),
            'awaiting' => PackageOrder::where('status', 'submitted')->sum('due_today'),
            'onlineCollected' => (int) PackageOrder::where('status', 'paid')->where('payment_method', 'flutterwave')->sum('paid_amount'),
            'momoCollected' => (int) PackageOrder::where('status', 'paid')->where('payment_method', 'momo_manual')->sum('paid_amount'),
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
            'totalUsers' => User::count(),
            'adminCount' => User::where('is_admin', true)->count(),
            'newCount' => User::where('created_at', '>=', now()->subDays(30))->count(),
            'signupChart' => [
                'labels' => collect(range(5, 0))->map(fn ($i) => now()->subMonths($i)->format('M'))->all(),
                'values' => collect(range(5, 0))->map(fn ($i) => User::whereYear('created_at', now()->subMonths($i)->year)
                    ->whereMonth('created_at', now()->subMonths($i)->month)->count())->all(),
            ],
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
