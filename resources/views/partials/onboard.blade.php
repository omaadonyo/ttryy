{{-- Dismissible per-page onboarding hint. Usage:
     @include('partials.onboard', ['key' => 'scraper', 'title' => '...', 'body' => '...', 'link' => url, 'linkLabel' => '...']) --}}
<div data-onboard-banner="{{ $key }}" class="hidden card card-pad border-brand-600/25 bg-brand-50/60 dark:border-brand-500/25 dark:bg-brand-500/10">
    <div class="flex items-start gap-3">
        <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-brand-600 text-white">
            <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 3v3M12 18v3M3 12h3M18 12h3M5.6 5.6l2.1 2.1M16.3 16.3l2.1 2.1M18.4 5.6l-2.1 2.1M7.7 16.3l-2.1 2.1"/><circle cx="12" cy="12" r="3.2"/></svg>
        </span>
        <div class="min-w-0 flex-1">
            <p class="text-sm font-extrabold">{{ $title }}</p>
            <p class="mt-0.5 text-[13px] text-zinc-600 dark:text-zinc-400">{{ $body }}</p>
            @if(!empty($link))
            <a href="{{ $link }}" class="mt-1.5 inline-block text-[13px] font-bold text-brand-700 hover:underline dark:text-brand-300">{{ $linkLabel ?? 'Learn more →' }}</a>
            @endif
        </div>
        <button data-onboard-dismiss="{{ $key }}" class="grid size-8 shrink-0 place-items-center rounded-full text-zinc-400 transition hover:bg-zinc-950/5 hover:text-zinc-700 dark:hover:bg-white/10 dark:hover:text-white" aria-label="Dismiss">✕</button>
    </div>
</div>
