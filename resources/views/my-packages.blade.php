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
                <a href="{{ route('wallet.index') }}" class="btn-ghost">Wallet · UGX {{ number_format((int) auth()->user()->balance_ugx) }}</a>
                <a href="{{ route('checkout') }}" class="btn-brand">New order</a>
                <button onclick="startTour_packages()" class="btn-ghost btn-sm">Take tour</button>
            </x-slot:actions>
        </x-page-head>

        @include('partials.tour', ['key' => 'packages', 'steps' => [
            ['target' => '#tour-pstats', 'title' => 'Portfolio at a glance', 'body' => 'Active websites, verified payments, what is still to pay and your wallet balance.'],
            ['target' => '#order-grid', 'title' => 'Your orders', 'body' => 'Each card shows the plan, next due date, payment progress and lets you top up or pay the order instantly.'],
        ]])

        @unless($orders->isEmpty())
        <div id="tour-pstats" class="grid auto-rows-min gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-stat label="Active websites" :value="$activeCount" hint="Subscriptions running now" />
            <x-stat label="Paid so far" :value="'UGX '.number_format($paidSum)" hint="Verified payments" />
            <x-stat label="Still to pay" :value="'UGX '.number_format($remainingSum)" hint="Across all orders" />
            <x-stat label="Wallet balance" :value="'UGX '.number_format($walletBalance)" hint="Usable to pay any order" />
        </div>
        @endunless

        @if($orders->isEmpty())
        <div class="card card-pad">
            <x-empty-state icon="orders" title="No orders yet" message="Choose a package to get your website and prospect list started." :actionUrl="route('checkout')" actionLabel="Choose a package" />
        </div>
        @else
        <div class="grid gap-4 lg:grid-cols-2" id="order-grid">
            @foreach($orders as $order)
            @php
                $remaining = $order->amountRemaining();
                $due = $order->nextDueAt();
                $overdue = $due && $due->isPast() && $remaining > 0;
            @endphp
            <div class="order-card card card-pad flex flex-col" data-status="{{ $order->status }}">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-mono text-xs text-zinc-400">{{ $order->reference }}</p>
                        <p class="font-display mt-1 text-2xl font-extrabold tracking-tight text-zinc-950 dark:text-white">{{ $order->package }}</p>
                        <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">{{ $order->business_name }}@if($order->niche) · {{ $order->niche }}@endif</p>
                    </div>
                    @if($order->isRefunded())
                    <span class="pill-zinc">refunded</span>
                    @elseif($order->status === 'paid')
                    <span class="pill-green">paid</span>
                    @elseif($order->status === 'submitted')
                    <span class="pill-amber">submitted</span>
                    @else
                    <span class="pill-zinc">pending</span>
                    @endif
                </div>

                {{-- Key facts --}}
                <dl class="mt-4 grid grid-cols-3 gap-3 rounded-xl border border-zinc-200/80 bg-zinc-50 p-3 dark:border-white/10 dark:bg-white/5">
                    <div>
                        <dt class="text-[11px] font-semibold tracking-wide text-zinc-500 uppercase">Total</dt>
                        <dd class="mt-0.5 text-sm font-extrabold text-zinc-950 dark:text-white">UGX {{ number_format($order->total_amount) }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-semibold tracking-wide text-zinc-500 uppercase">Rate</dt>
                        <dd class="mt-0.5 text-sm font-extrabold text-zinc-950 dark:text-white">{{ $order->installmentLabel() }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-semibold tracking-wide text-zinc-500 uppercase">Term</dt>
                        <dd class="mt-0.5 text-sm font-extrabold text-zinc-950 dark:text-white">{{ $order->duration_months }} months</dd>
                    </div>
                </dl>

                {{-- Next due --}}
                <div class="mt-3 flex items-center justify-between gap-3 rounded-xl px-3 py-2.5 {{ $overdue ? 'bg-red-50 dark:bg-red-500/10' : 'bg-brand-50 dark:bg-brand-500/10' }}">
                    <div>
                        <p class="text-[11px] font-bold tracking-wide uppercase {{ $overdue ? 'text-red-700 dark:text-red-300' : 'text-brand-700 dark:text-brand-300' }}">
                            {{ $order->billing_frequency === 'full' ? 'Billing' : ($overdue ? 'Next due · overdue' : 'Next due') }}
                        </p>
                        <p class="text-sm font-bold text-zinc-950 dark:text-white">
                            @if($order->billing_frequency === 'full')
                                Paid in one payment
                            @elseif($remaining <= 0)
                                Fully paid — nothing due
                            @elseif($due)
                                {{ $due->format('d M Y') }} · {{ max(0, (int) now()->diffInDays($due, false)) }} day(s)
                            @else
                                Awaiting first payment
                            @endif
                        </p>
                    </div>
                    <p class="text-right">
                        <span class="block text-[11px] font-semibold tracking-wide text-zinc-500 uppercase">Left to pay</span>
                        <span class="font-display text-lg font-extrabold text-zinc-950 dark:text-white">UGX {{ number_format($remaining) }}</span>
                    </p>
                </div>

                {{-- Progress --}}
                <div class="mt-3">
                    <div class="flex justify-between text-xs text-zinc-500">
                        <span>Paid UGX {{ number_format($order->paid_amount) }}</span>
                        <span class="font-bold text-zinc-700 dark:text-zinc-300">{{ $order->paymentProgressPercent() }}%</span>
                    </div>
                    <x-progress class="mt-1.5" :value="$order->paid_amount" :max="$order->total_amount" tone="emerald" size="sm" />
                </div>

                {{-- Subscription time / status --}}
                @if($order->credentials_handed_over)
                <p class="mt-3 text-xs font-semibold text-emerald-700 dark:text-emerald-400">cPanel + credentials handed over{{ $order->handed_over_at ? ' · '.$order->handed_over_at->format('d M Y') : '' }} — the website is fully yours.</p>
                @elseif($order->isActive())
                <x-progress class="mt-3" :percent="$order->progressPercent()" :caption="$order->daysLeft().' days left · expires '.$order->expiresAt()->format('d M Y')" />
                @elseif($order->isExpired())
                <p class="mt-3 text-xs font-semibold text-red-600 dark:text-red-400">Expired {{ $order->expiresAt()->format('d M Y') }} — renew to keep your website running.</p>
                @else
                <p class="mt-3 text-xs text-zinc-500">Awaiting payment — subscription starts once paid.</p>
                @endif

                {{-- Actions --}}
                <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-zinc-100 pt-4 dark:border-white/10">
                    @if($remaining > 0)
                    <button type="button" class="btn-brand btn-sm pay-open" data-order="{{ $order->reference }}" data-remaining="{{ $remaining }}" data-rate="{{ $order->amount_per_period }}">Pay this order</button>
                    <a href="{{ route('wallet.index') }}" class="btn-ghost btn-sm">Top up wallet</a>
                    @endif
                    <a href="{{ route('orders.invoice', $order) }}" class="btn-ghost btn-sm">Invoice PDF</a>
                    @if($order->hosted_website_id)
                    <a href="{{ route('hosting.show', $order->hosted_website_id) }}" class="btn-ghost btn-sm">Website</a>
                    @endif
                </div>

                {{-- Inline pay panel --}}
                @if($remaining > 0)
                <div class="pay-panel mt-3 hidden" data-order="{{ $order->reference }}">
                    <div class="rounded-xl border border-zinc-200/80 bg-white p-4 dark:border-white/10 dark:bg-[#141416]">
                        <div class="grid gap-3 sm:grid-cols-[1fr_auto] sm:items-end">
                            <div>
                                <label class="text-xs font-semibold">Amount (UGX)</label>
                                <input type="number" min="1" max="{{ $remaining }}" value="{{ min($remaining, max($order->amount_per_period, (int) config('packages.min_first_payment', 10000))) }}" class="pay-amount mt-1 w-full bg-white dark:bg-[#17171A] border border-zinc-200 dark:border-white/15 rounded-lg px-4 py-2 text-sm outline-none focus:border-brand-600 text-zinc-900 dark:text-white">
                                <p class="mt-1 text-[11px] text-zinc-500">Max UGX {{ number_format($remaining) }}.</p>
                            </div>
                            <div class="text-xs text-zinc-500">Wallet: <strong class="text-zinc-900 dark:text-white">UGX {{ number_format($walletBalance) }}</strong></div>
                        </div>
                        <div class="mt-3">
                            <label class="text-xs font-semibold">Mobile Money number (for MoMo payments)</label>
                            <input type="tel" inputmode="tel" placeholder="0772 123456" class="pay-phone mt-1 w-full bg-white dark:bg-[#17171A] border border-zinc-200 dark:border-white/15 rounded-lg px-4 py-2 text-sm outline-none focus:border-brand-600 text-zinc-900 dark:text-white">
                        </div>
                        <p class="pay-msg mt-2 hidden text-sm font-semibold"></p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <button type="button" class="btn-brand btn-sm pay-go" data-method="wallet">
                                <span data-spin class="hidden size-3.5 rounded-full border-2 border-white/70 border-t-transparent animate-spin"></span>
                                <span>Pay from wallet</span>
                            </button>
                            <button type="button" class="btn-ghost btn-sm pay-go" data-method="momo">
                                <span data-spin class="hidden size-3.5 rounded-full border-2 border-current border-t-transparent animate-spin"></span>
                                <span>Pay with MoMo</span>
                            </button>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            @endforeach
        </div>
        <p id="no-match" class="hidden py-6 text-center text-sm text-zinc-500">No orders with this status.</p>
        @endif
    </div>

<script>
(function(){
  const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
  const post = (url, body) => fetch(url, { method: 'POST',
    headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
    body: JSON.stringify(body || {}) });

  // Status filter
  const sel = document.getElementById('statusSel');
  if(sel){
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
      item.addEventListener('click', () => { sel.value = o.value; syncLabel(); closeList(); applyFilter(); });
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
  }

  // Pay panels
  document.querySelectorAll('.pay-open').forEach(b => b.addEventListener('click', () => {
    const panel = document.querySelector('.pay-panel[data-order="' + b.dataset.order + '"]');
    if(panel) panel.classList.toggle('hidden');
  }));
  document.querySelectorAll('.pay-go').forEach(btn => btn.addEventListener('click', async () => {
    const panel = btn.closest('.pay-panel');
    const ref = panel.dataset.order;
    const amountEl = panel.querySelector('.pay-amount');
    const phoneEl = panel.querySelector('.pay-phone');
    const msg = panel.querySelector('.pay-msg');
    const amount = parseInt(amountEl.value, 10);
    const method = btn.dataset.method;
    msg.classList.add('hidden');
    if(!amount || amount < 1){ msg.textContent = 'Enter a valid amount.'; msg.className = 'pay-msg mt-2 text-sm font-semibold text-red-600'; msg.classList.remove('hidden'); return; }
    if(method === 'momo' && !(phoneEl.value || '').trim()){ msg.textContent = 'Enter the Mobile Money number.'; msg.className = 'pay-msg mt-2 text-sm font-semibold text-red-600'; msg.classList.remove('hidden'); phoneEl.focus(); return; }
    btn.disabled = true;
    const spin = btn.querySelector('[data-spin]'); if(spin) spin.classList.remove('hidden');
    try{
      const res = await post('/dashboard/orders/' + ref + '/pay', { amount, method, phone: (phoneEl.value||'').trim() });
      const j = await res.json().catch(() => ({}));
      if(!res.ok) throw new Error(j.message || 'Payment failed.');
      if(j.status === 'paid'){ flashToast('Payment received — thank you!'); location.reload(); return; }
      if(j.tx_id){ msg.textContent = 'Approve the prompt on your phone…'; msg.className = 'pay-msg mt-2 text-sm font-semibold text-brand-700 dark:text-brand-300'; msg.classList.remove('hidden'); pollStatus(ref, btn); return; }
      location.reload();
    }catch(e){
      msg.textContent = e.message || 'Payment failed.'; msg.className = 'pay-msg mt-2 text-sm font-semibold text-red-600'; msg.classList.remove('hidden');
      btn.disabled = false; if(spin) spin.classList.add('hidden');
    }
  }));
  function pollStatus(ref, btn){
    let tries = 40;
    const t = setInterval(async () => {
      tries--;
      try{
        const r = await (await fetch('/dashboard/orders/' + ref + '/pay/status', { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })).json();
        if(r.status === 'paid'){ clearInterval(t); flashToast('Payment received — thank you!'); location.reload(); return; }
        if(r.status === 'failed'){ clearInterval(t); location.reload(); return; }
      }catch(e){}
      if(tries <= 0){ clearInterval(t); location.reload(); }
    }, 3000);
  }
})();
</script>
</x-layouts::app>
