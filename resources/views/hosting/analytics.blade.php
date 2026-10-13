<x-layouts::app :title="__('Analytics')">
    @include('partials.toast')
    <div class="flex w-full flex-col gap-5">
        <x-page-head :title="'Analytics · '.$website->name" sub="First-party tracking. Uniques = one privacy-conscious identifier per day — never raw request counts.">
            <x-slot:actions>
                <a href="{{ route('hosting.show', $website) }}" class="btn-ghost btn-sm">← Website</a>
                <a href="{{ route('hosting.analytics.export', [$website, 'range' => $range]) }}" class="btn-ghost btn-sm">Export CSV</a>
            </x-slot:actions>
        </x-page-head>

        <div class="seg self-start" role="tablist" aria-label="Date range">
            @foreach([7 => '7 days', 30 => '30 days', 90 => '90 days'] as $r => $label)
            <a href="{{ route('hosting.analytics', [$website, 'range' => $r]) }}" class="rounded-full px-4 py-2 text-[13px] font-bold whitespace-nowrap transition {{ $range === $r ? 'bg-white text-zinc-950 shadow dark:bg-zinc-700 dark:text-white' : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-white' }}">{{ $label }}</a>
            @endforeach
        </div>

        <div class="grid auto-rows-min gap-4 sm:grid-cols-3">
            <x-stat label="Unique visitors" :value="number_format($totals['uniques'])" :hint="'Last '.$range.' days'" />
            <x-stat label="Visits" :value="number_format($totals['visits'])" :hint="'Last '.$range.' days'" />
            <x-stat label="Page views" :value="number_format($totals['pageviews'])" :hint="'Last '.$range.' days'" />
        </div>

        @if($days->isEmpty())
        <div class="card card-pad">
            <x-empty-state icon="search" title="No traffic yet" message="Install the tracking snippet below, then visit the website. Real data appears here — nothing is invented." />
            <div class="mx-auto mt-2 max-w-xl rounded-xl border border-zinc-200/80 bg-zinc-50 p-4 dark:border-white/10 dark:bg-white/5">
                <p class="text-xs font-bold tracking-wide text-zinc-500 uppercase">Tracking snippet</p>
                <code class="mt-2 block overflow-x-auto rounded-lg bg-zinc-950 p-3 font-mono text-xs text-emerald-300">&lt;img src="{{ route('hosting.beacon', $website->uuid) }}?p=PAGE_PATH&amp;ref=" alt="" width="1" height="1" /&gt;</code>
                <p class="mt-2 text-xs text-zinc-500">Replace PAGE_PATH with the page path. Optional: &amp;utm_source=… for sources, &amp;c=UG for country.</p>
            </div>
        </div>
        @else
        <div class="card card-pad">
            <h2 class="section-title">Traffic over time</h2>
            <div class="mt-3 h-60"><canvas id="w-traffic"></canvas></div>
        </div>
        <div class="grid gap-4 md:grid-cols-3">
            <div class="card card-pad">
                <h2 class="section-title">Top pages</h2>
                <ul class="mt-3 space-y-2 text-sm">
                    @forelse($topPages as $row)
                    <li class="flex justify-between gap-2"><span class="truncate font-mono text-xs">{{ $row['label'] }}</span><strong>{{ $row['count'] }}</strong></li>
                    @empty
                    <li class="text-sm text-zinc-500">No data.</li>
                    @endforelse
                </ul>
            </div>
            <div class="card card-pad">
                <h2 class="section-title">Sources</h2>
                <ul class="mt-3 space-y-2 text-sm">
                    @forelse($sources as $row)
                    <li class="flex justify-between gap-2"><span class="truncate">{{ $row['label'] }}</span><strong>{{ $row['count'] }}</strong></li>
                    @empty
                    <li class="text-sm text-zinc-500">No data.</li>
                    @endforelse
                </ul>
            </div>
            <div class="card card-pad">
                <h2 class="section-title">Devices</h2>
                <ul class="mt-3 space-y-2 text-sm">
                    @forelse($devices as $row)
                    <li class="flex justify-between gap-2"><span class="truncate capitalize">{{ $row['label'] }}</span><strong>{{ $row['count'] }}</strong></li>
                    @empty
                    <li class="text-sm text-zinc-500">No data.</li>
                    @endforelse
                </ul>
            </div>
        </div>
        @endif
    </div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function(){
  const el = document.getElementById('w-traffic');
  if(!el || typeof Chart === 'undefined') return;
  new Chart(el, {
    data: { labels: @json($days->map(fn($d) => $d->date->format('d M'))->all()),
      datasets: [
        { type: 'line', label: 'Uniques', data: @json($days->pluck('uniques')->all()), borderColor: '#9e005d', tension: .35, pointRadius: 0 },
        { type: 'bar', label: 'Page views', data: @json($days->pluck('pageviews')->all()), backgroundColor: '#d4d4d8', borderRadius: 4 },
      ] },
    options: { responsive: true, maintainAspectRatio: false,
      plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 16 } } },
      scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } } });
})();
</script>
</x-layouts::app>
