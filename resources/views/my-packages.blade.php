<x-layouts::app :title="__('My packages')">
    @include('partials.toast')
    <div class="flex w-full flex-col gap-5">
        <x-page-head :title="'My packages'" :sub="'Orders for '.auth()->user()->name.'. Your website stays running while your plan is active.'">
            <x-slot:actions>
                <div class="cs-wrap relative" id="status-filter">
                    <select id="statusSel" class="sr-only" aria-label="Filter by status">
                        <option value="">All statuses</option>
                        <option value="pending">Pending</option>
                        <option value="submitted">Submitted</option>
                        <option value="paid">Paid</option>
                    </select>
                </div>
                <a href="{{ route('checkout') }}" class="btn-brand">New order</a>
                <button onclick="startTour_packages()" class="btn-ghost btn-sm">Take tour</button>
            </x-slot:actions>
        </x-page-head>

        @include('partials.tour', ['key' => 'packages', 'steps' => [
            ['target' => '#tour-pstats', 'title' => 'Portfolio at a glance', 'body' => 'Active websites, verified payments and what is still to pay across all orders.'],
            ['target' => '#order-grid', 'title' => 'Your orders', 'body' => 'Filter by status. Each card shows plan, payments progress, subscription time left and the invoice PDF.'],
        ]])

        @unless($orders->isEmpty())
        <div id="tour-pstats" class="grid auto-rows-min gap-4 sm:grid-cols-3">
            <x-stat label="Active websites" :value="$activeCount" hint="Subscriptions running now" />
            <x-stat label="Paid so far" :value="'UGX '.number_format($paidSum)" hint="Verified payments" />
            <x-stat label="Still to pay" :value="'UGX '.number_format($remainingSum)" hint="Across all orders" />
        </div>
        @endunless

        @if($orders->isEmpty())
        <div class="card card-pad">
            <x-empty-state icon="orders" title="No orders yet" message="Choose a package to get your website and prospect list started." :actionUrl="route('checkout')" actionLabel="Choose a package" />
        </div>
        @else
        <div class="grid gap-4 sm:grid-cols-2" id="order-grid">
            @foreach($orders as $order)
            <div class="order-card card card-pad" data-status="{{ $order->status }}">
                <div class="flex items-center justify-between gap-2">
                    <p class="font-mono text-xs text-zinc-400">{{ $order->reference }}</p>
                    @if($order->status === 'paid')
                    <span class="pill-green">{{ $order->status }}</span>
                    @elseif($order->status === 'submitted')
                    <span class="pill-amber">{{ $order->status }}</span>
                    @else
                    <span class="pill-zinc">{{ $order->status }}</span>
                    @endif
                </div>
                <p class="font-display mt-2 text-2xl font-extrabold tracking-tight">{{ $order->package }}</p>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ ucfirst($order->billing_frequency) }} · {{ $order->duration_months }} months · {{ $order->periods }} payments</p>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Domain: {{ $order->domain }} (UGX {{ number_format($order->domain_fee) }})</p>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $order->business_name }}@if($order->niche) · {{ $order->niche }}@endif</p>
                <p class="font-display mt-3 text-xl font-extrabold">UGX {{ number_format($order->total_amount) }}</p>
                <p class="mt-1 text-xs text-zinc-500">Due today was UGX {{ number_format($order->due_today) }} · Ordered {{ $order->created_at->format('d M Y') }}</p>
                <div class="mt-3 rounded-xl border border-zinc-200/80 bg-zinc-50 p-3 dark:border-white/10 dark:bg-white/5">
                    <div class="flex justify-between text-xs"><span class="text-zinc-500">Paid UGX {{ number_format($order->paid_amount) }}</span><span class="font-bold">UGX {{ number_format($order->amountRemaining()) }} left</span></div>
                    <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-zinc-200 dark:bg-white/10">
                        <div class="h-1.5 rounded-full bg-emerald-600" style="width:{{ $order->paymentProgressPercent() }}%"></div>
                    </div>
                    <p class="mt-1 text-[11px] text-zinc-500">{{ $order->paymentProgressPercent() }}% of the service paid for</p>
                </div>
                @if($order->credentials_handed_over)
                <p class="mt-3 text-xs font-semibold text-emerald-700 dark:text-emerald-400">cPanel + credentials handed over{{ $order->handed_over_at ? ' · '.$order->handed_over_at->format('d M Y') : '' }} — the website is fully yours.</p>
                @elseif($order->isActive())
                <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-zinc-100 dark:bg-white/10">
                    <div class="h-1.5 rounded-full bg-brand-600" style="width:{{ $order->progressPercent() }}%"></div>
                </div>
                <p class="mt-1.5 text-xs text-zinc-500">{{ $order->daysLeft() }} days left · expires {{ $order->expiresAt()->format('d M Y') }}</p>
                @elseif($order->isExpired())
                <p class="mt-3 text-xs font-semibold text-red-600 dark:text-red-400">Expired {{ $order->expiresAt()->format('d M Y') }} — renew to keep your website running.</p>
                @else
                <p class="mt-3 text-xs text-zinc-500">Awaiting payment — subscription starts once paid.</p>
                @endif
                <a href="{{ route('orders.invoice', $order) }}" class="mt-3 inline-block text-sm font-bold text-brand-700 hover:underline dark:text-brand-300">Download invoice PDF →</a>
            </div>
            @endforeach
        </div>
        <p id="no-match" class="hidden py-6 text-center text-sm text-zinc-500">No orders with this status.</p>
        @endif
    </div>

