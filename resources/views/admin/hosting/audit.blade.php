<x-layouts::app :title="__('Audit log')">
    @include('partials.toast')
    @include('partials.custom-select')
    <div class="flex w-full flex-col gap-5">
        <x-page-head title="Audit log" sub="Every hosting event with actor, outcome and correlation ID. Secrets are never stored here.">
            <x-slot:actions>
                <a href="{{ route('admin.hosting.dashboard') }}" class="btn-ghost btn-sm">← Control</a>
            </x-slot:actions>
        </x-page-head>

        <div class="card card-pad">
            <form method="GET" action="{{ route('admin.hosting.audit') }}" class="grid gap-3 sm:grid-cols-[1fr_220px_160px_auto]">
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search reason…" class="field">
                <input type="text" name="action" value="{{ $filters['action'] ?? '' }}" placeholder="Action contains…" class="field">
                <select name="outcome" class="field">
                    <option value="">All outcomes</option>
                    @foreach(['success', 'failure', 'pending'] as $o)
                    <option value="{{ $o }}" {{ ($filters['outcome'] ?? '') === $o ? 'selected' : '' }}>{{ $o }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-brand">Filter</button>
            </form>
        </div>
<script>
enhanceSelect(document.querySelector('select[name="outcome"]'));
</script>

        <div class="card card-pad">
            <div class="tbl-wrap rounded-none! border-0! shadow-none!">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>When</th>
                            <th>Action</th>
                            <th>Actor</th>
                            <th>Website</th>
                            <th>Outcome</th>
                            <th>Reason</th>
                            <th>Operation</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $a)
                        <tr>
                            <td class="text-xs whitespace-nowrap text-zinc-500">{{ $a->created_at->format('d M H:i') }}</td>
                            <td class="font-mono text-xs">{{ $a->action }}</td>
                            <td class="text-xs">{{ $a->actor_type }}@if($a->actor_id) #{{ $a->actor_id }}@endif</td>
                            <td><a href="{{ $a->hosted_website_id ? route('admin.hosting.websites.show', $a->hosted_website_id) : '#' }}" class="font-semibold hover:text-brand-700 dark:hover:text-brand-300">{{ $a->website?->name ?? '—' }}</a></td>
                            <td><span class="{{ $a->outcome === 'success' ? 'pill-green' : ($a->outcome === 'failure' ? 'pill-red' : 'pill-amber') }}">{{ $a->outcome }}</span></td>
                            <td class="max-w-[220px] truncate text-xs text-zinc-500">{{ $a->reason ?? '—' }}</td>
                            <td class="font-mono text-[11px] text-zinc-400">{{ $a->operation_id ? substr($a->operation_id, 0, 8) : '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="7"><x-empty-state icon="search" title="No audit events" message="Actions across the hosting platform will appear here." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $logs->links() }}</div>
        </div>
    </div>
</x-layouts::app>
