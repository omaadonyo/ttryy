<x-layouts::app :title="__('Prospect catalogue')">
    @include('partials.custom-select')
    @include('partials.toast')
    <div class="flex w-full flex-col gap-5">
        <x-page-head title="Prospect catalogue" sub="Every business indexed from web directories. Filter, then save what you want to market to." />

        @include('partials.onboard', ['key' => 'prospects', 'title' => 'Your permanent index', 'body' => 'Everything ever scraped lives here. Filter by niche, category or verified status — or browse A–Z — then save the best ones to market to.', 'link' => route('scraper.index'), 'linkLabel' => 'Run the scraper →'])

        <div class="grid auto-rows-min grid-cols-2 gap-4 xl:grid-cols-4">
            <x-stat label="Businesses indexed" :value="number_format($totals['indexed'])" />
            <x-stat label="Categories" :value="number_format($totals['categories'])" />
            <x-stat label="Verified" :value="number_format($totals['verified'])" />
            <x-stat label="Matching filters" :value="number_format($totals['showing'])" hint="Save them to market" />
        </div>

        <form method="GET" action="{{ route('prospects.index') }}" class="card card-pad grid gap-3 sm:grid-cols-[1fr_200px_160px_auto]">
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search name, category, phone…" class="field">
            <select name="niche" id="flt-niche" class="field">
                <option value="">All niches</option>
                @foreach($niches as $n)
                <option value="{{ $n }}" {{ ($filters['niche'] ?? '') === $n ? 'selected' : '' }}>{{ $n }}</option>
                @endforeach
            </select>
            <label class="inline-flex cursor-pointer items-center gap-2 px-1 text-sm">
                <input type="checkbox" name="verified" value="1" {{ !empty($filters['verified']) ? 'checked' : '' }} class="size-4 accent-brand-600">
                Verified only
            </label>
            <button type="submit" class="btn-brand">Filter</button>
        </form>

        <div class="card card-pad">
            <h2 class="section-title">Browse by category</h2>
            <p class="page-sub">A–Z of every business category in the index. Pick one to filter the catalogue.</p>
            @if($categories->isEmpty())
            <p class="text-sm text-zinc-500 mt-3">Categories appear here once the index has data — run the scraper or the bulk catalogue command.</p>
            @else
            <div class="mt-3 flex flex-wrap gap-1.5" id="az-letters">
                @foreach($categories->keys() as $letter)
                <a href="#az-{{ $letter }}" class="grid h-8 w-8 place-items-center rounded-lg bg-zinc-100 text-xs font-bold transition hover:bg-zinc-950 hover:text-white dark:bg-white/10 dark:hover:bg-white dark:hover:text-zinc-950">{{ $letter }}</a>
                @endforeach
            </div>
            <div class="mt-4 space-y-5">
                @foreach($categories as $letter => $cats)
                <div id="az-{{ $letter }}">
                    <p class="font-display text-xl font-extrabold text-zinc-300 dark:text-zinc-600">{{ $letter }}</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach($cats as $cat)
                        <a href="{{ route('prospects.index', ['category' => $cat->category]) }}" class="inline-flex items-center gap-1.5 rounded-full bg-zinc-100 px-3.5 py-1.5 text-[13px] transition hover:bg-zinc-950 hover:text-white dark:bg-white/10 dark:hover:bg-white dark:hover:text-zinc-950 {{ ($filters['category'] ?? '') === $cat->category ? 'bg-brand-600! text-white! hover:bg-brand-700! dark:bg-brand-600! dark:text-white!' : '' }}">{{ $cat->category }} <span class="opacity-60">{{ $cat->c }}</span></a>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
            @if(!empty($filters['category']))
            <a href="{{ route('prospects.index') }}" class="mt-4 inline-block text-xs font-bold text-brand-700 hover:underline dark:text-brand-300">Clear category filter ✕</a>
            @endif
            @endif
        </div>

        <div class="card card-pad">
            @if($prospects->isEmpty())
                <x-empty-state icon="search" title="No prospects indexed yet" message="Run the prospect scraper first — everything found lands here permanently." :actionUrl="route('scraper.index')" actionLabel="Open scraper" />
            @else
            <div class="tbl-wrap rounded-none! border-0! shadow-none!">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>Business</th>
                            <th>Category</th>
                            <th>Phone</th>
                            <th>District</th>
                            <th>Niche</th>
                            <th class="text-right!">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($prospects as $p)
                        <tr>
                            <td class="font-semibold">{{ $p->name }}
                                @if($p->verified)<span class="ml-1 text-[10px] font-bold text-emerald-700 uppercase dark:text-emerald-400">· verified</span>@endif
                            </td>
                            <td>{{ $p->category ?? '—' }}</td>
                            <td class="font-mono text-xs">{{ $p->phone ?? '—' }}</td>
                            <td>{{ $p->district ?? '—' }}</td>
                            <td class="text-xs text-zinc-500">{{ $p->niche }}</td>
                            <td class="text-right whitespace-nowrap">
                                <button data-save-row="{{ $p->id }}" class="text-xs font-bold text-brand-700 hover:underline dark:text-brand-300">Save</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $prospects->links() }}</div>
            @endif
        </div>
    </div>

<script>
enhanceSelect(document.getElementById('flt-niche'));
document.querySelectorAll('[data-save-row]').forEach(btn => btn.addEventListener('click', async () => {
  const row = btn.closest('tr');
  const cells = row.querySelectorAll('td');
  const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
  btn.disabled = true; btn.textContent = 'Saving…';
  try {
    const res = await fetch("{{ route('scraper.save') }}", { method: 'POST',
      headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
      body: JSON.stringify({ niche: cells[4].textContent.trim(), source: 'web', contacts: [{
        name: cells[0].childNodes[0].textContent.trim(),
        type: cells[1].textContent.trim() === '—' ? null : cells[1].textContent.trim(),
        district: cells[3].textContent.trim() === '—' ? null : cells[3].textContent.trim(),
        phone: cells[2].textContent.trim() === '—' ? null : cells[2].textContent.trim(),
      }]}) });
    if(!res.ok) throw new Error('save failed');
    const j = await res.json().catch(() => ({}));
    btn.textContent = 'Saved ✓'; btn.classList.add('text-emerald-600');
    toast('Contact saved — find it under Saved contacts.');
  } catch(e){ btn.disabled = false; btn.textContent = 'Save'; toast('Could not save. Please try again.', 'danger'); }
}));
</script>
</x-layouts::app>
