@props([
    'icon' => null,
    'title' => 'Nothing here yet',
    'description' => null,
    'action' => null,
    'actionLabel' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center px-6 py-16 text-center']) }}>
    <span class="grid size-14 place-items-center rounded-2xl bg-gradient-to-br from-zinc-100 to-zinc-200 text-zinc-400 dark:from-zinc-800 dark:to-zinc-700 dark:text-zinc-400">
        @if($icon)
            <svg class="size-7" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">{!! $icon !!}</svg>
        @else
            <svg class="size-7" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20 13V7a2 2 0 00-2-2H6a2 2 0 00-2 2v10a2 2 0 002 2h6"/><path d="M8 9h8M8 13h5"/></svg>
        @endif
    </span>
    <p class="font-display mt-4 text-base font-extrabold tracking-tight">{{ $title }}</p>
    @if($description)
        <p class="mt-1 max-w-sm text-sm text-zinc-500 dark:text-zinc-400">{{ $description }}</p>
    @endif
    @if($action)
        <button type="button" x-on:click="{!! $action !!}" class="btn-brand btn-sm mt-4">{{ $actionLabel }}</button>
    @endif
</div>
