<x-layouts::app :title="__('Business Tools — Balance Sheet')">
    @include('partials.toast')
    <div class="flex w-full flex-col gap-5">
        <x-page-head title="Balance Sheet" sub="What the business owns vs what it owes — live right now." />
        @include('tools._tabs', ['active' => $active])

        <div class="grid items-start gap-4 lg:grid-cols-2">
            <div class="card card-pad">
                <h2 class="section-title">Assets — UGX {{ number_format($assets) }}</h2>
                <dl class="mt-3 space-y-2.5 text-[15px]">
                    <div class="flex justify-between gap-3"><dt>Cash on hand <span class="block text-xs font-normal text-zinc-500">Receipts minus paid expenses</span></dt><dd class="font-extrabold">UGX {{ number_format($cash) }}</dd></div>
                    <div class="flex justify-between gap-3"><dt>Customer debts <span class="block text-xs font-normal text-zinc-500">Unpaid invoice balances</span></dt><dd class="font-extrabold">UGX {{ number_format($receivables) }}</dd></div>
                    <div class="flex justify-between gap-3"><dt>Stock on shelves <span class="block text-xs font-normal text-zinc-500">Valued at cost price</span></dt><dd class="font-extrabold">UGX {{ number_format($inventory) }}</dd></div>
                </dl>
            </div>
            <div class="space-y-4">
                <div class="card card-pad">
                    <h2 class="section-title">Liabilities — UGX {{ number_format($payables) }}</h2>
                    <dl class="mt-3 space-y-2.5 text-[15px]">
                        <div class="flex justify-between gap-3"><dt>Unpaid bills <span class="block text-xs font-normal text-zinc-500">Expenses marked unpaid</span></dt><dd class="font-extrabold">UGX {{ number_format($payables) }}</dd></div>
                    </dl>
                </div>
                <div class="card card-pad">
                    <h2 class="section-title">Owner's equity</h2>
                    <p class="stat-num mt-1 {{ $equity >= 0 ? '' : 'text-red-500' }}">UGX {{ number_format($equity) }}</p>
                    <p class="mt-1 text-xs text-zinc-500">Assets minus liabilities. Grows when you profit and collect.</p>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
