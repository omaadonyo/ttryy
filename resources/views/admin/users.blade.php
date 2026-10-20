<x-layouts::app :title="__('Manage users')">
    @include('partials.toast')
    <div class="flex w-full flex-col gap-5">
        <x-page-head title="Users" sub="Customer accounts, order activity and administrator access.">
            <x-slot:actions>
                <button onclick="startTour_ausers()" class="btn-ghost btn-sm">Take tour</button>
                @if($show === 'trash')
                <a href="{{ route('admin.users') }}" class="btn-ghost btn-sm">← All users</a>
                @else
                <a href="{{ route('admin.users', ['show' => 'suspended']) }}" class="btn-ghost btn-sm">Suspended ({{ $suspendedCount }})</a>
                <a href="{{ route('admin.users', ['show' => 'trash']) }}" class="btn-ghost btn-sm">Trash ({{ $trashCount }})</a>
                @endif
            </x-slot:actions>
        </x-page-head>

        @include('partials.tour', ['key' => 'ausers', 'steps' => [
            ['target' => '#tour-austats', 'title' => 'Base at a glance', 'body' => 'Registered users, administrators and 30-day growth.'],
            ['target' => '#tour-auschart', 'title' => 'Signup momentum', 'body' => 'New accounts per month for the last 6 months.'],
            ['target' => '#tour-autable', 'title' => 'Manage access', 'body' => 'Grant or revoke admin, suspend or reactivate, or move an account to trash — never your own account.'],
        ]])

        <div id="tour-austats" class="grid auto-rows-min gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-stat label="Registered users" :value="$totalUsers" />
            <x-stat label="Administrators" :value="$adminCount" />
            <x-stat label="New · 30 days" :value="$newCount" />
            <x-stat label="Suspended" :value="$suspendedCount" />
        </div>

        <div id="tour-auschart" class="card card-pad">
            <h2 class="section-title">Signups per month</h2>
            <p class="page-sub">New accounts, last 6 months.</p>
            <div class="mt-3 h-48"><canvas id="admin-signups-chart"></canvas></div>
        </div>

        <form method="GET" action="{{ route('admin.users') }}" class="card card-pad flex flex-col gap-3 sm:flex-row">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search name or email…" class="field flex-1">
            <button type="submit" class="btn-brand">Search</button>
        </form>

        <div id="tour-autable" class="card card-pad">
            <div class="tbl-wrap rounded-none! border-0! shadow-none!">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Registered</th>
                            <th>Orders</th>
                            <th>Total spent</th>
                            <th>Role</th>
                            <th>Status</th>
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
                            <td>
                                @if($show === 'trash')
                                <span class="pill-zinc">deleted</span>
                                @elseif($user->suspended_at)
                                <span class="pill-red">suspended</span>
                                @else
                                <span class="pill-green">active</span>
                                @endif
                            </td>
                            <td class="text-right whitespace-nowrap">
                                @if($show === 'trash')
                                <form method="POST" action="{{ route('admin.users.restore', $user->id) }}" class="inline" onsubmit="return confirm('Restore {{ $user->email }}?')">
                                    @csrf
                                    <button type="submit" class="text-xs font-bold text-emerald-600 hover:underline dark:text-emerald-400">Restore</button>
                                </form>
                                <span class="mx-1 text-zinc-300 dark:text-zinc-600">·</span>
                                <form method="POST" action="{{ route('admin.users.purge', $user->id) }}" class="inline" onsubmit="return confirm('PERMANENTLY delete {{ $user->email }} and all their data? This cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-bold text-red-600 hover:underline dark:text-red-400">Delete forever</button>
                                </form>
                                @elseif($user->id === auth()->id())
                                <span class="text-xs text-zinc-400">you</span>
                                @else
                                <form method="POST" action="{{ route('admin.users.admin', $user) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-xs font-bold text-zinc-600 hover:underline dark:text-zinc-300">{{ $user->is_admin ? 'Remove admin' : 'Make admin' }}</button>
                                </form>
                                @unless($user->is_admin)
                                <span class="mx-1 text-zinc-300 dark:text-zinc-600">·</span>
                                @if($user->suspended_at)
                                <form method="POST" action="{{ route('admin.users.activate', $user) }}" class="inline" onsubmit="return confirm('Reactivate {{ $user->email }}?')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-xs font-bold text-emerald-600 hover:underline dark:text-emerald-400">Activate</button>
                                </form>
                                @else
                                <form method="POST" action="{{ route('admin.users.suspend', $user) }}" class="inline" onsubmit="return confirm('Suspend {{ $user->email }}? They are signed out immediately.')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-xs font-bold text-amber-600 hover:underline dark:text-amber-400">Suspend</button>
                                </form>
                                @endif
                                <span class="mx-1 text-zinc-300 dark:text-zinc-600">·</span>
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline" onsubmit="return confirm('Move {{ $user->email }} to trash? Their data is kept until purged.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-bold text-red-600 hover:underline dark:text-red-400">Delete</button>
                                </form>
                                <span class="mx-1 text-zinc-300 dark:text-zinc-600">·</span>
                                <details class="relative inline-block text-left">
                                    <summary class="cursor-pointer list-none text-xs font-bold text-brand-700 hover:underline dark:text-brand-300">Credit</summary>
                                    <form method="POST" action="{{ route('admin.users.tokens', $user) }}" class="card absolute right-0 bottom-full z-40 mb-2 w-60 space-y-2 p-3 text-left shadow-2xl">
                                        @csrf
                                        @method('PATCH')
                                        <p class="text-xs font-bold whitespace-normal">Credit tokens to {{ $user->name }}</p>
                                        <input type="number" name="amount" min="1" max="10000000" placeholder="Tokens" class="field" required>
                                        <input type="text" name="reason" maxlength="120" placeholder="Reason (optional)" class="field">
                                        <button type="submit" class="btn-brand btn-sm w-full">Add tokens</button>
                                    </form>
                                </details>
                                @endunless
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7"><x-empty-state icon="users" :title="$show === 'trash' ? 'Trash is empty' : 'No users found'" :message="$show === 'trash' ? 'Deleted accounts land here and can be restored.' : 'Try a different name or email search.'" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $users->links() }}</div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function(){
  const el = document.getElementById('admin-signups-chart');
  if(!el || typeof Chart === 'undefined') return;
  new Chart(el, { type: 'bar',
    data: { labels: @json($signupChart['labels']),
      datasets: [{ label: 'New users', data: @json($signupChart['values']),
        backgroundColor: '#9e005d', borderRadius: 6 }] },
    options: { responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } } });
})();
</script>
</x-layouts::app>
