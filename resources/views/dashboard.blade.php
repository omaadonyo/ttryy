<x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4">
        <div>
            <flux:heading size="xl">Welcome back, {{ auth()->user()->name }}</flux:heading>
            <flux:text class="mt-1">Your websites, prospect lists and payments — all in one place.</flux:text>
        </div>

        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-5">
                <flux:text>Orders placed</flux:text>
                <p class="mt-1 text-3xl font-bold">{{ $totalOrders }}</p>
            </div>
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-5">
                <flux:text>Awaiting payment</flux:text>
                <p class="mt-1 text-3xl font-bold">{{ $pendingOrders }}</p>
            </div>
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-5">
                <flux:text>Total committed</flux:text>
                <p class="mt-1 text-3xl font-bold">UGX {{ number_format($totalCommitted) }}</p>
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            <flux:button :href="route('checkout')" variant="primary" wire:navigate>New order</flux:button>
            <flux:button :href="route('packages.index')" variant="ghost" wire:navigate>My packages</flux:button>
            <flux:button href="{{ route('home') }}#pricing" variant="ghost">View pricing</flux:button>
        </div>

        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-5">
            <flux:heading>Recent orders</flux:heading>
            @if($recentOrders->isEmpty())
                <flux:text class="mt-2">No orders yet. Choose a package to get your website and prospect list started.</flux:text>
            @else
                <div class="mt-3 overflow-x-auto">
                    <table class="w-full text-sm min-w-[520px]">
                        <thead>
                            <tr class="text-left text-zinc-500">
                                <th class="py-2 pr-4 font-medium">Reference</th>
                                <th class="py-2 pr-4 font-medium">Package</th>
                                <th class="py-2 pr-4 font-medium">Plan</th>
                                <th class="py-2 pr-4 font-medium">Total</th>
                                <th class="py-2 font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                            @foreach($recentOrders as $order)
                            <tr>
                                <td class="py-2.5 pr-4 font-mono text-xs">{{ $order->reference }}</td>
                                <td class="py-2.5 pr-4 font-semibold">{{ $order->package }}</td>
                                <td class="py-2.5 pr-4">{{ ucfirst($order->billing_frequency) }} · {{ $order->duration_months }} mo</td>
                                <td class="py-2.5 pr-4 font-semibold">UGX {{ number_format($order->total_amount) }}</td>
                                <td class="py-2.5"><flux:badge size="sm">{{ $order->status }}</flux:badge></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-layouts::app>
