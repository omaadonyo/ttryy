@props(['icon' => 'inbox', 'title' => 'Nothing here yet', 'message' => '', 'actionUrl' => null, 'actionLabel' => null])

<div class="py-10 px-6 text-center">
    <div class="w-14 h-14 mx-auto grid place-items-center rounded-full bg-zinc-100 dark:bg-white/10 text-zinc-400">
        @switch($icon)
            @case('orders')
                <svg class="size-7" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M6 7h12l1 13H5L6 7z"/><path d="M9 10V6a3 3 0 016 0v4"/></svg>
                @break
            @case('payments')
                <svg class="size-7" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 10h18M7 15h4"/></svg>
                @break
            @case('users')
                <svg class="size-7" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c.8-3.2 3.4-5 6.5-5s5.7 1.8 6.5 5M16 4.6a3.5 3.5 0 010 6.8M17.5 15.4c2 .7 3.5 2.3 4 4.6"/></svg>
                @break
            @case('search')
                <svg class="size-7" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
                @break
            @default
                <svg class="size-7" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 13l2.5-7.5A1 1 0 016.45 4.5h11.1a1 1 0 01.95 1L21 13v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5z"/><path d="M3 13h6l1.5 2h3L15 13h6"/></svg>
        @endswitch
    </div>
    <p class="font-display font-bold text-lg mt-4 text-zinc-950 dark:text-white">{{ $title }}</p>
    @if($message)
    <p class="text-sm text-zinc-500 mt-1 max-w-sm mx-auto">{{ $message }}</p>
    @endif
    @if($actionUrl)
    <a href="{{ $actionUrl }}" class="inline-block mt-5 bg-[#9e005d] hover:bg-[#7e0049] text-white text-sm font-bold px-6 py-2.5 rounded-full transition">{{ $actionLabel ?? 'Get started' }}</a>
    @endif
</div>
