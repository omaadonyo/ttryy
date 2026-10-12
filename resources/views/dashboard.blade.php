<x-layouts::app :title="__('Dashboard')">
    @include('partials.toast')
    @php
        $hour = now()->hour;
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
        $deltaPill = fn($d) => $d > 0 ? 'pill-green' : ($d < 0 ? 'pill-red' : 'pill-zinc');
        $deltaSign = fn($d) => $d > 0 ? '+' : '';
        $spotlight = $orders->first(fn ($o) => $o->isActive()) ?? $orders->first();
    @endphp
    <div class="flex w-full flex-col gap-5">
        <div class="card card-pad border-transparent! bg-zinc-950 text-white dark:border-transparent! dark:bg-white dark:text-zinc-950">
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div class="min-w-0">
                    <p class="text-[11px] font-bold tracking-[0.14em] uppercase opacity-50">{{ now()->format('l, d M Y') }} · Overview</p>
                    <h1 class="font-display mt-1.5 text-2xl leading-tight font-extrabold tracking-tight">{{ $greeting }}, {{ auth()->user()->name }}</h1>
                    <div class="mt-2.5 flex flex-wrap items-center gap-x-5 gap-y-1.5 text-[13px]">
                        <span class="inline-flex items-center gap-1.5"><strong class="font-extrabold">{{ $pendingOrders }}</strong><span class="opacity-55">awaiting payment</span></span>
                        <span class="inline-flex items-center gap-1.5"><strong class="font-extrabold">{{ number_format($tokenBalance) }}</strong><span class="opacity-55">tokens</span></span>
                        <span class="inline-flex items-center gap-1.5"><strong class="font-extrabold">{{ $mktStats['rate'] }}%</strong><span class="opacity-55">email open rate</span></span>
                    </div>
                </div>
                <div class="flex shrink-0 items-center gap-2 flex-wrap">
                    <a href="{{ route('marketing.index') }}" class="rounded-lg bg-white/10 px-4 py-2 text-sm font-bold text-white transition hover:bg-white/20 dark:bg-zinc-950/5 dark:text-zinc-900 dark:hover:bg-zinc-950/10">Send campaign</a>
                    <a href="{{ route('orders.new') }}" class="btn-brand btn-sm">New order</a>
                </div>
            </div>
        </div>

        <div class="grid auto-rows-min gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="card card-pad">
                <div class="flex items-center justify-between gap-2">
                    <p class="eyebrow">Orders · 30d</p>
                    <span class="{{ $deltaPill($kpis[0]['delta']) }}">{{ $deltaSign($kpis[0]['delta']) }}{{ $kpis[0]['delta'] }}%</span>
                </div>
                <p class="stat-num mt-2">{{ $kpis[0]['orders'] }}</p>
                <p class="mt-1.5 text-[13px] text-zinc-500 dark:text-zinc-400">{{ $totalOrders }} lifetime · vs prior 30 days</p>
            </div>
            <div class="card card-pad">
                <div class="flex items-center justify-between gap-2">
                    <p class="eyebrow">Committed · 30d</p>
                    <span class="{{ $deltaPill($kpis[1]['delta']) }}">{{ $deltaSign($kpis[1]['delta']) }}{{ $kpis[1]['delta'] }}%</span>
                </div>
                <p class="stat-num mt-2">UGX {{ number_format($kpis[1]['spend']) }}</p>
                <p class="mt-1.5 text-[13px] text-zinc-500 dark:text-zinc-400">UGX {{ number_format($totalCommitted) }} lifetime · vs prior 30 days</p>
            </div>
            <div class="card card-pad">
                <div class="flex items-center justify-between gap-2">
                    <p class="eyebrow">Emails sent · 30d</p>
                    <span class="{{ $deltaPill($kpis[2]['delta']) }}">{{ $deltaSign($kpis[2]['delta']) }}{{ $kpis[2]['delta'] }}%</span>
                </div>
                <p class="stat-num mt-2">{{ $kpis[2]['emails'] }}</p>
                <p class="mt-1.5 text-[13px] text-zinc-500 dark:text-zinc-400">{{ $mktStats['sent'] }} lifetime · vs prior 30 days</p>
            </div>
            <div class="card card-pad border-transparent! bg-brand-600 text-white dark:border-transparent!">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-[11px] font-bold tracking-[0.14em] text-white/60 uppercase">Open rate</p>
                    <span class="grid size-9 place-items-center rounded-lg bg-white/15 text-white">
                        <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>
                    </span>
                </div>
                <p class="stat-num mt-2 text-white!">{{ $mktStats['rate'] }}%</p>
                <p class="mt-1.5 text-[13px] text-white/60">{{ $mktStats['opened'] }} opened all time</p>
            </div>
        </div>

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

        @unless($checklistDone)
        <div data-onboard-banner="welcome" class="card card-pad border-brand-600/25 bg-brand-50/50 dark:border-brand-500/25 dark:bg-brand-500/10">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[11px] font-bold tracking-[0.14em] text-brand-700 uppercase dark:text-brand-300">Welcome to Ttryy — here's what to expect</p>
                    <h2 class="section-title mt-1">{{ $isNewUser ? 'Your first 4 steps' : 'Almost there — finish setup' }}</h2>
                </div>
                <button data-onboard-dismiss="welcome" class="btn-ghost btn-sm shrink-0">Skip tour</button>
            </div>
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                @foreach($checklist as $i => $step)
                <a href="{{ $step['url'] }}" class="flex gap-3 rounded-xl border border-zinc-200/80 bg-white p-4 transition hover:border-brand-600/40 dark:border-white/10 dark:bg-zinc-900 dark:hover:border-brand-500/40">
                    <span class="grid size-8 shrink-0 place-items-center rounded-lg {{ $step['done'] ? 'bg-emerald-600 text-white' : 'bg-brand-600 text-white' }} text-sm font-extrabold">
                        @if($step['done'])
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                        @else
                        {{ $i + 1 }}
                        @endif
                    </span>
                    <span class="min-w-0">
                        <span class="block text-sm font-extrabold">{{ $step['label'] }}</span>
                        <span class="mt-0.5 block text-[13px] text-zinc-500 dark:text-zinc-400">{{ $step['blurb'] }}</span>
                        <span class="mt-1 block text-[13px] font-bold text-brand-700 dark:text-brand-300">{{ $step['done'] ? 'Done — review →' : 'Start →' }}</span>
                    </span>
                </a>
                @endforeach
            </div>
        </div>
        @endunless

        <div class="grid gap-4 xl:grid-cols-2">
            <div class="card card-pad">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="section-title">Marketing performance</h2>
                    <a href="{{ route('marketing.index') }}" class="btn-ghost btn-sm">Open marketing</a>
                </div>
                @if($mktStats['campaigns'] === 0)
                <p class="mt-3 text-center text-sm text-zinc-500">No email campaigns yet — send your first one from the marketing tool.</p>
                @endif
                <div class="mt-3 h-52"><canvas id="dash-mkt-chart"></canvas></div>
                @if($mktStats['campaigns'] > 0)
                <div class="mt-2 divide-y divide-zinc-100 dark:divide-white/5">
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
                    <h2 class="section-title">Last 14 days</h2>
                    <a href="{{ route('analytics.index') }}" class="btn-ghost btn-sm">Full analytics</a>
                </div>
                <div class="mt-3 h-52"><canvas id="dash-activity-chart"></canvas></div>
                <div class="mt-2 flex items-center gap-4 text-xs text-zinc-500">
                    <span class="inline-flex items-center gap-1.5"><span class="size-2.5 rounded-sm bg-brand-600"></span>Orders placed</span>
                    <span class="inline-flex items-center gap-1.5"><span class="size-2.5 rounded-sm bg-zinc-300 dark:bg-zinc-600"></span>Emails sent</span>
                </div>
            </div>
        </div>

        <div class="grid gap-4 xl:grid-cols-3">
            <div class="card card-pad xl:col-span-2">
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
            <div class="card card-pad">
                <h2 class="section-title">Recent activity</h2>
                @if($activity->isEmpty())
                <p class="py-4 text-center text-sm text-zinc-500">Nothing yet — orders, payments and campaigns will appear here.</p>
                @else
                <div class="mt-2 space-y-1">
                    @foreach($activity as $n)
                    <a href="{{ route('notifications.open', $n->id) }}" class="flex gap-2.5 rounded-lg px-2 py-2 transition hover:bg-zinc-950/5 dark:hover:bg-white/10">
                        <span class="mt-1.5 size-2 shrink-0 rounded-full {{ ($n->data['kind'] ?? '') === 'payment' ? 'bg-emerald-500' : ((($n->data['kind'] ?? '') === 'tokens') ? 'bg-amber-500' : ((($n->data['kind'] ?? '') === 'email') ? 'bg-sky-500' : 'bg-brand-600')) }}"></span>
                        <span class="min-w-0">
                            <span class="block truncate text-[13px] font-bold">{{ $n->data['title'] ?? 'Notification' }}</span>
                            <span class="block text-[11px] text-zinc-400">{{ $n->created_at->diffForHumans() }}</span>
                        </span>
                    </a>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function(){
  if(typeof Chart === 'undefined') return;
  const mkt = document.getElementById('dash-mkt-chart');
  if(mkt) new Chart(mkt, { type: 'bar',
    data: { labels: @json($mktChart['labels']),
      datasets: [
        { label: 'Sent', data: @json($mktChart['sent']), backgroundColor: '#a1a1aa', borderRadius: 5 },
        { label: 'Opened', data: @json($mktChart['opened']), backgroundColor: '#9e005d', borderRadius: 5 },
      ] },
    options: { responsive: true, maintainAspectRatio: false,
      plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 16 } } },
      scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } } });
  const act = document.getElementById('dash-activity-chart');
  if(act) new Chart(act, { type: 'bar',
    data: { labels: @json($activityChart['labels']),
      datasets: [
        { label: 'Orders', data: @json($activityChart['orders']), backgroundColor: '#9e005d', borderRadius: 4 },
        { label: 'Emails', data: @json($activityChart['emails']), backgroundColor: '#d4d4d8', borderRadius: 4 },
      ] },
    options: { responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true, ticks: { precision: 0 } }, x: { ticks: { maxTicksLimit: 7 } } } } });
})();
</script>
</x-layouts::app>
