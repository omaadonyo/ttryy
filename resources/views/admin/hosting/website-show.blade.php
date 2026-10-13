<x-layouts::app :title="__($website->name)">
    @include('partials.toast')
    @php
        $statusPill = fn($s) => match($s) {
            'active' => 'pill-green', 'suspended', 'failed' => 'pill-red',
            'provisioning', 'pending_setup' => 'pill-amber', default => 'pill-zinc',
        };
    @endphp
    <div class="flex w-full flex-col gap-5">
        <x-page-head :title="$website->name" :sub="'Client: '.$website->user?->name.' · Plan: '.($website->plan?->code ?? '—')">
            <x-slot:actions>
                <a href="{{ route('admin.hosting.websites') }}" class="btn-ghost btn-sm">← Websites</a>
            </x-slot:actions>
        </x-page-head>

        @if(session('new_service_secret'))
        <div class="card card-pad border-amber-500/40 bg-amber-500/5">
            <p class="text-sm font-extrabold">Service secret — shown once, never again</p>
            <div class="mt-2 grid gap-2 font-mono text-xs sm:grid-cols-2">
                <p class="rounded-lg bg-zinc-950 p-3 text-emerald-300">KEY&nbsp;&nbsp;{{ session('new_service_secret')['key_id'] }}</p>
                <p class="rounded-lg bg-zinc-950 p-3 text-emerald-300">SECRET&nbsp;&nbsp;{{ session('new_service_secret')['secret'] }}</p>
            </div>
            <p class="mt-2 text-xs text-zinc-500">Store both in the hosted site's .env as HOSTING_SERVICE_KEY_ID / HOSTING_SERVICE_SECRET. The secret cannot be recovered — only revoked and re-issued.</p>
        </div>
        @endif

        <div class="grid auto-rows-min gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="card card-pad"><p class="eyebrow">Status</p><p class="mt-2"><span class="{{ $statusPill($website->status) }}">{{ str_replace('_', ' ', $website->status) }}</span></p><p class="mt-1.5 font-mono text-[11px] text-zinc-400">ID {{ substr($website->uuid, 0, 8) }}</p></div>
            <div class="card card-pad"><p class="eyebrow">Service state</p><p class="mt-2 font-display text-xl font-extrabold">{{ str_replace('_', ' ', $website->service_state) }}</p><p class="mt-1.5 text-[13px] text-zinc-500">{{ $website->service_connected ? 'Connected' : 'Not connected' }}{{ $website->service_version ? ' · v'.$website->service_version : '' }}</p></div>
            <div class="card card-pad"><p class="eyebrow">Health</p><p class="mt-2 font-display text-xl font-extrabold">{{ $website->health_status }}</p><p class="mt-1.5 text-[13px] text-zinc-500">{{ $website->last_health_check_at?->diffForHumans() ?? 'never checked' }}</p></div>
            <div class="card card-pad"><p class="eyebrow">Primary domain</p><p class="mt-2 font-mono text-sm font-bold">{{ $website->primaryDomain?->domain ?? '—' }}</p><p class="mt-1.5 text-[13px] text-zinc-500">{{ $website->domains->count() }} domain(s)</p></div>
        </div>

        <div class="card card-pad">
            <h2 class="section-title">Service actions</h2>
            <p class="page-sub">Every action is a tracked operation. Website status changes only on service acknowledgment.</p>
            <div class="mt-3 flex flex-wrap gap-2">
                @if(in_array($website->status, ['active', 'failed'], true))
                <form method="POST" action="{{ route('admin.hosting.websites.suspend', $website) }}" class="inline-flex items-center gap-2" onsubmit="return confirm('Suspend {{ $website->name }}? Visitors will be restricted once the service acknowledges.')">
                    @csrf
                    <input name="reason" required placeholder="Reason (required)" class="field max-w-56">
                    <button type="submit" class="btn-danger btn-sm">Suspend</button>
                </form>
                @endif
                @if($website->status === 'suspended')
                <form method="POST" action="{{ route('admin.hosting.websites.restore', $website) }}" class="inline" onsubmit="return confirm('Restore {{ $website->name }}?')">
                    @csrf
                    <button type="submit" class="btn-brand btn-sm">Restore</button>
                </form>
                @endif
                <form method="POST" action="{{ route('admin.hosting.websites.health', $website) }}" class="inline">
                    @csrf
                    <button type="submit" class="btn-ghost btn-sm">Run health check</button>
                </form>
                @if(in_array($website->status, ['pending_setup', 'provisioning', 'failed'], true))
                <form method="POST" action="{{ route('admin.hosting.websites.retry', $website) }}" class="inline">
                    @csrf
                    <button type="submit" class="btn-ghost btn-sm">Retry provisioning</button>
                </form>
                <form method="POST" action="{{ route('admin.hosting.websites.activate', $website) }}" class="inline" onsubmit="return confirm('Mark {{ $website->name }} active without service acknowledgment? Use only after manual verification.')">
                    @csrf
                    <button type="submit" class="btn-ghost btn-sm">Mark active manually</button>
                </form>
                @endif
            </div>
        </div>

        <div class="grid gap-4 xl:grid-cols-2">
            <div class="card card-pad">
                <h2 class="section-title">Service connection</h2>
                <form method="POST" action="{{ route('admin.hosting.websites.service-url', $website) }}" class="mt-3 flex gap-2">
                    @csrf
                    <input name="service_url" value="{{ old('service_url', $website->service_url) }}" placeholder="https://client-site.example" class="field">
                    <button type="submit" class="btn-brand btn-sm shrink-0">Save</button>
                </form>
                <p class="mt-2 text-xs text-zinc-500">Base URL of the hosted site. Commands POST to <span class="font-mono">/hosting-service/commands</span> there. Last heartbeat {{ $website->last_heartbeat_at?->diffForHumans() ?? 'never' }}{{ $website->heartbeatStale() ? ' (stale)' : '' }}.</p>
                <div class="mt-4">
                    <div class="flex items-center justify-between gap-2">
                        <h3 class="text-sm font-extrabold">Credentials</h3>
                        <form method="POST" action="{{ route('admin.hosting.websites.credentials', $website) }}" class="inline" onsubmit="return confirm('Issue a new service credential? The secret shows once.')">
                            @csrf
                            <button type="submit" class="btn-ghost btn-sm">Issue credential</button>
                        </form>
                    </div>
                    <ul class="mt-2 space-y-2 text-sm">
                        @forelse($website->credentials as $c)
                        <li class="flex items-center justify-between gap-2">
                            <span class="font-mono text-xs">{{ $c->key_id }} <span class="text-zinc-400">{{ $c->fingerprint() }}</span></span>
                            @if($c->revoked_at)
                            <span class="pill-zinc">revoked</span>
                            @else
                            <form method="POST" action="{{ route('admin.hosting.websites.credentials.revoke', [$website, $c->id]) }}" class="inline" onsubmit="return confirm('Revoke {{ $c->key_id }}? The service will lose access immediately.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-semibold text-zinc-400 hover:text-red-600">Revoke</button>
                            </form>
                            @endif
                        </li>
                        @empty
                        <li class="text-sm text-zinc-500">No credentials issued. Remote commands need one.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
            <div class="card card-pad">
                <h2 class="section-title">Domains ({{ $website->domains->count() }})</h2>
                <ul class="mt-3 space-y-2 text-sm">
                    @forelse($website->domains as $d)
                    <li class="flex items-center justify-between gap-2">
                        <span class="font-mono text-xs">{{ $d->domain }} @if($d->is_primary)<span class="pill-brand ml-1">primary</span>@endif</span>
                        <span class="text-xs text-zinc-500">{{ $d->type }} · {{ $d->verification }} · SSL {{ $d->ssl_status }}</span>
                    </li>
                    @empty
                    <li class="text-sm text-zinc-500">No domains registered.</li>
                    @endforelse
                </ul>
                <h2 class="section-title mt-5">Orders</h2>
                <ul class="mt-2 space-y-2 text-sm">
                    @forelse($website->orders as $o)
                    <li class="flex items-center justify-between gap-2"><span class="font-mono text-xs">{{ $o->reference }}</span><strong>UGX {{ number_format($o->paid_amount) }} · {{ $o->status }}</strong></li>
                    @empty
                    <li class="text-sm text-zinc-500">No linked orders.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="card card-pad">
            <h2 class="section-title">Domain operations</h2>
            <p class="page-sub">You set everything up here — clients see read-only status.</p>
            <div class="mt-3 grid gap-3 md:grid-cols-2">
                <form method="POST" action="{{ route('admin.hosting.domains.subdomain', $website) }}" class="rounded-xl border border-zinc-200/80 p-4 dark:border-white/10">
                    @csrf
                    <p class="text-sm font-extrabold">Add free subdomain</p>
                    <div class="mt-2 flex gap-2">
                        <input name="label" placeholder="label (empty = auto)" class="field">
                        <button type="submit" class="btn-brand btn-sm shrink-0">Add</button>
                    </div>
                </form>
                <form method="POST" action="{{ route('admin.hosting.domains.custom', $website) }}" class="rounded-xl border border-zinc-200/80 p-4 dark:border-white/10">
                    @csrf
                    <p class="text-sm font-extrabold">Add custom domain</p>
                    <div class="mt-2 flex gap-2">
                        <input name="domain" placeholder="example.co.ug" class="field">
                        <button type="submit" class="btn-brand btn-sm shrink-0">Add</button>
                    </div>
                </form>
            </div>
            <ul class="mt-3 space-y-2 text-sm">
                @foreach($website->domains as $d)
                <li class="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-zinc-200/70 px-3.5 py-2.5 dark:border-white/10">
                    <span class="font-mono text-xs">{{ $d->domain }} @if($d->is_primary)<span class="pill-brand ml-1">primary</span>@endif
                        <span class="ml-1 text-zinc-400">{{ $d->verification }} · SSL {{ $d->ssl_status }}</span></span>
                    <span class="flex shrink-0 items-center gap-2">
                        @if($d->type === 'custom' && $d->verification !== 'verified')
                        <form method="POST" action="{{ route('admin.hosting.domains.verify', [$website, $d]) }}" class="inline">
                            @csrf
                            <button type="submit" class="text-xs font-bold text-brand-700 hover:underline dark:text-brand-300">Check DNS</button>
                        </form>
                        @endif
                        @if($d->isVerified() && ! $d->is_primary)
                        <form method="POST" action="{{ route('admin.hosting.domains.primary', [$website, $d]) }}" class="inline">
                            @csrf
                            <button type="submit" class="text-xs font-bold text-zinc-600 hover:underline dark:text-zinc-300">Make primary</button>
                        </form>
                        @endif
                        @if(! $d->is_primary)
                        <form method="POST" action="{{ route('admin.hosting.domains.destroy', [$website, $d]) }}" class="inline" onsubmit="return confirm('Remove {{ $d->domain }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs font-semibold text-zinc-400 hover:text-red-600">Remove</button>
                        </form>
                        @endif
                    </span>
                </li>
                @endforeach
            </ul>
            <div class="mt-4 border-t border-zinc-100 pt-4 dark:border-white/10">
                <p class="text-sm font-extrabold">Link a package order</p>
                <p class="page-sub">Billing runs off the linked order's rhythm (daily / weekly / monthly).</p>
                <form method="POST" action="{{ route('admin.hosting.websites.link-order', $website) }}" class="mt-2 flex gap-2">
                    @csrf
                    <select name="order_id" class="field" required>
                        <option value="">Choose {{ $website->user?->name }}'s order…</option>
                        @foreach($linkableOrders as $o)
                        <option value="{{ $o->id }}">{{ $o->reference }} · {{ $o->package }} · {{ ucfirst($o->billing_frequency) }} · {{ $o->status }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn-brand btn-sm shrink-0">Link</button>
                </form>
            </div>
        </div>

        <div class="card card-pad">
            <h2 class="section-title">Operations</h2>
            <ul class="mt-3 space-y-2 text-sm">
                @forelse($commands as $c)
                <li class="flex items-center justify-between gap-2">
                    <span class="font-mono text-xs">{{ substr($c->uuid, 0, 8) }} · {{ $c->action }} · {{ $c->actor_type }} · {{ $c->attempts }} attempt(s)</span>
                    <span class="{{ $c->state === 'succeeded' ? 'pill-green' : ($c->state === 'failed' ? 'pill-red' : 'pill-amber') }}">{{ $c->state }}</span>
                </li>
                @empty
                <li class="text-sm text-zinc-500">No operations yet.</li>
                @endforelse
            </ul>
            @if($commands->isNotEmpty())
            <p class="mt-2 text-xs text-zinc-400">Failed or retry operations can be re-queued from the Operations page.</p>
            @endif
        </div>

        <div class="card card-pad">
            <h2 class="section-title">Audit trail</h2>
            <ul class="mt-3 space-y-2 text-sm">
                @forelse($audit as $a)
                <li class="flex items-center justify-between gap-2">
                    <span><span class="font-mono text-xs text-zinc-400">{{ substr($a->operation_id ?? '', 0, 8) }}</span> {{ $a->action }} <span class="text-xs text-zinc-400">by {{ $a->actor_type }}</span></span>
                    <span class="shrink-0 text-xs text-zinc-400">{{ $a->created_at->diffForHumans() }}</span>
                </li>
                @empty
                <li class="text-sm text-zinc-500">No audit events yet.</li>
                @endforelse
            </ul>
        </div>
    </div>
</x-layouts::app>
