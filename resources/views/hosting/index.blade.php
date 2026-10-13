<x-layouts::app :title="__('Hosting')">
    @include('partials.toast')
    @php
        $statusPill = fn($s) => match($s) {
            'active' => 'pill-green', 'suspended', 'failed' => 'pill-red',
            'provisioning', 'pending_setup' => 'pill-amber', default => 'pill-zinc',
        };
        $healthPill = fn($s) => match($s) {
            'healthy' => 'pill-green', 'degraded' => 'pill-amber',
            'unavailable' => 'pill-red', default => 'pill-zinc',
        };
    @endphp
    <div class="flex w-full flex-col gap-5">
        <x-page-head title="Hosting" :sub="'Your websites, domains and service health.'">
            <x-slot:actions>
                <button onclick="startTour_hosting()" class="btn-ghost btn-sm">Take tour</button>
                <a href="{{ route('hosting.create') }}" class="btn-brand">Register website</a>
            </x-slot:actions>
        </x-page-head>

        @include('partials.tour', ['key' => 'hosting', 'steps' => [
            ['target' => '#tour-hstats', 'title' => 'Fleet at a glance', 'body' => 'Total, active, suspended and pending websites plus connected domains.'],
            ['target' => '#tour-hplan', 'title' => 'Plan and balance', 'body' => 'Current plan, outstanding balance and next renewal across your account.'],
            ['target' => '#tour-htable', 'title' => 'Every website', 'body' => 'Search, filter and sort. Open any row to manage domains, health, analytics and billing.'],
        ]])

        <div id="tour-hstats" class="grid auto-rows-min grid-cols-2 gap-4 xl:grid-cols-4">
            <x-stat label="Total websites" :value="$stats['total']" />
            <x-stat label="Active" :value="$stats['active']" />
            <x-stat label="Suspended" :value="$stats['suspended']" />
            <x-stat label="Pending / provisioning" :value="$stats['pending']" />
        </div>

        <div id="tour-hplan" class="card card-pad">
            <div class="grid gap-3 sm:grid-cols-3">
                <div><p class="eyebrow">Current plan</p><p class="font-display mt-1 text-xl font-extrabold">{{ $plan ?? '—' }}</p></div>
                <div><p class="eyebrow">Outstanding balance</p><p class="font-display mt-1 text-xl font-extrabold">UGX {{ number_format($outstanding) }}</p></div>
                <div><p class="eyebrow">Next renewal</p><p class="font-display mt-1 text-xl font-extrabold">{{ $nextRenewal ? $nextRenewal->format('d M Y') : '—' }}</p></div>
            </div>
        </div>

        <div class="card card-pad">
            <form method="GET" action="{{ route('hosting.index') }}" class="grid gap-3 sm:grid-cols-[1fr_180px_160px_auto]">
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search name or domain…" class="field">
                <select name="status" class="field">
                    <option value="">All statuses</option>
                    @foreach(\App\Models\HostedWebsite::STATUSES as $s)
                    <option value="{{ $s }}" {{ ($filters['status'] ?? '') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                    @endforeach
                </select>
                <select name="sort" class="field">
                    <option value="latest" {{ ($filters['sort'] ?? 'latest') === 'latest' ? 'selected' : '' }}>Newest</option>
                    <option value="name" {{ ($filters['sort'] ?? '') === 'name' ? 'selected' : '' }}>Name A–Z</option>
                    <option value="status" {{ ($filters['sort'] ?? '') === 'status' ? 'selected' : '' }}>Status</option>
                </select>
                <button type="submit" class="btn-brand">Filter</button>
            </form>
        </div>

        <div id="tour-htable" class="card card-pad">
            @if($websites->isEmpty())
                <x-empty-state icon="orders" title="No websites yet" message="Register your first website — a free subdomain is generated automatically." :actionUrl="route('hosting.create')" actionLabel="Register website" />
            @else
            <div class="tbl-wrap rounded-none! border-0! shadow-none!">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>Website</th>
                            <th>Primary domain</th>
                            <th>Plan</th>
                            <th>Status</th>
                            <th>Health</th>
                            <th>Renewal</th>
                            <th class="text-right!">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($websites as $w)
                        <tr>
                            <td class="font-semibold">{{ $w->name }}<p class="font-mono text-[11px] font-normal text-zinc-400">{{ substr($w->uuid, 0, 8) }}</p></td>
                            <td class="font-mono text-xs">{{ $w->primaryDomain?->domain ?? '—' }}</td>
                            <td>{{ $w->plan?->code ?? '—' }}</td>
                            <td><span class="{{ $statusPill($w->status) }}">{{ str_replace('_', ' ', $w->status) }}</span></td>
                            <td><span class="{{ $healthPill($w->health_status) }}">{{ $w->health_status }}</span><p class="text-[11px] text-zinc-400">{{ $w->last_health_check_at?->diffForHumans() ?? 'never checked' }}</p></td>
                            <td class="text-xs whitespace-nowrap text-zinc-500">{{ $w->next_renewal_at?->format('d M Y') ?? '—' }}</td>
                            <td class="text-right whitespace-nowrap"><a href="{{ route('hosting.show', $w) }}" class="text-xs font-bold text-brand-700 hover:underline dark:text-brand-300">Manage →</a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $websites->links() }}</div>
            @endif
        </div>
    </div>
</x-layouts::app>
