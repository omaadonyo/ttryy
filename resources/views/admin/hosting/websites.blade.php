<x-layouts::app :title="__('Hosted websites')">
    @include('partials.toast')
    @include('partials.custom-select')
    @php
        $statusPill = fn($s) => match($s) {
            'active' => 'pill-green', 'suspended', 'failed' => 'pill-red',
            'provisioning', 'pending_setup' => 'pill-amber', default => 'pill-zinc',
        };
    @endphp
    <div class="flex w-full flex-col gap-5">
        <x-page-head :title="$trash ? 'Trash — deleted websites' : 'Hosted websites'" :sub="$trash ? 'Restore brings a website and its domains back. Purging is permanent.' : 'Every client website on the platform.'">
            <x-slot:actions>
                @if($trash)
                <a href="{{ route('admin.hosting.websites') }}" class="btn-ghost btn-sm">← All websites</a>
                @else
                <a href="{{ route('admin.hosting.websites', ['show' => 'trash']) }}" class="btn-ghost btn-sm">Trash ({{ $trashCount }})</a>
                @endif
                <a href="{{ route('admin.hosting.dashboard') }}" class="btn-ghost btn-sm">← Control</a>
            </x-slot:actions>
        </x-page-head>

        <div class="card card-pad">
            <form method="GET" action="{{ route('admin.hosting.websites') }}" class="grid gap-3 sm:grid-cols-[1fr_170px_170px_170px_auto]">
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search name, ID, domain, client…" class="field">
                <select name="status" class="field">
                    <option value="">All statuses</option>
                    @foreach(\App\Models\HostedWebsite::STATUSES as $s)
                    <option value="{{ $s }}" {{ ($filters['status'] ?? '') === $s ? 'selected' : '' }}>{{ str_replace('_', ' ', $s) }}</option>
                    @endforeach
                </select>
                <select name="health" class="field">
                    <option value="">All health</option>
                    @foreach(['unknown', 'healthy', 'degraded', 'unavailable'] as $s)
                    <option value="{{ $s }}" {{ ($filters['health'] ?? '') === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
                <select name="plan" class="field">
                    <option value="">All plans</option>
                    @foreach($plans as $p)
                    <option value="{{ $p->id }}" {{ (string) ($filters['plan'] ?? '') === (string) $p->id ? 'selected' : '' }}>{{ $p->code }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-brand">Filter</button>
            </form>
        </div>
<script>
['status', 'health', 'plan'].forEach(n => enhanceSelect(document.querySelector('select[name="' + n + '"]')));
</script>

        <div class="card card-pad">
            @if($websites->isEmpty())
                <x-empty-state icon="search" :title="$trash ? 'Trash is empty' : 'No websites found'" :message="$trash ? 'Deleted websites land here and can be restored.' : 'Try clearing the search or filters.'" />
            @else
            <div class="tbl-wrap rounded-none! border-0! shadow-none!">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>Website</th>
                            <th>Client</th>
                            <th>Primary domain</th>
                            <th>Plan</th>
                            <th>Status</th>
                            <th>Health</th>
                            <th>Payment</th>
                            <th class="text-right!">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($websites as $w)
                        <tr>
                            <td class="font-semibold">{{ $w->name }}<p class="font-mono text-[11px] font-normal text-zinc-400">{{ substr($w->uuid, 0, 8) }} · {{ $w->created_at->format('d M Y') }}</p></td>
                            <td>{{ $w->user?->name }}<p class="text-xs text-zinc-500">{{ $w->user?->email }}</p></td>
                            <td class="font-mono text-xs">{{ $w->primaryDomain?->domain ?? '—' }}</td>
                            <td>{{ $w->plan?->code ?? '—' }}</td>
                            <td><span class="{{ $statusPill($w->status) }}">{{ str_replace('_', ' ', $w->status) }}</span></td>
                            <td class="text-xs text-zinc-500">{{ $w->health_status }}<p class="text-[11px] text-zinc-400">{{ $w->last_health_check_at?->diffForHumans() ?? 'never' }}</p></td>
                            <td class="text-xs">UGX {{ number_format($w->orders()->where('status', 'paid')->sum('paid_amount')) }} paid</td>
                            <td class="text-right whitespace-nowrap">
                                @if($trash)
                                <form method="POST" action="{{ route('admin.hosting.websites.recover', $w->id) }}" class="inline" onsubmit="return confirm('Restore {{ $w->name }} with its domains?')">
                                    @csrf
                                    <button class="text-xs font-bold text-emerald-600 hover:underline dark:text-emerald-400">Restore</button>
                                </form>
                                <span class="mx-1 text-zinc-300 dark:text-zinc-600">·</span>
                                <form method="POST" action="{{ route('admin.hosting.websites.purge', $w->id) }}" class="inline" onsubmit="return confirm('PERMANENTLY delete {{ $w->name }}? This cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-xs font-bold text-red-600 hover:underline dark:text-red-400">Delete forever</button>
                                </form>
                                @else
                                @if($w->status === 'suspended')
                                <form method="POST" action="{{ route('admin.hosting.websites.restore', $w) }}" class="inline" onsubmit="return confirm('Unsuspend {{ $w->name }}? It goes live immediately.')">
                                    @csrf
                                    <button class="text-xs font-bold text-emerald-600 hover:underline dark:text-emerald-400">Unsuspend</button>
                                </form>
                                <span class="mx-1 text-zinc-300 dark:text-zinc-600">·</span>
                                @elseif(in_array($w->status, ['active', 'failed'], true))
                                <form method="POST" action="{{ route('admin.hosting.websites.suspend', $w) }}" class="inline" onsubmit="return confirm('Suspend {{ $w->name }}? It goes offline immediately.')">
                                    @csrf
                                    <input type="hidden" name="reason" value="Suspended by admin">
                                    <button class="text-xs font-bold text-red-600 hover:underline dark:text-red-400">Suspend</button>
                                </form>
                                <span class="mx-1 text-zinc-300 dark:text-zinc-600">·</span>
                                @endif
                                <form method="POST" action="{{ route('admin.hosting.websites.destroy', $w) }}" class="inline" onsubmit="return confirm('Move {{ $w->name }} to trash? Its domains go with it and it can be restored.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-bold text-red-600 hover:underline dark:text-red-400">Delete</button>
                                </form>
                                <span class="mx-1 text-zinc-300 dark:text-zinc-600">·</span>
                                <a href="{{ route('admin.hosting.websites.show', $w) }}" class="text-xs font-bold text-brand-700 hover:underline dark:text-brand-300">Open →</a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $websites->links() }}</div>
            @endif
        </div>
    </div>
</x-layouts::app>
