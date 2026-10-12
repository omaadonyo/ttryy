<x-layouts::app :title="__('Payments')">
    @include('partials.toast')
    <div class="flex w-full flex-col gap-5">
        <x-page-head title="Payments" sub="Verified collections and manual Mobile Money claims awaiting confirmation." />

        <div class="grid auto-rows-min gap-4 md:grid-cols-2">
            <div class="card card-pad border-transparent! bg-brand-600 text-white dark:border-transparent!">
                <p class="text-[11px] font-bold tracking-[0.14em] text-white/60 uppercase">Collected (verified)</p>
                <p class="stat-num mt-2 text-white!">UGX {{ number_format($collected) }}</p>
            </div>
            <x-stat label="Awaiting confirmation" :value="'UGX '.number_format($awaiting)" hint="Manual MoMo claims to verify" />
        </div>

        <div class="card card-pad">
            <h2 class="section-title">Collection mix</h2>
            <p class="page-sub">Where verified money came from.</p>
            @php $mixTotal = max(1, $onlineCollected + $momoCollected); @endphp
            <div class="mt-3 space-y-2.5">
                <div class="flex items-center gap-3">
                    <span class="w-36 shrink-0 text-[13px] font-semibold">Online · card/MoMo</span>
                    <div class="h-2.5 flex-1 overflow-hidden rounded-full bg-zinc-100 dark:bg-white/10">
                        <div class="h-2.5 rounded-full bg-brand-600" style="width:{{ round($onlineCollected / $mixTotal * 100) }}%"></div>
                    </div>
                    <span class="w-28 shrink-0 text-right text-xs font-bold">UGX {{ number_format($onlineCollected) }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="w-36 shrink-0 text-[13px] font-semibold">Manual MoMo</span>
                    <div class="h-2.5 flex-1 overflow-hidden rounded-full bg-zinc-100 dark:bg-white/10">
                        <div class="h-2.5 rounded-full bg-emerald-600" style="width:{{ round($momoCollected / $mixTotal * 100) }}%"></div>
                    </div>
                    <span class="w-28 shrink-0 text-right text-xs font-bold">UGX {{ number_format($momoCollected) }}</span>
                </div>
            </div>
        </div>

        <div class="card card-pad">
            <h2 class="section-title">All payments</h2>
            <div class="tbl-wrap mt-3 rounded-none! border-0! shadow-none!">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Customer</th>
                            <th>Method</th>
                            <th>Paid</th>
                            <th>Due today</th>
                            <th>When</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr>
                            <td class="font-mono text-xs">{{ $order->reference }}</td>
                            <td>{{ $order->user->name }}<p class="text-xs text-zinc-500">{{ $order->phone }}</p></td>
                            <td>{{ $order->payment_method === 'momo_manual' ? 'MoMo manual' : $order->payment_method }}@if($order->tx_ref)<p class="font-mono text-xs text-zinc-500">{{ $order->tx_ref }}</p>@endif</td>
                            <td class="font-semibold whitespace-nowrap">{{ $order->paid_amount > 0 ? 'UGX '.number_format($order->paid_amount) : '—' }}</td>
                            <td class="whitespace-nowrap">UGX {{ number_format($order->due_today) }}</td>
                            <td class="text-xs whitespace-nowrap text-zinc-500">{{ $order->paid_at ? $order->paid_at->format('d M Y H:i') : $order->created_at->format('d M Y H:i') }}</td>
                            <td>
                                @if($order->status === 'paid')
                                <span class="pill-green">{{ $order->status }}</span>
                                @else
                                <span class="pill-amber">{{ $order->status }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7"><x-empty-state icon="payments" title="No payments yet" message="Verified collections and manual Mobile Money claims will appear here." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $orders->links() }}</div>
        </div>
    </div>
</x-layouts::app>
