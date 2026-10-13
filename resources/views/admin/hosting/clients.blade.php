<x-layouts::app :title="__('Hosting clients')">
    @include('partials.toast')
    <div class="flex w-full flex-col gap-5">
        <x-page-head title="Hosting clients" sub="Everyone with at least one touchpoint on the platform — filter to operators later." />
        <div class="card card-pad">
            <form method="GET" action="{{ route('admin.hosting.clients') }}" class="flex flex-col gap-3 sm:flex-row">
                <input type="text" name="q" value="{{ $q }}" placeholder="Search name or email…" class="field flex-1">
                <button type="submit" class="btn-brand">Search</button>
            </form>
        </div>
        <div class="card card-pad">
            <div class="tbl-wrap rounded-none! border-0! shadow-none!">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Websites</th>
                            <th>Committed</th>
                            <th>Joined</th>
                            <th class="text-right!">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clients as $c)
                        <tr>
                            <td><p class="font-semibold">{{ $c->name }}</p><p class="text-xs text-zinc-500">{{ $c->email }}</p></td>
                            <td class="font-semibold">{{ $c->hosted_websites_count }}</td>
                            <td class="font-semibold">UGX {{ number_format($c->package_orders_sum_total_amount ?? 0) }}</td>
                            <td class="text-xs text-zinc-500">{{ $c->created_at->format('d M Y') }}</td>
                            <td class="text-right"><a href="{{ route('admin.hosting.clients.show', $c) }}" class="text-xs font-bold text-brand-700 hover:underline dark:text-brand-300">Open →</a></td>
                        </tr>
                        @empty
                        <tr><td colspan="5"><x-empty-state icon="users" title="No clients found" message="Try a different search." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $clients->links() }}</div>
        </div>
    </div>
</x-layouts::app>
