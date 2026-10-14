@php
$tabs = [
    'quotations' => ['Quotations', 'tools.quotations'],
    'invoices' => ['Invoicing', 'tools.invoices'],
    'receipts' => ['Receipts', 'tools.receipts'],
    'pos' => ['POS', 'tools.pos'],
    'inventory' => ['Inventory', 'tools.inventory'],
    'customers' => ['Customers', 'tools.customers'],
    'suppliers' => ['Suppliers', 'tools.suppliers'],
    'expenses' => ['Expenses', 'tools.expenses'],
    'reports' => ['Reports', 'tools.reports'],
    'cashflow' => ['Cash Flow', 'tools.cashflow'],
    'pnl' => ['P&L', 'tools.pnl'],
    'balancesheet' => ['Balance Sheet', 'tools.balancesheet'],
];
@endphp
<div class="seg" role="tablist" aria-label="Business Tools">
    @foreach($tabs as $key => [$label, $route])
    <a href="{{ route($route) }}" role="tab" aria-selected="{{ ($active ?? '') === $key ? 'true' : 'false' }}"
        class="shrink-0 rounded-full px-4 py-2 text-sm font-bold transition {{ ($active ?? '') === $key ? 'bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 shadow' : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-white' }}">{{ $label }}</a>
    @endforeach
</div>
