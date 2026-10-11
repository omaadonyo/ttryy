<x-layouts::app :title="__('Manage users')">
    @include('partials.toast')
    <div class="flex w-full flex-col gap-5">
        <x-page-head title="Users" sub="Customer accounts, order activity and administrator access." />

        <form method="GET" action="{{ route('admin.users') }}" class="card card-pad flex flex-col gap-3 sm:flex-row">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search name or email…" class="field flex-1">
            <button type="submit" class="btn-brand">Search</button>
        </form>

        <div class="card card-pad">
            <div class="tbl-wrap rounded-none! border-0! shadow-none!">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Registered</th>
                            <th>Orders</th>
                            <th>Total spent</th>
                            <th>Role</th>
                            <th class="text-right!">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td><p class="font-semibold">{{ $user->name }}</p><p class="text-xs text-zinc-500">{{ $user->email }}</p></td>
                            <td class="text-xs whitespace-nowrap text-zinc-500">{{ $user->created_at->format('d M Y') }}</td>
                            <td class="font-semibold">{{ $user->package_orders_count }}</td>
                            <td class="font-semibold whitespace-nowrap">UGX {{ number_format($user->package_orders_sum_total_amount ?? 0) }}</td>
                            <td>
                                @if($user->is_admin)
                                <span class="pill-green">admin</span>
                                @else
                                <span class="pill-zinc">customer</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.admin', $user) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn-ghost btn-sm">{{ $user->is_admin ? 'Remove admin' : 'Make admin' }}</button>
                                </form>
                                @else
                                <span class="text-xs text-zinc-400">you</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6"><x-empty-state icon="users" title="No users found" message="Try a different name or email search." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $users->links() }}</div>
        </div>
    </div>
</x-layouts::app>
