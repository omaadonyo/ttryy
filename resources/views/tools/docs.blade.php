@php
$titles = ['quotation' => ['Quotations', 'Quote a customer — convert to invoice in one tap when they accept.'], 'invoice' => ['Invoicing', 'Bill customers. Stock deducts automatically.'], 'receipt' => ['Receipts', 'Proof of every shilling received.']];
[$title, $sub] = $titles[$type];
$createRoute = route('tools.doc.create', ['type' => str($type)->plural()]);
$statuses = $type === 'quotation' ? ['draft', 'sent', 'accepted', 'declined'] : ($type === 'invoice' ? ['unpaid', 'partial', 'paid'] : ['posted']);
@endphp
<x-layouts::app :title="__('Business Tools — '.$title)">
    @include('partials.toast')
    <div class="flex w-full flex-col gap-5">
        <x-page-head :title="$title" :sub="$sub">
            <x-slot:actions>
                @if($type === 'invoice')<span class="pill-amber">UGX {{ number_format($totals['outstanding']) }} outstanding</span>@endif
                <a href="{{ $createRoute }}" class="btn-brand btn-sm">New {{ rtrim($title, 's') }}</a>
            </x-slot:actions>
        </x-page-head>
        @include('tools._tabs', ['active' => $active])

        @if(session('status'))
        <div class="card card-pad border-emerald-500/40 bg-emerald-500/5 text-sm font-semibold text-emerald-700 dark:text-emerald-300">{{ session('status') }}</div>
        @endif
        @if($errors->any())
        <div class="card card-pad border-red-300 bg-red-50 text-sm text-red-700 dark:bg-red-500/10 dark:text-red-300">{{ $errors->first() }}</div>
        @endif

        <div class="card card-pad">
            <form method="GET" class="flex flex-col gap-3 sm:flex-row">
                <input name="q" value="{{ $q }}" placeholder="Search number or customer…" class="field">
                <select name="status" class="field sm:max-w-48">
                    <option value="">All statuses</option>
                    @foreach($statuses as $s)<option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>@endforeach
                </select>
                <button class="btn-brand">Filter</button>
            </form>
        </div>

        <div class="card card-pad">
            @if($docs->isEmpty())
            <x-empty-state icon="shopping-bag" :title="'No '.$title.' yet'" :message="'Create your first one — it takes seconds.'" />
            @else
            <div class="tbl-wrap rounded-none! border-0! shadow-none!">
                <table class="tbl">
                    <thead><tr><th>Number</th><th>Customer</th><th>Date</th><th>Status</th><th class="text-right">Total</th><th></th></tr></thead>
                    <tbody>
                        @foreach($docs as $d)
                        <tr>
                            <td><a href="{{ route('tools.doc.show', $d) }}" class="font-bold hover:underline">{{ $d->number }}</a></td>
                            <td>{{ $d->contact?->name ?? 'Walk-in' }}</td>
                            <td class="whitespace-nowrap">{{ $d->issue_date->format('d M Y') }}</td>
                            <td>
                                @if(in_array($d->status, ['paid', 'posted', 'accepted']))<span class="pill-green">{{ $d->status }}</span>
                                @elseif(in_array($d->status, ['partial', 'sent']))<span class="pill-amber">{{ $d->status }}</span>
                                @elseif($d->isOverdue())<span class="pill-red">overdue</span>
                                @elseif($d->status === 'declined')<span class="pill-red">declined</span>
                                @else<span class="pill-zinc">{{ $d->status }}</span>@endif
                            </td>
                            <td class="text-right font-bold">UGX {{ number_format($d->total) }}</td>
                            <td class="text-right"><a href="{{ route('tools.doc.show', $d) }}" class="text-xs font-bold text-brand-700 hover:underline dark:text-brand-300">Open →</a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $docs->links() }}</div>
            @endif
        </div>
    </div>
</x-layouts::app>
