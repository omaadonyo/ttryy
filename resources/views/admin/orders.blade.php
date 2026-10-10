<x-layouts::app :title="__('Manage orders')">
    @include('partials.toast')
    <style>
      .cs-btn svg{ transition:transform .25s ease; }
      .cs-btn[aria-expanded="true"] svg{ transform:rotate(180deg); }
      .cs-wrap:focus-within .cs-btn{ border-color:#9e005d; }
    </style>
    <div class="flex h-full w-full flex-1 flex-col gap-4">
        <div>
            <flux:heading size="xl">Orders</flux:heading>
            <flux:text class="mt-1">Confirm manual Mobile Money payments and follow up on pending orders.</flux:text>
        </div>

        <form method="GET" action="{{ route('admin.orders') }}" class="rounded-xl bg-white dark:bg-white/[.04] p-4 shadow-[0_12px_32px_-12px_rgba(10,10,12,.18)] dark:shadow-[0_12px_32px_-12px_rgba(0,0,0,.7)] grid sm:grid-cols-[1fr_160px_160px_auto] gap-3">
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search reference, business, phone…" class="rounded-[0.575rem] border border-neutral-300 dark:border-white/15 bg-white dark:bg-white/5 px-4 py-2.5 text-sm outline-none focus:border-[#9e005d]">
            <select name="status" class="rounded-[0.575rem] border border-neutral-300 dark:border-white/15 bg-white dark:bg-white/5 px-4 py-2.5 text-sm outline-none focus:border-[#9e005d]">
                <option value="">All statuses</option>
                @foreach(['pending','submitted','paid'] as $s)
                <option value="{{ $s }}" {{ ($filters['status'] ?? '') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <select name="package" class="rounded-[0.575rem] border border-neutral-300 dark:border-white/15 bg-white dark:bg-white/5 px-4 py-2.5 text-sm outline-none focus:border-[#9e005d]">
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
                                @if($order->credentials_handed_over)
                                <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-400">Handed over ✓</span>
                                @else
                                @if($order->status !== 'paid')
                                <form method="POST" action="{{ route('admin.orders.paid', $order) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <flux:button type="submit" size="sm" variant="primary" class="!bg-[#9e005d] hover:!bg-[#7e0049]">Mark paid</flux:button>
                                </form>
                                @else
                                <span class="text-xs text-zinc-400">UGX {{ number_format($order->paid_amount) }} ✓</span>
                                @endif
                                <form method="POST" action="{{ route('admin.orders.handover', $order) }}" class="inline ml-2">
                                    @csrf
                                    @method('PATCH')
                                    <flux:button type="submit" size="sm" variant="ghost">Hand over</flux:button>
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
  btn.className='cs-btn w-full flex items-center justify-between gap-2 rounded-[0.575rem] border border-neutral-300 dark:border-white/15 bg-white dark:bg-white/5 px-4 py-2.5 text-sm outline-none';
  btn.setAttribute('aria-expanded','false');
  btn.innerHTML='<span class="cs-label truncate">Select…</span><svg class="w-4 h-4 shrink-0 text-zinc-400 transition-transform" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>';
  const list=document.createElement('div');
  list.className='cs-list hidden absolute z-30 left-0 right-0 mt-2 rounded-xl border border-neutral-200 dark:border-white/15 bg-white dark:bg-[#141416] shadow-2xl p-1.5 max-h-60 overflow-y-auto';
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
