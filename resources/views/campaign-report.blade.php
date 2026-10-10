<x-layouts::app :title="__('Campaign report')">
    <div class="flex h-full w-full flex-1 flex-col gap-4">
        <div class="flex items-end justify-between gap-3 flex-wrap">
            <div>
                <flux:heading size="xl">{{ $campaign->subject }}</flux:heading>
                <flux:text class="mt-1">Sent {{ $campaign->created_at->format('d M Y H:i') }} · {{ $campaign->total }} recipients</flux:text>
            </div>
            <flux:button :href="route('marketing.index')" variant="ghost" wire:navigate>Back to marketing</flux:button>
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

        <div class="rounded-xl bg-white dark:bg-white/[.04] p-5 shadow-[0_12px_32px_-12px_rgba(10,10,12,.18)] dark:shadow-[0_12px_32px_-12px_rgba(0,0,0,.7)]">
            <flux:heading>Opens over time</flux:heading>
            @if(empty($opensByDay))
            <p class="text-sm text-zinc-500 mt-2">No opens tracked yet — opens register when recipients load images in the email.</p>
            @else
            <div class="mt-3 h-56"><canvas id="opens-chart"></canvas></div>
            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
            <script>
            (function(){
              const el = document.getElementById('opens-chart');
              if(!el || typeof Chart === 'undefined') return;
              new Chart(el, { type: 'bar',
                data: { labels: @json(array_column($opensByDay, 'label')),
                  datasets: [{ label: 'Opens', data: @json(array_column($opensByDay, 'opens')),
                    backgroundColor: '#9e005d', borderRadius: 6 }] },
                options: { responsive: true, maintainAspectRatio: false,
                  plugins: { legend: { display: false } },
                  scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } } });
            })();
            </script>
            @endif
        </div>

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
