<?php

namespace App\Http\Controllers;

use App\Mail\OrderPlaced;
use App\Models\PackageOrder;
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

        $billing = (string) $request->query('billing', 'monthly');
        if (! in_array($billing, config('packages.frequencies'), true)) {
            $billing = 'monthly';
        }

        $domain = (string) $request->query('domain', 'budget');
        if (! array_key_exists($domain, config('packages.domains'))) {
            $domain = 'budget';
        }

        $niche = (string) $request->query('niche', '');
        if (! in_array($niche, config('packages.niches'), true)) {
            $niche = '';
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
        ]);
    }

    public function store(Request $request)
    {
        $packages = config('packages.packages');

        $validated = $request->validate([
            'package' => ['required', Rule::in(array_keys($packages))],
            'billing_frequency' => ['required', Rule::in(config('packages.frequencies'))],
            'domain' => ['required', Rule::in(array_keys(config('packages.domains')))],
            'payment_method' => ['nullable', Rule::in(['online', 'momo_manual'])],
            'duration_months' => ['nullable', 'integer', Rule::in(config('packages.durations'))],
            'business_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'niche' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $method = $validated['payment_method'] ?? 'online';

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
            'duration_months' => $quote['duration_months'],
            'periods' => $quote['periods'],
            'amount_per_period' => $quote['amount_per_period'],
            'domain_fee' => $quote['domain_fee'],
            'total_amount' => $quote['total'],
            'due_today' => $quote['due_today'],
            'currency' => config('packages.currency'),
            'status' => $method === 'momo_manual' ? 'submitted' : 'pending',
            'payment_method' => $method === 'momo_manual' ? 'momo_manual' : 'flutterwave',
            'business_name' => $validated['business_name'],
            'phone' => $validated['phone'],
            'niche' => $validated['niche'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        $this->sendOrderEmails($order);

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

        $order = PackageOrder::where('reference', $data['tx_ref'])
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

        return view('my-packages', ['orders' => $orders]);
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
     * The domain fee is a one-time payment, always due upfront together
     * with the first installment (or the full price).
     *
     * @return array{duration_months:int, periods:int, amount_per_period:int, domain_fee:int, package_total:int, total:int, due_today:int, remaining:int, period_label:string}
     */
    public static function quote(string $package, string $frequency, int $durationMonths, string $domain = 'none'): array
    {
        $packages = config('packages.packages');
        $data = $packages[$package];
        $domainFee = config('packages.domains')[$domain]['fee'] ?? 0;

        if ($frequency === 'full') {
            return [
                'duration_months' => 12,
                'periods' => 1,
                'amount_per_period' => $data['price'],
                'domain_fee' => $domainFee,
                'package_total' => $data['price'],
                'total' => $data['price'] + $domainFee,
                'due_today' => $data['price'] + $domainFee,
                'remaining' => 0,
                'period_label' => 'one-time payment',
            ];
        }

        $durations = config('packages.durations');
        $durationMonths = in_array($durationMonths, $durations, true) ? $durationMonths : 12;

        [$periods, $rate, $label] = match ($frequency) {
            'monthly' => [$durationMonths, $data['monthly'], 'per month'],
            'weekly' => [(int) round($durationMonths * 52 / 12), $data['weekly'], 'per week'],
            default => [(int) round($durationMonths * 365 / 12), $data['daily'], 'per day'],
        };

        $packageTotal = $rate * $periods;

        return [
            'duration_months' => $durationMonths,
            'periods' => $periods,
            'amount_per_period' => $rate,
            'domain_fee' => $domainFee,
            'package_total' => $packageTotal,
            'total' => $packageTotal + $domainFee,
            'due_today' => $rate + $domainFee,
            'remaining' => $packageTotal + $domainFee - ($rate + $domainFee),
            'period_label' => $label,
        ];
    }
}
