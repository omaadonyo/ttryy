<x-layouts::app :title="__('Admin overview')">
    @include('partials.toast')
    <div class="flex h-full w-full flex-1 flex-col gap-4">
        <div>
            <flux:heading size="xl">Administration</flux:heading>
            <flux:text class="mt-1">Orders, payments, users and package performance.</flux:text>
        </div>

        @if(session('status'))
        <div class="rounded-xl bg-emerald-500/10 border border-emerald-500/30 px-5 py-3 text-sm text-emerald-700 dark:text-emerald-400">{{ session('status') }}</div>
        @endif

        <div class="grid auto-rows-min gap-4 md:grid-cols-4">
            <div class="rounded-xl bg-white dark:bg-white/[.04] p-5 shadow-[0_12px_32px_-12px_rgba(10,10,12,.18)] dark:shadow-[0_12px_32px_-12px_rgba(0,0,0,.7)]">
                <flux:text>Total orders</flux:text>
                <p class="mt-1 text-3xl font-bold">{{ $totalOrders }}</p>
                <p class="mt-1 text-xs text-zinc-500">{{ $pendingOrders }} pending · {{ $submittedOrders }} submitted · {{ $paidOrders }} paid</p>
            </div>
            <div class="rounded-xl bg-white dark:bg-white/[.04] p-5 shadow-[0_12px_32px_-12px_rgba(10,10,12,.18)] dark:shadow-[0_12px_32px_-12px_rgba(0,0,0,.7)]">
                <flux:text>Collected</flux:text>
                <p class="mt-1 text-3xl font-bold text-[#9e005d]">UGX {{ number_format($collected) }}</p>
                <p class="mt-1 text-xs text-zinc-500">Verified payments</p>
            </div>
            <div class="rounded-xl bg-white dark:bg-white/[.04] p-5 shadow-[0_12px_32px_-12px_rgba(10,10,12,.18)] dark:shadow-[0_12px_32px_-12px_rgba(0,0,0,.7)]">
                <flux:text>Outstanding</flux:text>
                <p class="mt-1 text-3xl font-bold">UGX {{ number_format($outstanding) }}</p>
                <p class="mt-1 text-xs text-zinc-500">Unpaid order totals</p>
            </div>
            <div class="rounded-xl bg-zinc-950 dark:bg-[#9e005d] text-white p-5 shadow-[0_12px_32px_-12px_rgba(10,10,12,.35)]">
                <p class="text-sm opacity-70">Registered users</p>
                <p class="mt-1 text-3xl font-bold">{{ $totalUsers }}</p>
                <p class="mt-1 text-xs opacity-60">Across the platform</p>
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            <flux:button :href="route('admin.orders')" variant="primary" class="!bg-[#9e005d] hover:!bg-[#7e0049]" wire:navigate>Manage orders</flux:button>
            <flux:button :href="route('admin.payments')" variant="ghost" wire:navigate>Payments</flux:button>
            <flux:button :href="route('admin.users')" variant="ghost" wire:navigate>Users</flux:button>
            <flux:button :href="route('admin.packages')" variant="ghost" wire:navigate>Packages</flux:button>
        </div>

        <div class="rounded-xl bg-white dark:bg-white/[.04] p-5 shadow-[0_12px_32px_-12px_rgba(10,10,12,.18)] dark:shadow-[0_12px_32px_-12px_rgba(0,0,0,.7)]">
            <flux:heading>Latest orders</flux:heading>
            <div class="mt-3 overflow-x-auto">
                <table class="w-full text-sm min-w-[640px]">
                    <thead>
                        <tr class="text-left text-zinc-500">
                            <th class="py-2 pr-4 font-medium">Reference</th>
                            <th class="py-2 pr-4 font-medium">Customer</th>
                            <th class="py-2 pr-4 font-medium">Package</th>
                            <th class="py-2 pr-4 font-medium">Total</th>
                            <th class="py-2 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-white/10">
                        @forelse($recentOrders as $order)
                        <tr>
                            <td class="py-2.5 pr-4 font-mono text-xs">{{ $order->reference }}</td>
                            <td class="py-2.5 pr-4">{{ $order->user->name }}</td>
                            <td class="py-2.5 pr-4 font-semibold">{{ $order->package }}</td>
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
                        @empty
                        <tr><td colspan="5" class="py-6 text-center text-zinc-500">No orders yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts::app>
