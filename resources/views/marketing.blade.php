<x-layouts::app :title="__('Marketing tool')">
    @include('partials.custom-select')
    <style>
      .cs-btn svg{ transition:transform .25s ease; }
      .cs-btn[aria-expanded="true"] svg{ transform:rotate(180deg); }
      .cs-wrap:focus-within .cs-btn{ border-color:#9e005d; }
    </style>
    <div class="flex h-full w-full flex-1 flex-col gap-4">
        <div class="flex items-end justify-between gap-3 flex-wrap">
            <div>
                <flux:heading size="xl">Marketing tool</flux:heading>
                <flux:text class="mt-1">Outreach, email campaigns, templates and growth communities.</flux:text>
            </div>
            <a href="{{ route('wallet.index') }}" class="inline-flex items-center gap-2 rounded-full bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 text-sm font-bold px-4 py-2" wire:navigate>
                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 10h18M7 15h4"/></svg>
                <span id="token-balance">{{ number_format($balance) }}</span>&nbsp;tokens
            </a>
        </div>

        <div class="inline-flex self-start rounded-full bg-zinc-100 dark:bg-white/10 p-1" role="tablist" aria-label="Marketing tools">
            <button data-mtab="outreach" role="tab" class="mtab rounded-full px-4 py-2 text-[13px] font-bold transition">WhatsApp outreach</button>
            <button data-mtab="email" role="tab" class="mtab rounded-full px-4 py-2 text-[13px] font-bold transition">Email campaigns</button>
            <button data-mtab="community" role="tab" class="mtab rounded-full px-4 py-2 text-[13px] font-bold transition">Communities</button>
            <button data-mtab="templates" role="tab" class="mtab rounded-full px-4 py-2 text-[13px] font-bold transition">Templates</button>
        </div>

        <!-- TAB: WhatsApp outreach -->
        <div data-mtab-panel="outreach" class="flex flex-col gap-4">
            <div class="rounded-xl bg-white dark:bg-white/[.04] p-5 shadow-[0_12px_32px_-12px_rgba(10,10,12,.18)] dark:shadow-[0_12px_32px_-12px_rgba(0,0,0,.7)]">
                <div class="flex items-center justify-between gap-2 flex-wrap">
                    <flux:heading>AI sales writer <span class="text-xs font-semibold text-zinc-500">· {{ $aiCost }} tokens per message</span></flux:heading>
                    <span id="ai-status" class="text-xs font-semibold text-zinc-500"></span>
                </div>
                <div class="mt-3 grid sm:grid-cols-[1fr_160px_160px_auto] gap-2">
                    <select id="ai-contact" class="rounded-[0.575rem] border border-neutral-300 dark:border-white/15 bg-white dark:bg-white/5 px-4 py-2.5 text-sm outline-none focus:border-[#9e005d]">
                        <option value="">General message (no contact)</option>
                        @foreach($contacts as $c)
                        <option value="{{ $c->id }}">{{ $c->name }} — {{ $c->contact }}</option>
                        @endforeach
                    </select>
                    <select id="ai-tone" class="rounded-[0.575rem] border border-neutral-300 dark:border-white/15 bg-white dark:bg-white/5 px-4 py-2.5 text-sm outline-none focus:border-[#9e005d]">
                        <option value="professional">Professional</option>
                        <option value="friendly">Friendly</option>
                        <option value="urgent">Urgent</option>
                    </select>
                    <select id="ai-goal" class="rounded-[0.575rem] border border-neutral-300 dark:border-white/15 bg-white dark:bg-white/5 px-4 py-2.5 text-sm outline-none focus:border-[#9e005d]">
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
                <textarea id="mkt-template" rows="4" class="mt-3 w-full rounded-[0.575rem] border border-neutral-300 dark:border-white/15 bg-white dark:bg-white/5 px-4 py-3 text-sm outline-none focus:border-[#9e005d]" placeholder="Hi {name}, ...">Hi {name}, this is {{ auth()->user()->name }}. We help businesses like {business} grow. Are you open to a quick chat about {need}?</textarea>
                <div class="mt-3 flex items-center gap-3 flex-wrap">
                    <select id="mkt-template-pick" class="rounded-[0.575rem] border border-neutral-300 dark:border-white/15 bg-white dark:bg-white/5 px-4 py-2 text-sm outline-none focus:border-[#9e005d] max-w-full">
                        <option value="">Load a template…</option>
                        @foreach($templates as $t)
                        <option value="{{ $t->id }}" data-body="{{ e($t->body) }}">{{ $t->name }}{{ $t->user_id ? '' : ' · built-in' }}</option>
                        @endforeach
                    </select>
                    <button id="mkt-save-tpl" class="text-xs font-bold text-[#9e005d] hover:underline">Save current as template</button>
                    <select id="mkt-filter" class="rounded-[0.575rem] border border-neutral-300 dark:border-white/15 bg-white dark:bg-white/5 px-4 py-2 text-sm outline-none focus:border-[#9e005d]">
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
                <flux:heading>Your outreach list</flux:heading>
                @if($contacts->isEmpty())
                    <x-empty-state icon="users" title="No contacts to market to" message="Scrape prospects and save them first — they will appear here ready for outreach." :actionUrl="route('scraper.index')" actionLabel="Open scraper" />
                @else
                <div id="mkt-list" class="mt-3 space-y-3"></div>
                @endif
            </div>
        </div>

        <!-- TAB: Email campaigns -->
        <div data-mtab-panel="email" class="hidden flex-col gap-4">
            <div class="rounded-xl bg-white dark:bg-white/[.04] p-5 shadow-[0_12px_32px_-12px_rgba(10,10,12,.18)] dark:shadow-[0_12px_32px_-12px_rgba(0,0,0,.7)]">
                <flux:heading>New email campaign</flux:heading>
                <flux:text class="mt-1 text-sm">Sends from your configured mail account. Opens are tracked per recipient — max 50 recipients per send.</flux:text>
                <div class="mt-3 grid gap-3">
                    <div><label class="text-xs font-semibold">Subject *</label><input id="em-subject" type="text" placeholder="Quick idea for {business}" class="mt-1 w-full rounded-[0.575rem] border border-neutral-300 dark:border-white/15 bg-white dark:bg-white/5 px-4 py-2.5 text-sm outline-none focus:border-[#9e005d]"></div>
                    <div><label class="text-xs font-semibold">Message * <span class="font-normal text-zinc-500">({name} personalizes per recipient)</span></label><textarea id="em-body" rows="5" class="mt-1 w-full rounded-[0.575rem] border border-neutral-300 dark:border-white/15 bg-white dark:bg-white/5 px-4 py-3 text-sm outline-none focus:border-[#9e005d]" placeholder="Hi {name}, ..."></textarea></div>
                    <div class="grid sm:grid-cols-2 gap-3">
                        <div><label class="text-xs font-semibold">To saved contacts with email</label><div id="em-contacts" class="mt-1 max-h-44 overflow-y-auto rounded-xl border border-neutral-200 dark:border-white/10 divide-y divide-neutral-100 dark:divide-white/5">
                            @forelse($contacts->whereNotNull('email') as $c)
                            <label class="flex items-center gap-2.5 px-4 py-2 text-sm cursor-pointer hover:bg-zinc-50 dark:hover:bg-white/5"><input type="checkbox" value="{{ $c->id }}" class="em-contact accent-[#9e005d]"><span class="truncate">{{ $c->name }} <span class="text-zinc-500">· {{ $c->email }}</span></span></label>
                            @empty
                            <p class="px-4 py-4 text-xs text-zinc-500">No saved contacts have emails yet — add them below or type addresses manually.</p>
                            @endforelse
                        </div></div>
                        <div><label class="text-xs font-semibold">Or type addresses (comma separated)</label><textarea id="em-manual" rows="4" placeholder="jane@company.com, brian@business.co.ug" class="mt-1 w-full rounded-[0.575rem] border border-neutral-300 dark:border-white/15 bg-white dark:bg-white/5 px-4 py-3 text-sm outline-none focus:border-[#9e005d]"></textarea></div>
                    </div>
                    <div class="flex items-center gap-3">
                        <flux:button id="em-send" variant="primary" class="!bg-[#9e005d] hover:!bg-[#7e0049]">Send campaign</flux:button>
                        <span id="em-status" class="text-xs font-semibold text-zinc-500"></span>
                    </div>
                </div>
            </div>
            <div class="rounded-xl bg-white dark:bg-white/[.04] p-5 shadow-[0_12px_32px_-12px_rgba(10,10,12,.18)] dark:shadow-[0_12px_32px_-12px_rgba(0,0,0,.7)]">
                <flux:heading>Past campaigns</flux:heading>
                <div class="mt-2 divide-y divide-neutral-200 dark:divide-white/10">
                    @forelse($campaigns as $cp)
                    <a href="{{ route('marketing.campaigns.show', $cp) }}" class="flex items-center justify-between gap-3 py-3 hover:bg-zinc-50 dark:hover:bg-white/5 rounded-lg px-2 -mx-2">
                        <div class="min-w-0"><p class="font-bold text-sm truncate">{{ $cp->subject }}</p><p class="text-xs text-zinc-500">{{ $cp->created_at->format('d M Y') }} · {{ $cp->total }} recipients</p></div>
                        <span class="text-xs font-bold shrink-0">{{ $cp->sent }} sent · {{ $cp->recipients()->whereNotNull('opened_at')->count() }} read</span>
                    </a>
                    @empty
                    <p class="text-sm text-zinc-500 py-4 text-center">No campaigns yet — your reports will land here.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- TAB: Communities -->
        <div data-mtab-panel="community" class="hidden flex-col gap-4">
            <div class="rounded-xl bg-white dark:bg-white/[.04] p-5 shadow-[0_12px_32px_-12px_rgba(10,10,12,.18)] dark:shadow-[0_12px_32px_-12px_rgba(0,0,0,.7)]">
            <flux:heading>WhatsApp growth communities</flux:heading>
            <flux:text class="mt-1 text-sm">Active Ugandan business groups by niche. Unlock a join link once with tokens — it is yours forever.</flux:text>
            <div class="mt-3 grid sm:grid-cols-[1fr_200px_160px] gap-2">
                <input id="grp-search" type="text" placeholder="Search groups…" class="rounded-[0.575rem] border border-neutral-300 dark:border-white/15 bg-white dark:bg-white/5 px-4 py-2.5 text-sm outline-none focus:border-[#9e005d]">
                <select id="grp-niche" class="rounded-[0.575rem] border border-neutral-300 dark:border-white/15 bg-white dark:bg-white/5 px-4 py-2.5 text-sm outline-none focus:border-[#9e005d]">
                    <option value="">All niches</option>
                    @foreach($groups->unique('niche') as $g)
                    <option value="{{ $g->niche }}">{{ $g->niche }}</option>
                    @endforeach
                </select>
                <select id="grp-sort" class="rounded-[0.575rem] border border-neutral-300 dark:border-white/15 bg-white dark:bg-white/5 px-4 py-2.5 text-sm outline-none focus:border-[#9e005d]">
                    <option value="members">Most members</option>
                    <option value="name">Name A–Z</option>
                </select>
            </div>
                <div class="mt-4 grid sm:grid-cols-2 gap-3" id="group-grid">
                    @forelse($groups as $g)
                    @php $unlocked = in_array($g->id, $unlockedGroupIds); @endphp
                    <div class="rounded-xl bg-zinc-50 dark:bg-white/5 p-4" data-group="{{ $g->id }}" data-members="{{ $g->member_count }}" data-search="{{ strtolower($g->name.' '.$g->niche.' '.($g->description ?? '')) }}" data-niche="{{ $g->niche }}">
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
                <flux:heading>Suggest a group</flux:heading>
                <flux:text class="mt-1 text-sm">Know an active Ugandan business group? Send it in — we review and list the good ones.</flux:text>
                <div class="mt-3 grid sm:grid-cols-2 gap-3">
                    <input id="sg-name" type="text" placeholder="Group name *" class="rounded-[0.575rem] border border-neutral-300 dark:border-white/15 bg-white dark:bg-white/5 px-4 py-2.5 text-sm outline-none focus:border-[#9e005d]">
                    <input id="sg-niche" type="text" placeholder="Niche / category" class="rounded-[0.575rem] border border-neutral-300 dark:border-white/15 bg-white dark:bg-white/5 px-4 py-2.5 text-sm outline-none focus:border-[#9e005d]">
                    <input id="sg-link" type="url" placeholder="Invite link (https://…) *" class="rounded-[0.575rem] border border-neutral-300 dark:border-white/15 bg-white dark:bg-white/5 px-4 py-2.5 text-sm outline-none focus:border-[#9e005d] sm:col-span-2">
                    <textarea id="sg-desc" rows="2" placeholder="What is shared in this group?" class="rounded-[0.575rem] border border-neutral-300 dark:border-white/15 bg-white dark:bg-white/5 px-4 py-2.5 text-sm outline-none focus:border-[#9e005d] sm:col-span-2"></textarea>
                </div>
                <div class="mt-3 flex items-center gap-3">
                    <flux:button id="sg-send" variant="primary" class="!bg-[#9e005d] hover:!bg-[#7e0049]">Suggest group</flux:button>
                    <span id="sg-status" class="text-xs font-semibold text-zinc-500"></span>
                </div>
            </div>
        </div>

        <!-- TAB: Templates -->
        <div data-mtab-panel="templates" class="hidden flex-col gap-4">
            <div class="rounded-xl bg-white dark:bg-white/[.04] p-5 shadow-[0_12px_32px_-12px_rgba(10,10,12,.18)] dark:shadow-[0_12px_32px_-12px_rgba(0,0,0,.7)]">
                <flux:heading>Message templates</flux:heading>
                <flux:text class="mt-1 text-sm">Built-in closers plus your own. Placeholders: {name}, {business}, {need}, {phone}.</flux:text>
                <div class="mt-4 space-y-3" id="tpl-list">
                    @foreach($templates as $t)
                    <div class="rounded-xl bg-zinc-50 dark:bg-white/5 p-4" data-tpl-row="{{ $t->id }}">
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-bold text-sm">{{ $t->name }} @if(!$t->user_id)<span class="text-[11px] font-semibold text-zinc-500">· built-in</span>@endif</p>
                            <div class="flex gap-2 shrink-0">
                                <button data-use-tpl="{{ $t->id }}" class="text-xs font-bold text-[#9e005d] hover:underline">Use</button>
                                @if($t->user_id)
                                <button data-del-tpl="{{ $t->id }}" class="text-xs font-semibold text-zinc-400 hover:text-red-600">Delete</button>
                                @endif
                            </div>
                        </div>
                        <p class="mt-1 text-[13px] text-zinc-600 dark:text-zinc-400 whitespace-pre-wrap" data-tpl-body="{{ $t->id }}">{{ $t->body }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

<script>
const MKT_CONTACTS = @json($contactsJson);
const MKT_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content || '';
/* Universal custom dropdowns (copies data-* attributes, syncs native select) */
function enhanceSelect(sel){
  if(!sel || sel.closest('.cs-wrap')) return;
  const wrap=document.createElement('div'); wrap.className='cs-wrap relative';
  sel.parentNode.insertBefore(wrap, sel); wrap.appendChild(sel);
  sel.classList.add('sr-only'); sel.tabIndex=-1; sel.setAttribute('aria-hidden','true');
  const btn=document.createElement('button');
  btn.type='button';
  btn.className='cs-btn w-full flex items-center justify-between gap-2 rounded-[0.575rem] border border-neutral-300 dark:border-white/15 bg-white dark:bg-white/5 px-4 py-2.5 text-sm outline-none';
  btn.setAttribute('aria-expanded','false');
  btn.innerHTML='<span class="cs-label truncate text-zinc-500">Select…</span><svg class="w-4 h-4 shrink-0 text-zinc-400 transition-transform" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>';
  const list=document.createElement('div');
  list.className='cs-list hidden absolute z-30 left-0 right-0 mt-2 rounded-xl border border-neutral-200 dark:border-white/15 bg-white dark:bg-[#141416] shadow-2xl p-1.5 max-h-60 overflow-y-auto';
  list.setAttribute('role','listbox');
  function sync(){
    const opt=sel.options[sel.selectedIndex];
    const label=wrap.querySelector('.cs-label');
    if(label && opt){ label.textContent=opt.textContent; label.classList.toggle('text-zinc-500', opt.value===''); }
    wrap.querySelectorAll('.cs-list button').forEach(b=>{
      const tick=b.querySelector('.cs-tick');
      if(tick) tick.classList.toggle('hidden', b.dataset.value!==sel.value);
    });
  }
  [...sel.options].forEach(o=>{
    const item=document.createElement('button');
    item.type='button'; item.dataset.value=o.value; item.setAttribute('role','option');
    [...o.attributes].forEach(a=>{ if(a.name.indexOf('data-')===0) item.dataset[a.name.slice(5)]=a.value; });
    item.className='w-full flex items-center justify-between gap-2 text-left px-4 py-2 rounded-lg text-sm transition hover:bg-zinc-100 dark:hover:bg-white/10 '+(o.value===''?'text-zinc-400':'text-zinc-800 dark:text-zinc-200');
    const esc=o.textContent.replaceAll('&','&amp;').replaceAll('<','&lt;');
    item.innerHTML='<span class="truncate">'+esc+'</span><svg class="cs-tick w-4 h-4 shrink-0 text-zinc-900 dark:text-white hidden" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>';
    item.addEventListener('click',()=>{ sel.value=o.value; sel.dispatchEvent(new Event('change',{bubbles:true})); sync(); closeAllCs(); });
    list.appendChild(item);
  });
  function closeAllCs(){ document.querySelectorAll('.cs-list').forEach(l=>l.classList.add('hidden')); document.querySelectorAll('.cs-btn').forEach(b=>b.setAttribute('aria-expanded','false')); }
  btn.addEventListener('click',()=>{ const open=list.classList.contains('hidden'); closeAllCs(); if(open){ list.classList.remove('hidden'); btn.setAttribute('aria-expanded','true'); } });
  wrap.appendChild(btn); wrap.appendChild(list);
  sync();
}
document.addEventListener('click',e=>{ if(!e.target.closest || !e.target.closest('.cs-wrap')) document.querySelectorAll('.cs-list').forEach(l=>l.classList.add('hidden')); });
document.addEventListener('keydown',e=>{ if(e.key==='Escape') document.querySelectorAll('.cs-list').forEach(l=>l.classList.add('hidden')); });
['ai-contact','ai-tone','ai-goal','mkt-template-pick','mkt-filter'].forEach(id=>enhanceSelect(document.getElementById(id)));
function mktFill(tpl, c){
  return (tpl || '').replaceAll('{name}', (c.name || '').split(' ')[0] || 'there')
    .replaceAll('{business}', c.business || 'your business')
    .replaceAll('{need}', c.need || c.type || 'our services')
    .replaceAll('{phone}', c.phone || '');
}
/* tabs */
function paintMtabs(active){
  document.querySelectorAll('[data-mtab]').forEach(b => {
    const on = b.dataset.mtab === active;
    b.className = 'mtab rounded-full px-4 py-2 text-[13px] font-bold transition ' + (on ? 'bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 shadow' : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-white');
  });
  document.querySelectorAll('[data-mtab-panel]').forEach(p => {
    const on = p.dataset.mtabPanel === active;
    p.classList.toggle('hidden', !on);
    p.classList.toggle('flex', on);
  });
}
document.querySelectorAll('[data-mtab]').forEach(b => b.addEventListener('click', () => paintMtabs(b.dataset.mtab)));
paintMtabs('outreach');
/* outreach list */
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
async function delTpl(id){
  if(!confirm('Delete this template?')) return;
  await fetch(`/dashboard/marketing/templates/${id}`, { method: 'DELETE',
    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': MKT_TOKEN, 'X-Requested-With': 'XMLHttpRequest' } });
  location.reload();
}
document.querySelectorAll('[data-del-tpl]').forEach(b => b.addEventListener('click', () => delTpl(b.dataset.delTpl)));
document.querySelectorAll('[data-use-tpl]').forEach(b => b.addEventListener('click', () => {
  const body = document.querySelector(`[data-tpl-body="${b.dataset.useTpl}"]`);
  if(body){ paintMtabs('outreach'); document.getElementById('mkt-template').value = body.textContent; mktRender(); window.scrollTo({ top: 0, behavior: 'smooth' }); }
}));
/* AI writer */
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
  paintMtabs('outreach');
  document.getElementById('mkt-template').value = document.getElementById('ai-message').textContent;
  mktRender();
});
/* groups */
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
/* groups search + filter + sort */
function filterGroups(){
  const q = (document.getElementById('grp-search').value || '').toLowerCase().trim();
  const niche = document.getElementById('grp-niche').value;
  const sort = document.getElementById('grp-sort').value;
  const grid = document.getElementById('group-grid');
  const cards = [...grid.querySelectorAll('[data-group]')];
  let visible = 0;
  cards.forEach(c => {
    const ok = (!q || c.dataset.search.includes(q)) && (!niche || c.dataset.niche === niche);
    c.classList.toggle('hidden', !ok);
    if(ok) visible++;
  });
  cards.filter(c => !c.classList.contains('hidden'))
    .sort((a, b) => sort === 'name'
      ? a.querySelector('p.font-bold').textContent.localeCompare(b.querySelector('p.font-bold').textContent)
      : (parseInt(b.dataset.members, 10) || 0) - (parseInt(a.dataset.members, 10) || 0))
    .forEach(c => grid.appendChild(c));
  let empty = document.getElementById('grp-empty');
  if(visible === 0 && !empty){
    empty = document.createElement('p');
    empty.id = 'grp-empty';
    empty.className = 'text-sm text-zinc-500 col-span-2 py-4 text-center';
    empty.textContent = 'No groups match — try a different search or suggest one below.';
    grid.appendChild(empty);
  } else if(empty && visible > 0){ empty.remove(); }
}
['grp-search'].forEach(id => document.getElementById(id)?.addEventListener('input', filterGroups));
['grp-niche','grp-sort'].forEach(id => {
  const el = document.getElementById(id);
  if(el){ enhanceSelect(el); el.addEventListener('change', filterGroups); }
});
/* suggest group */
document.getElementById('sg-send')?.addEventListener('click', async () => {
  const status = document.getElementById('sg-status');
  const payload = { name: document.getElementById('sg-name').value.trim(), niche: document.getElementById('sg-niche').value.trim(),
    invite_link: document.getElementById('sg-link').value.trim(), description: document.getElementById('sg-desc').value.trim() };
  if(!payload.name || !payload.invite_link){ status.textContent = 'Name and invite link are required.'; return; }
  status.textContent = 'Sending…';
  try{
    const res = await fetch("{{ route('marketing.groups.suggest') }}", { method: 'POST',
      headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': MKT_TOKEN, 'X-Requested-With': 'XMLHttpRequest' },
      body: JSON.stringify(payload) });
    const j = await res.json();
    status.textContent = j.message || (res.ok ? 'Sent!' : 'Failed.');
  }catch(e){ status.textContent = 'Failed. Try again.'; }
});
/* email campaigns */
document.getElementById('em-send')?.addEventListener('click', async () => {
  const status = document.getElementById('em-status');
  const btn = document.getElementById('em-send');
  const subject = document.getElementById('em-subject').value.trim();
  const body = document.getElementById('em-body').value.trim();
  if(!subject || !body){ status.textContent = 'Subject and message are required.'; return; }
  const ids = [...document.querySelectorAll('.em-contact:checked')].map(c => parseInt(c.value, 10));
  btn.disabled = true; btn.classList.add('opacity-60'); status.textContent = 'Sending…';
  try{
    const res = await fetch("{{ route('marketing.campaigns.send') }}", { method: 'POST',
      headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': MKT_TOKEN, 'X-Requested-With': 'XMLHttpRequest' },
      body: JSON.stringify({ subject, body, contact_ids: ids, emails: document.getElementById('em-manual').value }) });
    const j = await res.json();
    if(!res.ok) throw new Error(j.message || 'Send failed.');
    window.location.href = j.redirect;
  }catch(e){ status.textContent = e.message || 'Send failed.'; }
  btn.disabled = false; btn.classList.remove('opacity-60');
});
</script>
</x-layouts::app>
