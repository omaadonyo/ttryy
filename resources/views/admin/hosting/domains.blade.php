<x-layouts::app :title="__('All domains')">
    @include('partials.toast')
    @include('partials.custom-select')
    <div class="flex w-full flex-col gap-5">
        <x-page-head title="All domains" sub="Subdomains and custom domains across every website.">
            <x-slot:actions>
                <a href="{{ route('admin.hosting.dashboard') }}" class="btn-ghost btn-sm">← Control</a>
            </x-slot:actions>
        </x-page-head>

        <div class="card card-pad">
            <form method="GET" action="{{ route('admin.hosting.domains') }}" class="grid gap-3 sm:grid-cols-[1fr_180px_180px_auto]">
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search domain…" class="field">
                <select name="type" class="field">
                    <option value="">All types</option>
                    <option value="subdomain" {{ ($filters['type'] ?? '') === 'subdomain' ? 'selected' : '' }}>Subdomains</option>
                    <option value="custom" {{ ($filters['type'] ?? '') === 'custom' ? 'selected' : '' }}>Custom</option>
                </select>
                <select name="verification" class="field">
                    <option value="">All verification</option>
                    @foreach(['unverified', 'pending', 'verified'] as $v)
                    <option value="{{ $v }}" {{ ($filters['verification'] ?? '') === $v ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-brand">Filter</button>
            </form>
        </div>
<script>
['type', 'verification'].forEach(n => enhanceSelect(document.querySelector('select[name="' + n + '"]')));
</script>

        <div class="card card-pad">
            <div class="mb-3 flex flex-wrap gap-2">
                @foreach($sslCounts as $ssl => $c)
                <span class="pill-zinc">SSL {{ $ssl }} · {{ $c }}</span>
                @endforeach
            </div>
            <div class="tbl-wrap rounded-none! border-0! shadow-none!">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>Domain</th>
                            <th>Website</th>
                            <th>Client</th>
                            <th>Type</th>
                            <th>Verification</th>
                            <th>SSL</th>
                            <th class="text-right!">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($domains as $d)
                        <tr>
                            <td class="font-mono text-xs">{{ $d->domain }} @if($d->is_primary)<span class="pill-brand ml-1">primary</span>@endif</td>
                            <td><a href="{{ route('admin.hosting.websites.show', $d->website) }}" class="font-semibold hover:text-brand-700 dark:hover:text-brand-300">{{ $d->website?->name ?? '—' }}</a></td>
                            <td class="text-xs text-zinc-500">{{ $d->user?->email ?? '—' }}</td>
                            <td class="text-xs">{{ $d->type }}</td>
                            <td><span class="{{ $d->verification === 'verified' ? 'pill-green' : ($d->verification === 'pending' ? 'pill-amber' : 'pill-zinc') }}">{{ $d->verification }}</span></td>
                            <td><span class="{{ $d->ssl_status === 'active' ? 'pill-green' : ($d->ssl_status === 'pending' ? 'pill-amber' : ($d->ssl_status === 'failed' ? 'pill-red' : 'pill-zinc')) }}">{{ $d->ssl_status }}</span></td>
                            <td class="text-right whitespace-nowrap">
                                @if($d->website)
                                <form method="POST" action="{{ route('admin.hosting.domains.destroy', [$d->website, $d]) }}" class="inline" onsubmit="return confirm('Delete {{ $d->domain }}? This is permanent.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-bold text-red-600 hover:underline dark:text-red-400">Delete</button>
                                </form>
                                @else
                                <span class="text-xs text-zinc-400">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7"><x-empty-state icon="search" title="No domains found" message="Try different filters." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $domains->links() }}</div>
        </div>
    </div>
</x-layouts::app>
