<x-layouts::app :title="__('Operations')">
    @include('partials.toast')
    <div class="flex w-full flex-col gap-5">
        <x-page-head title="Service operations" sub="Every suspend, restore, provision and check command — with explicit states. Status changes only on acknowledgment.">
            <x-slot:actions>
                <a href="{{ route('admin.hosting.dashboard') }}" class="btn-ghost btn-sm">← Control</a>
            </x-slot:actions>
        </x-page-head>

        <div class="card card-pad">
            <form method="GET" action="{{ route('admin.hosting.operations') }}" class="grid gap-3 sm:grid-cols-[180px_180px_auto_1fr]">
                <select name="state" class="field">
                    <option value="">All states</option>
                    @foreach(['pending', 'processing', 'succeeded', 'failed', 'retry'] as $s)
                    <option value="{{ $s }}" {{ ($filters['state'] ?? '') === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
                <select name="action" class="field">
                    <option value="">All actions</option>
                    @foreach(\App\Models\HostingCommand::ACTIONS as $a)
                    <option value="{{ $a }}" {{ ($filters['action'] ?? '') === $a ? 'selected' : '' }}>{{ $a }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-brand">Filter</button>
            </form>
        </div>

        <div class="card card-pad">
            <div class="tbl-wrap rounded-none! border-0! shadow-none!">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>Operation</th>
                            <th>Website</th>
                            <th>Action</th>
                            <th>Actor</th>
                            <th>Attempts</th>
                            <th>State</th>
                            <th>Error</th>
                            <th class="text-right!">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($commands as $c)
                        <tr>
                            <td class="font-mono text-xs">{{ substr($c->uuid, 0, 8) }}<p class="text-[11px] text-zinc-400">{{ $c->created_at->format('d M H:i') }}</p></td>
                            <td><a href="{{ route('admin.hosting.websites.show', $c->website) }}" class="font-semibold hover:text-brand-700 dark:hover:text-brand-300">{{ $c->website?->name ?? '—' }}</a></td>
                            <td class="text-xs">{{ $c->action }}</td>
                            <td class="text-xs text-zinc-500">{{ $c->actor_type }}</td>
                            <td class="text-xs">{{ $c->attempts }}</td>
                            <td><span class="{{ $c->state === 'succeeded' ? 'pill-green' : ($c->state === 'failed' ? 'pill-red' : 'pill-amber') }}">{{ $c->state }}</span></td>
                            <td class="max-w-[220px] truncate text-xs text-zinc-500">{{ $c->error ?? '—' }}</td>
                            <td class="text-right">
                                @if(in_array($c->state, ['failed', 'retry'], true))
                                <form method="POST" action="{{ route('admin.hosting.operations.retry', $c) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-xs font-bold text-brand-700 hover:underline dark:text-brand-300">Retry</button>
                                </form>
                                @else
                                <span class="text-xs text-zinc-400">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8"><x-empty-state icon="search" title="No operations found" message="Commands appear here when suspensions, restores or provisions run." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $commands->links() }}</div>
        </div>
    </div>
</x-layouts::app>
