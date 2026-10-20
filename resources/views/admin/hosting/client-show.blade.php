<x-layouts::app :title="__($client->name)">
    @include('partials.toast')
    <div class="flex w-full flex-col gap-5">
        <x-page-head :title="$client->name" :sub="$client->email.' · joined '.$client->created_at->format('d M Y')">
            <x-slot:actions>
                <a href="{{ route('admin.hosting.clients') }}" class="btn-ghost btn-sm">← Clients</a>
            </x-slot:actions>
        </x-page-head>

        <div class="grid auto-rows-min gap-4 sm:grid-cols-3">
            <x-stat label="Websites" :value="$websites->count()" />
            <x-stat label="Committed" :value="'UGX '.number_format($orders->sum('total_amount'))" />
            <x-stat label="Token balance" :value="number_format($balance)" />
        </div>

        <div class="card card-pad">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="section-title">Credit tokens</h2>
                    <p class="page-sub">Add tokens to {{ $client->name }}'s wallet with an audit note.</p>
                </div>
                <form method="POST" action="{{ route('admin.users.tokens', $client) }}" class="flex flex-wrap items-end gap-2">
                    @csrf
                    @method('PATCH')
                    <input type="number" name="amount" min="1" max="10000000" placeholder="Tokens" class="field w-32" required>
                    <input type="text" name="reason" maxlength="120" placeholder="Reason (optional)" class="field w-52">
                    <button type="submit" class="btn-brand">Add tokens</button>
                </form>
            </div>
        </div>

        <div class="card card-pad">
            <h2 class="section-title">Websites</h2>
            <ul class="mt-3 space-y-2 text-sm">
                @forelse($websites as $w)
                <li class="flex items-center justify-between gap-2">
                    <a href="{{ route('admin.hosting.websites.show', $w) }}" class="font-semibold hover:text-brand-700 dark:hover:text-brand-300">{{ $w->name }}</a>
                    <span class="{{ $w->status === 'active' ? 'pill-green' : ($w->status === 'suspended' ? 'pill-red' : 'pill-zinc') }}">{{ str_replace('_', ' ', $w->status) }}</span>
                </li>
                @empty
                <li class="text-sm text-zinc-500">No websites registered.</li>
                @endforelse
            </ul>
        </div>

        <div class="card card-pad">
            <h2 class="section-title">Payment history</h2>
            <ul class="mt-3 space-y-2 text-sm">
                @forelse($orders as $o)
                <li class="flex items-center justify-between gap-2">
                    <span class="font-mono text-xs">{{ $o->reference }} · {{ $o->package }}</span>
                    <strong>UGX {{ number_format($o->paid_amount) }} · {{ $o->status }}</strong>
                </li>
                @empty
                <li class="text-sm text-zinc-500">No orders yet.</li>
                @endforelse
            </ul>
        </div>

        <div class="card card-pad">
            <h2 class="section-title">Recent hosting activity</h2>
            <ul class="mt-3 space-y-2 text-sm">
                @forelse($audit as $a)
                <li class="flex items-center justify-between gap-2">
                    <span>{{ $a->action }} <span class="text-xs text-zinc-400">· {{ $a->hostedWebsite?->name ?? '' }}</span></span>
                    <span class="shrink-0 text-xs text-zinc-400">{{ $a->created_at->diffForHumans() }}</span>
                </li>
                @empty
                <li class="text-sm text-zinc-500">No hosting activity yet.</li>
                @endforelse
            </ul>
        </div>
    </div>
</x-layouts::app>
