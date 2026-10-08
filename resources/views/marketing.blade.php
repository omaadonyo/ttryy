<x-layouts::app :title="__('Marketing tool')">
    <div class="flex h-full w-full flex-1 flex-col gap-4">
        <div class="flex items-end justify-between gap-3 flex-wrap">
            <div>
                <flux:heading size="xl">Marketing tool</flux:heading>
                <flux:text class="mt-1">AI sales messages, templates, saved contacts and growth communities.</flux:text>
            </div>
            <a href="{{ route('wallet.index') }}" class="inline-flex items-center gap-2 rounded-full bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 text-sm font-bold px-4 py-2">
                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 10h18M7 15h4"/></svg>
                <span id="token-balance">{{ number_format($balance) }}</span>&nbsp;tokens
            </a>
        </div>

        <div class="rounded-xl bg-white dark:bg-white/[.04] p-5 shadow-[0_12px_32px_-12px_rgba(10,10,12,.18)] dark:shadow-[0_12px_32px_-12px_rgba(0,0,0,.7)]">
            <div class="flex items-center justify-between gap-2 flex-wrap">
                <flux:heading>AI sales writer <span class="text-xs font-semibold text-zinc-500">· {{ $aiCost }} tokens per message</span></flux:heading>
                <span id="ai-status" class="text-xs font-semibold text-zinc-500"></span>
            </div>
            <div class="mt-3 grid sm:grid-cols-[1fr_160px_160px_auto] gap-2">
                <select id="ai-contact" class="rounded-xl border border-neutral-300 dark:border-white/15 bg-white dark:bg-white/5 px-4 py-2.5 text-sm outline-none focus:border-[#9e005d]">
                    <option value="">General message (no contact)</option>
                    @foreach($contacts as $c)
                    <option value="{{ $c->id }}">{{ $c->name }} — {{ $c->contact }}</option>
                    @endforeach
                </select>
                <select id="ai-tone" class="rounded-xl border border-neutral-300 dark:border-white/15 bg-white dark:bg-white/5 px-4 py-2.5 text-sm outline-none focus:border-[#9e005d]">
                    <option value="professional">Professional</option>
                    <option value="friendly">Friendly</option>
                    <option value="urgent">Urgent</option>
                </select>
                <select id="ai-goal" class="rounded-xl border border-neutral-300 dark:border-white/15 bg-white dark:bg-white/5 px-4 py-2.5 text-sm outline-none focus:border-[#9e005d]">
                    <option value="first_outreach">First outreach</option>
                    <option value="follow_up">Follow-up</option>
                    <option value="closing">Closing</option>
                </select>
                <flux:button id="ai-generate" variant="primary" class="!bg-[#9e005d] hover:!bg-[#7e0049]">Generate</flux:button>
            </div>
            <div id="ai-result" class="hidden mt-3 rounded-xl bg-zinc-50 dark:bg-white/5 p-4">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-xs font-semibold text-zinc-500" id="ai-source">Generated message</p>
                    <button id="ai-use" class="text-xs font-bold text-[#9e005d] hover:underline">Use in composer ↓</button>
                </div>
                <p id="ai-message" class="mt-1 text-sm whitespace-pre-wrap"></p>
            </div>
        </div>

        <div class="rounded-xl bg-white dark:bg-white/[.04] p-5 shadow-[0_12px_32px_-12px_rgba(10,10,12,.18)] dark:shadow-[0_12px_32px_-12px_rgba(0,0,0,.7)]">
            <flux:heading>Message composer</flux:heading>
            <flux:text class="mt-1 text-sm">Use <span class="font-mono text-xs">{name}</span>, <span class="font-mono text-xs">{business}</span>, <span class="font-mono text-xs">{need}</span> — they personalize per contact.</flux:text>
            <textarea id="mkt-template" rows="4" class="mt-3 w-full rounded-xl border border-neutral-300 dark:border-white/15 bg-white dark:bg-white/5 px-4 py-3 text-sm outline-none focus:border-[#9e005d]" placeholder="Hi {name}, ...">Hi {name}, this is {{ auth()->user()->name }}. We help businesses like {business} grow. Are you open to a quick chat about {need}?</textarea>
            <div class="mt-3 flex items-center gap-3 flex-wrap">
                <select id="mkt-template-pick" class="rounded-xl border border-neutral-300 dark:border-white/15 bg-white dark:bg-white/5 px-4 py-2 text-sm outline-none focus:border-[#9e005d] max-w-full">
                    <option value="">Load a template…</option>
                    @foreach($templates as $t)
                    <option value="{{ $t->id }}" data-body="{{ e($t->body) }}">{{ $t->name }}{{ $t->user_id ? '' : ' · built-in' }}</option>
                    @endforeach
                </select>
                <button id="mkt-save-tpl" class="text-xs font-bold text-[#9e005d] hover:underline">Save current as template</button>
                <select id="mkt-filter" class="rounded-xl border border-neutral-300 dark:border-white/15 bg-white dark:bg-white/5 px-4 py-2 text-sm outline-none focus:border-[#9e005d]">
                    <option value="">All saved contacts ({{ $contacts->count() }})</option>
                    @foreach($contacts->unique('niche') as $c)
                    <option value="{{ $c->niche }}">{{ $c->niche }}</option>
                    @endforeach
                </select>
                <span class="text-xs text-zinc-500" id="mkt-count"></span>
            </div>
            <div id="my-templates" class="mt-2 flex flex-wrap gap-2">
                @foreach($templates->whereNotNull('user_id') as $t)
                <span class="inline-flex items-center gap-1.5 text-xs bg-zinc-100 dark:bg-white/10 rounded-full pl-3 pr-1.5 py-1" data-tpl="{{ $t->id }}">{{ $t->name }}<button data-del-tpl="{{ $t->id }}" class="text-zinc-400 hover:text-red-600 font-bold px-1" aria-label="Delete template">×</button></span>
                @endforeach
            </div>
        </div>

        <div class="rounded-xl bg-white dark:bg-white/[.04] p-5 shadow-[0_12px_32px_-12px_rgba(10,10,12,.18)] dark:shadow-[0_12px_32px_-12px_rgba(0,0,0,.7)]">
            <flux:heading>WhatsApp growth communities</flux:heading>
            <flux:text class="mt-1 text-sm">Join buyer and seller groups in your niche. Unlocking a join link costs tokens — once unlocked, it is yours.</flux:text>
            <div class="mt-4 grid sm:grid-cols-2 gap-3" id="group-grid">
                @forelse($groups as $g)
                @php $unlocked = in_array($g->id, $unlockedGroupIds); @endphp
                <div class="rounded-xl bg-zinc-50 dark:bg-white/5 p-4" data-group="{{ $g->id }}">
                    <div class="flex items-center justify-between gap-2">
                        <p class="font-bold text-sm">{{ $g->name }}</p>
                        <span class="text-[11px] font-semibold text-zinc-500 shrink-0">{{ number_format($g->member_count) }} members</span>
                    </div>
                    <p class="text-xs text-zinc-500 mt-1">{{ $g->niche }}</p>
                    <p class="text-[13px] text-zinc-600 dark:text-zinc-400 mt-1">{{ $g->description }}</p>
                    <div class="mt-3 group-action">
                        @if($unlocked)
                        <a target="_blank" href="{{ $g->invite_link }}" class="inline-block bg-[#9e005d] hover:bg-[#7e0049] text-white text-xs font-bold px-4 py-2 rounded-full transition">Join group →</a>
                        @else
                        <button data-unlock-group="{{ $g->id }}" class="border border-zinc-300 dark:border-white/20 hover:border-zinc-950 dark:hover:border-white text-xs font-bold px-4 py-2 rounded-full transition">Unlock link · {{ $g->token_cost }} tokens</button>
                        @endif
                    </div>
                </div>
                @empty
                <p class="text-sm text-zinc-500 col-span-2 py-4 text-center">No groups available right now — check back soon.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-xl bg-white dark:bg-white/[.04] p-5 shadow-[0_12px_32px_-12px_rgba(10,10,12,.18)] dark:shadow-[0_12px_32px_-12px_rgba(0,0,0,.7)]">
            <flux:heading>Your outreach list</flux:heading>
            @if($contacts->isEmpty())
                <x-empty-state icon="users" title="No contacts to market to" message="Scrape prospects and save them first — they will appear here ready for outreach." :actionUrl="route('scraper.index')" actionLabel="Open scraper" />
            @else
            <div id="mkt-list" class="mt-3 space-y-3"></div>
            @endif
        </div>
    </div>

