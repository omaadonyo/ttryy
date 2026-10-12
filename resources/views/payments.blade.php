<x-layouts::app :title="__('Payment history')">
    @include('partials.toast')
    <div class="flex w-full flex-col gap-5">
        <x-page-head title="Payment history" sub="Every payment toward your websites, in one place.">
            <x-slot:actions>
                <a href="{{ route('orders.new') }}" class="btn-brand">New order</a>
                <button onclick="startTour_payments()" class="btn-ghost btn-sm">Take tour</button>
            </x-slot:actions>
        </x-page-head>

        @include('partials.tour', ['key' => 'payments', 'steps' => [
            ['target' => '#tour-pstats', 'title' => 'Money in, money owed', 'body' => 'Verified total paid vs what is still outstanding across your orders.'],
            ['target' => '#tour-pchart', 'title' => 'Paid per month', 'body' => 'Verified payments over the last 6 months — watch the habit build.'],
            ['target' => '#tour-ptable', 'title' => 'Every payment', 'body' => 'Date, reference, method, amount and status for the full history.'],
        ]])

        <div id="tour-pstats" class="grid auto-rows-min gap-4 md:grid-cols-2">
            <div class="card card-pad border-transparent! bg-brand-600 text-white dark:border-transparent!">
                <p class="text-[11px] font-bold tracking-[0.14em] text-white/60 uppercase">Total paid</p>
                <p class="stat-num mt-2 text-white!">UGX {{ number_format($paidTotal) }}</p>
            </div>
            <x-stat label="Still outstanding" :value="'UGX '.number_format($outstanding)" hint="Clears as payments land" />
        </div>

        <div id="tour-pchart" class="card card-pad">
            <h2 class="section-title">Paid per month</h2>
            <p class="page-sub">Verified payments, last 6 months.</p>
            <div class="mt-3 h-52"><canvas id="pay-month-chart"></canvas></div>
        </div>

        <div id="tour-ptable" class="card card-pad">
            <h2 class="section-title">All payments</h2>
            @if($orders->isEmpty())
                <x-empty-state icon="payments" title="No payments yet" message="Payments appear here once you pay toward an order." :actionUrl="route('orders.new')" actionLabel="Place an order" />
            @else
            <div class="tbl-wrap mt-4 rounded-none! border-0! shadow-none!">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Reference</th>
                            <th>Package</th>
                            <th>Method</th>
                            <th>Paid</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                        <tr>
                            <td class="text-xs whitespace-nowrap text-zinc-500">{{ ($order->paid_at ?? $order->created_at)->format('d M Y') }}</td>
                            <td class="font-mono text-xs">{{ $order->reference }}</td>
                            <td class="font-semibold">{{ $order->package }}</td>
                            <td>{{ $order->payment_method === 'momo_manual' ? 'MoMo manual' : ucfirst($order->payment_method) }}</td>
                            <td class="font-semibold">{{ $order->paid_amount > 0 ? 'UGX '.number_format($order->paid_amount) : '—' }}</td>
                            <td>
                                @if($order->status === 'paid')
                                <span class="pill-green">{{ $order->status }}</span>
                                @elseif($order->status === 'submitted')
                                <span class="pill-amber">{{ $order->status }}</span>
                                @else
                                <span class="pill-zinc">{{ $order->status }}</span>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function(){
  const el = document.getElementById('pay-month-chart');
  if(!el || typeof Chart === 'undefined') return;
  new Chart(el, { type: 'bar',
    data: { labels: @json($paidByMonth['labels']),
      datasets: [{ label: 'UGX paid', data: @json($paidByMonth['values']),
        backgroundColor: '#9e005d', borderRadius: 6 }] },
    options: { responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } } });
})();
</script>
</x-layouts::app>
