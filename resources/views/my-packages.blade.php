<x-layouts::app :title="__('My packages')">
    <div class="flex h-full w-full flex-1 flex-col gap-4">
        <div class="flex items-end justify-between gap-3 flex-wrap">
            <div>
                <flux:heading size="xl">My packages</flux:heading>
                <flux:text class="mt-1">Orders for <strong>{{ auth()->user()->name }}</strong>. Your website stays running while your plan is active.</flux:text>
            </div>
            <div class="flex items-center gap-2">
                <div class="cs-wrap relative" id="status-filter">
                    <select id="statusSel" class="sr-only" aria-label="Filter by status">
                        <option value="">All statuses</option>
                        <option value="pending">Pending</option>
                        <option value="submitted">Submitted</option>
                        <option value="paid">Paid</option>
                    </select>
                </div>
                <flux:button :href="route('orders.new')" variant="primary" class="!bg-[#9e005d] hover:!bg-[#7e0049]" >New order</flux:button>
            </div>
        </div>

        @if($orders->isEmpty())
        <div class="rounded-xl bg-white dark:bg-white/[.04] p-6 shadow-[0_12px_32px_-12px_rgba(10,10,12,.18)] dark:shadow-[0_12px_32px_-12px_rgba(0,0,0,.7)]">
            <x-empty-state icon="orders" title="No orders yet" message="Choose a package to get your website and prospect list started." :actionUrl="route('orders.new')" actionLabel="Choose a package" />
        </div>
        @else
        <div class="grid sm:grid-cols-2 gap-4" id="order-grid">
            @foreach($orders as $order)
            <div class="order-card rounded-xl bg-white dark:bg-white/[.04] p-6 shadow-[0_12px_32px_-12px_rgba(10,10,12,.18)] dark:shadow-[0_12px_32px_-12px_rgba(0,0,0,.7)]" data-status="{{ $order->status }}">
                <div class="flex items-center justify-between gap-2">
                    <p class="font-mono text-xs text-zinc-500">{{ $order->reference }}</p>
                    @if($order->status === 'paid')
                    <flux:badge size="sm" color="emerald">{{ $order->status }}</flux:badge>
                    @elseif($order->status === 'submitted')
                    <flux:badge size="sm" color="sky">{{ $order->status }}</flux:badge>
                    @else
                    <flux:badge size="sm" color="zinc">{{ $order->status }}</flux:badge>
                    @endif
                </div>
                <p class="text-2xl font-extrabold mt-2">{{ $order->package }}</p>
                <p class="text-sm text-zinc-500 mt-1">{{ ucfirst($order->billing_frequency) }} · {{ $order->duration_months }} months · {{ $order->periods }} payments</p>
                <p class="text-sm text-zinc-500">Domain: {{ $order->domain }} (UGX {{ number_format($order->domain_fee) }})</p>
                <p class="text-sm text-zinc-500">{{ $order->business_name }}@if($order->niche) · {{ $order->niche }}@endif</p>
                <p class="text-xl font-extrabold mt-3">UGX {{ number_format($order->total_amount) }}</p>
                <p class="text-xs text-zinc-500 mt-1">Due today was UGX {{ number_format($order->due_today) }} · Ordered {{ $order->created_at->format('d M Y') }}</p>
                <div class="mt-3 rounded-lg bg-zinc-50 dark:bg-white/5 p-3">
                    <div class="flex justify-between text-xs"><span class="text-zinc-500">Paid UGX {{ number_format($order->paid_amount) }}</span><span class="font-bold">UGX {{ number_format($order->amountRemaining()) }} left</span></div>
                    <div class="mt-1.5 h-1.5 rounded-full bg-zinc-200 dark:bg-white/10 overflow-hidden">
                        <div class="h-1.5 rounded-full bg-emerald-600" style="width:{{ $order->paymentProgressPercent() }}%"></div>
                    </div>
                    <p class="mt-1 text-[11px] text-zinc-500">{{ $order->paymentProgressPercent() }}% of the service paid for</p>
                </div>
                @if($order->credentials_handed_over)
                <p class="mt-3 text-xs font-semibold text-emerald-700 dark:text-emerald-400">cPanel + credentials handed over{{ $order->handed_over_at ? ' · '.$order->handed_over_at->format('d M Y') : '' }} — the website is fully yours.</p>
                @elseif($order->isActive())
                <div class="mt-3 h-1.5 rounded-full bg-zinc-100 dark:bg-white/10 overflow-hidden">
                    <div class="h-1.5 rounded-full bg-[#9e005d]" style="width:{{ $order->progressPercent() }}%"></div>
                </div>
                <p class="mt-1.5 text-xs text-zinc-500">{{ $order->daysLeft() }} days left · expires {{ $order->expiresAt()->format('d M Y') }}</p>
                @elseif($order->isExpired())
                <p class="mt-3 text-xs font-semibold text-red-600 dark:text-red-400">Expired {{ $order->expiresAt()->format('d M Y') }} — renew to keep your website running.</p>
                @else
                <p class="mt-3 text-xs text-zinc-500">Awaiting payment — subscription starts once paid.</p>
                @endif
                <a href="{{ route('orders.invoice', $order) }}" class="inline-block mt-3 text-sm font-bold text-[#9e005d] hover:underline">Download invoice PDF →</a>
            </div>
            @endforeach
        </div>
        <p id="no-match" class="hidden text-sm text-zinc-500 py-6 text-center">No orders with this status.</p>
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
  btn.className = 'flex items-center justify-between gap-2 rounded-[0.575rem] border border-neutral-300 dark:border-white/15 bg-white dark:bg-white/5 px-4 py-2 text-sm font-semibold outline-none min-w-[150px]';
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

