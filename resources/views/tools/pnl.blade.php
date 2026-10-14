<x-layouts::app :title="__('Business Tools — Profit & Loss')">
    @include('partials.toast')
    <div class="flex w-full flex-col gap-5">
        <x-page-head title="Profit & Loss" sub="Cash basis: counts money actually received and spent." />
        @include('tools._tabs', ['active' => $active])

        <div class="card card-pad">
            <form method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <div><label class="lbl">From</label><input name="from" type="date" value="{{ $from }}" class="field"></div>
                <div><label class="lbl">To</label><input name="to" type="date" value="{{ $to }}" class="field"></div>
                <button class="btn-brand">Apply</button>
            </form>
        </div>

        <div class="card card-pad max-w-2xl">
            <dl class="space-y-2.5 text-[15px]">
                <div class="flex justify-between gap-3"><dt class="font-bold">Revenue <span class="font-normal text-zinc-500">(cash received)</span></dt><dd class="font-extrabold">UGX {{ number_format($revenue) }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-zinc-600 dark:text-zinc-400">Cost of goods sold <span class="text-xs">({{ $paidCount }} fully-paid sale(s) · item cost at sale time)</span></dt><dd class="font-semibold">−UGX {{ number_format($cogs) }}</dd></div>
                <div class="flex justify-between gap-3 border-t border-zinc-200 pt-2.5 font-bold dark:border-white/10"><dt>Gross profit</dt><dd class="{{ $gross >= 0 ? 'text-emerald-600' : 'text-red-500' }}">UGX {{ number_format($gross) }}</dd></div>
                <div class="mt-2 flex justify-between gap-3"><dt class="font-bold">Operating expenses</dt><dd class="font-extrabold">−UGX {{ number_format($expenseTotal) }}</dd></div>
                @foreach($byCategory as $cat => $amt)
                <div class="flex justify-between gap-3 pl-4 text-sm"><dt class="text-zinc-500">{{ $cat }}</dt><dd>{{ number_format($amt) }}</dd></div>
                @endforeach
                <div class="flex justify-between gap-3 rounded-xl bg-zinc-950 px-4 py-3 text-lg font-extrabold text-white dark:bg-white dark:text-zinc-950"><dt>Net {{ $net >= 0 ? 'profit' : 'loss' }}</dt><dd>UGX {{ number_format(abs($net)) }}</dd></div>
            </dl>
        </div>
    </div>
</x-layouts::app>
