<x-layouts::app :title="__('Manage users')">
    <div class="flex h-full w-full flex-1 flex-col gap-4">
        <div>
            <flux:heading size="xl">Users</flux:heading>
            <flux:text class="mt-1">Customer accounts, order activity and administrator access.</flux:text>
        </div>

        @if(session('status'))
        <div class="rounded-xl bg-emerald-500/10 border border-emerald-500/30 px-5 py-3 text-sm text-emerald-700 dark:text-emerald-400">{{ session('status') }}</div>
        @endif

        <form method="GET" action="{{ route('admin.users') }}" class="rounded-xl bg-white dark:bg-white/[.04] p-4 shadow-[0_12px_32px_-12px_rgba(10,10,12,.18)] dark:shadow-[0_12px_32px_-12px_rgba(0,0,0,.7)] flex flex-col sm:flex-row gap-3">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search name or email…" class="flex-1 rounded-[0.575rem] border border-neutral-300 dark:border-white/15 bg-white dark:bg-white/5 px-4 py-2.5 text-sm outline-none focus:border-[#9e005d]">
            <flux:button type="submit" variant="primary" class="!bg-[#9e005d] hover:!bg-[#7e0049]">Search</flux:button>
        </form>

        <div class="rounded-xl bg-white dark:bg-white/[.04] p-5 shadow-[0_12px_32px_-12px_rgba(10,10,12,.18)] dark:shadow-[0_12px_32px_-12px_rgba(0,0,0,.7)]">
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[680px]">
                    <thead>
                        <tr class="text-left text-zinc-500">
                            <th class="py-2 pr-4 font-medium">User</th>
                            <th class="py-2 pr-4 font-medium">Registered</th>
                            <th class="py-2 pr-4 font-medium">Orders</th>
                            <th class="py-2 pr-4 font-medium">Total spent</th>
                            <th class="py-2 pr-4 font-medium">Role</th>
                            <th class="py-2 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-white/10">
                        @forelse($users as $user)
                        <tr>
                            <td class="py-2.5 pr-4"><p class="font-semibold">{{ $user->name }}</p><p class="text-xs text-zinc-500">{{ $user->email }}</p></td>
                            <td class="py-2.5 pr-4 text-xs text-zinc-500">{{ $user->created_at->format('d M Y') }}</td>
                            <td class="py-2.5 pr-4 font-semibold">{{ $user->package_orders_count }}</td>
                            <td class="py-2.5 pr-4 font-semibold">UGX {{ number_format($user->package_orders_sum_total_amount ?? 0) }}</td>
                            <td class="py-2.5 pr-4">
                                @if($user->is_admin)
                                <flux:badge size="sm" color="emerald">admin</flux:badge>
                                @else
                                <flux:badge size="sm" color="zinc">customer</flux:badge>
                                @endif
                            </td>
                            <td class="py-2.5 text-right">
                                @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.admin', $user) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <flux:button type="submit" size="sm" variant="ghost">{{ $user->is_admin ? 'Remove admin' : 'Make admin' }}</flux:button>
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
