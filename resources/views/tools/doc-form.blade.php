@php
$titles = ['quotation' => 'New Quotation', 'invoice' => 'New Invoice', 'receipt' => 'New Receipt'];
@endphp
<x-layouts::app :title="__('Business Tools — '.$titles[$type])">
    @include('partials.toast')
    <div class="flex w-full flex-col gap-5">
        <x-page-head :title="$titles[$type]" sub="Totals are computed on the server — what you see is what the PDF prints." />
        @include('tools._tabs', ['active' => $active])

        @if($errors->any())
        <div class="card card-pad border-red-300 bg-red-50 text-sm text-red-700 dark:bg-red-500/10 dark:text-red-300">
            <p class="font-bold">Please fix the following:</p>
            <ul class="list-disc ml-5 mt-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <form method="POST" action="{{ route('tools.doc.store', ['type' => str($type)->plural()]) }}" id="doc-form" class="grid gap-5 lg:grid-cols-[1fr_320px] items-start">
            @csrf
            <div class="space-y-5">
                <div class="card card-pad">
                    <h2 class="section-title">Who & when</h2>
                    <div class="mt-3 grid gap-4 sm:grid-cols-2">
                        <div><label class="lbl">Customer</label><select name="contact_id" class="field"><option value="">Walk-in / none</option>@foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->name }}{{ $c->phone ? ' · '.$c->phone : '' }}</option>@endforeach</select></div>
                        <div><label class="lbl">Date *</label><input name="issue_date" type="date" required value="{{ now()->toDateString() }}" class="field"></div>
                        @if($type === 'quotation')
                        <div><label class="lbl">Valid until</label><input name="valid_until" type="date" value="{{ now()->addDays(14)->toDateString() }}" class="field"></div>
                        @elseif($type === 'invoice')
                        <div><label class="lbl">Due date</label><input name="due_date" type="date" value="{{ now()->addDays(14)->toDateString() }}" class="field"></div>
                        @else
                        <div><label class="lbl">Payment method</label><select name="payment_method" class="field"><option value="cash">Cash</option><option value="momo">Mobile Money</option><option value="card">Card</option><option value="bank">Bank</option></select></div>
                        @endif
                        @if($type === 'receipt')
                        <div class="sm:col-span-2"><label class="lbl">Against invoice (optional — leave empty for a walk-in sale)</label><select name="invoice_id" id="pay-invoice" class="field"><option value="">Standalone receipt</option>@foreach($invoices as $inv)<option value="{{ $inv->id }}" data-balance="{{ $inv->balance() }}">{{ $inv->number }} — {{ $inv->contact?->name ?? 'Walk-in' }} (UGX {{ number_format($inv->balance()) }} due)</option>@endforeach</select></div>
                        <div id="pay-amount-row" class="hidden"><label class="lbl">Amount received *</label><input name="amount" type="number" min="1" class="field" placeholder="Full balance if empty"></div>
                        @endif
                    </div>
                </div>

                <div class="card card-pad" id="lines-card">
                    <div class="flex items-center justify-between">
                        <h2 class="section-title">Line items</h2>
                        <button type="button" id="add-line" class="btn-ghost btn-sm">+ Add line</button>
                    </div>
                    <div id="lines" class="mt-3 space-y-3"></div>
                    <p class="mt-2 text-xs text-zinc-500">Pick an item to pull its price (stock checks run on save), or type a custom service line.</p>
                </div>

                <div class="card card-pad">
                    <h2 class="section-title">Totals & notes</h2>
                    <div class="mt-3 grid gap-4 sm:grid-cols-3">
                        <div><label class="lbl">Discount (UGX)</label><input name="discount" id="f-discount" type="number" min="0" value="0" class="field"></div>
                        <div><label class="lbl">Tax %</label><input name="tax_rate" id="f-tax" type="number" step="0.01" min="0" max="100" value="{{ $settings->tax_rate }}" class="field"></div>
                        <div><label class="lbl">Estimated total</label><p id="live-total" class="field font-extrabold">UGX 0</p></div>
                    </div>
                    <div class="mt-3"><label class="lbl">Notes (optional)</label><textarea name="notes" rows="2" class="field" placeholder="Payment terms, delivery notes…"></textarea></div>
                </div>
            </div>

            <aside class="lg:sticky lg:top-24 card card-pad h-fit">
                <p class="eyebrow">{{ $titles[$type] }}</p>
                <p class="stat-num mt-1" id="side-total">UGX 0</p>
                <p class="mt-1 text-xs text-zinc-500" id="side-lines">0 lines</p>
                <button class="btn-brand mt-4 w-full py-3!">Save {{ substr($titles[$type], 4) }}</button>
                <a href="{{ route('tools.'.str($type)->plural()->toString()) }}" class="btn-ghost mt-2 w-full">Cancel</a>
            </aside>
        </form>
    </div>
