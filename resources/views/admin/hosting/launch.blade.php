<x-layouts::app :title="__('Launch 10K signups')">
    @include('partials.toast')
    @include('partials.custom-select')
    <div class="flex w-full flex-col gap-5">
        <x-page-head title="Launch 10K signups" sub="Campaign applications — review, contact, convert into accounts.">
            <x-slot:actions>
                <a href="{{ route('launch') }}" target="_blank" class="btn-ghost btn-sm">View campaign page ↗</a>
            </x-slot:actions>
        </x-page-head>

        @if(session('new_launch_user'))
        <div class="card card-pad border-amber-500/40 bg-amber-500/5">
            <p class="text-sm font-extrabold">Account credentials — shown once, never again</p>
            <div class="mt-2 grid gap-2 font-mono text-xs sm:grid-cols-2">
                <p class="rounded-lg bg-zinc-950 p-3 text-emerald-300">EMAIL&nbsp;&nbsp;{{ session('new_launch_user')['email'] }}</p>
                <p class="rounded-lg bg-zinc-950 p-3 text-emerald-300">PASSWORD&nbsp;&nbsp;{{ session('new_launch_user')['password'] }}</p>
            </div>
        </div>
        @endif

        <div class="grid auto-rows-min gap-4 sm:grid-cols-3">
            <x-stat label="New" :value="$counts['new']" hint="Awaiting review" />
            <x-stat label="Contacted" :value="$counts['contacted']" hint="In conversation" />
            <x-stat label="Converted" :value="$counts['converted']" hint="Have accounts" />
        </div>

        <div class="card card-pad">
            <form method="GET" action="{{ route('admin.launch.index') }}" class="flex flex-col gap-3 sm:flex-row">
                <select name="status" class="field sm:max-w-56">
                    <option value="">All statuses</option>
                    @foreach(['new', 'contacted', 'converted', 'declined'] as $s)
                    <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-brand">Filter</button>
            </form>
        </div>
<script>
document.querySelectorAll('select[name="status"]').forEach(enhanceSelect);
</script>

        <div class="card card-pad">
            @if($signups->isEmpty())
                <x-empty-state icon="users" title="No applications yet" message="Share the launch campaign page to start receiving ideas." :actionUrl="route('launch')" actionLabel="Open campaign page" />
            @else
            <div class="space-y-3">
                @foreach($signups as $s)
                <div class="rounded-xl bg-zinc-50 p-4 dark:bg-white/5">
                    <div class="flex items-start justify-between gap-3 flex-wrap">
                        <div class="min-w-0">
                            <p class="font-extrabold">{{ $s->idea_name }}</p>
                            <p class="text-[13px] text-zinc-500">{{ $s->name }} · {{ $s->phone }}{{ $s->email ? ' · '.$s->email : '' }}{{ $s->niche ? ' · '.$s->niche : '' }}</p>
                        </div>
                        <span class="{{ $s->status === 'converted' ? 'pill-green' : ($s->status === 'declined' ? 'pill-red' : ($s->status === 'contacted' ? 'pill-amber' : 'pill-brand')) }}">{{ $s->status }}</span>
                    </div>
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400 whitespace-pre-wrap">{{ $s->idea }}</p>
                    @if($s->admin_note)<p class="mt-2 text-xs text-zinc-500">Note: {{ $s->admin_note }}</p>@endif
                    <p class="mt-1 text-[11px] text-zinc-400">Applied {{ $s->created_at->diffForHumans() }}{{ $s->user_id ? ' · account #'.$s->user_id : '' }}</p>
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <form method="POST" action="{{ route('admin.launch.status', $s) }}" class="inline-flex items-center gap-2">
                            @csrf
                            @method('PATCH')
                            <select name="status" class="field max-w-40">
                                @foreach(['new', 'contacted', 'converted', 'declined'] as $st)
                                <option value="{{ $st }}" {{ $s->status === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                                @endforeach
                            </select>
                            <input name="admin_note" placeholder="Note (optional)" value="{{ $s->admin_note }}" class="field max-w-56">
                            <button type="submit" class="btn-ghost btn-sm">Save</button>
                        </form>
                        @if(! $s->user_id)
                        <form method="POST" action="{{ route('admin.launch.create-user', $s) }}" class="inline" onsubmit="return confirm('Create a login account for {{ $s->name }}?')">
                            @csrf
                            <button type="submit" class="btn-brand btn-sm">Create account</button>
                        </form>
                        @endif
                        <a target="_blank" href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $s->phone) }}" class="btn-ghost btn-sm">WhatsApp →</a>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="mt-4">{{ $signups->links() }}</div>
            @endif
        </div>
    </div>
</x-layouts::app>
