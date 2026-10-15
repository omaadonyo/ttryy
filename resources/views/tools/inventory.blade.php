<x-layouts::app :title="__('Business Tools — Inventory')">
    @include('partials.toast')
    <div class="flex w-full flex-col gap-5">
        <x-page-head title="Inventory" sub="Stock, costs and prices. Sales deduct stock automatically.">
            <x-slot:actions>
                <span class="pill-zinc">Stock value: UGX {{ number_format($stockValue) }}</span>
                <button type="button" onclick="document.getElementById('item-modal').showModal()" class="btn-brand btn-sm">Add item</button>
            </x-slot:actions>
        </x-page-head>
        @include('tools._tabs', ['active' => $active])

        @if($errors->any())
        <div class="card card-pad border-red-300 bg-red-50 text-sm text-red-700 dark:bg-red-500/10 dark:text-red-300">{{ $errors->first() }}</div>
        @endif
        @if(session('status'))
        <div class="card card-pad border-emerald-500/40 bg-emerald-500/5 text-sm font-semibold text-emerald-700 dark:text-emerald-300">{{ session('status') }}</div>
        @endif

        <dialog id="item-modal" class="m-auto max-h-[92vh] w-[calc(100%-2rem)] max-w-md overflow-y-auto rounded-xl bg-white p-4 backdrop:bg-zinc-950/60 sm:p-6 dark:bg-[#141416]">
            <div class="mb-3 flex items-center justify-between gap-2">
                <h2 class="section-title">Add item</h2>
                <button type="button" onclick="document.getElementById('item-modal').close()" class="grid size-8 shrink-0 place-items-center rounded-full text-zinc-400 transition hover:bg-zinc-950/5 hover:text-zinc-700 dark:hover:bg-white/10 dark:hover:text-white" aria-label="Close">✕</button>
            </div>
            <form method="POST" action="{{ route('tools.items.store') }}" class="space-y-3">
                @csrf
                <div><label class="lbl">Name *</label><input name="name" required class="field" placeholder="e.g. Cement 50kg"></div>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="lbl">SKU</label><input name="sku" class="field"></div>
                    <div><label class="lbl">Unit</label><input name="unit" value="pcs" class="field"></div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="lbl">Cost price *</label><input name="cost_price" type="number" min="0" required class="field"></div>
                    <div><label class="lbl">Sale price *</label><input name="sale_price" type="number" min="0" required class="field"></div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="lbl">Opening stock *</label><input name="stock" type="number" min="0" value="0" required class="field"></div>
                    <div><label class="lbl">Reorder at</label><input name="reorder_level" type="number" min="0" value="5" class="field"></div>
                </div>
                <button class="btn-brand w-full">Save item</button>
            </form>
        </dialog>
        @if($errors->any())
        <script>document.getElementById('item-modal')?.showModal();</script>
        @endif

        <div class="card card-pad">
                <form method="GET" class="mb-3 flex flex-col gap-3 sm:flex-row">
                    <input name="q" value="{{ $q }}" placeholder="Search name or SKU…" class="field">
                    <button class="btn-brand">Search</button>
                </form>
                @if($items->isEmpty())
                <x-empty-state icon="shopping-bag" title="No items yet" message="Add what you sell — POS and invoices pull from here." />
                @else
                <div class="tbl-wrap rounded-none! border-0! shadow-none!">
                    <table class="tbl">
                        <thead><tr><th>Item</th><th class="text-right">Cost</th><th class="text-right">Price</th><th class="text-right">Stock</th><th></th></tr></thead>
                        <tbody>
                            @foreach($items as $i)
                            <tr>
                                <td><strong>{{ $i->name }}</strong><span class="block text-xs text-zinc-500">{{ $i->sku }} · per {{ $i->unit }}</span></td>
                                <td class="text-right">{{ number_format($i->cost_price) }}</td>
                                <td class="text-right font-bold">{{ number_format($i->sale_price) }}</td>
                                <td class="text-right">@if($i->isLow())<span class="pill-amber">{{ $i->stock }} low</span>@else{{ $i->stock }}@endif</td>
                                <td class="text-right whitespace-nowrap">
                                    <details class="inline-block text-left">
                                        <summary class="btn-ghost btn-sm cursor-pointer">Manage</summary>
                                        <div class="mt-2 space-y-3 rounded-xl bg-zinc-50 dark:bg-white/5 p-3">
                                            <form method="POST" action="{{ route('tools.items.update', $i) }}" class="grid grid-cols-2 gap-2">
                                                @csrf @method('PATCH')
                                                <input name="name" required value="{{ $i->name }}" class="field col-span-2" aria-label="Name">
                                                <input name="cost_price" type="number" min="0" required value="{{ $i->cost_price }}" class="field" aria-label="Cost">
                                                <input name="sale_price" type="number" min="0" required value="{{ $i->sale_price }}" class="field" aria-label="Price">
                                                <input name="sku" value="{{ $i->sku }}" class="field" aria-label="SKU" placeholder="SKU">
                                                <input name="unit" value="{{ $i->unit }}" class="field" aria-label="Unit">
                                                <input name="reorder_level" type="number" min="0" value="{{ $i->reorder_level }}" class="field col-span-2" aria-label="Reorder level">
                                                <button class="btn-brand btn-sm col-span-2">Save</button>
                                            </form>
                                            <form method="POST" action="{{ route('tools.items.adjust', $i) }}" class="flex gap-2">
                                                @csrf
                                                <input name="qty_change" type="number" required placeholder="+/- qty" class="field" aria-label="Quantity change">
                                                <input name="reason" required placeholder="Reason" value="Restock" class="field" aria-label="Reason">
                                                <button class="btn-dark btn-sm shrink-0">Apply</button>
                                            </form>
                                            <form method="POST" action="{{ route('tools.items.destroy', $i) }}" onsubmit="return confirm('Delete {{ $i->name }}?')">
                                                @csrf @method('DELETE')
                                                <button class="text-xs font-bold text-red-600 hover:underline dark:text-red-400">Delete item</button>
                                            </form>
                                        </div>
                                    </details>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $items->links() }}</div>
                @endif
        </div>
    </div>
</x-layouts::app>
