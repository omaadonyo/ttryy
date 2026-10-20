@props([
    'tab',
    'title' => 'Premium feature',
    'description' => 'Upgrade to Business Tools Premium to unlock this section with live, unlimited figures.',
])

<div class="relative">
    <div :class="isLocked('{{ $tab }}') && 'pointer-events-none select-none blur-[6px]'" aria-hidden="true">
        {{ $slot }}
    </div>

    <div x-show="isLocked('{{ $tab }}')" x-cloak
        class="absolute inset-0 z-10 flex flex-col items-center justify-start gap-3 rounded-2xl bg-white/70 px-6 pt-20 text-center backdrop-blur-sm dark:bg-zinc-950/70">
        <span class="grid size-12 place-items-center rounded-2xl bg-amber-100 text-amber-600 dark:bg-amber-500/15 dark:text-amber-300">
            <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V7a4 4 0 018 0v4"/></svg>
        </span>
        <p class="font-display text-lg font-extrabold tracking-tight">{{ $title }}</p>
        <p class="max-w-sm text-sm text-zinc-500 dark:text-zinc-400">{{ $description }}</p>
        <button type="button" @click="openUpgrade()" class="btn-brand btn-sm gap-1.5">
            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V7a4 4 0 018 0v4"/></svg>
            Unlock — UGX 800/day
        </button>
        <p class="text-[11px] text-zinc-400">The figures behind this blur are samples, not your data.</p>
    </div>
</div>
