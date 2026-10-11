@props(['title', 'sub' => null])

<div class="flex items-end justify-between gap-3 flex-wrap">
    <div class="min-w-0">
        <h1 class="page-title">{{ $title }}</h1>
        @if($sub)<p class="page-sub">{{ $sub }}</p>@endif
    </div>
    @if(isset($actions))
    <div class="flex items-center gap-2 flex-wrap">{{ $actions }}</div>
    @endif
</div>
