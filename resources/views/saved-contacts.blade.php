<x-layouts::app :title="__('Saved contacts')">
    @include('partials.toast')
    <div class="flex w-full flex-col gap-5">
        <x-page-head :title="'Saved contacts'" :sub="'Prospects you scraped, ready for outreach. '.$contacts->total().' saved.'">
            <x-slot:actions>
                <a href="{{ route('contacts.export') }}" class="btn-ghost btn-sm">Export CSV</a>
                <a href="{{ route('marketing.index') }}" class="btn-brand btn-sm">Market to them</a>
            </x-slot:actions>
        </x-page-head>

        <div class="grid auto-rows-min gap-4 sm:grid-cols-3">
            <x-stat label="Saved contacts" :value="$contacts->total()" hint="Ready for outreach" />
            <x-stat label="With email" :value="$emailCount" hint="Mailable for campaigns" />
            <x-stat label="With phone" :value="$phoneCount" hint="WhatsApp-ready" />
        </div>

        @if($topNiches->isNotEmpty())
        <div class="card card-pad">
            <h2 class="section-title">Top niches in your list</h2>
            <div class="mt-3 space-y-2.5">
                @php $maxNiche = max(1, $topNiches->max('c')); @endphp
                @foreach($topNiches as $row)
                <div class="flex items-center gap-3">
                    <span class="w-40 shrink-0 truncate text-[13px] font-semibold">{{ $row->niche ?? 'Unspecified' }}</span>
                    <div class="h-2.5 flex-1 overflow-hidden rounded-full bg-zinc-100 dark:bg-white/10">
                        <div class="h-2.5 rounded-full bg-brand-600" style="width:{{ round($row->c / $maxNiche * 100) }}%"></div>
                    </div>
                    <span class="w-8 shrink-0 text-right text-xs font-bold">{{ $row->c }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="card card-pad">
            @if($contacts->isEmpty())
                <x-empty-state icon="users" title="No saved contacts yet" message="Run the prospect scraper, then save the contacts you want to market to." :actionUrl="route('scraper.index')" actionLabel="Open scraper" />
            @else
            <div class="tbl-wrap rounded-none! border-0! shadow-none!">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>Business</th>
                            <th>Type</th>
                            <th>Contact</th>
                            <th>Phone</th>
                            <th>Niche</th>
                            <th class="text-right!">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($contacts as $c)
                        <tr>
                            <td class="font-semibold">{{ $c->name }}<p class="text-xs font-normal text-zinc-500">{{ $c->district }}</p></td>
                            <td>{{ $c->type }}</td>
                            <td>{{ $c->contact }}</td>
                            <td class="font-mono text-xs">{{ $c->phone }}</td>
                            <td class="text-xs text-zinc-500">{{ $c->niche }}</td>
                            <td class="text-right whitespace-nowrap">
                                @if($c->waNumber())
                                <a target="_blank" href="https://wa.me/{{ $c->waNumber() }}" class="mr-3 text-xs font-bold text-brand-700 hover:underline dark:text-brand-300">WhatsApp</a>
                                @endif
                                <form method="POST" action="{{ route('contacts.destroy', $c) }}" class="inline" onsubmit="return confirm('Remove this contact?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-xs font-semibold text-zinc-400 hover:text-red-600">Remove</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $contacts->links() }}</div>
            @endif
        </div>
    </div>
</x-layouts::app>
