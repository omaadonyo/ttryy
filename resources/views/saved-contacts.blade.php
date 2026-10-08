<x-layouts::app :title="__('Saved contacts')">
    <div class="flex h-full w-full flex-1 flex-col gap-4">
        <div class="flex items-end justify-between gap-3 flex-wrap">
            <div>
                <flux:heading size="xl">Saved contacts</flux:heading>
                <flux:text class="mt-1">Prospects you scraped, ready for outreach. {{ $contacts->total() }} saved.</flux:text>
            </div>
            <div class="flex items-center gap-2">
                <flux:button :href="route('contacts.export')" variant="ghost">Export CSV</flux:button>
                <flux:button :href="route('marketing.index')" variant="primary" class="!bg-[#9e005d] hover:!bg-[#7e0049]" wire:navigate>Market to them</flux:button>
            </div>
        </div>

        @if(session('status'))
        <div class="rounded-xl bg-emerald-500/10 border border-emerald-500/30 px-5 py-3 text-sm text-emerald-700 dark:text-emerald-400">{{ session('status') }}</div>
        @endif

        <div class="rounded-xl bg-white dark:bg-white/[.04] p-5 shadow-[0_12px_32px_-12px_rgba(10,10,12,.18)] dark:shadow-[0_12px_32px_-12px_rgba(0,0,0,.7)]">
            @if($contacts->isEmpty())
                <x-empty-state icon="users" title="No saved contacts yet" message="Run the prospect scraper, then save the contacts you want to market to." :actionUrl="route('scraper.index')" actionLabel="Open scraper" />
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[680px]">
                    <thead>
                        <tr class="text-left text-zinc-500">
                            <th class="py-2 pr-4 font-medium">Business</th>
                            <th class="py-2 pr-4 font-medium">Type</th>
                            <th class="py-2 pr-4 font-medium">Contact</th>
                            <th class="py-2 pr-4 font-medium">Phone</th>
                            <th class="py-2 pr-4 font-medium">Niche</th>
                            <th class="py-2 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-white/10">
                        @foreach($contacts as $c)
                        <tr>
                            <td class="py-2.5 pr-4 font-semibold">{{ $c->name }}<p class="text-xs font-normal text-zinc-500">{{ $c->district }}</p></td>
                            <td class="py-2.5 pr-4">{{ $c->type }}</td>
                            <td class="py-2.5 pr-4">{{ $c->contact }}</td>
                            <td class="py-2.5 pr-4 font-mono text-xs">{{ $c->phone }}</td>
                            <td class="py-2.5 pr-4 text-xs text-zinc-500">{{ $c->niche }}</td>
                            <td class="py-2.5 text-right whitespace-nowrap">
                                @if($c->waNumber())
                                <a target="_blank" href="https://wa.me/{{ $c->waNumber() }}" class="text-xs font-bold text-[#9e005d] hover:underline mr-3">WhatsApp</a>
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
