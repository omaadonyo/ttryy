<x-layouts::app :title="__('Business Tools — '.ucfirst($kind).'s')">
    @include('partials.toast')
    <div class="flex w-full flex-col gap-5">
        <x-page-head :title="ucfirst($kind).'s'" :sub="$kind === 'customer' ? 'Everyone you sell to — balances update live.' : 'Everyone you buy from — unpaid bills show on your balance sheet.'">
            <x-slot:actions>
                <button type="button" onclick="document.getElementById('contact-modal').showModal()" class="btn-brand btn-sm">Add {{ $kind }}</button>
            </x-slot:actions>
        </x-page-head>
        @include('tools._tabs', ['active' => $active])

        @if($errors->any())
        <div class="card card-pad border-red-300 bg-red-50 text-sm text-red-700 dark:bg-red-500/10 dark:text-red-300">{{ $errors->first() }}</div>
        @endif
        @if(session('status'))
        <div class="card card-pad border-emerald-500/40 bg-emerald-500/5 text-sm font-semibold text-emerald-700 dark:text-emerald-300">{{ session('status') }}</div>
        @endif

        <div class="card card-pad">
            <form method="GET" class="flex flex-col gap-3 sm:flex-row">
                <input name="q" value="{{ $q }}" placeholder="Search name or phone…" class="field">
                <button class="btn-brand">Search</button>
            </form>
        </div>

        <dialog id="contact-modal" class="m-auto max-h-[92vh] w-[calc(100%-2rem)] max-w-md overflow-y-auto rounded-xl bg-white p-4 backdrop:bg-zinc-950/60 sm:p-6 dark:bg-[#141416]">
            <div class="mb-3 flex items-center justify-between gap-2">
                <h2 class="section-title">Add {{ $kind }}</h2>
                <button type="button" onclick="document.getElementById('contact-modal').close()" class="grid size-8 shrink-0 place-items-center rounded-full text-zinc-400 transition hover:bg-zinc-950/5 hover:text-zinc-700 dark:hover:bg-white/10 dark:hover:text-white" aria-label="Close">✕</button>
            </div>
            <form method="POST" action="{{ route('tools.contacts.store') }}" class="space-y-3">
                @csrf
                <input type="hidden" name="kind" value="{{ $kind }}">
                <div><label class="lbl">Name *</label><input name="name" required class="field" placeholder="Full name or business"></div>
                <div><label class="lbl">Phone</label><input name="phone" class="field" placeholder="+256 700 000000"></div>
                <div><label class="lbl">Email</label><input name="email" type="email" class="field"></div>
                <div><label class="lbl">Address</label><input name="address" class="field"></div>
                <div><label class="lbl">Notes</label><textarea name="notes" rows="2" class="field"></textarea></div>
                <button class="btn-brand w-full">Save {{ $kind }}</button>
            </form>
        </dialog>
        @if($errors->any())
        <script>document.getElementById('contact-modal')?.showModal();</script>
        @endif

        <div class="card card-pad">
                @if($contacts->isEmpty())
                <x-empty-state icon="users" :title="'No '.$kind.'s yet'" :message="'Add your first '. $kind .' to start billing.'" />
                @else
                <div class="space-y-3">
                    @foreach($contacts as $c)
                    <details class="rounded-xl bg-zinc-50 dark:bg-white/5 p-4">
                        <summary class="flex cursor-pointer items-center justify-between gap-3">
                            <span class="min-w-0"><strong>{{ $c->name }}</strong><span class="block text-xs text-zinc-500">{{ $c->phone }}{{ $c->email ? ' · '.$c->email : '' }}</span></span>
                            @if($kind === 'customer' && $c->open_invoices > 0)
                            <span class="pill-amber">UGX {{ number_format($c->balanceOwed()) }} owed</span>
                            @endif
                        </summary>
                        <form method="POST" action="{{ route('tools.contacts.update', $c) }}" class="mt-3 grid gap-3 sm:grid-cols-2">
                            @csrf @method('PATCH')
                            <div><label class="lbl">Name *</label><input name="name" required value="{{ $c->name }}" class="field"></div>
                            <div><label class="lbl">Phone</label><input name="phone" value="{{ $c->phone }}" class="field"></div>
                            <div><label class="lbl">Email</label><input name="email" type="email" value="{{ $c->email }}" class="field"></div>
                            <div><label class="lbl">Address</label><input name="address" value="{{ $c->address }}" class="field"></div>
                            <div class="sm:col-span-2"><label class="lbl">Notes</label><textarea name="notes" rows="2" class="field">{{ $c->notes }}</textarea></div>
                            <div class="sm:col-span-2"><button class="btn-brand btn-sm">Save changes</button></div>
                        </form>
                        <form method="POST" action="{{ route('tools.contacts.destroy', $c) }}" class="mt-2" onsubmit="return confirm('Delete {{ $c->name }}?')">
                            @csrf @method('DELETE')
                            <button class="btn-ghost btn-sm">Delete</button>
                        </form>
                    </details>
                    @endforeach
                </div>
                <div class="mt-4">{{ $contacts->links() }}</div>
                @endif
        </div>
    </div>
</x-layouts::app>
