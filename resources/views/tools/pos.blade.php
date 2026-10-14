<x-layouts::app :title="__('Business Tools — POS')">
    @include('partials.toast')
    <div class="flex w-full flex-col gap-5">
        <x-page-head title="Point of Sale" sub="Tap items, take cash or MoMo, print the receipt. Stock deducts instantly." />
        @include('tools._tabs', ['active' => $active])

        @if($errors->any())
        <div class="card card-pad border-red-300 bg-red-50 text-sm text-red-700 dark:bg-red-500/10 dark:text-red-300">{{ $errors->first() }}</div>
        @endif
        @if(session('status'))
        <div class="card card-pad border-emerald-500/40 bg-emerald-500/5 text-sm font-semibold text-emerald-700 dark:text-emerald-300">{{ session('status') }}</div>
        @endif

        @if($items->isEmpty())
        <div class="card card-pad"><x-empty-state icon="shopping-bag" title="No stock to sell" message="Add items with stock in Inventory first." /></div>
        @else
        <div class="grid items-start gap-4 lg:grid-cols-[1fr_340px]">
            <div class="card card-pad">
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    @foreach($items as $i)
                    <button type="button" class="pos-item rounded-xl bg-zinc-50 p-4 text-left transition hover:bg-brand-50 dark:bg-white/5 dark:hover:bg-brand-500/10" data-id="{{ $i->id }}" data-name="{{ $i->name }}" data-price="{{ $i->sale_price }}" data-stock="{{ $i->stock }}" data-unit="{{ $i->unit }}">
                        <strong class="block truncate text-sm">{{ $i->name }}</strong>
                        <span class="mt-0.5 block text-sm font-extrabold text-brand-700 dark:text-brand-300">UGX {{ number_format($i->sale_price) }}</span>
                        <span class="text-[11px] text-zinc-500">{{ $i->stock }} {{ $i->unit }} left</span>
                    </button>
                    @endforeach
                </div>
            </div>

            <form method="POST" action="{{ route('tools.pos.checkout') }}" id="pos-form" class="card card-pad lg:sticky lg:top-24">
                @csrf
                <h2 class="section-title">Current sale</h2>
                <div class="mt-3"><label class="lbl">Customer (optional)</label><select name="contact_id" class="field"><option value="">Walk-in</option>@foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div>
                <div class="mt-3"><label class="lbl">Payment method</label><select name="payment_method" class="field"><option value="cash">Cash</option><option value="momo">Mobile Money</option><option value="card">Card</option><option value="bank">Bank</option></select></div>
                <div id="cart" class="mt-3 space-y-2"></div>
                <p id="cart-empty" class="mt-3 text-sm text-zinc-500">Tap items to add them.</p>
                <div class="mt-3 border-t border-zinc-200 pt-3 dark:border-white/10">
                    <div class="flex justify-between text-sm"><span class="text-zinc-500">Total</span><strong id="cart-total" class="stat-num">UGX 0</strong></div>
                    <div class="mt-2"><label class="lbl">Cash received (optional — shows change)</label><input name="tendered" id="tendered" type="number" min="0" class="field" placeholder="e.g. 50000"></div>
                    <p id="change-line" class="mt-1 hidden text-sm font-bold text-emerald-600">Change: <span id="change-amt">UGX 0</span></p>
                </div>
                <button class="btn-brand mt-4 w-full py-3!" id="pos-pay">Charge UGX 0</button>
            </form>
        </div>
        @endif
    </div>
<script>
(function(){
    const cart = new Map();
    const fmt = n => 'UGX ' + Number(n || 0).toLocaleString('en-US');
    const box = document.getElementById('cart');
    function paint(){
        box.innerHTML = '';
        let total = 0;
        cart.forEach((it, id) => {
            total += it.price * it.qty;
            const row = document.createElement('div');
            row.className = 'flex items-center gap-2 rounded-lg bg-zinc-50 dark:bg-white/5 px-3 py-2 text-sm';
            row.innerHTML = '<span class="min-w-0 flex-1"><strong></strong><span class="block text-xs text-zinc-500"></span></span>'
                + '<button type="button" data-a="-" class="w-7 h-7 rounded-full bg-zinc-200 dark:bg-white/10 font-bold">−</button>'
                + '<strong class="w-8 text-center"></strong>'
                + '<button type="button" data-a="+" class="w-7 h-7 rounded-full bg-zinc-200 dark:bg-white/10 font-bold">+</button>';
            row.querySelector('strong').textContent = it.name;
            row.querySelector('span span').textContent = fmt(it.price) + ' each';
            row.querySelectorAll('strong')[1].textContent = it.qty;
            row.querySelector('[data-a="-"]').addEventListener('click', () => { it.qty--; if(it.qty <= 0) cart.delete(id); paint(); });
            row.querySelector('[data-a="+"]').addEventListener('click', () => { if(it.qty < it.stock) it.qty++; paint(); });
            box.appendChild(row);
        });
        document.getElementById('cart-empty').classList.toggle('hidden', cart.size > 0);
        document.getElementById('cart-total').textContent = fmt(total);
        document.getElementById('pos-pay').textContent = 'Charge ' + fmt(total);
        change();
    }
    function change(){
        const t = parseInt(document.getElementById('tendered').value, 10) || 0;
        let total = 0;
        cart.forEach(it => total += it.price * it.qty);
        const show = t > total;
        document.getElementById('change-line').classList.toggle('hidden', !show);
        if(show) document.getElementById('change-amt').textContent = fmt(t - total);
    }
    document.querySelectorAll('.pos-item').forEach(b => b.addEventListener('click', () => {
        const id = b.dataset.id;
        const cur = cart.get(id) || { name: b.dataset.name, price: parseInt(b.dataset.price, 10), stock: parseInt(b.dataset.stock, 10), qty: 0 };
        if(cur.qty < cur.stock) cur.qty++;
        cart.set(id, cur);
        paint();
    }));
    document.getElementById('tendered').addEventListener('input', change);
    document.getElementById('pos-form').addEventListener('submit', function(e){
        if(cart.size === 0){ e.preventDefault(); toast('Add at least one item to the sale.', 'warning'); return; }
        cart.forEach((it, id) => {
            [['item_id', id], ['qty', it.qty]].forEach(([k, v], j) => {
                const h = document.createElement('input');
                h.type = 'hidden'; h.name = 'lines[' + [...cart.keys()].indexOf(id) + '][' + k + ']'; h.value = v;
                this.appendChild(h);
            });
        });
    });
    paint();
})();
</script>
</x-layouts::app>
