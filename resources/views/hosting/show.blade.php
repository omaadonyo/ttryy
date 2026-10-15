<x-layouts::app :title="__($website->name)">
    @include('partials.toast')
    @php
        $statusPill = fn($s) => match($s) {
            'active' => 'pill-green', 'suspended', 'failed' => 'pill-red',
            'provisioning', 'pending_setup' => 'pill-amber', default => 'pill-zinc',
        };
        $tabs = [
            ['Overview', route('hosting.show', $website), true],
            ['Domains', route('hosting.domains', $website), false],
            ['Analytics', route('hosting.analytics', $website), false],
            ['Health', route('hosting.health', $website), false],
            ['Billing', route('hosting.billing', $website), false],
            ['Reports', route('hosting.reports', $website), false],
        ];
    @endphp
    <div class="flex w-full flex-col gap-5">
        <x-page-head :title="$website->name" :sub="'ID '.substr($website->uuid, 0, 8).' · '.$website->plan?->code.' plan'">
            <x-slot:actions>
                @if($website->primaryDomain)
                <a target="_blank" href="{{ $website->primaryDomain->url() }}" class="btn-ghost btn-sm">View website ↗</a>
                @endif
                @if($website->status === \App\Models\HostedWebsite::STATUS_ACTIVE)
                <form method="POST" action="{{ route('hosting.suspend', $website) }}" class="inline" onsubmit="return confirm('Request suspension of {{ $website->name }}? The service must acknowledge before the status changes.')">
                    @csrf
                    <button type="submit" class="btn-danger btn-sm">Request suspension</button>
                </form>
                @elseif($website->status === \App\Models\HostedWebsite::STATUS_SUSPENDED)
                <form method="POST" action="{{ route('hosting.reactivate', $website) }}" class="inline" onsubmit="return confirm('Request restoration of {{ $website->name }}?')">
                    @csrf
                    <button type="submit" class="btn-brand btn-sm">Request reactivation</button>
                </form>
                @endif
            </x-slot:actions>
        </x-page-head>

        <div class="seg self-start" role="navigation" aria-label="Website sections">
            @foreach($tabs as [$label, $url, $on])
            <a href="{{ $url }}" class="rounded-full px-4 py-2 text-[13px] font-bold whitespace-nowrap transition {{ $on ? 'bg-white text-zinc-950 shadow dark:bg-zinc-700 dark:text-white' : 'text-zinc-500 bg-white/60 hover:text-zinc-900 dark:bg-transparent dark:hover:text-white' }}">{{ $label }}</a>
            @endforeach
        </div>

        @if(! $website->service_connected)
        <div class="card card-pad border-amber-500/30 bg-amber-500/5">
            <p class="text-sm font-bold">Service backend not connected</p>
            <p class="mt-1 text-[13px] text-zinc-500 dark:text-zinc-400">This record exists in ttryy, but no website service has connected yet{{ $website->service_url ? '' : ' and no service endpoint is configured' }}. Remote suspend/restore and live health reports activate once the service backend is embedded and heartbeats arrive. Ask support if you need help.</p>
        </div>
        @endif

        <div class="grid auto-rows-min gap-4 md:grid-cols-4">
            <div class="card card-pad"><p class="eyebrow">Status</p><p class="mt-2"><span class="{{ $statusPill($website->status) }}">{{ str_replace('_', ' ', $website->status) }}</span></p></div>
            <div class="card card-pad"><p class="eyebrow">Health</p><p class="mt-2 font-display text-xl font-extrabold">{{ $website->health_status }}</p><p class="text-xs text-zinc-500">{{ $website->last_health_check_at?->diffForHumans() ?? 'never checked' }}</p></div>
            <div class="card card-pad"><p class="eyebrow">Service state</p><p class="mt-2 font-display text-xl font-extrabold">{{ str_replace('_', ' ', $website->service_state) }}</p><p class="text-xs text-zinc-500">{{ $website->service_version ? 'v'.$website->service_version : 'no reports yet' }}</p></div>
            <div class="card card-pad"><p class="eyebrow">Renewal</p><p class="mt-2 font-display text-xl font-extrabold">{{ $website->next_renewal_at?->format('d M Y') ?? '—' }}</p></div>
        </div>

        @if($website->status === \App\Models\HostedWebsite::STATUS_SUSPENDED)
        <div class="card card-pad border-red-500/30 bg-red-500/5">
            <p class="text-sm font-bold">Suspended{{ $website->suspended_at ? ' since '.$website->suspended_at->format('d M Y H:i') : '' }}</p>
            <p class="mt-1 text-[13px] text-zinc-500">Reason: {{ $website->suspension_reason ?? '—' }}. Request reactivation above; access returns only after the service acknowledges.</p>
        </div>
        @endif

        <div class="card card-pad">
            <div class="flex items-center justify-between gap-2">
                <h2 class="section-title">Domains</h2>
                <a href="{{ route('hosting.domains', $website) }}" class="btn-ghost btn-sm">All domains</a>
            </div>
            <ul class="mt-3 space-y-2 text-sm">
                @forelse($website->domains as $d)
                <li class="flex items-center justify-between gap-2">
                    <span class="font-mono text-xs">{{ $d->domain }} @if($d->is_primary)<span class="pill-brand ml-1">primary</span>@endif</span>
                    <span class="flex shrink-0 items-center gap-2">
                        <span class="{{ $d->isVerified() ? 'pill-green' : 'pill-amber' }}">{{ $d->verification }}</span>
                        <span class="text-xs text-zinc-400">SSL {{ $d->ssl_status }}</span>
                    </span>
                </li>
                @empty
                <li class="text-sm text-zinc-500">No domains yet — our team sets these up during provisioning.</li>
                @endforelse
            </ul>
        </div>

        <div class="grid gap-4 xl:grid-cols-2">
            <div class="card card-pad">
                <div class="flex items-center justify-between gap-2">
                    <h2 class="section-title">Recent operations</h2>
                    <a href="{{ route('hosting.health', $website) }}" class="btn-ghost btn-sm">Health</a>
                </div>
                @if($recentCommands->isEmpty())
                <p class="py-4 text-center text-sm text-zinc-500">No operations yet.</p>
                @else
                <ul class="mt-2 space-y-2 text-sm">
                    @foreach($recentCommands as $c)
                    <li class="flex items-center justify-between gap-2">
                        <span class="font-mono text-xs">{{ substr($c->uuid, 0, 8) }} · {{ $c->action }}</span>
                        <span class="{{ $c->state === 'succeeded' ? 'pill-green' : ($c->state === 'failed' ? 'pill-red' : 'pill-amber') }}">{{ $c->state }}</span>
                    </li>
                    @endforeach
                </ul>
                @endif
            </div>
            <div class="card card-pad">
                <div class="flex items-center justify-between gap-2">
                    <h2 class="section-title">Latest activity</h2>
                    <a href="{{ route('hosting.reports', $website) }}" class="btn-ghost btn-sm">Reports</a>
                </div>
                @if($recentAudit->isEmpty())
                <p class="py-4 text-center text-sm text-zinc-500">No activity recorded yet.</p>
                @else
                <ul class="mt-2 space-y-2 text-sm">
                    @foreach($recentAudit as $a)
                    <li class="flex items-center justify-between gap-2">
                        <span>{{ $a->action }}</span>
                        <span class="shrink-0 text-xs text-zinc-400">{{ $a->created_at->diffForHumans() }}</span>
                    </li>
                    @endforeach
                </ul>
                @endif
            </div>
        </div>

        <div class="card card-pad">
            <div class="flex items-center justify-between gap-2 flex-wrap">
                <h2 class="section-title">Traffic · last 30 days</h2>
                <a href="{{ route('hosting.analytics', $website) }}" class="btn-ghost btn-sm">Full analytics</a>
            </div>
            <div class="mt-3 grid gap-3 sm:grid-cols-3">
                <div><p class="eyebrow">Unique visitors</p><p class="stat-num mt-1">{{ number_format($last30->sum('uniques')) }}</p></div>
                <div><p class="eyebrow">Visits</p><p class="stat-num mt-1">{{ number_format($last30->sum('visits')) }}</p></div>
                <div><p class="eyebrow">Page views</p><p class="stat-num mt-1">{{ number_format($last30->sum('pageviews')) }}</p></div>
            </div>
            <div class="mt-4 flex items-center gap-3 flex-wrap">
                <a href="{{ route('hosting.billing', $website) }}" class="btn-ghost btn-sm">Billing · UGX {{ number_format($totals['owed']) }} owed</a>
                @can('delete', $website)
                <form method="POST" action="{{ route('hosting.destroy', $website) }}" class="inline" onsubmit="return confirm('Cancel and permanently remove {{ $website->name }}? This cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger btn-sm">Cancel website</button>
                </form>
                @endcan
            </div>
        </div>
    </div>
</x-layouts::app>
