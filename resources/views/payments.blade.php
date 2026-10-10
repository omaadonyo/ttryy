<x-layouts::app :title="__('Payment history')">
    @include('partials.toast')
    <div class="flex h-full w-full flex-1 flex-col gap-4">
        <div class="flex items-end justify-between gap-3 flex-wrap">
            <div>
                <flux:heading size="xl">Payment history</flux:heading>
                <flux:text class="mt-1">Every payment toward your websites, in one place.</flux:text>
            </div>
            <flux:button :href="route('orders.new')" variant="primary" class="!bg-[#9e005d] hover:!bg-[#7e0049]" >New order</flux:button>
        </div>

        <div class="grid auto-rows-min gap-4 md:grid-cols-2">
            <div class="rounded-xl bg-[#9e005d] text-white p-5 shadow-[0_12px_32px_-12px_rgba(158,0,93,.4)]">
                <p class="text-sm opacity-70">Total paid</p>
                <p class="mt-1 text-3xl font-bold">UGX {{ number_format($paidTotal) }}</p>
            </div>
            <div class="rounded-xl bg-white dark:bg-white/[.04] p-5 shadow-[0_12px_32px_-12px_rgba(10,10,12,.18)] dark:shadow-[0_12px_32px_-12px_rgba(0,0,0,.7)]">
                <flux:text>Still outstanding</flux:text>
                <p class="mt-1 text-3xl font-bold">UGX {{ number_format($outstanding) }}</p>
            </div>
        </div>

        <div class="rounded-xl bg-white dark:bg-white/[.04] p-5 shadow-[0_12px_32px_-12px_rgba(10,10,12,.18)] dark:shadow-[0_12px_32px_-12px_rgba(0,0,0,.7)]">
            @if($orders->isEmpty())
                <x-empty-state icon="payments" title="No payments yet" message="Payments appear here once you pay toward an order." :actionUrl="route('orders.new')" actionLabel="Place an order" />
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[620px]">
                    <thead>
                        <tr class="text-left text-zinc-500">
                            <th class="py-2 pr-4 font-medium">Date</th>
                            <th class="py-2 pr-4 font-medium">Reference</th>
                            <th class="py-2 pr-4 font-medium">Package</th>
                            <th class="py-2 pr-4 font-medium">Method</th>
                            <th class="py-2 pr-4 font-medium">Paid</th>
                            <th class="py-2 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-white/10">
                        @foreach($orders as $order)
                        <tr>
                            <td class="py-2.5 pr-4 text-xs text-zinc-500">{{ ($order->paid_at ?? $order->created_at)->format('d M Y') }}</td>
                            <td class="py-2.5 pr-4 font-mono text-xs">{{ $order->reference }}</td>
                            <td class="py-2.5 pr-4 font-semibold">{{ $order->package }}</td>
                            <td class="py-2.5 pr-4">{{ $order->payment_method === 'momo_manual' ? 'MoMo manual' : ucfirst($order->payment_method) }}</td>
                            <td class="py-2.5 pr-4 font-semibold">{{ $order->paid_amount > 0 ? 'UGX '.number_format($order->paid_amount) : '—' }}</td>
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

