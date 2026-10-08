<x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4">
        <div class="flex items-end justify-between gap-3 flex-wrap">
            <div>
                <flux:heading size="xl">Welcome back, {{ auth()->user()->name }}</flux:heading>
                <flux:text class="mt-1">Your websites, prospect lists and payments — all in one place.</flux:text>
            </div>
            <flux:button :href="route('checkout')" variant="primary" class="!bg-[#9e005d] hover:!bg-[#7e0049]" wire:navigate>New order</flux:button>
        </div>

        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div class="rounded-xl bg-white dark:bg-white/[.04] p-5 shadow-[0_12px_32px_-12px_rgba(10,10,12,.18)] dark:shadow-[0_12px_32px_-12px_rgba(0,0,0,.7)]">
                <div class="flex items-center justify-between gap-2">
                    <flux:text>Orders placed</flux:text>
                    <span class="grid size-9 place-items-center rounded-lg bg-zinc-950 dark:bg-white text-white dark:text-zinc-950">
                        <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M6 7h12l1 13H5L6 7z"/><path d="M9 10V6a3 3 0 016 0v4"/></svg>
                    </span>
                </div>
                <p class="mt-2 text-3xl font-bold">{{ $totalOrders }}</p>
                <p class="mt-1 text-xs text-zinc-500">Lifetime orders on your account</p>
            </div>
            <div class="rounded-xl bg-white dark:bg-white/[.04] p-5 shadow-[0_12px_32px_-12px_rgba(10,10,12,.18)] dark:shadow-[0_12px_32px_-12px_rgba(0,0,0,.7)]">
                <div class="flex items-center justify-between gap-2">
                    <flux:text>Awaiting payment</flux:text>
                    <span class="grid size-9 place-items-center rounded-lg bg-[#9e005d] text-white">
                        <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                    </span>
                </div>
                <p class="mt-2 text-3xl font-bold">{{ $pendingOrders }}</p>
                <p class="mt-1 text-xs text-zinc-500">Need follow-up to go live</p>
            </div>
            <div class="rounded-xl bg-zinc-950 dark:bg-[#9e005d] text-white p-5 shadow-[0_12px_32px_-12px_rgba(10,10,12,.35)]">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-sm opacity-70">Total committed</p>
                    <span class="grid size-9 place-items-center rounded-lg bg-white/15 dark:bg-white/20 text-white">
                        <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 10h18M7 15h4"/></svg>
                    </span>
                </div>
                <p class="mt-2 text-3xl font-bold">UGX {{ number_format($totalCommitted) }}</p>
                <p class="mt-1 text-xs opacity-60">Across all orders</p>
            </div>
        </div>

        @php $spotlight = $orders->first(fn ($o) => $o->isActive()) ?? $orders->first(); @endphp
        @if($spotlight)
        <div class="rounded-xl bg-white dark:bg-white/[.04] p-5 shadow-[0_12px_32px_-12px_rgba(10,10,12,.18)] dark:shadow-[0_12px_32px_-12px_rgba(0,0,0,.7)]">
            <div class="flex items-center justify-between gap-3 flex-wrap">
                <div>
                    <flux:heading>{{ $spotlight->package }} subscription</flux:heading>
                    <flux:text class="mt-1 text-sm">
                        @if($spotlight->isActive())
                        {{ $spotlight->daysLeft() }} of {{ $spotlight->totalDays() }} days left · expires {{ $spotlight->expiresAt()->format('d M Y') }}
                        @elseif($spotlight->isExpired())
                        Expired {{ $spotlight->expiresAt()->format('d M Y') }} — renew to keep your website running.
                        @else
                        Awaiting payment — your website goes live once the first payment lands.
                        @endif
                    </flux:text>
                </div>
                @if($spotlight->status === 'paid')
                <flux:badge size="sm" color="emerald">{{ $spotlight->isActive() ? 'active' : 'expired' }}</flux:badge>
                @elseif($spotlight->status === 'submitted')
                <flux:badge size="sm" color="sky">{{ $spotlight->status }}</flux:badge>
                @else
                <flux:badge size="sm" color="zinc">{{ $spotlight->status }}</flux:badge>
                @endif
            </div>
            @if($spotlight->isActive() || $spotlight->isExpired())
            <div class="mt-3 h-2 rounded-full bg-zinc-100 dark:bg-white/10 overflow-hidden">
                <div class="h-2 rounded-full bg-[#9e005d]" style="width:{{ $spotlight->progressPercent() }}%"></div>
            </div>
            <p class="mt-1.5 text-xs text-zinc-500">{{ $spotlight->progressPercent() }}% of subscription period used</p>
            @endif
        </div>
        @endif

        <div class="rounded-xl bg-white dark:bg-white/[.04] p-5 shadow-[0_12px_32px_-12px_rgba(10,10,12,.18)] dark:shadow-[0_12px_32px_-12px_rgba(0,0,0,.7)]">
            <div class="flex items-center justify-between gap-3">
                <flux:heading>Recent orders</flux:heading>
                <flux:button :href="route('packages.index')" variant="ghost" size="sm" wire:navigate>View all</flux:button>
            </div>
            @if($recentOrders->isEmpty())
                <flux:text class="mt-2">No orders yet. Choose a package to get your website and prospect list started.</flux:text>
                <div class="mt-4 flex flex-wrap gap-3">
                    <flux:button :href="route('checkout')" variant="primary" class="!bg-[#9e005d] hover:!bg-[#7e0049]" wire:navigate>Choose a package</flux:button>
                    <flux:button :href="route('scraper.index')" variant="ghost" wire:navigate>Try the prospect scraper</flux:button>
                </div>
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
                        <tbody class="divide-y divide-neutral-200 dark:divide-white/10">
                            @foreach($recentOrders as $order)
                            <tr>
                                <td class="py-2.5 pr-4 font-mono text-xs">{{ $order->reference }}</td>
                                <td class="py-2.5 pr-4 font-semibold">{{ $order->package }}</td>
                                <td class="py-2.5 pr-4">{{ ucfirst($order->billing_frequency) }} · {{ $order->duration_months }} mo</td>
                                <td class="py-2.5 pr-4 font-semibold">UGX {{ number_format($order->total_amount) }}</td>
                                <td class="py-2.5">
                                    @if($order->status === 'paid')
                                    <flux:badge size="sm" color="emerald">{{ $order->status }}</flux:badge>
                                    @elseif($order->status === 'submitted')
                                    <flux:badge size="sm" color="sky">{{ $order->status }}</flux:badge>
                                    @else
                                    <flux:badge size="sm" color="zinc">{{ $order->status }}</flux:badge>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-layouts::app>
