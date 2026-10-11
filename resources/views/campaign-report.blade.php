<x-layouts::app :title="__('Campaign report')">
    @include('partials.toast')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <div class="flex w-full flex-col gap-5">
        <x-page-head :title="$campaign->subject" :sub="'Sent '.$campaign->created_at->format('d M Y H:i').' · '.$campaign->total.' recipients'">
            <x-slot:actions>
                <a href="{{ route('marketing.index') }}" class="btn-ghost btn-sm">Back to marketing</a>
            </x-slot:actions>
        </x-page-head>

        <div class="grid auto-rows-min gap-4 md:grid-cols-4">
            <x-stat label="Sent" :value="$campaign->sent" />
            <x-stat label="Delivered" :value="$campaign->delivered" />
            <x-stat label="Read" :value="$campaign->read_count" />
            <div class="card card-pad border-transparent! bg-zinc-950 text-white dark:border-transparent! dark:bg-brand-600">
                <p class="text-[11px] font-bold tracking-[0.14em] text-white/60 uppercase">Open rate</p>
                <p class="stat-num mt-2 text-white!">{{ $campaign->delivered > 0 ? round($campaign->read_count / $campaign->delivered * 100) : 0 }}%</p>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="card card-pad">
                <h2 class="section-title">Email funnel</h2>
                <p class="page-sub">Sent → delivered → opened. Opens register when a recipient loads images in the email.</p>
                <div class="mt-3 h-56"><canvas id="funnel-chart"></canvas></div>
            </div>
            <div class="card card-pad">
                <h2 class="section-title">Opens over time</h2>
                <p class="page-sub">When recipients opened this campaign, by day.</p>
                <div class="mt-3 h-56"><canvas id="opens-chart"></canvas></div>
            </div>
        </div>
        @php
            $funnelSent = (int) $campaign->sent;
            $funnelDelivered = (int) $campaign->delivered;
            $funnelRead = (int) $campaign->read_count;
            $dayLabels = empty($opensByDay) ? ['No opens yet'] : array_column($opensByDay, 'label');
            $dayValues = empty($opensByDay) ? [0] : array_column($opensByDay, 'opens');
        @endphp
        <script>
        (function(){
          toastStoredFlash();
          if(typeof Chart === 'undefined'){
            document.querySelectorAll('#funnel-chart, #opens-chart').forEach(el => {
              el.outerHTML = '<p class="text-sm text-zinc-500 py-8 text-center">Charts need an internet connection — check yours and reload.</p>';
            });
            return;
          }
          new Chart(document.getElementById('funnel-chart'), { type: 'doughnut',
            data: { labels: ['Sent', 'Delivered', 'Opened'],
              datasets: [{ data: [@json($funnelSent), @json($funnelDelivered), @json($funnelRead)],
                backgroundColor: ['#a1a1aa', '#18181b', '#9e005d'], borderWidth: 0, hoverOffset: 6 }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: '62%',
              plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 16 } } } } });
          new Chart(document.getElementById('opens-chart'), { type: 'bar',
            data: { labels: @json($dayLabels),
              datasets: [{ label: 'Opens', data: @json($dayValues),
                backgroundColor: '#9e005d', borderRadius: 6 }] },
            options: { responsive: true, maintainAspectRatio: false,
              plugins: { legend: { display: false } },
              scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } } });
        })();
        </script>

        <div class="card card-pad">
            <h2 class="section-title">Recipients</h2>
            <div class="tbl-wrap mt-2 rounded-none! border-0! shadow-none!">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>Name</th>
                            <th>Sent</th>
                            <th>Delivered</th>
                            <th>Read</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($campaign->recipients as $r)
                        <tr>
                            <td>{{ $r->email }}</td>
                            <td>{{ $r->name ?? '—' }}</td>
                            <td class="text-xs whitespace-nowrap text-zinc-500">{{ $r->sent_at?->format('d M H:i') ?? '—' }}</td>
                            <td class="text-xs whitespace-nowrap text-zinc-500">{{ $r->delivered_at?->format('d M H:i') ?? '—' }}</td>
                            <td>{{ $r->opened_at ? $r->opened_at->format('d M H:i').($r->open_count > 1 ? ' ×'.$r->open_count : '') : '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts::app>

