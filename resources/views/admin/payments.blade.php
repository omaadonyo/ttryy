<x-layouts::app :title="__('Payments')">
    <div class="flex h-full w-full flex-1 flex-col gap-4">
        <div>
            <flux:heading size="xl">Payments</flux:heading>
            <flux:text class="mt-1">Verified collections and manual Mobile Money claims awaiting confirmation.</flux:text>
        </div>

        <div class="grid auto-rows-min gap-4 md:grid-cols-2">
            <div class="rounded-xl bg-[#9e005d] text-white p-5 shadow-[0_12px_32px_-12px_rgba(158,0,93,.4)]">
                <p class="text-sm opacity-70">Collected (verified)</p>
                <p class="mt-1 text-3xl font-bold">UGX {{ number_format($collected) }}</p>
            </div>
            <div class="rounded-xl bg-white dark:bg-white/[.04] p-5 shadow-[0_12px_32px_-12px_rgba(10,10,12,.18)] dark:shadow-[0_12px_32px_-12px_rgba(0,0,0,.7)]">
                <flux:text>Awaiting confirmation</flux:text>
                <p class="mt-1 text-3xl font-bold">UGX {{ number_format($awaiting) }}</p>
            </div>
        </div>

        <div class="rounded-xl bg-white dark:bg-white/[.04] p-5 shadow-[0_12px_32px_-12px_rgba(10,10,12,.18)] dark:shadow-[0_12px_32px_-12px_rgba(0,0,0,.7)]">
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[720px]">
                    <thead>
                        <tr class="text-left text-zinc-500">
                            <th class="py-2 pr-4 font-medium">Reference</th>
                            <th class="py-2 pr-4 font-medium">Customer</th>
                            <th class="py-2 pr-4 font-medium">Method</th>
                            <th class="py-2 pr-4 font-medium">Paid</th>
                            <th class="py-2 pr-4 font-medium">Due today</th>
                            <th class="py-2 pr-4 font-medium">When</th>
                            <th class="py-2 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-white/10">
                        @forelse($orders as $order)
                        <tr>
                            <td class="py-2.5 pr-4 font-mono text-xs">{{ $order->reference }}</td>
                            <td class="py-2.5 pr-4">{{ $order->user->name }}<p class="text-xs text-zinc-500">{{ $order->phone }}</p></td>
                            <td class="py-2.5 pr-4">{{ $order->payment_method === 'momo_manual' ? 'MoMo manual' : $order->payment_method }}@if($order->tx_ref)<p class="text-xs text-zinc-500 font-mono">{{ $order->tx_ref }}</p>@endif</td>
                            <td class="py-2.5 pr-4 font-semibold">{{ $order->paid_amount > 0 ? 'UGX '.number_format($order->paid_amount) : '—' }}</td>
                            <td class="py-2.5 pr-4">UGX {{ number_format($order->due_today) }}</td>
                            <td class="py-2.5 pr-4 text-xs text-zinc-500">{{ $order->paid_at ? $order->paid_at->format('d M Y H:i') : $order->created_at->format('d M Y H:i') }}</td>
                            <td class="py-2.5">
                                @if($order->status === 'paid')
                                <flux:badge size="sm" color="emerald">{{ $order->status }}</flux:badge>
                                @else
                                <flux:badge size="sm" color="sky">{{ $order->status }}</flux:badge>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="py-6 text-center text-zinc-500">No payments yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $orders->links() }}</div>
        </div>
    </div>
</x-layouts::app>
