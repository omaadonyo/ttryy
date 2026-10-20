<x-layouts::app :title="__('Admin overview')">
    @include('partials.toast')
    <div class="flex w-full flex-col gap-5">
        <x-page-head title="Administration" sub="Orders, payments, users and package performance.">
            <x-slot:actions>
                <button onclick="startTour_admin()" class="btn-ghost btn-sm">Take tour</button>
            </x-slot:actions>
        </x-page-head>

        @include('partials.tour', ['key' => 'admin', 'steps' => [
            ['target' => '#tour-aostats', 'title' => 'Business health', 'body' => 'Orders by status, verified collections, outstanding totals and registered users.'],
            ['target' => '#tour-aorev', 'title' => 'Money in motion', 'body' => 'Collected revenue against order volume per month — spot slow months fast.'],
            ['target' => '#tour-aotable', 'title' => 'Latest orders', 'body' => 'Newest orders with customers and statuses. Full management lives under Orders.'],
        ]])

        <div id="tour-aostats" class="grid auto-rows-min gap-4 md:grid-cols-4">
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
            <a href="{{ route('admin.hosting.dashboard') }}" class="btn-ghost btn-sm">Hosting control</a>
            <a href="{{ route('admin.payments') }}" class="btn-ghost btn-sm">Payments</a>
            <a href="{{ route('admin.users') }}" class="btn-ghost btn-sm">Users</a>
            <a href="{{ route('admin.packages') }}" class="btn-ghost btn-sm">Packages</a>
        </div>

        <div id="tour-aorev" class="card card-pad">
            <h2 class="section-title">Revenue & order volume</h2>
            <p class="page-sub">Collected UGX and orders placed per month, last 6 months.</p>
            <div class="mt-3 h-60"><canvas id="admin-rev-chart"></canvas></div>
        </div>

        <div id="tour-aousage" class="hidden card card-pad">
            <div class="flex items-center justify-between gap-3 flex-wrap">
                <div>
                    <h2 class="section-title">Platform usage</h2>
                    <p class="page-sub">Moved to the Hosting control screen.</p>
                </div>
                <a href="{{ route('admin.hosting.dashboard') }}" class="btn-ghost btn-sm">Open Hosting control →</a>
            </div>
        </div>

        <div id="tour-aotable" class="card card-pad">
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

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function(){
  const el = document.getElementById('admin-rev-chart');
  if(!el || typeof Chart === 'undefined') return;
  new Chart(el, {
    data: { labels: @json($revenueChart['labels']),
      datasets: [
        { type: 'bar', label: 'UGX collected', data: @json($revenueChart['revenue']),
          backgroundColor: '#9e005d', borderRadius: 6, yAxisID: 'y' },
        { type: 'line', label: 'Orders', data: @json($revenueChart['orders']),
          borderColor: '#18181b', tension: .35, pointRadius: 3, yAxisID: 'y1' },
      ] },
    options: { responsive: true, maintainAspectRatio: false,
      plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 16 } } },
      scales: {
        y: { beginAtZero: true, ticks: { precision: 0 } },
        y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, ticks: { precision: 0 } },
      } } });
})();
</script>
</x-layouts::app>
