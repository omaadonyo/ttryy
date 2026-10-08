<x-layouts::app :title="__('Checkout')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div>
            <flux:heading size="xl">Checkout</flux:heading>
            <flux:text class="mt-1">Pick a package, a domain, a billing rhythm, and how long you want your website kept running.</flux:text>
        </div>

        <div class="flex items-center gap-2 text-sm flex-wrap">
            @auth
            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 px-3 py-1 text-xs font-semibold">Signed in as {{ auth()->user()->name }}</span>
            @else
            <span class="inline-flex items-center gap-1.5 rounded-full bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 px-3 py-1 text-xs font-semibold">Sign in below to order</span>
            @endauth
            <span class="inline-flex items-center gap-1.5 rounded-full bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 px-3 py-1 text-xs font-semibold">Package &amp; plan</span>
            <span class="text-zinc-300 dark:text-zinc-600">→</span>
            <span class="text-xs text-zinc-500">Confirm &amp; pay</span>
        </div>

        @guest
        <div id="checkout-auth" class="rounded-xl border-2 border-[#9e005d]/40 p-6">
            <flux:heading>Create an account or log in to place your order</flux:heading>
            <flux:text class="mt-1 text-sm">Your package choices below are saved automatically — nothing is lost when you sign in.</flux:text>
            <div class="mt-4 inline-flex rounded-full bg-zinc-100 dark:bg-white/10 p-1" role="tablist" aria-label="Account">
                <button type="button" id="tab-login" class="rounded-full px-5 py-2 text-sm font-bold transition">Log in</button>
                <button type="button" id="tab-register" class="rounded-full px-5 py-2 text-sm font-bold transition">Create account</button>
            </div>
            <div id="panel-login" class="mt-4 max-w-md">
                <livewire:checkout.login-form />
            </div>
            <div id="panel-register" class="mt-4 max-w-md hidden">
                <livewire:checkout.register-form />
            </div>
            <p id="auth-required-note" class="hidden mt-3 text-sm font-semibold text-[#9e005d]">Please log in or create an account first — then your order goes through with everything you selected.</p>
        </div>
        @endguest

        @if($errors->any())
        <div class="rounded-xl border border-red-300 bg-red-50 dark:bg-red-500/10 px-5 py-4 text-sm text-red-700 dark:text-red-300">
            <p class="font-bold">Please fix the following:</p>
            <ul class="list-disc ml-5 mt-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <form method="POST" action="{{ route('checkout.store') }}" id="checkout-form" class="grid lg:grid-cols-[1fr_320px] gap-6 items-start" data-guest="{{ auth()->check() ? '0' : '1' }}">
            @csrf
            <div class="space-y-8">
                <section>
                    <flux:heading>1. Your package</flux:heading>
                    <div class="mt-3 grid sm:grid-cols-2 xl:grid-cols-4 gap-3">
                        @foreach($packages as $name => $p)
                        <label class="cursor-pointer">
                            <input type="radio" name="package" value="{{ $name }}" class="peer sr-only" {{ $selectedPackage === $name ? 'checked' : '' }}>
                            <div class="h-full rounded-xl border-2 border-neutral-200 dark:border-neutral-700 p-5 transition peer-checked:border-[#9e005d] peer-checked:shadow-lg">
                                <p class="font-bold text-sm tracking-wide {{ $name === 'GROW' ? 'text-[#9e005d]' : 'text-zinc-500' }}">{{ $name }}</p>
                                <p class="mt-1 text-2xl font-extrabold">UGX {{ number_format($p['price']) }}</p>
                                <p class="text-xs text-zinc-500 mt-1">{{ $p['deposit'] }} deposit · {{ $p['blurb'] }}</p>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </section>

                <section>
                    <flux:heading>2. Your domain</flux:heading>
                    <flux:text class="mt-1 text-sm">The domain fee is a <strong>one-time payment</strong>, due upfront with your first payment — never billed again.</flux:text>
                    <div class="mt-3 grid sm:grid-cols-3 gap-3">
                        @foreach($domains as $key => $d)
                        <label class="cursor-pointer">
                            <input type="radio" name="domain" value="{{ $key }}" class="peer sr-only" {{ $selectedDomain === $key ? 'checked' : '' }}>
                            <div class="h-full rounded-xl border-2 border-neutral-200 dark:border-neutral-700 p-5 transition peer-checked:border-[#9e005d] peer-checked:shadow-lg">
                                <p class="font-bold text-sm">{{ $d['label'] }}</p>
                                <p class="mt-1 text-xl font-extrabold">{{ $d['fee'] === 0 ? 'Free' : 'UGX '.number_format($d['fee']).' once' }}</p>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </section>

                <section>
                    <flux:heading>3. How you pay</flux:heading>
                    <div class="mt-3 grid sm:grid-cols-2 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" name="billing_frequency" value="full" class="peer sr-only" {{ $selectedBilling === 'full' ? 'checked' : '' }}>
                            <div class="rounded-xl border-2 border-neutral-200 dark:border-neutral-700 px-5 py-4 transition peer-checked:border-[#9e005d]">
                                <p class="font-bold">Pay in full</p>
                                <p class="text-sm text-zinc-500" id="fullLabel">One payment · website runs 12 months</p>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="billing_frequency" value="monthly" class="peer sr-only" {{ $selectedBilling === 'monthly' ? 'checked' : '' }}>
                            <div class="rounded-xl border-2 border-neutral-200 dark:border-neutral-700 px-5 py-4 transition peer-checked:border-[#9e005d]">
                                <p class="font-bold">Monthly</p>
                                <p class="text-sm text-zinc-500" id="monthlyLabel">per month</p>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="billing_frequency" value="weekly" class="peer sr-only" {{ $selectedBilling === 'weekly' ? 'checked' : '' }}>
                            <div class="rounded-xl border-2 border-neutral-200 dark:border-neutral-700 px-5 py-4 transition peer-checked:border-[#9e005d]">
                                <p class="font-bold">Weekly</p>
                                <p class="text-sm text-zinc-500" id="weeklyLabel">per week</p>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="billing_frequency" value="daily" class="peer sr-only" {{ $selectedBilling === 'daily' ? 'checked' : '' }}>
                            <div class="rounded-xl border-2 border-neutral-200 dark:border-neutral-700 px-5 py-4 transition peer-checked:border-[#9e005d]">
                                <p class="font-bold">Daily</p>
                                <p class="text-sm text-zinc-500" id="dailyLabel">per day</p>
                            </div>
                        </label>
                    </div>
                </section>

                <section>
                    <flux:heading>4. How long you pay</flux:heading>
                    <flux:text class="mt-1 text-sm">Your website stays running while your plan is active. Full upfront payment covers 12 months.</flux:text>
                    <select name="duration_months" id="duration" class="mt-3 w-full sm:w-72 rounded-full border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-900 px-5 py-3 text-sm outline-none focus:border-[#9e005d]">
                        @foreach($durations as $d)
                        <option value="{{ $d }}" {{ $d === 12 ? 'selected' : '' }}>{{ $d }} months</option>
                        @endforeach
                    </select>
                </section>

                <section>
                    <flux:heading>5. Your business details</flux:heading>
                    <div class="mt-3 grid sm:grid-cols-2 gap-4">
                        <div><flux:label>Business name *</flux:label><flux:input id="f-business" name="business_name" value="{{ old('business_name') }}" placeholder="Savanna Build Ltd" required class="mt-1" /></div>
                        <div><flux:label>Phone / WhatsApp *</flux:label><flux:input id="f-phone" name="phone" value="{{ old('phone') }}" placeholder="+256 700 000000" required class="mt-1" /></div>
                        <div>
                            <flux:label>Industry / niche</flux:label>
                            <select name="niche" id="f-niche" class="mt-1 w-full rounded-full border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-900 px-5 py-3 text-sm outline-none focus:border-[#9e005d]"><option value="">Select…</option>@foreach(config('packages.niches') as $n)<option {{ old('niche', $selectedNiche) === $n ? 'selected' : '' }}>{{ $n }}</option>@endforeach</select>
                        </div>
                        <div><flux:label>Notes (optional)</flux:label><flux:input id="f-notes" name="notes" value="{{ old('notes') }}" placeholder="Anything we should know" class="mt-1" /></div>
                    </div>
                </section>
            </div>

            <aside class="lg:sticky lg:top-6 rounded-xl bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 p-6 shadow-xl h-fit">
                <p class="text-xs font-bold tracking-wide uppercase opacity-60">Order summary</p>
                <p class="font-bold text-xl mt-2" id="sumPackage">GROW</p>
                <dl class="mt-4 space-y-2 text-sm">
                    <div class="flex justify-between gap-2"><dt class="opacity-60">Package total</dt><dd class="font-semibold" id="sumPkgTotal">—</dd></div>
                    <div class="flex justify-between gap-2"><dt class="opacity-60">Domain · one-time</dt><dd class="font-semibold" id="sumDomain">—</dd></div>
                    <div class="flex justify-between gap-2"><dt class="opacity-60">Billing</dt><dd class="font-semibold" id="sumFreq">Monthly</dd></div>
                    <div class="flex justify-between gap-2"><dt class="opacity-60">Term</dt><dd class="font-semibold" id="sumDuration">12 months</dd></div>
                    <div class="flex justify-between gap-2"><dt class="opacity-60">Then</dt><dd class="font-semibold" id="sumRest">—</dd></div>
                </dl>
                <div class="mt-4 pt-4 border-t border-white/15 dark:border-zinc-950/10">
                    <p class="text-xs opacity-60">First payment — due today</p>
                    <p class="font-extrabold text-3xl" id="sumDue">—</p>
                    <p class="text-xs opacity-60 mt-1" id="sumDueNote">domain + first installment</p>
                    <p class="text-xs opacity-60 mt-2">Grand total over term: <strong id="sumTotal">—</strong></p>
                </div>
                <flux:button type="submit" variant="primary" class="mt-5 w-full !bg-[#9e005d] hover:!bg-[#7e0049]">Place order</flux:button>
                <p class="mt-3 text-[11px] opacity-60">No payment is taken now. We confirm your order on WhatsApp and share Mobile Money payment details. You pay exactly what is shown here.</p>
            </aside>
        </form>
    </div>

