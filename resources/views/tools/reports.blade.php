<x-layouts::app :title="__('Business Tools — Reports')">
    @include('partials.toast')
    <div class="flex w-full flex-col gap-5">
        <x-page-head title="Business Tools" sub="Quotations, invoicing, receipts, POS, inventory and books — one workspace.">
            <x-slot:actions>
                <a href="{{ route('tools.pos') }}" class="btn-brand btn-sm">New sale (POS)</a>
                <a href="{{ route('tools.doc.create', ['type' => 'invoices']) }}" class="btn-ghost btn-sm">New invoice</a>
            </x-slot:actions>
        </x-page-head>

        @include('tools._tabs', ['active' => $active])

        @if(session('status'))
        <div class="card card-pad border-emerald-500/40 bg-emerald-500/5 text-sm font-semibold text-emerald-700 dark:text-emerald-300">{{ session('status') }}</div>
        @endif

        <div class="grid auto-rows-min gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-stat label="Collected this month" :value="'UGX '.number_format($stats['collectedMonth'])" hint="Receipts" />
            <x-stat label="Owed by customers" :value="'UGX '.number_format($stats['receivable'])" hint="Open invoices" />
            <x-stat label="Spent this month" :value="'UGX '.number_format($stats['expensesMonth'])" hint="Paid expenses" />
            <x-stat label="Cash on hand" :value="'UGX '.number_format($stats['cash'])" hint="Receipts minus paid expenses" />
        </div>

        @if($stats['overdue'] > 0 || $stats['lowStock'] > 0 || $stats['pendingQuotes'] > 0)
        <div class="card card-pad">
            <h2 class="section-title">Needs attention</h2>
            <div class="mt-2 flex flex-wrap gap-2 text-sm">
                @if($stats['overdue'] > 0)<a href="{{ route('tools.invoices') }}" class="pill-red">{{ $stats['overdue'] }} overdue invoice(s) →</a>@endif
                @if($stats['lowStock'] > 0)<a href="{{ route('tools.inventory') }}" class="pill-amber">{{ $stats['lowStock'] }} item(s) low on stock →</a>@endif
                @if($stats['pendingQuotes'] > 0)<a href="{{ route('tools.quotations') }}" class="pill-zinc">{{ $stats['pendingQuotes'] }} open quotation(s) →</a>@endif
            </div>
        </div>
        @endif

        <div class="card card-pad">
            <h2 class="section-title">Money in vs money out — last 6 months</h2>
            <div class="mt-3 h-64"><canvas id="bt-chart"></canvas></div>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <div class="card card-pad">
                <h2 class="section-title">Latest receipts</h2>
                <div class="mt-3 space-y-2 text-sm">
                    @forelse($recentReceipts as $r)
                    <a href="{{ route('tools.doc.show', $r) }}" class="flex items-center justify-between gap-2 rounded-lg px-2 py-2 hover:bg-zinc-950/5 dark:hover:bg-white/5">
                        <span class="min-w-0"><strong>{{ $r->number }}</strong> <span class="text-zinc-500">{{ $r->contact?->name ?? 'Walk-in' }}</span></span>
                        <span class="font-extrabold">UGX {{ number_format($r->total) }}</span>
                    </a>
                    @empty
                    <p class="text-sm text-zinc-500">No receipts yet — record your first sale in POS.</p>
                    @endforelse
                </div>
            </div>
            <div class="card card-pad">
                <h2 class="section-title">Latest expenses</h2>
                <div class="mt-3 space-y-2 text-sm">
                    @forelse($recentExpenses as $e)
                    <div class="flex items-center justify-between gap-2 rounded-lg px-2 py-2">
                        <span class="min-w-0"><strong>{{ $e->category }}</strong> <span class="text-zinc-500">{{ $e->note }}</span></span>
                        <span class="font-extrabold">UGX {{ number_format($e->amount) }}</span>
                    </div>
                    @empty
                    <p class="text-sm text-zinc-500">No expenses recorded yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="card card-pad">
            <h2 class="section-title">Business profile — prints on every PDF</h2>
            <form method="POST" action="{{ route('tools.settings.update') }}" class="mt-3 grid gap-4 sm:grid-cols-2">
                @csrf @method('PATCH')
                <div><label class="lbl">Business name *</label><input name="business_name" required value="{{ old('business_name', $settings->business_name) }}" class="field" placeholder="Savanna Build Ltd"></div>
                <div><label class="lbl">Phone</label><input name="phone" value="{{ old('phone', $settings->phone) }}" class="field" placeholder="+256 700 000000"></div>
                <div><label class="lbl">Email</label><input name="email" type="email" value="{{ old('email', $settings->email) }}" class="field" placeholder="you@business.com"></div>
                <div><label class="lbl">Address</label><input name="address" value="{{ old('address', $settings->address) }}" class="field" placeholder="Kampala, Uganda"></div>
                <div><label class="lbl">Default tax %</label><input name="tax_rate" type="number" step="0.01" min="0" max="100" value="{{ old('tax_rate', $settings->tax_rate) }}" class="field"></div>
                <div><label class="lbl">Receipt footer message</label><input name="receipt_footer" value="{{ old('receipt_footer', $settings->receipt_footer) }}" class="field" placeholder="Thank you for your business!"></div>
                <div class="sm:col-span-2"><button class="btn-brand">Save profile</button></div>
            </form>
        </div>
    </div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function(){
    const el = document.getElementById('bt-chart');
    if(!el || typeof Chart === 'undefined') return;
    const dark = document.documentElement.classList.contains('dark');
    new Chart(el, { type: 'bar',
        data: { labels: @json(array_column($months, 'label')),
            datasets: [
                { label: 'Money in', data: @json(array_column($months, 'in')), backgroundColor: '#9e005d', borderRadius: 6 },
                { label: 'Money out', data: @json(array_column($months, 'out')), backgroundColor: dark ? '#52525b' : '#d4d4d8', borderRadius: 6 },
            ] },
        options: { responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 16 } } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } } });
})();
</script>
</x-layouts::app>
