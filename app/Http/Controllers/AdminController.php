<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\CampaignRecipient;
use App\Models\EmailCampaign;
use App\Models\PackageOrder;
use App\Models\SavedContact;
use App\Models\ScrapedProspect;
use App\Models\Suggestion;
use App\Models\TokenTopup;
use App\Models\TokenTransaction;
use App\Models\User;
use App\Models\WhatsappGroup;
use App\Notifications\SiteAlert;
use App\Services\BusinessPlan;
use App\Services\Hosting\HostingSubscription;
use App\Services\Wallet;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function index()
    {
        $orders = PackageOrder::query();
        $months = collect(range(5, 0))->map(fn ($i) => now()->subMonths($i));

        $emailSent = (int) EmailCampaign::sum('sent');
        $emailOpened = (int) CampaignRecipient::whereNotNull('opened_at')->count();

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
                'tokensSold' => (int) TokenTopup::where('status', 'paid')->sum('tokens'),
                'tokensUsed' => (int) abs(TokenTransaction::where('type', 'debit')->sum('amount')),
                'tokensHeld' => (int) User::sum('token_balance'),
                'topupRevenue' => (int) TokenTopup::where('status', 'paid')->sum('amount_ugx'),
                'campaigns' => EmailCampaign::count(),
                'emailSent' => $emailSent,
                'emailOpened' => $emailOpened,
                'emailRate' => $emailSent > 0 ? (int) round($emailOpened / $emailSent * 100) : 0,
                'prospectsIndexed' => ScrapedProspect::count(),
                'contactsSaved' => SavedContact::count(),
                'groupsListed' => WhatsappGroup::where('is_active', true)->count(),
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

        HostingSubscription::recordPayment($order->fresh());

        $order->user->notify(new SiteAlert(
            "Payment confirmed for {$order->reference}",
            'UGX '.number_format($order->paid_amount).' received. Website work is underway.',
            route('packages.index'),
            'payment'
        ));

        return back()->with('status', "Order {$order->reference} marked as paid.");
    }

    /**
     * Credit cash or Mobile Money received out-of-band. Amounts accumulate
     * on paid_amount; the order flips paid once the total is covered and
     * the subscription renews pro-rata via recordPayment.
     */
    public function recordPayment(Request $request, PackageOrder $order)
    {
        $remaining = max(0, $order->total_amount - $order->paid_amount);
        abort_if($remaining <= 0, 422, 'This order is already fully paid.');

        $data = $request->validate([
            'amount' => ['required', 'integer', 'min:1', 'max:'.$remaining],
            'method' => ['required', Rule::in(['cash', 'momo_manual'])],
        ]);

        $newPaid = $order->paid_amount + $data['amount'];

        $order->update([
            'paid_amount' => min($order->total_amount, $newPaid),
            'paid_at' => now(),
            'payment_method' => $data['method'],
            'status' => $newPaid >= $order->total_amount ? 'paid'
                : ($order->status === 'pending' ? 'submitted' : $order->status),
        ]);

        HostingSubscription::recordPayment($order->fresh(), $data['amount']);

        $left = max(0, $order->fresh()->total_amount - $order->fresh()->paid_amount);

        $order->user->notify(new SiteAlert(
            "Payment received — {$order->reference}",
            'UGX '.number_format($data['amount']).' received ('.($data['method'] === 'cash' ? 'cash' : 'Mobile Money').').'
                .($left > 0 ? ' UGX '.number_format($left).' still to pay.' : ' Fully paid — thank you.'),
            route('packages.index'),
            'payment'
        ));

        return back()->with('status', 'UGX '.number_format($data['amount'])." credited to {$order->reference}.");
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
        $query = PackageOrder::with('user')->whereIn('status', ['paid', 'submitted', 'refunded'])->latest();

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
        $show = (string) $request->string('show');

        $query = User::withCount('packageOrders')->withSum('packageOrders', 'total_amount')->latest();

        if ($show === 'trash') {
            $query->onlyTrashed();
        } elseif ($show === 'suspended') {
            $query->whereNotNull('suspended_at');
        }

        if ($request->filled('search')) {
            $search = '%'.$request->string('search').'%';
            $query->where(fn ($q) => $q
                ->where('name', 'like', $search)
                ->orWhere('email', 'like', $search));
        }

        return view('admin.users', [
            'users' => $query->paginate(15)->withQueryString(),
            'search' => (string) $request->string('search'),
            'show' => $show,
            'totalUsers' => User::count(),
            'adminCount' => User::where('is_admin', true)->count(),
            'newCount' => User::where('created_at', '>=', now()->subDays(30))->count(),
            'suspendedCount' => User::whereNotNull('suspended_at')->count(),
            'trashCount' => User::onlyTrashed()->count(),
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

    /**
     * Credit tokens to a customer's wallet with an audit reason. The user
     * is notified and the credit lands in their token ledger immediately.
     */
    public function creditTokens(Request $request, User $user)
    {
        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:1', 'max:10000000'],
            'reason' => ['nullable', 'string', 'max:120'],
        ]);

        $amount = (int) $validated['amount'];
        $reason = trim((string) ($validated['reason'] ?? ''));

        Wallet::credit($user, $amount, $reason !== '' ? $reason : 'Credited by ttryy team');

        $user->notify(new SiteAlert(
            'Tokens added to your wallet',
            number_format($amount).' tokens were credited to your account'.($reason !== '' ? ': '.$reason : '.'),
            route('wallet.index'),
            'tokens'
        ));

        return back()->with('status', number_format($amount)." tokens credited to {$user->email}.");
    }

    /**
     * Suspend an account: the user is signed out on their next request and
     * cannot reach the dashboard until an administrator reactivates them.
     */
    public function suspendUser(User $user)
    {
        abort_if($user->id === request()->user()->id, 422, 'You cannot suspend your own account.');
        abort_if($user->is_admin, 422, 'Remove administrator access before suspending this account.');

        $user->forceFill(['suspended_at' => now()])->save();

        $user->notify(new SiteAlert(
            'Account suspended',
            'Your ttryy account has been suspended. Contact support to have it reinstated.',
            url('/'),
            'order'
        ));

        return back()->with('status', "{$user->email} suspended.");
    }

    public function activateUser(User $user)
    {
        $user->forceFill(['suspended_at' => null])->save();

        $user->notify(new SiteAlert(
            'Account reinstated',
            'Your ttryy account is active again. Welcome back.',
            route('dashboard'),
            'order'
        ));

        return back()->with('status', "{$user->email} reactivated.");
    }

    public function destroyUser(User $user)
    {
        abort_if($user->id === request()->user()->id, 422, 'You cannot delete your own account.');
        abort_if($user->is_admin, 422, 'Remove administrator access before deleting this account.');

        $email = $user->email;
        $user->delete();

        return back()->with('status', "{$email} moved to trash — restore from the Trash tab.");
    }

    public function restoreUser(int $id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->forceFill(['suspended_at' => null])->save();
        $user->restore();

        return back()->with('status', "{$user->email} restored.");
    }

    public function purgeUser(int $id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $email = $user->email;

        $user->forceDelete();

        return back()->with('status', "{$email} permanently deleted with all its data.");
    }

    public function destroyOrder(PackageOrder $order)
    {
        $reference = $order->reference;
        $order->delete();

        return back()->with('status', "Order {$reference} deleted.");
    }

    /**
     * Record a refund against everything paid on the order, mark it refunded
     * and tell the customer. The order drops off active billing immediately.
     */
    public function refundOrder(PackageOrder $order)
    {
        abort_if($order->paid_amount <= 0, 422, 'Nothing has been paid on this order.');
        abort_if($order->isRefunded(), 422, 'This order has already been refunded.');

        $order->forceFill([
            'status' => 'refunded',
            'refunded_at' => now(),
            'refund_amount' => $order->paid_amount,
        ])->save();

        $order->user->notify(new SiteAlert(
            "Refund issued — {$order->reference}",
            'UGX '.number_format($order->refund_amount).' refunded to you. The order is now closed.',
            route('packages.index'),
            'payment'
        ));

        return back()->with('status', 'UGX '.number_format($order->refund_amount)." refunded on {$order->reference}.");
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

    /* --------------------------- business tools --------------------------- */

    public function businesses(Request $request)
    {
        $query = Business::with('owner')->withCount(['users', 'items', 'documents']);

        if ($request->filled('status')) {
            if ($request->string('status')->toString() === 'premium') {
                $query->where('plan', BusinessPlan::PREMIUM)->where('premium_until', '>', now());
            } else {
                $query->where(fn ($q) => $q->where('plan', '!=', BusinessPlan::PREMIUM)->orWhereNull('premium_until')->orWhere('premium_until', '<=', now()));
            }
        }

        if ($request->filled('search')) {
            $search = '%'.$request->string('search').'%';
            $query->where(fn ($q) => $q->where('name', 'like', $search)
                ->orWhere('phone', 'like', $search)
                ->orWhere('email', 'like', $search));
        }

        return view('admin.businesses', [
            'businesses' => $query->latest()->paginate(15)->withQueryString(),
            'filters' => $request->only(['status', 'search']),
            'prices' => BusinessPlan::PRICES,
            'summary' => [
                'total' => Business::count(),
                'premium' => Business::where('plan', BusinessPlan::PREMIUM)->where('premium_until', '>', now())->count(),
                'revenue' => (int) BusinessSubscription::where('source', 'wallet')->whereIn('status', ['active', 'expired'])->sum('amount'),
            ],
        ]);
    }

    public function grantPremium(Request $request, Business $business)
    {
        $data = $request->validate([
            'cycle' => ['required', Rule::in([BusinessSubscription::DAILY, BusinessSubscription::MONTHLY])],
        ]);

        $subscription = BusinessPlan::subscribe($business, $request->user(), $data['cycle'], $request->user(), false);

        $business->owner?->notify(new SiteAlert(
            'Business Tools Premium active',
            $business->name.' now has premium until '.$subscription->ends_at->toFormattedDateString().'.',
            route('tools.index'),
            'payment'
        ));

        return back()->with('status', $business->name.' upgraded until '.$subscription->ends_at->toFormattedDateString().'.');
    }

    public function revokePremium(Business $business)
    {
        BusinessPlan::revoke($business);

        return back()->with('status', $business->name.' returned to the free plan.');
    }

    /* --------------------------- suggestions --------------------------- */

    public function suggestions(Request $request)
    {
        $query = Suggestion::with('user');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return view('admin.suggestions', [
            'suggestions' => $query->orderByDesc('votes')->latest()->paginate(20)->withQueryString(),
            'filters' => $request->only(['status']),
            'statuses' => Suggestion::STATUSES,
            'counts' => collect(Suggestion::STATUSES)->mapWithKeys(fn ($s) => [$s => Suggestion::where('status', $s)->count()]),
        ]);
    }

    public function updateSuggestion(Request $request, Suggestion $suggestion)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(Suggestion::STATUSES)],
            'reply' => ['nullable', 'string', 'max:255'],
        ]);

        $suggestion->update($data);

        return back()->with('status', 'Idea updated to '.$data['status'].'.');
    }
}