<script>
(function(){
    const items = @json($items->map(fn($i) => ['id' => $i->id, 'name' => $i->name, 'price' => $i->sale_price, 'stock' => $i->stock, 'unit' => $i->unit]));
    const box = document.getElementById('lines');
    const fmt = n => 'UGX ' + Number(n || 0).toLocaleString('en-US');
    function row(){
        const d = document.createElement('div');
        d.className = 'grid gap-2 rounded-xl bg-zinc-50 dark:bg-white/5 p-3 sm:grid-cols-[1fr_90px_110px_110px_auto] sm:items-end';
        d.innerHTML =
            '<div><label class="lbl">Item</label><select class="field ln-item"><option value="">Custom line…</option>' + items.map(i => '<option value="' + i.id + '" data-price="' + i.price + '">' + i.name.replace(/</g, '&lt;') + ' (' + i.stock + ' ' + i.unit + ')</option>').join('') + '</select>'
            + '<input class="field ln-desc mt-2" placeholder="Description"></div>'
            + '<div><label class="lbl">Qty</label><input type="number" min="0.01" step="0.01" value="1" class="field ln-qty"></div>'
            + '<div><label class="lbl">Unit price</label><input type="number" min="0" value="0" class="field ln-price"></div>'
            + '<div><label class="lbl">Line total</label><p class="field font-bold ln-total">UGX 0</p></div>'
            + '<button type="button" class="btn-ghost btn-sm ln-del">✕</button>';
        const sel = d.querySelector('.ln-item'), desc = d.querySelector('.ln-desc'),
              qty = d.querySelector('.ln-qty'), price = d.querySelector('.ln-price');
        sel.addEventListener('change', () => {
            const o = sel.options[sel.selectedIndex];
            if(sel.value){ price.value = o.dataset.price || 0; if(!desc.value) desc.value = o.text.replace(/ \(\d+ .*\)$/, ''); }
            calc();
        });
        [qty, price].forEach(el => el.addEventListener('input', calc));
        d.querySelector('.ln-del').addEventListener('click', () => { d.remove(); calc(); });
        box.appendChild(d);
        return d;
    }
    function calc(){
        let sub = 0, n = 0;
        box.querySelectorAll(':scope > div').forEach(d => {
            const q = parseFloat(d.querySelector('.ln-qty').value) || 0;
            const p = parseInt(d.querySelector('.ln-price').value, 10) || 0;
            const t = Math.round(q * p);
            d.querySelector('.ln-total').textContent = fmt(t);
            sub += t; n++;
        });
        const disc = Math.min(parseInt(document.getElementById('f-discount').value, 10) || 0, sub);
        const tax = Math.min(parseFloat(document.getElementById('f-tax').value) || 0, 100);
        const total = Math.round((sub - disc) * (1 + tax / 100));
        document.getElementById('live-total').textContent = fmt(total);
        document.getElementById('side-total').textContent = fmt(total);
        document.getElementById('side-lines').textContent = n + (n === 1 ? ' line' : ' lines');
    }
    document.getElementById('add-line').addEventListener('click', row);
    document.getElementById('f-discount').addEventListener('input', calc);
    document.getElementById('f-tax').addEventListener('input', calc);
    document.getElementById('doc-form').addEventListener('submit', () => {
        box.querySelectorAll(':scope > div').forEach((d, i) => {
            const val = (cls, name) => { const h = document.createElement('input'); h.type = 'hidden'; h.name = 'lines[' + i + '][' + name + ']'; h.value = d.querySelector(cls).value; d.appendChild(h); };
            val('.ln-item', 'item_id'); val('.ln-desc', 'description'); val('.ln-qty', 'qty'); val('.ln-price', 'unit_price');
        });
    });
    const paySel = document.getElementById('pay-invoice');
    if(paySel) paySel.addEventListener('change', () => {
        const linked = !!paySel.value;
        document.getElementById('pay-amount-row').classList.toggle('hidden', !linked);
        document.getElementById('lines-card').classList.toggle('hidden', linked);
    });
    row(); calc();
})();
</script>
</x-layouts::app>
