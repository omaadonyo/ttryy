<x-layouts::app :title="__('Manage orders')">
    @include('partials.toast')
    <style>
      .cs-btn svg{ transition:transform .25s ease; }
      .cs-btn[aria-expanded="true"] svg{ transform:rotate(180deg); }
      .cs-wrap:focus-within .cs-btn{ border-color:#9e005d; }
    </style>
    <div class="flex w-full flex-col gap-5">
        <x-page-head title="Orders" sub="Confirm manual Mobile Money payments and follow up on pending orders." />

        <form method="GET" action="{{ route('admin.orders') }}" class="card card-pad grid gap-3 sm:grid-cols-[1fr_160px_160px_auto]">
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search reference, business, phone…" class="field">
            <select name="status" class="field">
                <option value="">All statuses</option>
                @foreach(['pending','submitted','paid'] as $s)
                <option value="{{ $s }}" {{ ($filters['status'] ?? '') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <select name="package" class="field">
                <option value="">All packages</option>
                @foreach($packages as $p)
                <option value="{{ $p }}" {{ ($filters['package'] ?? '') === $p ? 'selected' : '' }}>{{ $p }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-brand">Filter</button>
        </form>

        <div class="card card-pad">
            <div class="tbl-wrap rounded-none! border-0! shadow-none!">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Customer</th>
                            <th>Package / plan</th>
                            <th>Due today</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th class="text-right!">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr>
                            <td class="font-mono text-xs">{{ $order->reference }}</td>
                            <td><p class="font-semibold">{{ $order->business_name }}</p><p class="text-xs text-zinc-500">{{ $order->user->name }} · {{ $order->phone }}</p></td>
                            <td>{{ $order->package }} · {{ ucfirst($order->billing_frequency) }}</td>
                            <td class="font-semibold">UGX {{ number_format($order->due_today) }}</td>
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
                            <td class="text-right whitespace-nowrap">
                                @if($order->credentials_handed_over)
                                <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-400">Handed over ✓</span>
                                @else
                                @if($order->status !== 'paid')
                                <form method="POST" action="{{ route('admin.orders.paid', $order) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn-brand btn-sm">Mark paid</button>
                                </form>
                                @else
                                <span class="text-xs text-zinc-400">UGX {{ number_format($order->paid_amount) }} ✓</span>
                                @endif
                                <form method="POST" action="{{ route('admin.orders.handover', $order) }}" class="ml-2 inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn-ghost btn-sm">Hand over</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7"><x-empty-state icon="search" title="No orders found" message="Try clearing the search or choosing different filters." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $orders->links() }}</div>
        </div>
    </div>

<script>
function enhanceSelect(sel){
  if(!sel || sel.closest('.cs-wrap')) return;
  const wrap=document.createElement('div'); wrap.className='cs-wrap relative';
  sel.parentNode.insertBefore(wrap, sel); wrap.appendChild(sel);
  sel.classList.add('sr-only'); sel.tabIndex=-1; sel.setAttribute('aria-hidden','true');
  const btn=document.createElement('button');
  btn.type='button';
  btn.className='cs-btn w-full flex items-center justify-between gap-2 rounded-xl border border-zinc-300 dark:border-white/15 bg-white dark:bg-white/5 px-4 py-2.5 text-sm outline-none';
  btn.setAttribute('aria-expanded','false');
  btn.innerHTML='<span class="cs-label truncate">Select…</span><svg class="w-4 h-4 shrink-0 text-zinc-400 transition-transform" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>';
  const list=document.createElement('div');
  list.className='cs-list hidden absolute z-30 left-0 right-0 mt-2 rounded-xl border border-zinc-200 dark:border-white/15 bg-white dark:bg-[#141416] shadow-2xl p-1.5 max-h-60 overflow-y-auto';
  list.setAttribute('role','listbox');
  function sync(){
    const opt=sel.options[sel.selectedIndex];
    const label=wrap.querySelector('.cs-label');
    if(label && opt){ label.textContent=opt.textContent; label.classList.toggle('text-zinc-500', opt.value===''); }
    wrap.querySelectorAll('.cs-list button').forEach(b=>{
      const tick=b.querySelector('.cs-tick');
      if(tick) tick.classList.toggle('hidden', b.dataset.value!==sel.value);
    });
  }
  [...sel.options].forEach(o=>{
    const item=document.createElement('button');
    item.type='button'; item.dataset.value=o.value; item.setAttribute('role','option');
    [...o.attributes].forEach(a=>{ if(a.name.indexOf('data-')===0) item.dataset[a.name.slice(5)]=a.value; });
    item.className='w-full flex items-center justify-between gap-2 text-left px-4 py-2 rounded-lg text-sm transition hover:bg-zinc-100 dark:hover:bg-white/10 '+(o.value===''?'text-zinc-400':'text-zinc-800 dark:text-zinc-200');
    const esc=o.textContent.replaceAll('&','&amp;').replaceAll('<','&lt;');
    item.innerHTML='<span class="truncate">'+esc+'</span><svg class="cs-tick w-4 h-4 shrink-0 text-zinc-900 dark:text-white hidden" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>';
    item.addEventListener('click',()=>{ sel.value=o.value; sel.dispatchEvent(new Event('change',{bubbles:true})); sync(); closeAllCs(); });
    list.appendChild(item);
  });
  function closeAllCs(){ document.querySelectorAll('.cs-list').forEach(l=>l.classList.add('hidden')); document.querySelectorAll('.cs-btn').forEach(b=>b.setAttribute('aria-expanded','false')); }
  btn.addEventListener('click',()=>{ const open=list.classList.contains('hidden'); closeAllCs(); if(open){ list.classList.remove('hidden'); btn.setAttribute('aria-expanded','true'); } });
  wrap.appendChild(btn); wrap.appendChild(list);
  sync();
}
document.addEventListener('click',e=>{ if(!e.target.closest || !e.target.closest('.cs-wrap')) document.querySelectorAll('.cs-list').forEach(l=>l.classList.add('hidden')); });
document.addEventListener('keydown',e=>{ if(e.key==='Escape') document.querySelectorAll('.cs-list').forEach(l=>l.classList.add('hidden')); });
document.querySelectorAll('select[name="status"],select[name="package"]').forEach(enhanceSelect);
</script>
</x-layouts::app>
