<x-layouts::app :title="__('Billing')">
    @include('partials.toast')
    <div class="flex w-full flex-col gap-5">
        <x-page-head :title="'Billing · '.$website->name" sub="Uses your existing ttryy orders — no separate billing system.">
            <x-slot:actions>
                <a href="{{ route('hosting.show', $website) }}" class="btn-ghost btn-sm">← Website</a>
                <a href="{{ route('checkout') }}" class="btn-brand btn-sm">New order</a>
            </x-slot:actions>
        </x-page-head>

        <div class="grid auto-rows-min gap-4 sm:grid-cols-3">
            <x-stat label="Plan" :value="$website->plan?->code ?? '—'" />
            <x-stat label="Paid here" :value="'UGX '.number_format($paid)" />
            <x-stat label="Still owed" :value="'UGX '.number_format($owed)" />
        </div>

        <div class="card card-pad">
            <h2 class="section-title">Linked orders</h2>
            @if($orders->isEmpty())
            <p class="py-4 text-center text-sm text-zinc-500">No orders linked to this website yet. Place an order and it will appear here with verified payments.</p>
            @else
            <div class="tbl-wrap mt-3 rounded-none! border-0! shadow-none!">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Paid</th>
                            <th>Status</th>
                            <th class="text-right!">Invoice</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $o)
                        <tr>
                            <td class="font-mono text-xs">{{ $o->reference }}</td>
                            <td class="text-xs text-zinc-500">{{ $o->created_at->format('d M Y') }}</td>
                            <td class="font-semibold">UGX {{ number_format($o->total_amount) }}</td>
                            <td class="font-semibold">UGX {{ number_format($o->paid_amount) }}</td>
                            <td><span class="{{ $o->status === 'paid' ? 'pill-green' : ($o->status === 'submitted' ? 'pill-amber' : 'pill-zinc') }}">{{ $o->status }}</span></td>
                            <td class="text-right"><a href="{{ route('orders.invoice', $o) }}" class="text-xs font-bold text-brand-700 hover:underline dark:text-brand-300">PDF →</a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</x-layouts::app>
