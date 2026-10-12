<x-layouts::app :title="__('Analytics')">
    @include('partials.toast')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <div class="flex w-full flex-col gap-5">
        <x-page-head title="Analytics" sub="Spending, email results, token usage and saved contacts — last 6 months." />

        <div class="grid auto-rows-min gap-4 md:grid-cols-4">
            <x-stat label="Total committed" :value="'UGX '.number_format($totalCommitted)" hint="All orders" />
            <x-stat label="Total paid" :value="'UGX '.number_format($totalPaid)" hint="Verified payments" />
            <x-stat label="Email open rate" :value="$mktTotals['rate'].'%'" :hint="$mktTotals['opened'].' opened of '.$mktTotals['sent'].' sent'" />
            <x-stat label="Token balance" :value="number_format($tokenBalance)" :hint="$savedTotal.' saved contacts'" />
        </div>

        <div class="card card-pad">
            <h2 class="section-title">Order spend by month</h2>
            <p class="page-sub">Committed UGX per month, last 6 months.</p>
            <div class="mt-3 h-60"><canvas id="an-spend"></canvas></div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="card card-pad">
                <h2 class="section-title">Campaign results</h2>
                <p class="page-sub">Sent vs opened per campaign.</p>
                <div class="mt-3 h-60"><canvas id="an-campaigns"></canvas></div>
            </div>
            <div class="card card-pad">
                <h2 class="section-title">Token spend, last 30 days</h2>
                <p class="page-sub">Tokens used per day.</p>
                <div class="mt-3 h-60"><canvas id="an-tokens"></canvas></div>
            </div>
        </div>

        <div class="card card-pad">
            <h2 class="section-title">Saved contacts by niche</h2>
            <p class="page-sub">Where your prospect list is strongest.</p>
            @if($savedByNiche->isEmpty())
            <p class="py-4 text-center text-sm text-zinc-500">No saved contacts yet — run the scraper and save prospects.</p>
            @else
            <div class="mt-3 space-y-2.5">
                @php $max = max(1, $savedByNiche->max('c')); @endphp
                @foreach($savedByNiche as $row)
                <div class="flex items-center gap-3">
                    <span class="w-40 shrink-0 truncate text-[13px] font-semibold">{{ $row->niche ?? 'Unspecified' }}</span>
                    <div class="h-2.5 flex-1 overflow-hidden rounded-full bg-zinc-100 dark:bg-white/10">
                        <div class="h-2.5 rounded-full bg-brand-600" style="width:{{ round($row->c / $max * 100) }}%"></div>
                    </div>
                    <span class="w-8 shrink-0 text-right text-xs font-bold">{{ $row->c }}</span>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

<script>
(function(){
  if(typeof Chart === 'undefined') return;
  const base = { responsive: true, maintainAspectRatio: false,
    plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 16 } } },
    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } };
  new Chart(document.getElementById('an-spend'), { type: 'bar',
    data: { labels: @json($spendByMonth->keys()->all()),
      datasets: [{ label: 'UGX committed', data: @json($spendByMonth->values()->all()),
        backgroundColor: '#9e005d', borderRadius: 6 }] },
    options: base });
  new Chart(document.getElementById('an-campaigns'), { type: 'bar',
    data: { labels: @json($campaigns->map(fn($c) => mb_substr($c->subject, 0, 16))->all()),
      datasets: [
        { label: 'Sent', data: @json($campaigns->map(fn($c) => (int) $c->sent)->all()), backgroundColor: '#a1a1aa', borderRadius: 5 },
        { label: 'Opened', data: @json($campaigns->map(fn($c) => (int) $c->read_count)->all()), backgroundColor: '#9e005d', borderRadius: 5 },
      ] },
    options: base });
  new Chart(document.getElementById('an-tokens'), { type: 'line',
    data: { labels: @json($tokensByDay->keys()->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))->all()),
      datasets: [{ label: 'Tokens used', data: @json($tokensByDay->values()->all()),
        borderColor: '#9e005d', backgroundColor: 'rgba(158,0,93,.12)', fill: true, tension: .35, pointRadius: 0 }] },
    options: { responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true, ticks: { precision: 0 } }, x: { ticks: { maxTicksLimit: 8 } } } } });
})();
</script>
</x-layouts::app>
