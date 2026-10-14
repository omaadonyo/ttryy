<x-layouts::app :title="__('Business Tools — Expenses')">
    @include('partials.toast')
    <div class="flex w-full flex-col gap-5">
        <x-page-head title="Expenses" sub="Every shilling out. Unpaid bills count as what you owe.">
            <x-slot:actions><span class="pill-zinc">This month: UGX {{ number_format($monthTotal) }}</span></x-slot:actions>
        </x-page-head>
        @include('tools._tabs', ['active' => $active])

        @if($errors->any())
        <div class="card card-pad border-red-300 bg-red-50 text-sm text-red-700 dark:bg-red-500/10 dark:text-red-300">{{ $errors->first() }}</div>
        @endif
        @if(session('status'))
        <div class="card card-pad border-emerald-500/40 bg-emerald-500/5 text-sm font-semibold text-emerald-700 dark:text-emerald-300">{{ session('status') }}</div>
        @endif

        <div class="grid gap-4 lg:grid-cols-[320px_1fr]">
            <div class="card card-pad h-fit">
                <h2 class="section-title">Record expense</h2>
                <form method="POST" action="{{ route('tools.expenses.store') }}" class="mt-3 space-y-3">
                    @csrf
                    <div><label class="lbl">Date *</label><input name="date" type="date" required value="{{ now()->toDateString() }}" class="field"></div>
                    <div><label class="lbl">Category *</label><input name="category" required list="exp-cats" class="field" placeholder="e.g. Rent, Transport, Stock">
                        <datalist id="exp-cats"><option>Rent</option><option>Transport</option><option>Stock purchase</option><option>Salaries</option><option>Airtime & data</option><option>Utilities</option><option>Marketing</option><option>Other</option></datalist></div>
                    <div><label class="lbl">Amount (UGX) *</label><input name="amount" type="number" min="1" required class="field"></div>
                    <div><label class="lbl">Status</label><select name="status" class="field"><option value="paid">Paid</option><option value="unpaid">Unpaid (I owe)</option></select></div>
                    <div><label class="lbl">Supplier (optional)</label><select name="contact_id" class="field"><option value="">—</option>@foreach($suppliers as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select></div>
                    <div><label class="lbl">Note</label><input name="note" class="field" placeholder="What was this for?"></div>
                    <button class="btn-brand w-full">Save expense</button>
                </form>
            </div>

            <div class="card card-pad">
                @if($expenses->isEmpty())
                <x-empty-state icon="banknotes" title="No expenses yet" message="Record rent, stock, transport — everything counts." />
                @else
                <div class="tbl-wrap rounded-none! border-0! shadow-none!">
                    <table class="tbl">
                        <thead><tr><th>Date</th><th>Category</th><th class="text-right">Amount</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                            @foreach($expenses as $e)
                            <tr>
                                <td class="whitespace-nowrap">{{ $e->date->format('d M Y') }}</td>
                                <td><strong>{{ $e->category }}</strong><span class="block text-xs text-zinc-500">{{ $e->note }}{{ $e->supplier ? ' · '.$e->supplier->name : '' }}</span></td>
                                <td class="text-right font-bold">{{ number_format($e->amount) }}</td>
                                <td>{!! $e->status === 'paid' ? '<span class="pill-green">paid</span>' : '<span class="pill-amber">unpaid</span>' !!}</td>
                                <td class="text-right whitespace-nowrap">
                                    <form method="POST" action="{{ route('tools.expenses.update', $e) }}" class="inline">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="date" value="{{ $e->date->toDateString() }}">
                                        <input type="hidden" name="category" value="{{ $e->category }}">
                                        <input type="hidden" name="amount" value="{{ $e->amount }}">
                                        <input type="hidden" name="note" value="{{ $e->note }}">
                                        <input type="hidden" name="status" value="{{ $e->status === 'paid' ? 'unpaid' : 'paid' }}">
                                        <button class="text-xs font-bold text-brand-700 hover:underline dark:text-brand-300">{{ $e->status === 'paid' ? 'Mark unpaid' : 'Mark paid' }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('tools.expenses.destroy', $e) }}" class="inline ml-2" onsubmit="return confirm('Delete this expense?')">
                                        @csrf @method('DELETE')
                                        <button class="text-xs font-bold text-red-600 hover:underline dark:text-red-400">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $expenses->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-layouts::app>
