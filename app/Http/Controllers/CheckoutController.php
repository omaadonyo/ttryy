<?php

namespace App\Http\Controllers;

use App\Mail\OrderPlaced;
use App\Models\PackageOrder;
use App\Models\User;
use App\Models\WebsiteDomain;
use App\Notifications\SiteAlert;
use App\Services\Hosting\HostingSubscription;
use App\Services\Hosting\OrderProvisioner;
use App\Services\Hosting\SubdomainGenerator;
use App\Services\IotecPay;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CheckoutController extends Controller
{
    public function index(Request $request)
    {
        $packages = config('packages.packages');
        $package = strtoupper((string) $request->query('package', 'GROW'));

        if (! array_key_exists($package, $packages)) {
            $package = 'GROW';
        }

        $billing = (string) $request->query('billing', 'daily');
        if (! in_array($billing, config('packages.frequencies'), true)) {
            $billing = 'daily';
        }

        $domain = (string) $request->query('domain', 'none');
        if (! array_key_exists($domain, config('packages.domains'))) {
            $domain = 'budget';
        }

        $niche = (string) $request->query('niche', '');
        if (! in_array($niche, config('packages.niches'), true)) {
            $niche = '';
        }

        // Pre-compute every package/term/cadence breakdown server-side so the
        // summary JS can never drift from the authoritative quote().
        $quotes = [];
        foreach ($packages as $code => $p) {
            foreach (config('packages.durations') as $months) {
                foreach (config('packages.frequencies') as $freq) {
                    $q = self::quote($code, $freq, $months, 'none');
                    $quotes[$code][$months][$freq] = [
                        'periods' => $q['periods'],
                        'rate' => $q['amount_per_period'],
                        'total' => $q['package_total'],
                        'due' => $q['due_today'],
                        'per' => $q['per'],
                        'freq_label' => $q['freq_label'],
                        'term' => $q['duration_months'],
                    ];
                }
            }
        }

        return view('checkout', [
            'packages' => $packages,
            'selectedPackage' => $package,
            'selectedBilling' => $billing,
            'selectedDomain' => $domain,
            'selectedNiche' => $niche,
            'durations' => config('packages.durations'),
            'domains' => config('packages.domains'),
            'currency' => config('packages.currency'),
            'flwKey' => config('services.flutterwave.public_key'),
            'momoCode' => config('services.momo.merchant_code'),
            'iotecEnabled' => IotecPay::enabled(),
            'quotes' => $quotes,
            'dailyFrom' => collect($packages)->map(fn ($p, $code) => self::quote($code, 'daily', 12, 'none')['amount_per_period'])->all(),
        ]);
    }

    public function store(Request $request)
    {
        $packages = config('packages.packages');

        $validated = $request->validate([
            'package' => ['required', Rule::in(array_keys($packages))],
            'billing_frequency' => ['required', Rule::in(config('packages.frequencies'))],
            'domain' => ['required', Rule::in(array_keys(config('packages.domains')))],
            'payment_method' => ['nullable', Rule::in(['online', 'momo_manual', 'iotec'])],
            'duration_months' => ['nullable', 'integer', Rule::in(config('packages.durations'))],
            'business_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'niche' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'subdomain_label' => ['nullable', 'string', 'max:63'],
            'custom_domain' => ['nullable', 'string', 'max:253'],
        ]);

        $subdomainLabel = null;
        $customDomain = null;

        if (($validated['domain'] ?? null) === 'none') {
            $raw = strtolower(trim((string) ($validated['custom_domain'] ?? '')));
            $raw = preg_replace('#^https?://#', '', $raw) ?? '';
            $raw = rtrim($raw, '/');

            if ($raw !== '') {
                if (! self::validHostname($raw)
                    || $raw === config('hosting.base_domain')
                    || str_ends_with($raw, '.'.config('hosting.base_domain'))) {
                    return back()->withErrors(['custom_domain' => 'Enter a valid domain you own (not a '.config('hosting.base_domain').' subdomain).'])->withInput();
                }

                if (WebsiteDomain::where('domain', $raw)->exists()) {
                    return back()->withErrors(['custom_domain' => 'That domain is already registered on ttryy.'])->withInput();
                }

                $customDomain = $raw;
            }
        }

        if (($validated['domain'] ?? null) === 'subdomain') {
            $label = strtolower(trim((string) ($validated['subdomain_label'] ?? '')));

            if (! SubdomainGenerator::validLabel($label)) {
                return back()->withErrors(['subdomain_label' => 'Use letters, numbers and hyphens only.'])->withInput();
            }

            if (SubdomainGenerator::isReserved($label)) {
                return back()->withErrors(['subdomain_label' => 'That subdomain is reserved.'])->withInput();
            }

            if (WebsiteDomain::where('domain', SubdomainGenerator::fullDomain($label))->exists()) {
                return back()->withErrors(['subdomain_label' => 'That subdomain is already taken.'])->withInput();
            }

            $subdomainLabel = $label;
        }

        $method = $validated['payment_method'] ?? 'iotec';

        $quote = $this->quote(
            $validated['package'],
            $validated['billing_frequency'],
            (int) ($validated['duration_months'] ?? 12),
            $validated['domain']
        );

        do {
            $reference = 'TTRYY-'.strtoupper(Str::random(6));
        } while (PackageOrder::where('reference', $reference)->exists());

        $order = PackageOrder::create([
            'user_id' => $request->user()->id,
            'reference' => $reference,
            'package' => $validated['package'],
            'billing_frequency' => $validated['billing_frequency'],
            'domain' => $validated['domain'],
            'subdomain' => $subdomainLabel,
            'custom_domain' => $customDomain,
            'duration_months' => $quote['duration_months'],
            'periods' => $quote['periods'],
            'amount_per_period' => $quote['amount_per_period'],
            'domain_fee' => $quote['domain_fee'],
            'total_amount' => $quote['total'],
            'due_today' => $quote['due_today'],
            'currency' => config('packages.currency'),
            'status' => $method === 'momo_manual' ? 'submitted' : 'pending',
            'payment_method' => $method === 'momo_manual' ? 'momo_manual' : ($method === 'online' ? 'flutterwave' : 'iotec'),
            'business_name' => $validated['business_name'],
            'phone' => $validated['phone'],
            'niche' => $validated['niche'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Create the (pending) hosting record from what the customer gave us.
        // It stays pending_setup until an admin activates it.
        try {
            OrderProvisioner::createForOrder($order);
        } catch (\Throwable $e) {
            Log::warning('Order hosting provisioning failed', ['order' => $order->reference, 'error' => $e->getMessage()]);
        }

        $this->sendOrderEmails($order);

        $request->user()->notify(new SiteAlert(
            "Order {$order->reference} received",
            "{$order->package} · UGX ".number_format($order->due_today).' due today.',
            route('checkout.success', $order),
            'order'
        ));

        User::where('is_admin', true)->where('id', '!=', $request->user()->id)->each(fn ($admin) => $admin->notify(new SiteAlert(
            "New order {$order->reference}",
            "{$order->business_name} · {$order->package} · ".ucfirst($order->billing_frequency).'.',
            route('admin.orders'),
            'order'
        )));

        if ($request->expectsJson()) {
            return response()->json([
                'reference' => $order->reference,
                'due_today' => $order->due_today,
                'currency' => $order->currency,
                'email' => $request->user()->email,
                'name' => $request->user()->name,
                'phone' => $order->phone,
                'public_key' => config('services.flutterwave.public_key'),
                'order_url' => route('checkout.success', $order),
            ]);
        }

        return redirect()->route('checkout.success', $order);
    }

    public function verify(Request $request)
    {
        $data = $request->validate([
            'transaction_id' => ['required'],
            'tx_ref' => ['required', 'string'],
        ]);

        // tx_ref carries a per-attempt timestamp suffix (TTRYY-XXXXXX-<ms>)
        // so retries never collide on a duplicate reference.
        $reference = preg_match('/^(TTRYY-[A-Z0-9]{6})-\d+$/', $data['tx_ref'], $m)
            ? $m[1]
            : $data['tx_ref'];

        $order = PackageOrder::where('reference', $reference)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if ($order->status === 'paid') {
            return response()->json(['redirect' => route('checkout.success', $order)]);
        }

        $secret = config('services.flutterwave.secret_key');

        if (! $secret) {
            return response()->json(['message' => 'Online verification is not configured yet.'], 422);
        }

        $response = Http::withToken($secret)->acceptJson()->get(
            'https://api.flutterwave.com/v3/transactions/'.$data['transaction_id'].'/verify'
        );

        if (! $response->successful()) {
            return response()->json(['message' => 'Could not reach the payment provider.'], 422);
        }

        $tx = $response->json('data', []);

        if (($tx['status'] ?? null) === 'successful'
            && ($tx['currency'] ?? null) === $order->currency
            && (int) ($tx['amount'] ?? 0) >= $order->due_today) {
            $order->update([
                'status' => 'paid',
                'paid_amount' => (int) $tx['amount'],
                'paid_at' => now(),
                'tx_ref' => (string) ($tx['id'] ?? $data['transaction_id']),
                'payment_method' => 'flutterwave',
            ]);

            HostingSubscription::recordPayment($order->fresh(), (int) $tx['amount']);

            return response()->json(['redirect' => route('checkout.success', $order)]);
        }

        return response()->json(['message' => 'Payment could not be verified.'], 422);
    }

    public function invoice(PackageOrder $order)
    {
        abort_if($order->user_id !== request()->user()->id && ! request()->user()->is_admin, 403);

        $pdf = Pdf::loadView('invoice-pdf', [
            'order' => $order,
            'user' => $order->user,
            'momoCode' => config('services.momo.merchant_code'),
        ]);

        return $pdf->download($order->reference.'-invoice.pdf');
    }

    public function success(PackageOrder $order)
    {
        abort_if($order->user_id !== request()->user()->id && ! request()->user()->is_admin, 403);

        return view('order-confirmation', ['order' => $order]);
    }

    public function orders()
    {
        $orders = request()->user()->packageOrders()->latest()->get();

        return view('my-packages', [
            'orders' => $orders,
            'activeCount' => $orders->filter(fn ($o) => $o->isActive())->count(),
            'paidSum' => (int) $orders->sum('paid_amount'),
            'remainingSum' => (int) $orders->sum(fn ($o) => max(0, $o->total_amount - $o->paid_amount)),
            'walletBalance' => (int) request()->user()->balance_ugx,
        ]);
    }

    /**
     * Email the customer their copy and the admin inbox its copy.
     * Mail failures must never break order placement.
     */
    protected function sendOrderEmails(PackageOrder $order): void
    {
        try {
            Mail::to($order->user->email)->send(new OrderPlaced($order));
            Mail::to(config('packages.admin_email'))->send(new OrderPlaced($order, true));
        } catch (\Throwable $e) {
            Log::warning('Order email failed', ['order' => $order->reference, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Server-side quote. Amounts are never trusted from the client.
     *
     * The installment rate is DERIVED from the package price across the
     * chosen term (price ÷ periods), so the whole package total is always
     * covered inside the term — whether the customer pays per day, week or
     * month. The first payment is at least `min_first_payment` and covers at
     * least `min_first_days` at the package's per-day rate. The one-time
     * domain fee rides on top and is always due upfront.
     *
     * @return array{duration_months:int, periods:int, amount_per_period:int, daily_rate:int, domain_fee:int, package_total:int, total:int, due_today:int, remaining:int, period_label:string, freq_label:string, per:string}
     */
    public static function quote(string $package, string $frequency, int $durationMonths, string $domain = 'none'): array
    {
        $packages = config('packages.packages');
        $price = (int) $packages[$package]['price'];
        $domainFee = (int) (config('packages.domains')[$domain]['fee'] ?? 0);

        if ($frequency === 'full') {
            return [
                'duration_months' => 12,
                'periods' => 1,
                'amount_per_period' => $price,
                'daily_rate' => $price,
                'domain_fee' => $domainFee,
                'package_total' => $price,
                'total' => $price + $domainFee,
                'due_today' => $price + $domainFee,
                'remaining' => 0,
                'period_label' => 'one-time payment',
                'freq_label' => 'One-time',
                'per' => '',
            ];
        }

        $durations = config('packages.durations');
        $durationMonths = in_array($durationMonths, $durations, true) ? $durationMonths : 12;

        $dailyPeriods = max(1, (int) round($durationMonths * 365 / 12));
        $dailyRate = max(1, (int) ceil($price / $dailyPeriods));

        [$periods, $per, $freqLabel] = match ($frequency) {
            'monthly' => [$durationMonths, '/mo', 'Monthly'],
            'weekly' => [max(1, (int) round($durationMonths * 52 / 12)), '/wk', 'Weekly'],
            default => [$dailyPeriods, '/day', 'Daily'],
        };

        $periods = max(1, (int) $periods);
        $rate = max(1, (int) ceil($price / $periods));

        $minFirst = max(
            (int) config('packages.min_first_payment', 10000),
            (int) config('packages.min_first_days', 10) * $dailyRate
        );
        $baseDue = min($price, max($minFirst, $rate));

        $total = $price + $domainFee;
        $dueToday = $baseDue + $domainFee;

        return [
            'duration_months' => $durationMonths,
            'periods' => $periods,
            'amount_per_period' => $rate,
            'daily_rate' => $dailyRate,
            'domain_fee' => $domainFee,
            'package_total' => $price,
            'total' => $total,
            'due_today' => $dueToday,
            'remaining' => max(0, $total - $dueToday),
            'period_label' => 'per '.ltrim($per, '/'),
            'freq_label' => $freqLabel,
            'per' => $per,
        ];
    }

    /**
     * A registrable hostname the customer says they already own.
     */
    public static function validHostname(string $domain): bool
    {
        return (bool) preg_match('/^(?!-)[a-z0-9-]{1,63}(?<!-)(\.[a-z0-9-]{1,63})+$/i', $domain);
    }
}
