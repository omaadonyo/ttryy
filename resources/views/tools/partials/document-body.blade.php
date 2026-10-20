@php
    // Shared document "paper" used by the composer's live preview and the
    // document preview modal. `$bind` is the Alpine expression that holds the
    // document-shaped object (e.g. "previewDoc" or "draftPreviewDoc").
    $bind = $bind ?? 'previewDoc';
    $showFooter = $showFooter ?? true;
@endphp
<div class="bt-paper overflow-hidden rounded-2xl shadow-2xl">
    <div class="h-2 w-full bg-gradient-to-r from-brand-500 to-brand-700"></div>
    <div class="p-6 text-zinc-900 sm:p-10">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="font-display text-2xl font-extrabold text-zinc-900" x-text="settings.business_name || 'My Business'"></p>
                <p class="mt-1 text-sm text-zinc-500" x-text="settings.address || ''"></p>
                <p class="text-sm text-zinc-500" x-text="(settings.phone || '') + (settings.email ? ' · ' + settings.email : '')"></p>
            </div>
            <div class="text-right">
                <p class="font-display text-3xl font-extrabold uppercase tracking-tight text-brand-600" x-text="{{ $bind }}.label"></p>
                <p class="text-lg font-bold text-zinc-900" x-text="{{ $bind }}.number"></p>
                <span class="mt-1 inline-block rounded-full bg-zinc-500/10 px-2.5 py-1 text-[11px] font-bold capitalize text-zinc-600" x-text="{{ $bind }}.status"></span>
            </div>
        </div>

        <div class="mt-8 grid gap-6 sm:grid-cols-2">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-zinc-400">Billed to</p>
                <p class="mt-1 font-bold text-zinc-900" x-text="{{ $bind }}.contact_name || 'Walk-in customer'"></p>
            </div>
            <div class="space-y-0.5 sm:text-right">
                <p class="text-sm text-zinc-500">Issued: <span class="font-semibold text-zinc-700" x-text="{{ $bind }}.issue_date"></span></p>
                <p class="text-sm text-zinc-500" x-show="{{ $bind }}.due_date">Due: <span class="font-semibold text-zinc-700" x-text="{{ $bind }}.due_date"></span></p>
                <p class="text-sm text-zinc-500" x-show="{{ $bind }}.valid_until">Valid until: <span class="font-semibold text-zinc-700" x-text="{{ $bind }}.valid_until"></span></p>
                <p class="text-sm text-zinc-500" x-show="{{ $bind }}.payment_method">Paid via: <span class="font-semibold capitalize text-zinc-700" x-text="{{ $bind }}.payment_method"></span></p>
            </div>
        </div>

        <table class="mt-6 w-full border-collapse text-sm">
            <thead>
                <tr class="bg-zinc-950 text-white">
                    <th class="p-2.5 text-left">Description</th>
                    <th class="p-2.5 text-center">Qty</th>
                    <th class="p-2.5 text-right">Unit price</th>
                    <th class="p-2.5 text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(l, i) in {{ $bind }}.lines" :key="'pl'+i">
                    <tr class="border-b border-zinc-100">
                        <td class="p-2.5" x-text="l.description || (l.item_id ? 'Item' : '—')"></td>
                        <td class="p-2.5 text-center" x-text="l.qty"></td>
                        <td class="p-2.5 text-right" x-text="money(l.unit_price)"></td>
                        <td class="p-2.5 text-right font-semibold" x-text="money(l.line_total)"></td>
                    </tr>
                </template>
                <tr x-show="!{{ $bind }}.lines || !{{ $bind }}.lines.length">
                    <td class="p-4 text-center text-zinc-400" colspan="4">No line items yet.</td>
                </tr>
            </tbody>
        </table>

        <div class="mt-4 flex justify-end">
            <div class="w-full max-w-xs space-y-1.5 text-sm">
                <div class="flex justify-between"><span class="text-zinc-500">Subtotal</span><span class="font-semibold" x-text="money({{ $bind }}.subtotal)"></span></div>
                <div class="flex justify-between" x-show="{{ $bind }}.discount > 0"><span class="text-zinc-500">Discount</span><span class="font-semibold" x-text="'− ' + money({{ $bind }}.discount)"></span></div>
                <div class="flex justify-between" x-show="{{ $bind }}.tax_amount > 0"><span class="text-zinc-500">Tax</span><span class="font-semibold" x-text="money({{ $bind }}.tax_amount)"></span></div>
                <div class="flex justify-between border-t-2 border-zinc-900 pt-1.5"><span class="font-display text-lg font-extrabold">Total</span><span class="font-display text-lg font-extrabold" x-text="money({{ $bind }}.total)"></span></div>
                <template x-if="{{ $bind }}.type === 'invoice'">
                    <div>
                        <div class="flex justify-between text-emerald-600"><span>Paid</span><span class="font-semibold" x-text="money({{ $bind }}.paid_amount)"></span></div>
                        <div class="flex justify-between"><span class="font-bold">Balance due</span><span class="font-bold" x-text="money({{ $bind }}.balance)"></span></div>
                    </div>
                </template>
            </div>
        </div>

        <template x-if="{{ $bind }}.receipts && {{ $bind }}.receipts.length">
            <div class="mt-6">
                <p class="text-[11px] font-bold uppercase tracking-wider text-zinc-400">Payments received</p>
                <table class="mt-2 w-full border-collapse text-sm">
                    <tbody>
                        <template x-for="r in {{ $bind }}.receipts" :key="'pr'+r.id">
                            <tr class="border-b border-zinc-100">
                                <td class="py-2 font-semibold" x-text="r.number"></td>
                                <td class="py-2 text-zinc-500" x-text="r.issue_date"></td>
                                <td class="py-2 capitalize text-zinc-500" x-text="r.payment_method"></td>
                                <td class="py-2 text-right font-semibold" x-text="money(r.total)"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </template>

        <p class="mt-4 text-sm text-zinc-600" x-show="{{ $bind }}.notes" x-text="{{ $bind }}.notes"></p>

        @if($showFooter)
            <div class="mt-8 flex flex-wrap items-center gap-4 border-t border-zinc-200 pt-5">
                <img x-show="{{ $bind }}.qr" :src="{{ $bind }}.qr" alt="QR code" class="size-24 rounded-lg border border-zinc-200" />
                <div class="min-w-0 text-xs text-zinc-500">
                    <p class="font-bold text-zinc-700">Scan to verify this document</p>
                    <p class="mt-1 break-all" x-text="{{ $bind }}.verify_url || ''"></p>
                    <p class="mt-2" x-text="footerFor({{ $bind }}.type)"></p>
                </div>
            </div>
        @endif
    </div>
</div>
