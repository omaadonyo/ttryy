<x-layouts::app :title="__('Integrations')">
    @include('partials.toast')
    <div class="flex w-full flex-col gap-5">
        <x-page-head title="Service integrations" sub="Honest status of every hosting integration. Unconfigured means unconfigured — nothing is simulated.">
            <x-slot:actions>
                <a href="{{ route('admin.hosting.dashboard') }}" class="btn-ghost btn-sm">← Control</a>
            </x-slot:actions>
        </x-page-head>

        <div class="card card-pad">
            <ul class="divide-y divide-zinc-100 dark:divide-white/5">
                @foreach($items as $item)
                <li class="flex items-start gap-3 py-3.5">
                    <span class="mt-0.5 grid size-7 shrink-0 place-items-center rounded-lg {{ $item['ok'] ? 'bg-emerald-600 text-white' : 'bg-zinc-200 text-zinc-500 dark:bg-white/10 dark:text-zinc-400' }}">
                        @if($item['ok'])
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                        @else
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" viewBox="0 0 24 24"><path d="M12 8v5M12 16.5v.01"/><circle cx="12" cy="12" r="9"/></svg>
                        @endif
                    </span>
                    <span class="min-w-0">
                        <span class="block text-sm font-extrabold">{{ $item['label'] }}</span>
                        @if($item['hint'])<span class="mt-0.5 block text-[13px] text-zinc-500 dark:text-zinc-400">{{ $item['hint'] }}</span>@endif
                    </span>
                </li>
                @endforeach
            </ul>
        </div>

        <div class="card card-pad">
            <h2 class="section-title">Embed the service backend</h2>
            <p class="page-sub">Ship <span class="font-mono text-xs">App\Services\Hosting\Service\ServiceCommandProcessor</span> (v{{ \App\Services\Hosting\Service\ServiceCommandProcessor::VERSION }}) inside any hosted Laravel site, mount its three routes, and store the issued key + secret in that site's .env. Full steps, suspension semantics and the HMAC envelope are documented in the project README.</p>
            <div class="mt-3 grid gap-2 font-mono text-xs sm:grid-cols-3">
                <p class="rounded-lg bg-zinc-950 p-3 text-emerald-300">POST /hosting-service/commands</p>
                <p class="rounded-lg bg-zinc-950 p-3 text-emerald-300">GET /hosting-service/status</p>
                <p class="rounded-lg bg-zinc-950 p-3 text-emerald-300">GET /hosting-service/health</p>
            </div>
        </div>
    </div>
</x-layouts::app>
