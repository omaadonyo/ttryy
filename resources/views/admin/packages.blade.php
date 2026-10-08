<x-layouts::app :title="__('Manage packages')">
    <div class="flex h-full w-full flex-1 flex-col gap-4">
        <div>
            <flux:heading size="xl">Packages</flux:heading>
            <flux:text class="mt-1">How each tier is performing. Prices live in <span class="font-mono text-xs">config/packages.php</span>.</flux:text>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            @foreach($stats as $name => $s)
            <div class="rounded-xl bg-white dark:bg-white/[.04] p-6 shadow-[0_12px_32px_-12px_rgba(10,10,12,.18)] dark:shadow-[0_12px_32px_-12px_rgba(0,0,0,.7)]">
                <div class="flex items-center justify-between gap-2">
                    <p class="font-bold tracking-wide">{{ $name }}</p>
                    <flux:badge size="sm" color="zinc">{{ $s['active'] }} active</flux:badge>
                </div>
                <p class="mt-1 text-3xl font-extrabold">UGX {{ number_format($s['data']['price']) }}</p>
                <p class="text-xs text-zinc-500 mt-1">{{ $s['data']['monthly'] ? number_format($s['data']['monthly']).'/mo · ' : '' }}{{ $s['data']['blurb'] }}</p>
                <dl class="mt-4 grid grid-cols-3 gap-2 text-sm">
                    <div class="rounded-lg bg-zinc-50 dark:bg-white/5 p-3"><dt class="text-[11px] text-zinc-500">Orders</dt><dd class="font-extrabold text-lg">{{ $s['orders'] }}</dd></div>
                    <div class="rounded-lg bg-zinc-50 dark:bg-white/5 p-3"><dt class="text-[11px] text-zinc-500">Committed</dt><dd class="font-extrabold text-lg">{{ number_format($s['revenue'] / 1000) }}K</dd></div>
                    <div class="rounded-lg bg-[#9e005d]/10 p-3"><dt class="text-[11px] text-zinc-500">Collected</dt><dd class="font-extrabold text-lg text-[#9e005d]">{{ number_format($s['collected'] / 1000) }}K</dd></div>
                </dl>
            </div>
            @endforeach
        </div>
    </div>
</x-layouts::app>
