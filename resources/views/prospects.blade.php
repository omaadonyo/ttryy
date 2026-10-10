<x-layouts::app :title="__('Prospect catalogue')">
    @include('partials.custom-select')
    <div class="flex h-full w-full flex-1 flex-col gap-4">
        <div>
            <flux:heading size="xl">Prospect catalogue</flux:heading>
            <flux:text class="mt-1">Every business indexed from web directories. Filter, then save what you want to market to.</flux:text>
        </div>

        <form method="GET" action="{{ route('prospects.index') }}" class="rounded-xl bg-white dark:bg-white/[.04] p-4 shadow-[0_12px_32px_-12px_rgba(10,10,12,.18)] dark:shadow-[0_12px_32px_-12px_rgba(0,0,0,.7)] grid sm:grid-cols-[1fr_200px_160px_auto] gap-3">
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search name, category, phone…" class="rounded-[0.575rem] border border-neutral-300 dark:border-white/15 bg-white dark:bg-white/5 px-4 py-2.5 text-sm outline-none focus:border-[#9e005d]">
            <select name="niche" id="flt-niche" class="rounded-[0.575rem] border border-neutral-300 dark:border-white/15 bg-white dark:bg-white/5 px-4 py-2.5 text-sm outline-none focus:border-[#9e005d]">
                <option value="">All niches</option>
                @foreach($niches as $n)
                <option value="{{ $n }}" {{ ($filters['niche'] ?? '') === $n ? 'selected' : '' }}>{{ $n }}</option>
                @endforeach
            </select>
            <label class="inline-flex items-center gap-2 text-sm px-1 cursor-pointer">
                <input type="checkbox" name="verified" value="1" {{ !empty($filters['verified']) ? 'checked' : '' }} class="size-4 accent-[#9e005d]">
                Verified only
            </label>
            <flux:button type="submit" variant="primary" class="!bg-[#9e005d] hover:!bg-[#7e0049]">Filter</flux:button>
        </form>

        <div class="rounded-xl bg-white dark:bg-white/[.04] p-5 shadow-[0_12px_32px_-12px_rgba(10,10,12,.18)] dark:shadow-[0_12px_32px_-12px_rgba(0,0,0,.7)]">
            <flux:heading>Browse by category</flux:heading>
            <flux:text class="mt-1 text-sm">A–Z of every business category in the index. Pick one to filter the catalogue.</flux:text>
            @if($categories->isEmpty())
            <p class="text-sm text-zinc-500 mt-3">Categories appear here once the index has data — run the scraper or the bulk catalogue command.</p>
            @else
            <div class="mt-3 flex flex-wrap gap-1.5" id="az-letters">
                @foreach($categories->keys() as $letter)
                <a href="#az-{{ $letter }}" class="w-8 h-8 grid place-items-center rounded-lg text-xs font-bold bg-zinc-100 dark:bg-white/10 hover:bg-zinc-950 hover:text-white dark:hover:bg-white dark:hover:text-zinc-950 transition">{{ $letter }}</a>
                @endforeach
            </div>
            <div class="mt-4 space-y-5">
                @foreach($categories as $letter => $cats)
                <div id="az-{{ $letter }}">
                    <p class="font-display font-extrabold text-xl text-zinc-300 dark:text-zinc-600">{{ $letter }}</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach($cats as $cat)
                        <a href="{{ route('prospects.index', ['category' => $cat->category]) }}" class="inline-flex items-center gap-1.5 text-[13px] bg-zinc-100 dark:bg-white/10 hover:bg-zinc-950 hover:text-white dark:hover:bg-white dark:hover:text-zinc-950 rounded-full px-3.5 py-1.5 transition {{ ($filters['category'] ?? '') === $cat->category ? '!bg-[#9e005d] !text-white' : '' }}">{{ $cat->category }} <span class="opacity-60">{{ $cat->c }}</span></a>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
            @if(!empty($filters['category']))
            <a href="{{ route('prospects.index') }}" class="inline-block mt-4 text-xs font-bold text-[#9e005d] hover:underline">Clear category filter ✕</a>
            @endif
            @endif
        </div>

        <div class="rounded-xl bg-white dark:bg-white/[.04] p-5 shadow-[0_12px_32px_-12px_rgba(10,10,12,.18)] dark:shadow-[0_12px_32px_-12px_rgba(0,0,0,.7)]">
            @if($prospects->isEmpty())
                <x-empty-state icon="search" title="No prospects indexed yet" message="Run the prospect scraper first — everything found lands here permanently." :actionUrl="route('scraper.index')" actionLabel="Open scraper" />
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[680px]">
                    <thead>
                        <tr class="text-left text-zinc-500">
                            <th class="py-2 pr-4 font-medium">Business</th>
                            <th class="py-2 pr-4 font-medium">Category</th>
                            <th class="py-2 pr-4 font-medium">Phone</th>
                            <th class="py-2 pr-4 font-medium">District</th>
                            <th class="py-2 pr-4 font-medium">Niche</th>
                            <th class="py-2 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-white/10">
                        @foreach($prospects as $p)
                        <tr>
                            <td class="py-2.5 pr-4 font-semibold">{{ $p->name }}
                                @if($p->verified)<span class="ml-1 text-[10px] font-bold text-emerald-700 dark:text-emerald-400 uppercase">· verified</span>@endif
                            </td>
                            <td class="py-2.5 pr-4">{{ $p->category ?? '—' }}</td>
                            <td class="py-2.5 pr-4 font-mono text-xs">{{ $p->phone ?? '—' }}</td>
                            <td class="py-2.5 pr-4">{{ $p->district ?? '—' }}</td>
                            <td class="py-2.5 pr-4 text-xs text-zinc-500">{{ $p->niche }}</td>
                            <td class="py-2.5 text-right whitespace-nowrap">
                                <button data-save-row="{{ $p->id }}" class="text-xs font-bold text-[#9e005d] hover:underline">Save</button>
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
    btn.textContent = 'Saved ✓'; btn.classList.add('text-emerald-600');
  } catch(e){ btn.disabled = false; btn.textContent = 'Save'; alert('Could not save. Please try again.'); }
}));
</script>
</x-layouts::app>
