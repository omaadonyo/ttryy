<x-layouts::app :title="__('Campaign report')">
    @include('partials.toast')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <div class="flex h-full w-full flex-1 flex-col gap-4">
        <div class="flex items-end justify-between gap-3 flex-wrap">
            <div>
                <flux:heading size="xl">{{ $campaign->subject }}</flux:heading>
                <flux:text class="mt-1">Sent {{ $campaign->created_at->format('d M Y H:i') }} · {{ $campaign->total }} recipients</flux:text>
            </div>
            <flux:button :href="route('marketing.index')" variant="ghost">Back to marketing</flux:button>
        </div>

        <div class="grid auto-rows-min gap-4 md:grid-cols-4">
            <div class="rounded-xl bg-white dark:bg-white/[.04] p-5 shadow-[0_12px_32px_-12px_rgba(10,10,12,.18)] dark:shadow-[0_12px_32px_-12px_rgba(0,0,0,.7)]">
                <flux:text>Sent</flux:text>
                <p class="mt-1 text-3xl font-bold">{{ $campaign->sent }}</p>
            </div>
            <div class="rounded-xl bg-white dark:bg-white/[.04] p-5 shadow-[0_12px_32px_-12px_rgba(10,10,12,.18)] dark:shadow-[0_12px_32px_-12px_rgba(0,0,0,.7)]">
                <flux:text>Delivered</flux:text>
                <p class="mt-1 text-3xl font-bold">{{ $campaign->delivered }}</p>
            </div>
            <div class="rounded-xl bg-white dark:bg-white/[.04] p-5 shadow-[0_12px_32px_-12px_rgba(10,10,12,.18)] dark:shadow-[0_12px_32px_-12px_rgba(0,0,0,.7)]">
                <flux:text>Read</flux:text>
                <p class="mt-1 text-3xl font-bold text-[#9e005d]">{{ $campaign->read_count }}</p>
            </div>
            <div class="rounded-xl bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 p-5 shadow-[0_12px_32px_-12px_rgba(10,10,12,.35)]">
                <p class="text-sm opacity-70">Open rate</p>
                <p class="mt-1 text-3xl font-bold">{{ $campaign->delivered > 0 ? round($campaign->read_count / $campaign->delivered * 100) : 0 }}%</p>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="rounded-xl bg-white dark:bg-white/[.04] p-5 shadow-[0_12px_32px_-12px_rgba(10,10,12,.18)] dark:shadow-[0_12px_32px_-12px_rgba(0,0,0,.7)]">
                <flux:heading>Email funnel</flux:heading>
                <flux:text class="mt-1 text-sm">Sent → delivered → opened. Opens register when a recipient loads images in the email.</flux:text>
                <div class="mt-3 h-56"><canvas id="funnel-chart"></canvas></div>
            </div>
            <div class="rounded-xl bg-white dark:bg-white/[.04] p-5 shadow-[0_12px_32px_-12px_rgba(10,10,12,.18)] dark:shadow-[0_12px_32px_-12px_rgba(0,0,0,.7)]">
                <flux:heading>Opens over time</flux:heading>
                <flux:text class="mt-1 text-sm">When recipients opened this campaign, by day.</flux:text>
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

        <div class="rounded-xl bg-white dark:bg-white/[.04] p-5 shadow-[0_12px_32px_-12px_rgba(10,10,12,.18)] dark:shadow-[0_12px_32px_-12px_rgba(0,0,0,.7)]">
            <flux:heading>Recipients</flux:heading>
            <div class="mt-2 overflow-x-auto">
                <table class="w-full text-sm min-w-[560px]">
                    <thead>
                        <tr class="text-left text-zinc-500">
                            <th class="py-2 pr-4 font-medium">Email</th>
                            <th class="py-2 pr-4 font-medium">Name</th>
                            <th class="py-2 pr-4 font-medium">Sent</th>
                            <th class="py-2 pr-4 font-medium">Delivered</th>
                            <th class="py-2 font-medium">Read</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-white/10">
                        @foreach($campaign->recipients as $r)
                        <tr>
                            <td class="py-2 pr-4">{{ $r->email }}</td>
                            <td class="py-2 pr-4">{{ $r->name ?? '—' }}</td>
                            <td class="py-2 pr-4 text-xs text-zinc-500">{{ $r->sent_at?->format('d M H:i') ?? '—' }}</td>
                            <td class="py-2 pr-4 text-xs text-zinc-500">{{ $r->delivered_at?->format('d M H:i') ?? '—' }}</td>
                            <td class="py-2">{{ $r->opened_at ? $r->opened_at->format('d M H:i').($r->open_count > 1 ? ' ×'.$r->open_count : '') : '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts::app>

