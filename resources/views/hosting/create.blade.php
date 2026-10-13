<x-layouts::app :title="__('Register website')">
    @include('partials.toast')
    <div class="flex w-full flex-col gap-5">
        <x-page-head title="Register a website" sub="A database record first, a live site after provisioning and health checks succeed." />

        <div class="card card-pad max-w-2xl">
            @if($errors->any())
            <div class="mb-4 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-700 dark:bg-red-500/10 dark:text-red-300">
                <ul class="list-disc ml-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
            @endif
            <form method="POST" action="{{ route('hosting.store') }}" class="grid gap-4">
                @csrf
                <div><label class="lbl" for="f-name">Website name *</label><input id="f-name" name="name" required value="{{ old('name') }}" placeholder="Savanna Build Ltd" class="field"></div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label class="lbl" for="f-type">Business category</label><input id="f-type" name="site_type" value="{{ old('site_type') }}" placeholder="e.g. Construction" class="field"></div>
                    <div><label class="lbl" for="f-plan">Hosting plan *</label>
                        <select id="f-plan" name="hosting_plan_id" required class="field">
                            @foreach($plans as $p)
                            <option value="{{ $p->id }}" {{ old('hosting_plan_id') == $p->id ? 'selected' : '' }}>{{ $p->code }} — UGX {{ number_format($p->price) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div><label class="lbl" for="f-desc">Description (optional)</label><textarea id="f-desc" name="description" rows="2" class="field" placeholder="What does this website do?">{{ old('description') }}</textarea></div>
                <div>
                    <label class="lbl" for="f-sub">Preferred subdomain (optional)</label>
                    <div class="flex items-stretch gap-0">
                        <input id="f-sub" name="subdomain" value="{{ old('subdomain') }}" placeholder="savanna-build" class="field rounded-r-none!">
                        <span class="grid shrink-0 place-items-center rounded-r-md border border-l-0 border-zinc-300 bg-zinc-50 px-3 text-sm text-zinc-500 dark:border-white/15 dark:bg-white/5">.{{ $baseDomain }}</span>
                    </div>
                    <p class="mt-1 text-xs text-zinc-500">Letters, numbers and hyphens. Leave empty to auto-generate from the name. Reserved names are blocked and clashes get a numeric suffix.</p>
                </div>
                <div><label class="lbl" for="f-domain">Custom domain, if you own one (optional)</label><input id="f-domain" name="custom_domain" value="{{ old('custom_domain') }}" placeholder="example.co.ug" class="field">
                    <p class="mt-1 text-xs text-zinc-500">Added as unverified — you will prove ownership with a DNS TXT record next.</p>
                </div>
                <div class="flex items-center gap-3 pt-1">
                    <button type="submit" class="btn-brand">Register website</button>
                    <a href="{{ route('hosting.index') }}" class="btn-ghost">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>
