<x-layouts::app :title="__('Manage packages')">
    @include('partials.toast')
    <div class="flex w-full flex-col gap-5">
        <x-page-head title="Packages" sub="How each tier is performing. Prices live in config/packages.php." />

        <div class="grid gap-4 sm:grid-cols-2">
            @foreach($stats as $name => $s)
            <div class="card card-pad">
                <div class="flex items-center justify-between gap-2">
                    <p class="font-display text-sm font-extrabold tracking-widest">{{ $name }}</p>
                    <span class="pill-zinc">{{ $s['active'] }} active</span>
                </div>
                <p class="stat-num mt-1">UGX {{ number_format($s['data']['price']) }}</p>
                <p class="mt-1 text-xs text-zinc-500">{{ $s['data']['monthly'] ? number_format($s['data']['monthly']).'/mo · ' : '' }}{{ $s['data']['blurb'] }}</p>
                <dl class="mt-4 grid grid-cols-3 gap-2 text-sm">
                    <div class="rounded-xl border border-zinc-200/70 bg-zinc-50 p-3 dark:border-white/10 dark:bg-white/5"><dt class="text-[11px] text-zinc-500">Orders</dt><dd class="text-lg font-extrabold">{{ $s['orders'] }}</dd></div>
                    <div class="rounded-xl border border-zinc-200/70 bg-zinc-50 p-3 dark:border-white/10 dark:bg-white/5"><dt class="text-[11px] text-zinc-500">Committed</dt><dd class="text-lg font-extrabold">{{ number_format($s['revenue'] / 1000) }}K</dd></div>
                    <div class="rounded-xl border border-brand-600/20 bg-brand-50 p-3 dark:border-brand-500/30 dark:bg-brand-500/10"><dt class="text-[11px] text-zinc-500">Collected</dt><dd class="text-lg font-extrabold text-brand-700 dark:text-brand-200">{{ number_format($s['collected'] / 1000) }}K</dd></div>
                </dl>
            </div>
            @endforeach
        </div>
    </div>
</x-layouts::app>