<script>
(function(){
  const sel = document.getElementById('statusSel');
  if(!sel) return;
  const wrap = sel.parentNode;
  sel.classList.add('sr-only'); sel.tabIndex = -1;
  const btn = document.createElement('button');
  btn.type = 'button';
  btn.className = 'flex items-center justify-between gap-2 rounded-xl border border-zinc-300 dark:border-white/15 bg-white dark:bg-white/5 px-4 py-2 text-sm font-semibold outline-none min-w-[150px]';
  btn.setAttribute('aria-expanded', 'false');
  btn.innerHTML = '<span class="cs-label truncate text-zinc-500">All statuses</span><svg class="w-4 h-4 shrink-0 text-zinc-400" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>';
  const list = document.createElement('div');
  list.className = 'cs-list hidden absolute z-30 left-0 right-0 mt-2 rounded-xl border border-neutral-200 dark:border-white/15 bg-white dark:bg-[#141416] shadow-2xl p-1.5';
  list.setAttribute('role', 'listbox');
  [...sel.options].forEach(o => {
    const item = document.createElement('button');
    item.type = 'button'; item.dataset.value = o.value; item.setAttribute('role', 'option');
    item.className = 'w-full flex items-center justify-between gap-2 text-left px-4 py-2 rounded-lg text-sm transition hover:bg-zinc-100 dark:hover:bg-white/10 text-zinc-800 dark:text-zinc-200';
    item.innerHTML = '<span class="truncate">' + o.textContent + '</span>';
    item.addEventListener('click', () => {
      sel.value = o.value; syncLabel(); closeList(); applyFilter();
    });
    list.appendChild(item);
  });
  function syncLabel(){
    const opt = sel.options[sel.selectedIndex];
    const label = btn.querySelector('.cs-label');
    label.textContent = opt ? opt.textContent : 'All statuses';
    label.classList.toggle('text-zinc-500', !opt || opt.value === '');
  }
  function closeList(){ list.classList.add('hidden'); btn.setAttribute('aria-expanded', 'false'); }
  function applyFilter(){
    let visible = 0;
    document.querySelectorAll('.order-card').forEach(c => {
      const show = !sel.value || c.dataset.status === sel.value;
      c.classList.toggle('hidden', !show);
      if(show) visible++;
    });
    document.getElementById('no-match').classList.toggle('hidden', visible > 0);
  }
  btn.addEventListener('click', () => {
    const open = list.classList.contains('hidden');
    document.querySelectorAll('.cs-list').forEach(l => l.classList.add('hidden'));
    if(open){ list.classList.remove('hidden'); btn.setAttribute('aria-expanded', 'true'); }
  });
  document.addEventListener('click', e => { if(!wrap.contains(e.target)) closeList(); });
  document.addEventListener('keydown', e => { if(e.key === 'Escape') closeList(); });
  wrap.appendChild(btn); wrap.appendChild(list);
  syncLabel();
})();
</script>
</x-layouts::app>

