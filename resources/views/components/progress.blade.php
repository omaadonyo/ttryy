@props([
    'label' => null,
    'value' => null,
    'max' => null,
    'percent' => null,
    'tone' => 'brand',
    'caption' => null,
    'size' => 'md',
])

@php
    $tones = [
        'brand' => 'bg-brand-600',
        'emerald' => 'bg-emerald-600',
        'amber' => 'bg-amber-500',
        'red' => 'bg-red-600',
        'zinc' => 'bg-zinc-400',
    ];
    $bar = $tones[$tone] ?? $tones['brand'];

    if ($percent === null) {
        $percent = ($max !== null && (float) $max > 0)
            ? (int) round(min(100, max(0, ((float) $value / (float) $max) * 100)))
            : 0;
    }
    $percent = (int) min(100, max(0, $percent));

    $height = match ($size) {
        'sm' => 'h-1',
        'lg' => 'h-2.5',
        default => 'h-1.5',
    };
@endphp

<div {{ $attributes->merge(['class' => 'w-full']) }}>
    @if($label !== null || $value !== null)
    <div class="flex items-center justify-between gap-2">
        @if($label !== null)<span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400">{{ $label }}</span>@endif
        @if($value !== null)<span class="text-xs font-bold text-zinc-900 dark:text-white">{{ $value }}</span>@endif
    </div>
    @endif
    <div class="{{ $height }} {{ $label !== null || $value !== null ? 'mt-1.5' : '' }} overflow-hidden rounded-full bg-zinc-200 dark:bg-white/10" role="progressbar" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100">
        <div class="{{ $height }} rounded-full {{ $bar }} transition-[width] duration-500" style="width:{{ $percent }}%"></div>
    </div>
    @if($caption !== null)
    <p class="mt-1.5 text-[11px] text-zinc-500 dark:text-zinc-400">{{ $caption }}</p>
    @endif
</div>