<script>
const MKT_CONTACTS = @json($contactsJson);
const MKT_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content || '';
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
  list.innerHTML = shown.map((c, i) => {
    const msg = mktFill(tpl, c);
    const href = c.wa ? `https://wa.me/${c.wa}?text=${encodeURIComponent(msg)}` : '#';
    return `<div class="rounded-xl bg-zinc-50 dark:bg-white/5 p-4">`
      + `<div class="flex items-center justify-between gap-3 flex-wrap">`
      + `<div class="min-w-0"><p class="font-bold text-sm truncate">${c.business}</p><p class="text-xs text-zinc-500">${c.name || ''} · ${c.phone || 'no phone'}</p></div>`
      + `<div class="flex gap-2 shrink-0">`
      + `<button data-copy="${i}" class="border border-zinc-300 dark:border-white/20 text-xs font-bold px-3 py-2 rounded-full">Copy</button>`
      + (c.wa
        ? `<a target="_blank" href="${href}" class="bg-[#9e005d] hover:bg-[#7e0049] text-white text-xs font-bold px-4 py-2 rounded-full transition">Send on WhatsApp</a>`
        : `<span class="text-xs text-zinc-400">No phone saved</span>`)
      + `</div></div><p class="mt-2 text-[13px] text-zinc-600 dark:text-zinc-400 whitespace-pre-wrap" data-msg="${i}">${msg.replaceAll('<','&lt;')}</p></div>`;
  }).join('') || '<p class="text-sm text-zinc-500 py-4 text-center">No contacts in this niche.</p>';
  list.querySelectorAll('[data-copy]').forEach(b => b.addEventListener('click', () => {
    const p = list.querySelector(`[data-msg="${b.dataset.copy}"]`);
    if(p){ navigator.clipboard?.writeText(p.textContent); b.textContent = 'Copied!'; setTimeout(() => b.textContent = 'Copy', 1200); }
  }));
}
document.getElementById('mkt-template')?.addEventListener('input', mktRender);
document.getElementById('mkt-filter')?.addEventListener('change', mktRender);
mktRender();
document.getElementById('mkt-template-pick')?.addEventListener('change', e => {
  const opt = e.target.selectedOptions[0];
  if(opt && opt.dataset.body){ document.getElementById('mkt-template').value = opt.dataset.body; mktRender(); }
  e.target.value = '';
});
document.getElementById('mkt-save-tpl')?.addEventListener('click', async () => {
  const body = document.getElementById('mkt-template').value.trim();
  if(!body) return;
  const name = prompt('Name this template:');
  if(!name) return;
  const res = await fetch("{{ route('marketing.templates.store') }}", { method: 'POST',
    headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': MKT_TOKEN, 'X-Requested-With': 'XMLHttpRequest' },
    body: JSON.stringify({ name, body }) });
  if(res.ok){ location.reload(); } else { alert('Could not save template.'); }
});
document.querySelectorAll('[data-del-tpl]').forEach(b => b.addEventListener('click', async () => {
  if(!confirm('Delete this template?')) return;
  await fetch(`/dashboard/marketing/templates/${b.dataset.delTpl}`, { method: 'DELETE',
    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': MKT_TOKEN, 'X-Requested-With': 'XMLHttpRequest' } });
  location.reload();
}));
document.getElementById('ai-generate')?.addEventListener('click', async () => {
  const btn = document.getElementById('ai-generate'), status = document.getElementById('ai-status');
  const box = document.getElementById('ai-result'), out = document.getElementById('ai-message'), src = document.getElementById('ai-source');
  btn.disabled = true; btn.classList.add('opacity-60'); status.textContent = 'Writing…';
  try{
    const res = await fetch("{{ route('marketing.generate') }}", { method: 'POST',
      headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': MKT_TOKEN, 'X-Requested-With': 'XMLHttpRequest' },
      body: JSON.stringify({ contact_id: document.getElementById('ai-contact').value || null,
        tone: document.getElementById('ai-tone').value, goal: document.getElementById('ai-goal').value }) });
    const j = await res.json();
    if(!res.ok) throw new Error(j.message || 'Generation failed.');
    out.textContent = j.message;
    src.textContent = (j.source === 'ai' ? 'Written by AI' : 'From the Ttryy sales library') + ' · 10 tokens used';
    box.classList.remove('hidden');
    const bal = document.getElementById('token-balance');
    if(bal && typeof j.balance !== 'undefined') bal.textContent = Number(j.balance).toLocaleString('en-US');
  }catch(e){ status.textContent = e.message || 'Generation failed.'; setTimeout(() => status.textContent = '', 4000); }
  btn.disabled = false; btn.classList.remove('opacity-60');
});
document.getElementById('ai-use')?.addEventListener('click', () => {
  document.getElementById('mkt-template').value = document.getElementById('ai-message').textContent;
  mktRender();
  document.getElementById('mkt-template').scrollIntoView({ behavior: 'smooth', block: 'center' });
});
document.querySelectorAll('[data-unlock-group]').forEach(b => b.addEventListener('click', async () => {
  b.disabled = true; b.textContent = 'Unlocking…';
  try{
    const res = await fetch(`/dashboard/groups/${b.dataset.unlockGroup}/unlock`, { method: 'POST',
      headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': MKT_TOKEN, 'X-Requested-With': 'XMLHttpRequest' } });
    const j = await res.json();
    if(!res.ok) throw new Error(j.message || 'Unlock failed.');
    const box = b.closest('.group-action');
    box.innerHTML = `<a target="_blank" href="${j.invite_link}" class="inline-block bg-[#9e005d] hover:bg-[#7e0049] text-white text-xs font-bold px-4 py-2 rounded-full transition">Join group →</a>`;
    const bal = document.getElementById('token-balance');
    if(bal && typeof j.balance !== 'undefined') bal.textContent = Number(j.balance).toLocaleString('en-US');
  }catch(e){ b.disabled = false; b.textContent = 'Unlock link'; alert(e.message || 'Unlock failed.'); }
}));
</script>
</x-layouts::app>
