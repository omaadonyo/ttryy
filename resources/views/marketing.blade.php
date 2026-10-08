<x-layouts::app :title="__('Marketing tool')">
    <div class="flex h-full w-full flex-1 flex-col gap-4">
        <div>
            <flux:heading size="xl">Marketing tool</flux:heading>
            <flux:text class="mt-1">Write one message, personalize it per contact with <span class="font-mono text-xs">{name}</span>, <span class="font-mono text-xs">{business}</span>, <span class="font-mono text-xs">{need}</span> — then open each chat on WhatsApp and send.</flux:text>
        </div>

        <div class="rounded-xl bg-white dark:bg-white/[.04] p-5 shadow-[0_12px_32px_-12px_rgba(10,10,12,.18)] dark:shadow-[0_12px_32px_-12px_rgba(0,0,0,.7)]">
            <label class="text-xs font-semibold">Your message template</label>
            <textarea id="mkt-template" rows="4" class="mt-1 w-full rounded-xl border border-neutral-300 dark:border-white/15 bg-white dark:bg-white/5 px-4 py-3 text-sm outline-none focus:border-[#9e005d]" placeholder="Hi {name}, this is Jane from Savanna Build. We help businesses like {business} with quality construction. Are you open to a quick quote for {need}?">Hi {name}, this is {{ auth()->user()->name }}. We help businesses like {business} grow. Are you open to a quick chat about {need}?</textarea>
            <div class="mt-3 flex items-center gap-3 flex-wrap">
                <label class="text-xs font-semibold text-zinc-500">Send to:</label>
                <select id="mkt-filter" class="rounded-xl border border-neutral-300 dark:border-white/15 bg-white dark:bg-white/5 px-4 py-2 text-sm outline-none focus:border-[#9e005d]">
                    <option value="">All saved contacts ({{ $contacts->count() }})</option>
                    @foreach($contacts->unique('niche') as $c)
                    <option value="{{ $c->niche }}">{{ $c->niche }}</option>
                    @endforeach
                </select>
                <span class="text-xs text-zinc-500" id="mkt-count"></span>
            </div>
        </div>

        <div class="rounded-xl bg-white dark:bg-white/[.04] p-5 shadow-[0_12px_32px_-12px_rgba(10,10,12,.18)] dark:shadow-[0_12px_32px_-12px_rgba(0,0,0,.7)]">
            @if($contacts->isEmpty())
                <x-empty-state icon="users" title="No contacts to market to" message="Scrape prospects and save them first — they will appear here ready for outreach." :actionUrl="route('scraper.index')" actionLabel="Open scraper" />
            @else
            <div id="mkt-list" class="space-y-3"></div>
            @endif
        </div>
    </div>

<script>
const MKT_CONTACTS = @json($contactsJson);
function mktFill(tpl, c){
  return (tpl || '').replaceAll('{name}', (c.name || '').split(' ')[0] || 'there')
    .replaceAll('{business}', c.business || 'your business')
    .replaceAll('{need}', c.need || c.type || 'our services')
    .replaceAll('{phone}', c.phone || '');
}
function mktRender(){
  const tpl = document.getElementById('mkt-template').value;
  const niche = document.getElementById('mkt-filter').value;
  const list = document.getElementById('mkt-list');
  if(!list) return;
  const shown = MKT_CONTACTS.filter(c => !niche || c.niche === niche);
  document.getElementById('mkt-count').textContent = shown.length + ' selected';
  list.innerHTML = shown.map(c => {
    const msg = mktFill(tpl, c);
    const href = c.wa ? `https://wa.me/${c.wa}?text=${encodeURIComponent(msg)}` : '#';
    return `<div class="rounded-xl bg-zinc-50 dark:bg-white/5 p-4">`
      + `<div class="flex items-center justify-between gap-3 flex-wrap">`
      + `<div class="min-w-0"><p class="font-bold text-sm truncate">${c.business}</p><p class="text-xs text-zinc-500">${c.name || ''} · ${c.phone || 'no phone'}</p></div>`
      + (c.wa
        ? `<a target="_blank" href="${href}" class="shrink-0 bg-[#9e005d] hover:bg-[#7e0049] text-white text-xs font-bold px-4 py-2 rounded-full transition">Send on WhatsApp</a>`
        : `<span class="text-xs text-zinc-400">No phone saved</span>`)
      + `</div><p class="mt-2 text-[13px] text-zinc-600 dark:text-zinc-400 whitespace-pre-wrap">${msg.replaceAll('<','&lt;')}</p></div>`;
  }).join('') || '<p class="text-sm text-zinc-500 py-4 text-center">No contacts in this niche.</p>';
}
document.getElementById('mkt-template')?.addEventListener('input', mktRender);
document.getElementById('mkt-filter')?.addEventListener('change', mktRender);
mktRender();
</script>
</x-layouts::app>
