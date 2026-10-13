<x-layouts::app :title="__('Domains')">
    @include('partials.toast')
    @php
        $verifyPill = fn($d) => $d->isVerified() ? 'pill-green' : ($d->verification === 'pending' ? 'pill-amber' : 'pill-zinc');
        $sslPill = fn($s) => match($s) {
            'active' => 'pill-green', 'pending' => 'pill-amber', 'failed' => 'pill-red', default => 'pill-zinc',
        };
    @endphp
    <div class="flex w-full flex-col gap-5">
        <x-page-head :title="'Domains · '.$website->name" sub="Subdomains are verified by definition. Custom domains need DNS proof.">
            <x-slot:actions>
                <a href="{{ route('hosting.show', $website) }}" class="btn-ghost btn-sm">← Website</a>
            </x-slot:actions>
        </x-page-head>

        <div class="grid gap-4 xl:grid-cols-2">
            <div class="card card-pad">
                <h2 class="section-title">New Ttryy subdomain</h2>
                <p class="page-sub">Free, instant, auto-verified. Suggested: <strong>{{ $suggested }}.{{ $baseDomain }}</strong></p>
                @if($errors->has('label'))
                <p class="mt-2 text-sm font-semibold text-red-600">{{ $errors->first('label') }}</p>
                @endif
                <form method="POST" action="{{ route('hosting.subdomains.store', $website) }}" class="mt-3 flex gap-2">
                    @csrf
                    <input name="label" value="{{ old('label') }}" placeholder="leave empty to auto-generate" class="field">
                    <button type="submit" class="btn-brand shrink-0">Add</button>
                </form>
            </div>
            <div class="card card-pad">
                <h2 class="section-title">Custom domain you own</h2>
                <p class="page-sub">Added as unverified — ownership is proven with a DNS TXT record, never assumed.</p>
                @if($errors->has('domain'))
                <p class="mt-2 text-sm font-semibold text-red-600">{{ $errors->first('domain') }}</p>
                @endif
                <form method="POST" action="{{ route('hosting.domains.store', $website) }}" class="mt-3 flex gap-2">
                    @csrf
                    <input name="domain" value="{{ old('domain') }}" placeholder="example.co.ug" class="field">
                    <button type="submit" class="btn-brand shrink-0">Add</button>
                </form>
            </div>
        </div>

        <div class="card card-pad">
            <h2 class="section-title">All domains</h2>
            @if($domains->isEmpty())
            <p class="py-4 text-center text-sm text-zinc-500">No domains yet.</p>
            @else
            <div class="tbl-wrap mt-3 rounded-none! border-0! shadow-none!">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>Domain</th>
                            <th>Type</th>
                            <th>Verification</th>
                            <th>DNS / SSL</th>
                            <th class="text-right!">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($domains as $d)
                        <tr>
                            <td class="font-mono text-xs">{{ $d->domain }}
                                @if($d->is_primary)<span class="pill-brand ml-1">primary</span>@endif
                            </td>
                            <td class="text-xs">{{ $d->type === 'subdomain' ? 'Ttryy subdomain' : 'Custom' }}</td>
                            <td><span class="{{ $verifyPill($d) }}">{{ $d->verification }}</span></td>
                            <td class="text-xs text-zinc-500">DNS {{ $d->dns_ok ? 'ok' : '—' }} · <span class="{{ $sslPill($d->ssl_status) }}">SSL {{ $d->ssl_status }}</span></td>
                            <td class="text-right whitespace-nowrap">
                                @if($d->type === 'custom' && $d->verification !== 'verified')
                                <form method="POST" action="{{ route('hosting.domains.verify', [$website, $d]) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-xs font-bold text-brand-700 hover:underline dark:text-brand-300">Check DNS</button>
                                </form>
                                @endif
                                @if($d->isVerified() && ! $d->is_primary)
                                <form method="POST" action="{{ route('hosting.domains.primary', [$website, $d]) }}" class="inline ml-2">
                                    @csrf
                                    <button type="submit" class="text-xs font-bold text-zinc-600 hover:underline dark:text-zinc-300">Make primary</button>
                                </form>
                                @endif
                                @if(! $d->is_primary)
                                <form method="POST" action="{{ route('hosting.domains.destroy', [$website, $d]) }}" class="inline ml-2" onsubmit="return confirm('Remove {{ $d->domain }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold text-zinc-400 hover:text-red-600">Remove</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @if($d->type === 'custom' && $d->verification !== 'verified')
                        <tr>
                            <td colspan="5" class="!pt-0 text-xs text-zinc-500">
                                Add this TXT record at <strong>_ttryy-challenge.{{ $d->domain }}</strong> with value
                                <code class="rounded bg-zinc-100 px-1.5 py-0.5 font-mono dark:bg-white/10">{{ $d->verification_token }}</code>
                                then press Check DNS. Last checked {{ $d->dns_checked_at?->diffForHumans() ?? 'never' }}.
                            </td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</x-layouts::app>
