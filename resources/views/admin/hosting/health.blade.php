<x-layouts::app :title="__('Health checks')">
    @include('partials.toast')
    @php
        $healthPill = fn($s) => match($s) {
            'healthy' => 'pill-green', 'degraded' => 'pill-amber',
            'unavailable' => 'pill-red', default => 'pill-zinc',
        };
    @endphp
    <div class="flex w-full flex-col gap-5">
        <x-page-head title="Health checks" sub="Central availability probes. Scheduler runs them every 30 minutes — add cron schedule:run in production.">
            <x-slot:actions>
                <a href="{{ route('admin.hosting.dashboard') }}" class="btn-ghost btn-sm">← Control</a>
            </x-slot:actions>
        </x-page-head>

        <div class="seg self-start" role="tablist" aria-label="Health filter">
            @foreach(['all' => 'All', 'failures' => 'Failures', 'incidents' => 'Incidents'] as $f => $label)
            <a href="{{ route('admin.hosting.health', ['filter' => $f]) }}" class="rounded-full px-4 py-2 text-[13px] font-bold whitespace-nowrap transition {{ $filter === $f || ($f === 'all' && $filter === '') ? 'bg-white text-zinc-950 shadow dark:bg-zinc-700 dark:text-white' : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-white' }}">{{ $label }}</a>
            @endforeach
        </div>

        <div class="card card-pad">
            <div class="tbl-wrap rounded-none! border-0! shadow-none!">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>When</th>
                            <th>Website</th>
                            <th>Status</th>
                            <th>HTTP</th>
                            <th>Response</th>
                            <th>SSL</th>
                            <th>DNS</th>
                            <th>App</th>
                            <th>DB</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($checks as $c)
                        <tr>
                            <td class="text-xs whitespace-nowrap text-zinc-500">{{ $c->checked_at->format('d M H:i') }}</td>
                            <td><a href="{{ route('admin.hosting.websites.show', $c->website) }}" class="font-semibold hover:text-brand-700 dark:hover:text-brand-300">{{ $c->website?->name ?? '—' }}</a></td>
                            <td><span class="{{ $healthPill($c->status) }}">{{ $c->status }}</span>@if($c->incident)<span class="pill-red ml-1">incident</span>@endif</td>
                            <td class="font-mono text-xs">{{ $c->http_status ?? '—' }}</td>
                            <td class="text-xs">{{ $c->response_ms !== null ? $c->response_ms.'ms' : '—' }}</td>
                            <td class="text-xs">{{ $c->ssl_ok === null ? '—' : ($c->ssl_ok ? 'ok' : 'fail') }}</td>
                            <td class="text-xs">{{ $c->dns_ok === null ? '—' : ($c->dns_ok ? 'ok' : 'fail') }}</td>
                            <td class="text-xs">{{ $c->app_ok === null ? '—' : ($c->app_ok ? 'ok' : 'fail') }}</td>
                            <td class="text-xs">{{ $c->db_ok === null ? '—' : ($c->db_ok ? 'ok' : 'fail') }}</td>
                            <td class="max-w-[200px] truncate text-xs text-zinc-500">{{ $c->notes ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="10"><x-empty-state icon="search" title="No checks yet" message="Run hosting:health-check or wait for the scheduler." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $checks->links() }}</div>
        </div>
    </div>
</x-layouts::app>
