<x-layouts::app :title="__('Domains')">
    @include('partials.toast')
    @php
        $verifyPill = fn($d) => $d->isVerified() ? 'pill-green' : ($d->verification === 'pending' ? 'pill-amber' : 'pill-zinc');
        $sslPill = fn($s) => match($s) {
            'active' => 'pill-green', 'pending' => 'pill-amber', 'failed' => 'pill-red', default => 'pill-zinc',
        };
    @endphp
    <div class="flex w-full flex-col gap-5">
        <x-page-head :title="'Domains · '.$website->name" sub="Managed for you by the ttryy team.">
            <x-slot:actions>
                <a href="{{ route('hosting.show', $website) }}" class="btn-ghost btn-sm">← Website</a>
            </x-slot:actions>
        </x-page-head>

        <div data-onboard-banner="domains-client" class="hidden card card-pad border-brand-600/25 bg-brand-50/60 dark:border-brand-500/25 dark:bg-brand-500/10">
            <div class="flex items-start gap-3">
                <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-brand-600 text-white">
                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-extrabold">We handle domains for you</p>
                    <p class="mt-0.5 text-[13px] text-zinc-600 dark:text-zinc-400">Subdomains, custom domains, DNS and SSL are set up and monitored by our team. Need a change? Message us on WhatsApp and it gets done.</p>
                </div>
                <button data-onboard-dismiss="domains-client" class="grid size-8 shrink-0 place-items-center rounded-full text-zinc-400 transition hover:bg-zinc-950/5 hover:text-zinc-700 dark:hover:bg-white/10 dark:hover:text-white" aria-label="Dismiss">✕</button>
            </div>
        </div>

        <div class="card card-pad">
            <h2 class="section-title">All domains</h2>
            @if($domains->isEmpty())
            <p class="py-4 text-center text-sm text-zinc-500">No domains yet — our team sets these up during provisioning.</p>
            @else
            <div class="tbl-wrap mt-3 rounded-none! border-0! shadow-none!">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>Domain</th>
                            <th>Type</th>
                            <th>Verification</th>
                            <th>DNS / SSL</th>
                            <th>Expires</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($domains as $d)
                        <tr>
                            <td class="font-mono text-xs">{{ $d->domain }}
                                @if($d->is_primary)<span class="pill-brand ml-1">primary</span>@endif
                            </td>
                            <td class="text-xs">{{ $d->type === 'subdomain' ? 'Free ttryy subdomain' : 'Custom domain' }}</td>
                            <td><span class="{{ $verifyPill($d) }}">{{ $d->verification }}</span></td>
                            <td class="text-xs text-zinc-500">DNS {{ $d->dns_ok ? 'ok' : 'pending' }} · <span class="{{ $sslPill($d->ssl_status) }}">SSL {{ $d->ssl_status }}</span></td>
                            <td class="text-xs text-zinc-500">{{ $d->expires_at?->format('d M Y') ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</x-layouts::app>
