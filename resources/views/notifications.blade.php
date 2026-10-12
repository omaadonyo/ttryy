<x-layouts::app :title="__('Notifications')">
    @include('partials.toast')
    @php
        $kindMeta = [
            'order' => ['icon' => 'shopping-bag', 'chip' => 'bg-brand-600 text-white'],
            'payment' => ['icon' => 'banknotes', 'chip' => 'bg-emerald-600 text-white'],
            'email' => ['icon' => 'envelope', 'chip' => 'bg-sky-600 text-white'],
            'tokens' => ['icon' => 'bolt', 'chip' => 'bg-amber-500 text-white'],
        ];
    @endphp
    <div class="flex w-full flex-col gap-5">
        <x-page-head title="Notification center" :sub="$unreadCount.' unread · '.$totalCount.' total'">
            <x-slot:actions>
                @if($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}" class="inline">
                    @csrf
                    <button type="submit" class="btn-ghost btn-sm">Mark all read</button>
                </form>
                @endif
            </x-slot:actions>
        </x-page-head>

        <div class="seg self-start" role="tablist" aria-label="Notifications filter">
            <a href="{{ route('notifications.index') }}" class="rounded-lg px-4 py-2 text-[13px] font-bold whitespace-nowrap transition {{ $tab !== 'unread' ? 'bg-white text-zinc-950 shadow dark:bg-zinc-700 dark:text-white' : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-white' }}">All <span class="ml-1">{{ $totalCount }}</span></a>
            <a href="{{ route('notifications.index', ['tab' => 'unread']) }}" class="rounded-lg px-4 py-2 text-[13px] font-bold whitespace-nowrap transition {{ $tab === 'unread' ? 'bg-white text-zinc-950 shadow dark:bg-zinc-700 dark:text-white' : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-white' }}">Unread <span class="ml-1">{{ $unreadCount }}</span></a>
        </div>

        <div class="card card-pad">
            @if($items->isEmpty())
                <x-empty-state icon="inbox" title="{{ $tab === 'unread' ? 'All caught up' : 'No notifications yet' }}" message="{{ $tab === 'unread' ? 'You have read everything — new orders, payments and campaigns will land here.' : 'Orders, payments, top-ups and campaigns will show up here.' }}" />
            @else
            <div class="flex flex-col gap-1">
                @foreach($items as $n)
                @php $meta = $kindMeta[$n->data['kind'] ?? ''] ?? ['icon' => 'bell', 'chip' => 'bg-zinc-500 text-white']; @endphp
                <a href="{{ route('notifications.open', $n->id) }}" class="flex items-start gap-3.5 rounded-xl px-3.5 py-3 transition hover:bg-zinc-950/5 dark:hover:bg-white/10 {{ $n->read_at ? '' : 'bg-brand-50/60 dark:bg-brand-500/10' }}">
                    <span class="grid size-10 shrink-0 place-items-center rounded-lg {{ $meta['chip'] }}">
                        <flux:icon name="{{ $meta['icon'] }}" class="size-5" />
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="flex items-center gap-2">
                            <span class="truncate text-sm font-bold">{{ $n->data['title'] ?? 'Notification' }}</span>
                            @if(!$n->read_at)<span class="size-2 shrink-0 rounded-full bg-brand-600"></span>@endif
                        </span>
                        @if(!empty($n->data['body']))<span class="mt-0.5 block text-[13px] text-zinc-500 dark:text-zinc-400">{{ $n->data['body'] }}</span>@endif
                        <span class="mt-1 block text-[11px] text-zinc-400">{{ $n->created_at->diffForHumans() }} · {{ $n->created_at->format('d M Y H:i') }}</span>
                    </span>
                    <flux:icon name="chevron-right" class="mt-2.5 size-4 shrink-0 text-zinc-300 dark:text-zinc-600" />
                </a>
                @endforeach
            </div>
            <div class="mt-4">{{ $items->links() }}</div>
            @endif
        </div>
    </div>
</x-layouts::app>