<script>
const DATA = @json($packages);
const DOMAINS = @json($domains);
const fmt = n => 'UGX ' + Number(n).toLocaleString('en-US');
const pkgInputs = [...document.querySelectorAll('input[name="package"]')];
const freqInputs = [...document.querySelectorAll('input[name="billing_frequency"]')];
const domInputs = [...document.querySelectorAll('input[name="domain"]')];
const durationSel = document.getElementById('duration');
const LS_KEY = 'ttryy-checkout-v1';
const cur = () => ({
  pkg: (pkgInputs.find(i => i.checked) || {}).value || 'GROW',
  freq: (freqInputs.find(i => i.checked) || {}).value || 'monthly',
  dom: (domInputs.find(i => i.checked) || {}).value || 'budget',
  months: parseInt(durationSel.value, 10) || 12,
});
function calc(){
  const { pkg, freq, dom, months } = cur(), d = DATA[pkg], fee = (DOMAINS[dom] || {fee:0}).fee;
  if(freq === 'full') return { pkgTotal: d.price, total: d.price + fee, due: d.price + fee, rest: 'Nothing further — paid in full', rateLabel: fmt(d.price) + ' once', freqLabel: 'One-time', durText: '12 months' };
  let periods, rate, per;
  if(freq === 'monthly'){ periods = months; rate = d.monthly; per = '/mo'; }
  else if(freq === 'weekly'){ periods = Math.round(months * 52 / 12); rate = d.weekly; per = '/wk'; }
  else { periods = Math.round(months * 365 / 12); rate = d.daily; per = '/day'; }
  const pkgTotal = rate * periods;
  return { pkgTotal, total: pkgTotal + fee, due: rate + fee,
    rest: (periods - 1) + ' × ' + fmt(rate) + ' ' + freqLabel(freq),
    rateLabel: fmt(rate) + per, freqLabel: freqLabel(freq), durText: months + ' months' };
}
function freqLabel(f){ return f[0].toUpperCase() + f.slice(1); }
function save(){
  try{
    localStorage.setItem(LS_KEY, JSON.stringify({ ...cur(),
      business: document.getElementById('f-business').value, phone: document.getElementById('f-phone').value,
      niche: document.getElementById('f-niche').value, notes: document.getElementById('f-notes').value }));
  }catch(e){}
}
function restore(){
  let s = null;
  try{ s = JSON.parse(localStorage.getItem(LS_KEY)); }catch(e){}
  if(!s) return false;
  const hasQuery = new URLSearchParams(location.search).has('package');
  if(hasQuery) return false;
  const set = (inputs, v) => { const m = inputs.find(i => i.value === v); if(m) m.checked = true; };
  set(pkgInputs, s.pkg); set(freqInputs, s.freq); set(domInputs, s.dom);
  if(s.months) durationSel.value = String(s.months);
  if(s.business) document.getElementById('f-business').value = s.business;
  if(s.phone) document.getElementById('f-phone').value = s.phone;
  if(s.niche) document.getElementById('f-niche').value = s.niche;
  if(s.notes) document.getElementById('f-notes').value = s.notes;
  return true;
}
function refresh(){
  const { pkg, freq, dom } = cur(), d = DATA[pkg], q = calc();
  document.getElementById('sumPackage').textContent = pkg;
  document.getElementById('sumFreq').textContent = q.freqLabel;
  document.getElementById('sumDuration').textContent = q.durText;
  document.getElementById('sumPkgTotal').textContent = fmt(q.pkgTotal);
  document.getElementById('sumDomain').textContent = fmt((DOMAINS[dom] || {fee:0}).fee) + ' once';
  document.getElementById('sumRest').textContent = q.rest;
  document.getElementById('sumDue').textContent = fmt(q.due);
  document.getElementById('sumTotal').textContent = fmt(q.total);
  document.getElementById('monthlyLabel').textContent = fmt(d.monthly) + ' per month';
  document.getElementById('weeklyLabel').textContent = fmt(d.weekly) + ' per week';
  document.getElementById('dailyLabel').textContent = fmt(d.daily) + ' per day';
  document.getElementById('fullLabel').textContent = fmt(d.price) + ' once · website runs 12 months';
  durationSel.disabled = freq === 'full';
  durationSel.classList.toggle('opacity-50', freq === 'full');
}
function initCheckout(){
  restore();
  refresh();
  [...pkgInputs, ...freqInputs, ...domInputs].forEach(i => i.addEventListener('change', () => { refresh(); save(); }));
  durationSel.addEventListener('change', () => { refresh(); save(); });
  ['f-business','f-phone','f-niche','f-notes'].forEach(id => {
    const el = document.getElementById(id);
    if(el) el.addEventListener('input', save);
  });
  const tabLogin = document.getElementById('tab-login'), tabReg = document.getElementById('tab-register');
  const pLogin = document.getElementById('panel-login'), pReg = document.getElementById('panel-register');
  const paintTabs = login => {
    const on = 'rounded-full px-5 py-2 text-sm font-bold transition bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 shadow';
    const off = 'rounded-full px-5 py-2 text-sm font-bold transition text-zinc-500';
    if(!tabLogin) return;
    tabLogin.className = login ? on : off;
    tabReg.className = login ? off : on;
    pLogin.classList.toggle('hidden', !login);
    pReg.classList.toggle('hidden', login);
  };
  if(tabLogin){ tabLogin.addEventListener('click', () => paintTabs(true)); tabReg.addEventListener('click', () => paintTabs(false)); paintTabs(true); }
  const form = document.getElementById('checkout-form');
  form.addEventListener('submit', e => {
    if(form.dataset.guest !== '1') return;
    e.preventDefault();
    const note = document.getElementById('auth-required-note');
    if(note) note.classList.remove('hidden');
    const auth = document.getElementById('checkout-auth');
    if(auth) auth.scrollIntoView({ behavior: 'smooth', block: 'start' });
  });
}
if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initCheckout);
else initCheckout();
if(window.Livewire) document.addEventListener('livewire:navigated', initCheckout);
</script>
</x-layouts::app>
