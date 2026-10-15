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
<style>
  .bt-tabs-scroll{ scrollbar-width:none; -ms-overflow-style:none; scroll-behavior:smooth; }
  .bt-tabs-scroll::-webkit-scrollbar{ display:none; }
  .bt-tabs-fade{ transition:opacity .25s ease; }
</style>
<div class="bt-tabs relative" role="tablist" aria-label="Business Tools">
    <button type="button" class="bt-tabs-prev absolute left-0 top-1/2 z-10 hidden h-9 w-9 -translate-y-1/2 place-items-center rounded-full border border-zinc-200 bg-white shadow-lg dark:border-white/15 dark:bg-[#141416]" aria-label="Scroll tabs left">
        <svg class="mx-auto h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M15 6l-6 6 6 6"/></svg>
    </button>
    <div class="bt-tabs-fade-l bt-tabs-fade pointer-events-none absolute inset-y-0 left-0 z-[5] w-10 bg-gradient-to-r from-[#f4f4f5] to-transparent dark:from-zinc-950"></div>
    <div class="bt-tabs-scroll seg flex max-w-full items-center gap-1 overflow-x-auto p-1">
        @foreach($tabs as $key => [$label, $route])
        <a href="{{ route($route) }}" role="tab" aria-selected="{{ ($active ?? '') === $key ? 'true' : 'false' }}" data-bt-tab="{{ $key }}"
            class="shrink-0 rounded-full px-4 py-2 text-sm font-bold whitespace-nowrap transition {{ ($active ?? '') === $key ? 'bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 shadow' : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-white' }}">{{ $label }}</a>
        @endforeach
    </div>
    <div class="bt-tabs-fade-r bt-tabs-fade pointer-events-none absolute inset-y-0 right-0 z-[5] w-10 bg-gradient-to-l from-[#f4f4f5] to-transparent dark:from-zinc-950"></div>
    <button type="button" class="bt-tabs-next absolute right-0 top-1/2 z-10 hidden h-9 w-9 -translate-y-1/2 place-items-center rounded-full border border-zinc-200 bg-white shadow-lg dark:border-white/15 dark:bg-[#141416]" aria-label="Scroll tabs right">
        <svg class="mx-auto h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M9 6l6 6-6 6"/></svg>
    </button>
</div>
<script>
(function(){
    document.querySelectorAll('.bt-tabs').forEach(root => {
        const track = root.querySelector('.bt-tabs-scroll');
        const prev = root.querySelector('.bt-tabs-prev');
        const next = root.querySelector('.bt-tabs-next');
        const fadeL = root.querySelector('.bt-tabs-fade-l');
        const fadeR = root.querySelector('.bt-tabs-fade-r');
        if(!track) return;
        function paint(){
            const max = track.scrollWidth - track.clientWidth;
            const canLeft = track.scrollLeft > 8;
            const canRight = track.scrollLeft < max - 8;
            prev.classList.toggle('hidden', !canLeft);
            prev.classList.toggle('grid', canLeft);
            next.classList.toggle('hidden', !canRight);
            next.classList.toggle('grid', canRight);
            fadeL.style.opacity = canLeft ? '1' : '0';
            fadeR.style.opacity = canRight ? '1' : '0';
        }
        prev.addEventListener('click', () => track.scrollBy({ left: -220 }));
        next.addEventListener('click', () => track.scrollBy({ left: 220 }));
        track.addEventListener('scroll', paint, { passive: true });
        window.addEventListener('resize', paint);
        paint();
        const current = track.querySelector('[aria-selected="true"]');
        if(current) current.scrollIntoView({ block: 'nearest', inline: 'center' });
        setTimeout(paint, 60);
    });
})();
</script>
