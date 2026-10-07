<?php

namespace App\Http\Controllers;

use App\Models\PackageOrder;
use Illuminate\Http\Request;
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

        $niche = (string) $request->query('niche', '');
        if (! in_array($niche, config('packages.niches'), true)) {
            $niche = '';
        }

        return view('checkout', [
            'packages' => $packages,
            'selectedPackage' => $package,
            'selectedNiche' => $niche,
            'durations' => config('packages.durations'),
            'currency' => config('packages.currency'),
        ]);
    }

    public function store(Request $request)
    {
        $packages = config('packages.packages');

        $validated = $request->validate([
            'package' => ['required', Rule::in(array_keys($packages))],
            'billing_frequency' => ['required', Rule::in(config('packages.frequencies'))],
            'duration_months' => ['nullable', 'integer', Rule::in(config('packages.durations'))],
            'business_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'niche' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $quote = $this->quote(
            $validated['package'],
            $validated['billing_frequency'],
            (int) ($validated['duration_months'] ?? 12)
        );

        do {
            $reference = 'TTRYY-'.strtoupper(Str::random(6));
        } while (PackageOrder::where('reference', $reference)->exists());

        $order = PackageOrder::create([
            'user_id' => $request->user()->id,
            'reference' => $reference,
            'package' => $validated['package'],
            'billing_frequency' => $validated['billing_frequency'],
            'duration_months' => $quote['duration_months'],
            'periods' => $quote['periods'],
            'amount_per_period' => $quote['amount_per_period'],
            'total_amount' => $quote['total'],
            'currency' => config('packages.currency'),
            'status' => 'pending',
            'business_name' => $validated['business_name'],
            'phone' => $validated['phone'],
            'niche' => $validated['niche'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('checkout.success', $order);
    }

    public function success(PackageOrder $order)
    {
        abort_if($order->user_id !== request()->user()->id, 403);

        return view('order-confirmation', ['order' => $order]);
    }

    public function orders()
    {
        $orders = request()->user()->packageOrders()->latest()->get();

        return view('my-packages', ['orders' => $orders]);
    }

    /**
     * Server-side quote. Amounts are never trusted from the client.
     *
     * @return array{duration_months:int, periods:int, amount_per_period:int, total:int, period_label:string}
     */
    public static function quote(string $package, string $frequency, int $durationMonths): array
    {
        $packages = config('packages.packages');
        $data = $packages[$package];

        if ($frequency === 'full') {
            return [
                'duration_months' => 12,
                'periods' => 1,
                'amount_per_period' => $data['price'],
                'total' => $data['price'],
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

        return [
            'duration_months' => $durationMonths,
            'periods' => $periods,
            'amount_per_period' => $rate,
            'total' => $rate * $periods,
            'period_label' => $label,
        ];
    }
}
