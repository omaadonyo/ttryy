<x-layouts::app :title="__('Manage orders')">
    <div class="flex h-full w-full flex-1 flex-col gap-4">
        <div>
            <flux:heading size="xl">Orders</flux:heading>
            <flux:text class="mt-1">Confirm manual Mobile Money payments and follow up on pending orders.</flux:text>
        </div>

        @if(session('status'))
        <div class="rounded-xl bg-emerald-500/10 border border-emerald-500/30 px-5 py-3 text-sm text-emerald-700 dark:text-emerald-400">{{ session('status') }}</div>
        @endif

        <form method="GET" action="{{ route('admin.orders') }}" class="rounded-xl bg-white dark:bg-white/[.04] p-4 shadow-[0_12px_32px_-12px_rgba(10,10,12,.18)] dark:shadow-[0_12px_32px_-12px_rgba(0,0,0,.7)] grid sm:grid-cols-[1fr_160px_160px_auto] gap-3">
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search reference, business, phone…" class="rounded-xl border border-neutral-300 dark:border-white/15 bg-white dark:bg-white/5 px-4 py-2.5 text-sm outline-none focus:border-[#9e005d]">
            <select name="status" class="rounded-xl border border-neutral-300 dark:border-white/15 bg-white dark:bg-white/5 px-4 py-2.5 text-sm outline-none focus:border-[#9e005d]">
                <option value="">All statuses</option>
                @foreach(['pending','submitted','paid'] as $s)
                <option value="{{ $s }}" {{ ($filters['status'] ?? '') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <select name="package" class="rounded-xl border border-neutral-300 dark:border-white/15 bg-white dark:bg-white/5 px-4 py-2.5 text-sm outline-none focus:border-[#9e005d]">
                <option value="">All packages</option>
                @foreach($packages as $p)
                <option value="{{ $p }}" {{ ($filters['package'] ?? '') === $p ? 'selected' : '' }}>{{ $p }}</option>
                @endforeach
            </select>
            <flux:button type="submit" variant="primary" class="!bg-[#9e005d] hover:!bg-[#7e0049]">Filter</flux:button>
        </form>

        <div class="rounded-xl bg-white dark:bg-white/[.04] p-5 shadow-[0_12px_32px_-12px_rgba(10,10,12,.18)] dark:shadow-[0_12px_32px_-12px_rgba(0,0,0,.7)]">
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[760px]">
                    <thead>
                        <tr class="text-left text-zinc-500">
                            <th class="py-2 pr-4 font-medium">Reference</th>
                            <th class="py-2 pr-4 font-medium">Customer</th>
                            <th class="py-2 pr-4 font-medium">Package / plan</th>
                            <th class="py-2 pr-4 font-medium">Due today</th>
                            <th class="py-2 pr-4 font-medium">Total</th>
                            <th class="py-2 pr-4 font-medium">Status</th>
                            <th class="py-2 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-white/10">
                        @forelse($orders as $order)
                        <tr>
                            <td class="py-2.5 pr-4 font-mono text-xs">{{ $order->reference }}</td>
                            <td class="py-2.5 pr-4"><p class="font-semibold">{{ $order->business_name }}</p><p class="text-xs text-zinc-500">{{ $order->user->name }} · {{ $order->phone }}</p></td>
                            <td class="py-2.5 pr-4">{{ $order->package }} · {{ ucfirst($order->billing_frequency) }}</td>
                            <td class="py-2.5 pr-4 font-semibold">UGX {{ number_format($order->due_today) }}</td>
                            <td class="py-2.5 pr-4 font-semibold">UGX {{ number_format($order->total_amount) }}</td>
                            <td class="py-2.5 pr-4">
                                @if($order->status === 'paid')
                                <flux:badge size="sm" color="emerald">{{ $order->status }}</flux:badge>
                                @elseif($order->status === 'submitted')
                                <flux:badge size="sm" color="sky">{{ $order->status }}</flux:badge>
                                @else
                                <flux:badge size="sm" color="zinc">{{ $order->status }}</flux:badge>
                                @endif
                            </td>
                            <td class="py-2.5 text-right whitespace-nowrap">
                                @if($order->status !== 'paid')
                                <form method="POST" action="{{ route('admin.orders.paid', $order) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <flux:button type="submit" size="sm" variant="primary" class="!bg-[#9e005d] hover:!bg-[#7e0049]">Mark paid</flux:button>
                                </form>
                                @else
                                <span class="text-xs text-zinc-400">UGX {{ number_format($order->paid_amount) }} ✓</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="py-6 text-center text-zinc-500">No orders match these filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $orders->links() }}</div>
        </div>
    </div>
</x-layouts::app>
