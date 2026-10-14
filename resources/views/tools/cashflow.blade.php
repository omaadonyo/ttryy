<x-layouts::app :title="__('Business Tools — Cash Flow')">
    @include('partials.toast')
    <div class="flex w-full flex-col gap-5">
        <x-page-head title="Cash Flow" sub="Real money in vs real money out. Only received and paid cash counts." />
        @include('tools._tabs', ['active' => $active])

        <div class="card card-pad">
            <form method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <div><label class="lbl">From</label><input name="from" type="date" value="{{ $from }}" class="field"></div>
                <div><label class="lbl">To</label><input name="to" type="date" value="{{ $to }}" class="field"></div>
                <button class="btn-brand">Apply</button>
            </form>
        </div>

        <div class="grid auto-rows-min gap-4 sm:grid-cols-3">
            <x-stat label="Money in" :value="'UGX '.number_format($inTotal)" hint="{{ $inflows->count() }} receipt(s)" />
            <x-stat label="Money out" :value="'UGX '.number_format($outTotal)" hint="{{ $outflows->count() }} expense(s)" />
            <x-stat :label="$net >= 0 ? 'Net gain' : 'Net loss'" :value="'UGX '.number_format(abs($net))" hint="{{ $from }} → {{ $to }}" />
        </div>

        <div class="grid items-start gap-4 lg:grid-cols-2">
            <div class="card card-pad">
                <h2 class="section-title">Inflows ({{ $inflows->count() }})</h2>
                <div class="mt-3 space-y-1.5 text-sm">
                    @forelse($inflows as $r)
                    <a href="{{ route('tools.doc.show', $r) }}" class="flex justify-between gap-2 rounded-lg px-2 py-1.5 hover:bg-zinc-950/5 dark:hover:bg-white/5"><span class="min-w-0 truncate">{{ $r->created_at->format('d M') }} · {{ $r->number }} · {{ $r->contact?->name ?? 'Walk-in' }}</span><strong class="shrink-0 text-emerald-600">+{{ number_format($r->total) }}</strong></a>
                    @empty
                    <p class="text-sm text-zinc-500">No money in for this period.</p>
                    @endforelse
                </div>
            </div>
            <div class="card card-pad">
                <h2 class="section-title">Outflows ({{ $outflows->count() }})</h2>
                <div class="mt-3 space-y-1.5 text-sm">
                    @forelse($outflows as $e)
                    <div class="flex justify-between gap-2 rounded-lg px-2 py-1.5"><span class="min-w-0 truncate">{{ $e->date->format('d M') }} · {{ $e->category }}{{ $e->note ? ' — '.$e->note : '' }}</span><strong class="shrink-0 text-red-500">−{{ number_format($e->amount) }}</strong></div>
                    @empty
                    <p class="text-sm text-zinc-500">No money out for this period.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
