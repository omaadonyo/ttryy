<x-layouts::app :title="__('Business Tools — '.$doc->number)">
    @include('partials.toast')
    <div class="flex w-full flex-col gap-5">
        <x-page-head :title="$doc->label().' '.$doc->number" :sub="($doc->contact?->name ?? 'Walk-in customer').' · issued '.$doc->issue_date->format('d M Y')">
            <x-slot:actions>
                <a href="{{ route('tools.doc.pdf', $doc) }}" class="btn-brand btn-sm">Download PDF ↓</a>
                <a href="{{ route('tools.'.str($doc->type)->plural()->toString()) }}" class="btn-ghost btn-sm">← Back to list</a>
            </x-slot:actions>
        </x-page-head>
        @include('tools._tabs', ['active' => $active])

        @if(session('status'))
        <div class="card card-pad border-emerald-500/40 bg-emerald-500/5 text-sm font-semibold text-emerald-700 dark:text-emerald-300">{{ session('status') }}</div>
        @endif
        @if($errors->any())
        <div class="card card-pad border-red-300 bg-red-50 text-sm text-red-700 dark:bg-red-500/10 dark:text-red-300">{{ $errors->first() }}</div>
        @endif

        <div class="grid gap-4 lg:grid-cols-[1fr_320px] items-start">
            <div class="card card-pad">
                <div class="flex flex-wrap items-center gap-2">
                    @if(in_array($doc->status, ['paid', 'posted', 'accepted']))<span class="pill-green">{{ $doc->status }}</span>
                    @elseif(in_array($doc->status, ['partial', 'sent']))<span class="pill-amber">{{ $doc->status }}</span>
                    @elseif($doc->isOverdue())<span class="pill-red">overdue</span>
                    @elseif($doc->status === 'declined')<span class="pill-red">declined</span>
                    @else<span class="pill-zinc">{{ $doc->status }}</span>@endif
                    @if($doc->type === 'invoice' && $doc->balance() > 0)<span class="pill-amber">UGX {{ number_format($doc->balance()) }} due</span>@endif
                    @if($doc->due_date)<span class="text-xs text-zinc-500">Due {{ $doc->due_date->format('d M Y') }}</span>@endif
                    @if($doc->valid_until)<span class="text-xs text-zinc-500">Valid until {{ $doc->valid_until->format('d M Y') }}</span>@endif
                </div>
                <div class="tbl-wrap mt-4 rounded-none! border-0! shadow-none!">
                    <table class="tbl">
                        <thead><tr><th>Description</th><th class="text-right">Qty</th><th class="text-right">Unit price</th><th class="text-right">Amount</th></tr></thead>
                        <tbody>
                            @foreach($doc->lines as $l)
                            <tr><td>{{ $l->description }}</td><td class="text-right">{{ rtrim(rtrim(number_format($l->qty, 2), '0'), '.') }}</td><td class="text-right">{{ number_format($l->unit_price) }}</td><td class="text-right font-bold">{{ number_format($l->line_total) }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <dl class="ml-auto mt-4 w-64 space-y-1.5 text-sm">
                    <div class="flex justify-between"><dt class="text-zinc-500">Subtotal</dt><dd class="font-semibold">UGX {{ number_format($doc->subtotal) }}</dd></div>
                    @if($doc->discount > 0)<div class="flex justify-between"><dt class="text-zinc-500">Discount</dt><dd class="font-semibold">−UGX {{ number_format($doc->discount) }}</dd></div>@endif
                    @if($doc->tax_amount > 0)<div class="flex justify-between"><dt class="text-zinc-500">Tax ({{ $doc->tax_rate }}%)</dt><dd class="font-semibold">UGX {{ number_format($doc->tax_amount) }}</dd></div>@endif
                    <div class="flex justify-between border-t border-zinc-200 pt-1.5 text-base dark:border-white/10"><dt class="font-bold">Total</dt><dd class="font-extrabold">UGX {{ number_format($doc->total) }}</dd></div>
                    @if($doc->type === 'invoice')<div class="flex justify-between"><dt class="text-zinc-500">Paid</dt><dd class="font-semibold text-emerald-600">UGX {{ number_format($doc->paid_amount) }}</dd></div>@endif
                </dl>
                @if($doc->notes)<p class="mt-4 text-sm text-zinc-500 whitespace-pre-wrap">{{ $doc->notes }}</p>@endif
                @if($doc->type === 'invoice' && $doc->receipts->isNotEmpty())
                <div class="mt-4 border-t border-zinc-200 pt-3 dark:border-white/10">
                    <p class="eyebrow">Payments received</p>
                    <div class="mt-2 space-y-1.5 text-sm">
                        @foreach($doc->receipts as $r)
                        <a href="{{ route('tools.doc.show', $r) }}" class="flex justify-between hover:underline"><span>{{ $r->number }} · {{ $r->created_at->format('d M Y') }}</span><strong>UGX {{ number_format($r->total) }}</strong></a>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <aside class="card card-pad h-fit space-y-3">
                <p class="eyebrow">Actions</p>
                @if($doc->type === 'quotation' && in_array($doc->status, ['draft', 'sent']))
                <form method="POST" action="{{ route('tools.doc.convert', $doc) }}" onsubmit="return confirm('Convert {{ $doc->number }} to an invoice? Stock will deduct.')">
                    @csrf
                    <button class="btn-brand w-full">Convert to invoice →</button>
                </form>
                @endif
                @if($doc->type === 'quotation')
                <form method="POST" action="{{ route('tools.doc.status', $doc) }}" class="flex gap-2">
                    @csrf @method('PATCH')
                    <select name="status" class="field"><option value="sent" {{ $doc->status === 'sent' ? 'selected' : '' }}>Sent</option><option value="accepted" {{ $doc->status === 'accepted' ? 'selected' : '' }}>Accepted</option><option value="declined" {{ $doc->status === 'declined' ? 'selected' : '' }}>Declined</option><option value="draft" {{ $doc->status === 'draft' ? 'selected' : '' }}>Draft</option></select>
                    <button class="btn-ghost btn-sm shrink-0">Set</button>
                </form>
                @endif
                @if($doc->type === 'invoice' && $doc->balance() > 0)
                <form method="POST" action="{{ route('tools.doc.store', ['type' => 'receipts']) }}">
                    @csrf
                    <input type="hidden" name="contact_id" value="{{ $doc->contact_id }}">
                    <input type="hidden" name="issue_date" value="{{ now()->toDateString() }}">
                    <input type="hidden" name="invoice_id" value="{{ $doc->id }}">
                    <input type="hidden" name="payment_method" value="cash">
                    <label class="lbl">Record payment (UGX)</label>
                    <div class="flex gap-2">
                        <input name="amount" type="number" min="1" max="{{ $doc->balance() }}" value="{{ $doc->balance() }}" class="field">
                        <button class="btn-brand btn-sm shrink-0">Record</button>
                    </div>
                </form>
                @endif
                @if($doc->type === 'invoice' && $doc->paid_amount === 0)
                <form method="POST" action="{{ route('tools.doc.destroy', $doc) }}" onsubmit="return confirm('Delete {{ $doc->number }}? Stock returns to shelves.')">@csrf @method('DELETE')<button class="btn-ghost btn-sm w-full">Delete unpaid invoice</button></form>
                @elseif($doc->type !== 'invoice')
                <form method="POST" action="{{ route('tools.doc.destroy', $doc) }}" onsubmit="return confirm('Delete {{ $doc->number }}?')">@csrf @method('DELETE')<button class="btn-ghost btn-sm w-full">Delete</button></form>
                @endif
            </aside>
        </div>
    </div>
</x-layouts::app>
