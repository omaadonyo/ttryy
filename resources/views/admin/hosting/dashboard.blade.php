<x-layouts::app :title="__('Hosting control')">
    @include('partials.toast')
    @php
        $statusPill = fn($s) => match($s) {
            'active' => 'pill-green', 'suspended', 'failed' => 'pill-red',
            'provisioning', 'pending_setup' => 'pill-amber', default => 'pill-zinc',
        };
    @endphp
    <div class="flex w-full flex-col gap-5">
        <x-page-head title="Hosting control" sub="Every client website, domain, operation and event — one screen.">
            <x-slot:actions>
                <a href="{{ route('admin.hosting.websites') }}" class="btn-brand btn-sm">All websites</a>
                <a href="{{ route('admin.hosting.audit') }}" class="btn-ghost btn-sm">Audit log</a>
            </x-slot:actions>
        </x-page-head>

        <div class="grid auto-rows-min grid-cols-2 gap-4 xl:grid-cols-4">
            <x-stat label="Clients" :value="$clients" />
            <x-stat label="Websites" :value="$total" />
            <x-stat label="Active" :value="$active" />
            <x-stat label="Suspended" :value="$suspended" />
            <x-stat label="Provisioning" :value="$provisioning" />
            <x-stat label="Failed" :value="$failed" />
            <x-stat label="Domains" :value="$domains" :hint="$pendingVerification.' pending verification'" />
            <div class="card card-pad border-transparent! bg-zinc-950 text-white dark:border-transparent! dark:bg-brand-600">
                <p class="text-[11px] font-bold tracking-[0.14em] text-white/60 uppercase">Hosting revenue</p>
                <p class="stat-num mt-2 text-white!">UGX {{ number_format($revenue) }}</p>
                <p class="mt-1.5 text-[13px] text-white/60">UGX {{ number_format($outstanding) }} outstanding</p>
            </div>
        </div>

        <div class="card card-pad">
            <h2 class="section-title">Websites by status</h2>
            <div class="mt-3 h-48"><canvas id="ah-status"></canvas></div>
        </div>

        <div class="card card-pad">
            <div class="flex items-center justify-between gap-3 flex-wrap">
                <div>
                    <h2 class="section-title">Platform usage</h2>
                    <p class="page-sub">How the product is actually being used.</p>
                </div>
                <span class="pill-brand">{{ $total }} websites</span>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-3 xl:grid-cols-4">
                <div class="rounded-xl border border-zinc-200/70 bg-zinc-50 p-3 dark:border-white/10 dark:bg-white/5">
                    <p class="eyebrow">Tokens sold</p>
                    <p class="font-display mt-1 text-[22px] leading-none font-extrabold tracking-tight">{{ number_format($usage['tokensSold']) }}</p>
                </div>
                <div class="rounded-xl border border-zinc-200/70 bg-zinc-50 p-3 dark:border-white/10 dark:bg-white/5">
                    <p class="eyebrow">Tokens used</p>
                    <p class="font-display mt-1 text-[22px] leading-none font-extrabold tracking-tight">{{ number_format($usage['tokensUsed']) }}</p>
                </div>
                <div class="rounded-xl border border-zinc-200/70 bg-zinc-50 p-3 dark:border-white/10 dark:bg-white/5">
                    <p class="eyebrow">Tokens held</p>
                    <p class="font-display mt-1 text-[22px] leading-none font-extrabold tracking-tight text-brand-700 dark:text-brand-300">{{ number_format($usage['tokensHeld']) }}</p>
                </div>
                <div class="rounded-xl border border-zinc-200/70 bg-zinc-50 p-3 dark:border-white/10 dark:bg-white/5">
                    <p class="eyebrow">Top-up revenue</p>
                    <p class="font-display mt-1 text-[22px] leading-none font-extrabold tracking-tight">UGX {{ number_format($usage['topupRevenue']) }}</p>
                </div>
            </div>
            <div class="mt-4 h-52"><canvas id="admin-users-chart"></canvas></div>
            <div class="mt-4 grid grid-cols-2 gap-3 xl:grid-cols-4">
                <div class="rounded-xl border border-zinc-200/70 bg-zinc-50 p-3 dark:border-white/10 dark:bg-white/5">
                    <p class="eyebrow">Campaigns sent</p>
                    <p class="font-display mt-1 text-[22px] leading-none font-extrabold tracking-tight">{{ number_format($usage['campaigns']) }}</p>
                    <p class="mt-1 text-xs text-zinc-500">{{ number_format($usage['emailSent']) }} emails · {{ $usage['emailRate'] }}% opened</p>
                </div>
                <div class="rounded-xl border border-zinc-200/70 bg-zinc-50 p-3 dark:border-white/10 dark:bg-white/5">
                    <p class="eyebrow">Prospects indexed</p>
                    <p class="font-display mt-1 text-[22px] leading-none font-extrabold tracking-tight">{{ number_format($usage['prospectsIndexed']) }}</p>
                    <p class="mt-1 text-xs text-zinc-500">Across directories</p>
                </div>
                <div class="rounded-xl border border-zinc-200/70 bg-zinc-50 p-3 dark:border-white/10 dark:bg-white/5">
                    <p class="eyebrow">Contacts saved</p>
                    <p class="font-display mt-1 text-[22px] leading-none font-extrabold tracking-tight">{{ number_format($usage['contactsSaved']) }}</p>
                    <p class="mt-1 text-xs text-zinc-500">By all users</p>
                </div>
                <div class="rounded-xl border border-zinc-200/70 bg-zinc-50 p-3 dark:border-white/10 dark:bg-white/5">
                    <p class="eyebrow">Groups listed</p>
                    <p class="font-display mt-1 text-[22px] leading-none font-extrabold tracking-tight">{{ number_format($usage['groupsListed']) }}</p>
                    <p class="mt-1 text-xs text-zinc-500">Active communities</p>
                </div>
            </div>
        </div>

        <div class="grid gap-4 xl:grid-cols-2">
            <div class="card card-pad">
                <div class="flex items-center justify-between gap-2">
                    <h2 class="section-title">Recent operations</h2>
                    <a href="{{ route('admin.hosting.operations') }}" class="btn-ghost btn-sm">All</a>
                </div>
                <ul class="mt-3 space-y-2 text-sm">
                    @forelse($recentOps as $c)
                    <li class="flex items-center justify-between gap-2">
                        <span class="truncate font-mono text-xs">{{ substr($c->uuid, 0, 8) }} · {{ $c->action }} · {{ $c->website?->name ?? '—' }}</span>
                        <span class="{{ $c->state === 'succeeded' ? 'pill-green' : ($c->state === 'failed' ? 'pill-red' : 'pill-amber') }}">{{ $c->state }}</span>
                    </li>
                    @empty
                    <li class="py-4 text-center text-sm text-zinc-500">No operations yet.</li>
                    @endforelse
                </ul>
            </div>
            <div class="card card-pad">
                <div class="flex items-center justify-between gap-2">
                    <h2 class="section-title">Failed health checks</h2>
                    <a href="{{ route('admin.hosting.health') }}" class="btn-ghost btn-sm">All</a>
                </div>
                <ul class="mt-3 space-y-2 text-sm">
                    @forelse($failedChecks as $c)
                    <li class="flex items-center justify-between gap-2">
                        <span class="truncate">{{ $c->website?->name ?? '—' }} · {{ $c->notes ?? 'unreachable' }}</span>
                        <span class="shrink-0 text-xs text-zinc-400">{{ $c->checked_at->diffForHumans() }}</span>
                    </li>
                    @empty
                    <li class="py-4 text-center text-sm text-zinc-500">No failures recorded.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="grid gap-4 xl:grid-cols-3">
            <div class="card card-pad">
                <h2 class="section-title">Recent signups</h2>
                <ul class="mt-3 space-y-2 text-sm">
                    @forelse($recentSignups as $u)
                    <li class="flex items-center justify-between gap-2"><span class="truncate font-semibold">{{ $u->name }}</span><span class="shrink-0 text-xs text-zinc-400">{{ $u->created_at->diffForHumans() }}</span></li>
                    @empty
                    <li class="py-4 text-center text-sm text-zinc-500">None yet.</li>
                    @endforelse
                </ul>
            </div>
            <div class="card card-pad">
                <h2 class="section-title">Recent payments</h2>
                <ul class="mt-3 space-y-2 text-sm">
                    @forelse($recentPayments as $o)
                    <li class="flex items-center justify-between gap-2"><span class="truncate">{{ $o->reference }} · {{ $o->user?->name }}</span><strong class="shrink-0">UGX {{ number_format($o->paid_amount) }}</strong></li>
                    @empty
                    <li class="py-4 text-center text-sm text-zinc-500">None yet.</li>
                    @endforelse
                </ul>
            </div>
            <div class="card card-pad">
                <h2 class="section-title">Recent suspensions & restores</h2>
                <ul class="mt-3 space-y-2 text-sm">
                    @forelse($recentOps->whereIn('action', ['suspend', 'unsuspend']) as $c)
                    <li class="flex items-center justify-between gap-2"><span class="truncate font-mono text-xs">{{ substr($c->uuid, 0, 8) }} · {{ $c->action }}</span><span class="{{ $c->state === 'succeeded' ? 'pill-green' : ($c->state === 'failed' ? 'pill-red' : 'pill-amber') }}">{{ $c->state }}</span></li>
                    @empty
                    <li class="py-4 text-center text-sm text-zinc-500">None yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="card card-pad">
            <div class="flex items-center justify-between gap-2">
                <h2 class="section-title">Recent audit events</h2>
                <a href="{{ route('admin.hosting.audit') }}" class="btn-ghost btn-sm">Full log</a>
            </div>
            <ul class="mt-3 space-y-2 text-sm">
                @forelse($recentAudit as $a)
                <li class="flex items-center justify-between gap-2">
                    <span class="truncate"><span class="font-mono text-xs text-zinc-400">{{ substr($a->operation_id ?? '', 0, 8) }}</span> {{ $a->action }}</span>
                    <span class="shrink-0 text-xs text-zinc-400">{{ $a->created_at->diffForHumans() }}</span>
                </li>
                @empty
                <li class="py-4 text-center text-sm text-zinc-500">No events yet.</li>
                @endforelse
            </ul>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function(){
  const el = document.getElementById('ah-status');
  if(!el || typeof Chart === 'undefined') return;
  new Chart(el, { type: 'bar',
    data: { labels: @json($statusCounts->keys()->map(fn($s) => str_replace('_', ' ', $s))->all()),
      datasets: [{ data: @json($statusCounts->values()->all()),
        backgroundColor: ['#a1a1aa', '#f59e0b', '#10b981', '#ef4444', '#9e005d', '#71717a'], borderRadius: 6 }] },
    options: { responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } } });
  const users = document.getElementById('admin-users-chart');
  if(users && typeof Chart !== 'undefined') new Chart(users, { type: 'line',
    data: { labels: @json($usage['signupLabels']),
      datasets: [{ label: 'New users', data: @json($usage['signups']),
        borderColor: '#9e005d', backgroundColor: 'rgba(158,0,93,.12)', fill: true, tension: .35, pointRadius: 3 }] },
    options: { responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } } });
})();
</script>
</x-layouts::app>
