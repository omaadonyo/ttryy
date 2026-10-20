<x-layouts::app :title="__('Feature suggestions')">
    @include('partials.toast')
    <div class="flex w-full flex-col gap-5">
        <x-page-head title="Feature suggestions"
            sub="Ideas from Business Tools users, ranked by votes. Ship an idea by marking it shipped — voters are told when it ships." />

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.suggestions') }}" class="pill-zinc {{ ($filters['status'] ?? '') === '' ? 'ring-2 ring-brand-500' : '' }}">All</a>
            @foreach($statuses as $status)
                <a href="{{ route('admin.suggestions', ['status' => $status]) }}"
                   class="pill-zinc capitalize {{ ($filters['status'] ?? '') === $status ? 'ring-2 ring-brand-500' : '' }}">
                    {{ $status }} ({{ $counts[$status] }})
                </a>
            @endforeach
        </div>

        <div class="flex flex-col gap-3">
            @forelse($suggestions as $suggestion)
                <div class="card card-pad">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="pill-brand">{{ $suggestion->votes }} votes</span>
                                <span class="pill-zinc capitalize">{{ $suggestion->status }}</span>
                            </div>
                            <p class="mt-2 font-display font-extrabold">{{ $suggestion->title }}</p>
                            @if($suggestion->body)
                                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">{{ $suggestion->body }}</p>
                            @endif
                            <p class="mt-2 text-xs text-zinc-500">
                                {{ $suggestion->user?->name ?? 'Someone' }} · {{ $suggestion->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <form method="POST" action="{{ route('admin.suggestions.update', $suggestion) }}" class="flex flex-col gap-2 sm:flex-row sm:items-end">
                            @csrf @method('PATCH')
                            <label class="flex flex-col gap-1">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-500">Status</span>
                                <select name="status" class="field h-9 py-0 text-xs">
                                    @foreach($statuses as $status)
                                        <option value="{{ $status }}" @selected($suggestion->status === $status)>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="flex flex-col gap-1">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-500">Reply</span>
                                <input type="text" name="reply" value="{{ $suggestion->reply }}" class="field h-9 py-0 text-xs" placeholder="Note back to voters" />
                            </label>
                            <button class="btn-brand btn-sm">Save</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="card card-pad text-center text-zinc-500">No suggestions yet.</div>
            @endforelse
        </div>

        {{ $suggestions->links() }}
    </div>
</x-layouts::app>
