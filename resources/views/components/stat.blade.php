@props(['label', 'value', 'hint' => null])

<div class="card card-pad">
    <p class="eyebrow">{{ $label }}</p>
    <p class="stat-num mt-2">{{ $value }}</p>
    @if($hint)<p class="mt-1.5 text-[13px] text-zinc-500 dark:text-zinc-400">{{ $hint }}</p>@endif
</div>
