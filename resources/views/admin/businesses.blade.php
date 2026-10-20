<x-layouts::app :title="__('Business Tools subscriptions')">
    @include('partials.toast')
    <div class="flex w-full flex-col gap-5">
        <x-page-head title="Business Tools subscriptions"
            sub="Manage premium access per business. Free covers one business, two team members, three products and one document of each type." />

        <div class="grid gap-4 sm:grid-cols-3">
            <div class="card card-pad">
                <p class="text-xs font-bold uppercase tracking-wider text-zinc-500">Businesses</p>
                <p class="stat-num">{{ number_format($summary['total']) }}</p>
            </div>
            <div class="card card-pad">
                <p class="text-xs font-bold uppercase tracking-wider text-zinc-500">On premium</p>
                <p class="stat-num">{{ number_format($summary['premium']) }}</p>
            </div>
            <div class="card card-pad">
                <p class="text-xs font-bold uppercase tracking-wider text-zinc-500">Premium revenue</p>
                <p class="stat-num">UGX {{ number_format($summary['revenue']) }}</p>
                <p class="mt-1 text-xs text-zinc-500">UGX {{ number_format($prices['daily']) }}/day · UGX {{ number_format($prices['monthly']) }}/month</p>
            </div>
        </div>

        <form method="GET" class="card card-pad flex flex-wrap items-end gap-3">
            <label class="flex flex-col gap-1.5">
                <span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Status</span>
                <select name="status" class="field">
                    <option value="">All</option>
                    <option value="premium" @selected(($filters['status'] ?? '') === 'premium')>Premium</option>
                    <option value="free" @selected(($filters['status'] ?? '') === 'free')>Free</option>
                </select>
            </label>
            <label class="flex flex-col gap-1.5">
                <span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Search</span>
                <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" class="field" placeholder="Name, phone or email" />
            </label>
            <button class="btn-brand btn-sm">Filter</button>
            <a href="{{ route('admin.businesses') }}" class="btn-ghost btn-sm">Reset</a>
        </form>

        <div class="card overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-zinc-50 text-left text-xs uppercase tracking-wider text-zinc-500 dark:bg-white/5">
                    <tr>
                        <th class="px-4 py-3">Business</th>
                        <th class="px-4 py-3">Owner</th>
                        <th class="px-4 py-3">Usage</th>
                        <th class="px-4 py-3">Plan</th>
                        <th class="px-4 py-3 text-right">Manage</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-white/5">
                    @forelse($businesses as $business)
                        @php $premium = $business->isPremium(); @endphp
                        <tr>
                            <td class="px-4 py-3">
                                <p class="font-bold">{{ $business->name }}</p>
                                <p class="text-xs text-zinc-500">{{ $business->email ?: '—' }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-semibold">{{ $business->owner?->name ?? 'Unknown' }}</p>
                                <p class="text-xs text-zinc-500">{{ $business->owner?->email }}</p>
                            </td>
                            <td class="px-4 py-3 text-xs text-zinc-500">
                                {{ $business->users_count }} team · {{ $business->items_count }} products · {{ $business->documents_count }} documents
                            </td>
                            <td class="px-4 py-3">
                                @if($premium)
                                    <span class="pill-brand">Premium</span>
                                    <p class="mt-1 text-xs text-zinc-500">until {{ $business->premium_until->toFormattedDateString() }}</p>
                                @else
                                    <span class="pill-zinc">Free</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <form method="POST" action="{{ route('admin.businesses.premium', $business) }}" class="flex items-center gap-2">
                                        @csrf
                                        <select name="cycle" class="field h-9 py-0 text-xs">
                                            <option value="daily">+1 day</option>
                                            <option value="monthly" selected>+1 month</option>
                                        </select>
                                        <button class="btn-brand btn-sm">Grant</button>
                                    </form>
                                    @if($premium)
                                        <form method="POST" action="{{ route('admin.businesses.premium.revoke', $business) }}">
                                            @csrf @method('DELETE')
                                            <button class="btn-ghost btn-sm text-red-600">Revoke</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-zinc-500">No businesses yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $businesses->links() }}
    </div>
</x-layouts::app>
