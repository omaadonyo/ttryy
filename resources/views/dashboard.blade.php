<x-layouts::app :title="__('Dashboard')">
    @include('partials.toast')
    <div class="flex w-full flex-col gap-5">
        <x-page-head :title="'Welcome back, '.auth()->user()->name" sub="Your websites, prospect lists and payments — all in one place.">
            <x-slot:actions>
                <a href="{{ route('orders.new') }}" class="btn-brand">New order</a>
            </x-slot:actions>
        </x-page-head>

        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div class="card card-pad">
                <div class="flex items-center justify-between gap-2">
                    <p class="eyebrow">Orders placed</p>
                    <span class="grid size-10 place-items-center rounded-xl bg-zinc-950 text-white dark:bg-white dark:text-zinc-950">
                        <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M6 7h12l1 13H5L6 7z"/><path d="M9 10V6a3 3 0 016 0v4"/></svg>
                    </span>
                </div>
                <p class="stat-num mt-2">{{ $totalOrders }}</p>
                <p class="mt-1.5 text-[13px] text-zinc-500 dark:text-zinc-400">Lifetime orders on your account</p>
            </div>
            <div class="card card-pad">
                <div class="flex items-center justify-between gap-2">
                    <p class="eyebrow">Awaiting payment</p>
                    <span class="grid size-10 place-items-center rounded-xl bg-brand-600 text-white">
                        <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                    </span>
                </div>
                <p class="stat-num mt-2">{{ $pendingOrders }}</p>
                <p class="mt-1.5 text-[13px] text-zinc-500 dark:text-zinc-400">Need follow-up to go live</p>
            </div>
            <div class="card card-pad border-transparent! bg-zinc-950 text-white dark:border-transparent! dark:bg-brand-600">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-[11px] font-bold tracking-[0.14em] text-white/60 uppercase">Total committed</p>
                    <span class="grid size-10 place-items-center rounded-xl bg-white/15 text-white">
                        <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 10h18M7 15h4"/></svg>
                    </span>
                </div>
                <p class="stat-num mt-2 text-white!">UGX {{ number_format($totalCommitted) }}</p>
                <p class="mt-1.5 text-[13px] text-white/60">Across all orders</p>
            </div>
        </div>

        @php $spotlight = $orders->first(fn ($o) => $o->isActive()) ?? $orders->first(); @endphp
        @if($spotlight)
        <div class="card card-pad">
            <div class="flex items-center justify-between gap-3 flex-wrap">
                <div class="min-w-0">
                    <h2 class="section-title">{{ $spotlight->package }} subscription</h2>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                        @if($spotlight->isActive())
                        {{ $spotlight->daysLeft() }} of {{ $spotlight->totalDays() }} days left · expires {{ $spotlight->expiresAt()->format('d M Y') }}
                        @elseif($spotlight->isExpired())
                        Expired {{ $spotlight->expiresAt()->format('d M Y') }} — renew to keep your website running.
                        @else
                        Awaiting payment — your website goes live once the first payment lands.
                        @endif
                    </p>
                </div>
                @if($spotlight->status === 'paid')
                <span class="pill-green">{{ $spotlight->isActive() ? 'active' : 'expired' }}</span>
                @elseif($spotlight->status === 'submitted')
                <span class="pill-amber">{{ $spotlight->status }}</span>
                @else
                <span class="pill-zinc">{{ $spotlight->status }}</span>
                @endif
            </div>
            @if($spotlight->isActive() || $spotlight->isExpired())
            <div class="mt-4 h-2 overflow-hidden rounded-full bg-zinc-100 dark:bg-white/10">
                <div class="h-2 rounded-full bg-brand-600" style="width:{{ $spotlight->progressPercent() }}%"></div>
            </div>
            <p class="mt-1.5 text-xs text-zinc-500">{{ $spotlight->progressPercent() }}% of subscription period used</p>
            @endif
        </div>
        @endif

        <div class="card card-pad">
            <div class="flex items-center justify-between gap-3">
                <h2 class="section-title">Marketing performance</h2>
                <a href="{{ route('marketing.index') }}" class="btn-ghost btn-sm">Open marketing</a>
            </div>
            @if($mktStats['campaigns'] === 0)
            <p class="mt-3 text-center text-sm text-zinc-500">No email campaigns yet — send your first one from the marketing tool.</p>
            @endif
            <div class="mt-4 grid grid-cols-2 gap-3 md:grid-cols-4">
                <div class="rounded-xl border border-zinc-200/70 bg-zinc-50 p-3 dark:border-white/10 dark:bg-white/5">
                    <p class="eyebrow">Campaigns</p>
                    <p class="font-display mt-1 text-[22px] leading-none font-extrabold tracking-tight">{{ $mktStats['campaigns'] }}</p>
                </div>
                <div class="rounded-xl border border-zinc-200/70 bg-zinc-50 p-3 dark:border-white/10 dark:bg-white/5">
                    <p class="eyebrow">Emails sent</p>
                    <p class="font-display mt-1 text-[22px] leading-none font-extrabold tracking-tight">{{ $mktStats['sent'] }}</p>
                </div>
                <div class="rounded-xl border border-zinc-200/70 bg-zinc-50 p-3 dark:border-white/10 dark:bg-white/5">
                    <p class="eyebrow">Opened</p>
                    <p class="font-display mt-1 text-[22px] leading-none font-extrabold tracking-tight text-brand-700 dark:text-brand-300">{{ $mktStats['opened'] }}</p>
                </div>
                <div class="rounded-xl border border-zinc-200/70 bg-zinc-50 p-3 dark:border-white/10 dark:bg-white/5">
                    <p class="eyebrow">Open rate</p>
                    <p class="font-display mt-1 text-[22px] leading-none font-extrabold tracking-tight">{{ $mktStats['rate'] }}%</p>
                </div>
            </div>
            <div class="mt-4 h-52"><canvas id="dash-mkt-chart"></canvas></div>
            @if($mktStats['campaigns'] > 0)
            <div class="mt-3 divide-y divide-zinc-100 dark:divide-white/5">
                @foreach($campaigns as $cp)
                <a href="{{ route('marketing.campaigns.show', $cp) }}" class="flex items-center justify-between gap-3 py-2.5">
                    <span class="min-w-0 truncate text-sm font-semibold">{{ $cp->subject }}</span>
                    <span class="shrink-0 text-xs text-zinc-500">{{ $cp->sent }} sent · <span class="font-bold text-brand-700 dark:text-brand-300">{{ $cp->read_count }} opened</span></span>
                </a>
                @endforeach
            </div>
            @endif
        </div>

        <div class="card card-pad">
            <div class="flex items-center justify-between gap-3">
                <h2 class="section-title">Recent orders</h2>
                <a href="{{ route('packages.index') }}" class="btn-ghost btn-sm">View all</a>
            </div>
            @if($recentOrders->isEmpty())
                <x-empty-state icon="orders" title="No orders yet" message="Choose a package to get your website and prospect list started." :actionUrl="route('orders.new')" actionLabel="Choose a package" />
            @else
                <div class="tbl-wrap mt-4 rounded-none! border-0! shadow-none!">
                    <table class="tbl">
                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>Package</th>
                                <th>Plan</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentOrders as $order)
                            <tr>
                                <td class="font-mono text-xs">{{ $order->reference }}</td>
                                <td class="font-semibold">{{ $order->package }}</td>
                                <td>{{ ucfirst($order->billing_frequency) }} · {{ $order->duration_months }} mo</td>
                                <td class="font-semibold">UGX {{ number_format($order->total_amount) }}</td>
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
  const el = document.getElementById('dash-mkt-chart');
  if(!el || typeof Chart === 'undefined') return;
  new Chart(el, { type: 'bar',
    data: { labels: @json($mktChart['labels']),
      datasets: [
        { label: 'Sent', data: @json($mktChart['sent']), backgroundColor: '#a1a1aa', borderRadius: 5 },
        { label: 'Opened', data: @json($mktChart['opened']), backgroundColor: '#9e005d', borderRadius: 5 },
      ] },
    options: { responsive: true, maintainAspectRatio: false,
      plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 16 } } },
      scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } } });
})();
</script>
</x-layouts::app>
