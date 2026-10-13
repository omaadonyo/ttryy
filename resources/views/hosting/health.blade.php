<x-layouts::app :title="__('Health')">
    @include('partials.toast')
    @php
        $healthPill = fn($s) => match($s) {
            'healthy' => 'pill-green', 'degraded' => 'pill-amber',
            'unavailable' => 'pill-red', default => 'pill-zinc',
        };
    @endphp
    <div class="flex w-full flex-col gap-5">
        <x-page-head :title="'Health · '.$website->name" sub="Central availability probes plus reports from the website service backend.">
            <x-slot:actions>
                <a href="{{ route('hosting.show', $website) }}" class="btn-ghost btn-sm">← Website</a>
            </x-slot:actions>
        </x-page-head>

        <div class="grid auto-rows-min gap-4 sm:grid-cols-3">
            <div class="card card-pad">
                <p class="eyebrow">Current status</p>
                <p class="mt-2"><span class="{{ $healthPill($website->health_status) }}">{{ $website->health_status }}</span></p>
                <p class="mt-1.5 text-[13px] text-zinc-500">{{ $website->last_health_check_at?->diffForHumans() ?? 'never checked' }}</p>
            </div>
            <x-stat label="30-day uptime" :value="$uptime30 !== null ? $uptime30.'%' : '—'" hint="Successful probes share" />
            <div class="card card-pad">
                <p class="eyebrow">Last check</p>
                @if($latest)
                <p class="mt-2 font-mono text-sm">{{ $latest->http_status ?? '—' }} · {{ $latest->response_ms !== null ? $latest->response_ms.'ms' : '—' }}</p>
                <p class="mt-1.5 text-[13px] text-zinc-500">{{ $latest->checked_at->diffForHumans() }}</p>
                @else
                <p class="mt-2 font-display text-xl font-extrabold">—</p>
                <p class="mt-1.5 text-[13px] text-zinc-500">No checks yet</p>
                @endif
            </div>
        </div>

        @if($incidents->isNotEmpty())
        <div class="card card-pad border-red-500/30 bg-red-500/5">
            <h2 class="section-title">Recent incidents</h2>
            <ul class="mt-3 space-y-2 text-sm">
                @foreach($incidents as $i)
                <li class="flex items-center justify-between gap-2">
                    <span>{{ $i->notes ?? 'Website unreachable' }}</span>
                    <span class="shrink-0 text-xs text-zinc-400">{{ $i->checked_at->diffForHumans() }}</span>
                </li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="card card-pad">
            <h2 class="section-title">Check history</h2>
            @if($checks->isEmpty())
            <p class="py-4 text-center text-sm text-zinc-500">No checks recorded yet — the scheduler probes active websites every 30 minutes.</p>
            @else
            <div class="tbl-wrap mt-3 rounded-none! border-0! shadow-none!">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>When</th>
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
                        @foreach($checks as $c)
                        <tr>
                            <td class="text-xs whitespace-nowrap text-zinc-500">{{ $c->checked_at->format('d M H:i') }}</td>
                            <td><span class="{{ $healthPill($c->status) }}">{{ $c->status }}</span></td>
                            <td class="font-mono text-xs">{{ $c->http_status ?? '—' }}</td>
                            <td class="text-xs">{{ $c->response_ms !== null ? $c->response_ms.'ms' : '—' }}</td>
                            <td class="text-xs">{{ $c->ssl_ok === null ? '—' : ($c->ssl_ok ? 'ok' : 'fail') }}</td>
                            <td class="text-xs">{{ $c->dns_ok === null ? '—' : ($c->dns_ok ? 'ok' : 'fail') }}</td>
                            <td class="text-xs">{{ $c->app_ok === null ? '—' : ($c->app_ok ? 'ok' : 'fail') }}</td>
                            <td class="text-xs">{{ $c->db_ok === null ? '—' : ($c->db_ok ? 'ok' : 'fail') }}</td>
                            <td class="max-w-[220px] truncate text-xs text-zinc-500">{{ $c->notes ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $checks->links() }}</div>
            @endif
        </div>
    </div>
</x-layouts::app>
