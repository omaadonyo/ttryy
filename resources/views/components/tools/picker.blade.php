@props([
    'options' => '[]',
    'track' => 'id',
    'label' => 'name',
    'display' => null,
    'placeholder' => 'Select…',
])

<div
    x-data="btPicker({ options: {{ $options }}, track: @js($track), labelKey: @js($label), display: {!! $display ?: 'null' !!}, placeholder: @js($placeholder) })"
    x-modelable="value"
    {{ $attributes->merge(['class' => 'relative']) }}
    @click.outside="open = false"
>
    <button type="button" @click="open = !open"
        class="field flex w-full items-center justify-between gap-2 text-left">
        <span class="truncate" :class="selected() ? '' : 'text-zinc-400'"
            x-text="selected() ? label(selected()) : placeholder"></span>
        <svg class="size-4 shrink-0 text-zinc-400 transition" :class="open && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
    </button>

    <div x-show="open" x-cloak x-transition.opacity
        class="absolute z-30 mt-1 max-h-64 w-full min-w-[12rem] overflow-y-auto rounded-xl border border-zinc-200 bg-white p-1 shadow-2xl dark:border-white/10 dark:bg-zinc-900">
        <template x-for="opt in options" :key="opt[track]">
            <button type="button" @click="value = opt[track]; open = false"
                class="flex w-full items-center justify-between gap-2 rounded-lg px-3 py-2 text-left text-sm font-semibold hover:bg-zinc-100 dark:hover:bg-white/10">
                <span class="truncate" x-text="label(opt)"></span>
                <svg x-show="value === opt[track]" class="size-4 shrink-0 text-brand-600 dark:text-brand-300" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
            </button>
        </template>
        <p x-show="!options.length" class="px-3 py-6 text-center text-xs text-zinc-400">No options</p>
    </div>
</div>
