<x-layouts::app :title="__('Reports')">
    @include('partials.toast')
    <div class="flex w-full flex-col gap-5">
        <x-page-head :title="'Reports · '.$website->name" sub="Performance, health and payments — exportable.">
            <x-slot:actions>
                <a href="{{ route('hosting.show', $website) }}" class="btn-ghost btn-sm">← Website</a>
                <a href="{{ route('hosting.reports.pdf', $website) }}" class="btn-ghost btn-sm">Download PDF</a>
                <a href="{{ route('hosting.analytics.export', $website) }}" class="btn-brand btn-sm">Export CSV</a>
            </x-slot:actions>
        </x-page-head>

        <div class="grid auto-rows-min gap-4 sm:grid-cols-3">
            <x-stat label="Page views · 30d" :value="number_format($days->sum('pageviews'))" />
            <x-stat label="Checks recorded" :value="$checks->count()" />
            <x-stat label="Orders linked" :value="$orders->count()" />
        </div>

        <div class="card card-pad">
            <h2 class="section-title">Daily traffic · last 30 days</h2>
            <div class="tbl-wrap mt-3 rounded-none! border-0! shadow-none!">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Uniques</th>
                            <th>Visits</th>
                            <th>Page views</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($days as $d)
                        <tr>
                            <td class="text-xs text-zinc-500">{{ $d->date->format('d M Y') }}</td>
                            <td class="font-semibold">{{ number_format($d->uniques) }}</td>
                            <td class="font-semibold">{{ number_format($d->visits) }}</td>
                            <td class="font-semibold">{{ number_format($d->pageviews) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="py-6 text-center text-sm text-zinc-500">No traffic recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts::app>
