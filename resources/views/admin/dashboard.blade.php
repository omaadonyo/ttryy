<x-layouts::app :title="__('Admin overview')">
    @include('partials.toast')
    <div class="flex w-full flex-col gap-5">
        <x-page-head title="Administration" sub="Orders, payments, users and package performance." />

        <div class="grid auto-rows-min gap-4 md:grid-cols-4">
            <x-stat label="Total orders" :value="$totalOrders" :hint="$pendingOrders.' pending · '.$submittedOrders.' submitted · '.$paidOrders.' paid'" />
            <div class="card card-pad">
                <p class="eyebrow">Collected</p>
                <p class="stat-num mt-2 text-brand-700 dark:text-brand-300">UGX {{ number_format($collected) }}</p>
                <p class="mt-1.5 text-[13px] text-zinc-500 dark:text-zinc-400">Verified payments</p>
            </div>
            <x-stat label="Outstanding" :value="'UGX '.number_format($outstanding)" hint="Unpaid order totals" />
            <div class="card card-pad border-transparent! bg-zinc-950 text-white dark:border-transparent! dark:bg-brand-600">
                <p class="text-[11px] font-bold tracking-[0.14em] text-white/60 uppercase">Registered users</p>
                <p class="stat-num mt-2 text-white!">{{ $totalUsers }}</p>
                <p class="mt-1.5 text-[13px] text-white/60">Across the platform</p>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.orders') }}" class="btn-brand btn-sm">Manage orders</a>
            <a href="{{ route('admin.payments') }}" class="btn-ghost btn-sm">Payments</a>
            <a href="{{ route('admin.users') }}" class="btn-ghost btn-sm">Users</a>
            <a href="{{ route('admin.packages') }}" class="btn-ghost btn-sm">Packages</a>
        </div>

        <div class="card card-pad">
            <h2 class="section-title">Latest orders</h2>
            <div class="tbl-wrap mt-3 rounded-none! border-0! shadow-none!">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Customer</th>
                            <th>Package</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOrders as $order)
                        <tr>
                            <td class="font-mono text-xs">{{ $order->reference }}</td>
                            <td>{{ $order->user->name }}</td>
                            <td class="font-semibold">{{ $order->package }}</td>
                            <td class="font-semibold">UGX {{ number_format($order->total_amount) }}</td>
                            <td>
                                @if($order->status === 'paid')
                                <span class="pill-green">{{ $order->status }}</span>
                                @elseif($order->status === 'submitted')
                                <span class="pill-amber">{{ $order->status }}</span>
                                @else
                                <span class="pill-zinc">{{ $order->status }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="py-6 text-center text-zinc-500">No orders yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts::app>
