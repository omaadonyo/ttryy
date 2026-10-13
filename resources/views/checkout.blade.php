<x-layouts::checkout :title="__('Checkout')">
    @include('partials.toast')
    <style>
      .opt > div{ box-shadow:0 2px 12px -6px rgba(10,10,12,.14); }
      .dark .opt > div{ box-shadow:0 8px 24px -10px rgba(0,0,0,.75); }
      .opt .tick{ opacity:0; transform:scale(.4); transition:opacity .25s ease, transform .25s ease, background-color .25s ease; }
  .opt input:checked + div{ background:color-mix(in srgb, var(--ac) 10%, transparent); box-shadow:0 0 0 1.5px var(--ac); }
  .opt input:checked + div .tick{ opacity:1; transform:scale(1); background:var(--ac); border-color:var(--ac); color:#fff; }
  .opt input:focus-visible + div{ outline:2px solid var(--ac); outline-offset:2px; }
      .opt[data-accent="zinc"]{ --ac:#52525b; }
      .opt[data-accent="brand"]{ --ac:#9e005d; }
      .opt[data-accent="black"]{ --ac:#18181b; }
      .dark .opt[data-accent="black"]{ --ac:#e4e4e7; }
      .opt[data-accent="emerald"]{ --ac:#059669; }
      .opt[data-accent="amber"]{ --ac:#d97706; }
      .opt[data-accent="blue"]{ --ac:#2563eb; }
      .cs-wrap:focus-within .cs-btn{ border-color:#9e005d; }
      .cs-btn svg{ transition:transform .25s ease; }
      .cs-btn[aria-expanded="true"] svg{ transform:rotate(180deg); }
      .wiz-dot .wiz-num{ transition:all .25s ease; }
    </style>
    <div class="flex h-full w-full flex-1 flex-col gap-5">
  <p class="text-xs font-semibold tracking-wide uppercase text-zinc-500">Secure checkout</p>
  <h1 class="font-display font-extrabold text-3xl mt-1 text-zinc-950 dark:text-white">Get your website in 3 quick steps</h1>
  <p class="text-zinc-600 dark:text-zinc-400 mt-2">Pick a plan, tell us about your business, then pay with Mobile Money in seconds. You pay exactly what is shown — no hidden fees.</p>

  <div class="mt-5 flex items-center gap-2 text-sm flex-wrap">
    @auth
    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 px-3 py-1 text-xs font-semibold">Signed in as {{ auth()->user()->name }}</span>
    @else
    <span class="inline-flex items-center gap-1.5 rounded-full bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 px-3 py-1 text-xs font-semibold">Sign in below to order</span>
    @endauth
    <span class="inline-flex items-center gap-1.5 rounded-full bg-zinc-100 dark:bg-white/10 text-zinc-600 dark:text-zinc-300 px-3 py-1 text-xs font-semibold">Package &amp; plan</span>
    <span class="text-zinc-300 dark:text-zinc-600">→</span>
    <span class="text-xs text-zinc-500">Confirm &amp; pay</span>
    <button onclick="startTour_checkout()" class="ml-auto text-xs font-bold text-brand-700 hover:underline dark:text-brand-300">Take the tour</button>
  </div>

  @include('partials.tour', ['key' => 'checkout', 'steps' => [
      ['target' => '#wiz-nav', 'title' => 'Three quick steps', 'body' => 'Plan, details, then pay. Completed steps stay clickable so you can go back.'],
      ['target' => '[data-step="1"]', 'wiz' => 1, 'title' => '1 · Your plan', 'body' => 'START, GROW, BUSINESS or CORPORATE, plus your domain — a one-time fee, never billed again.'],
      ['target' => '[data-step="2"]', 'wiz' => 2, 'title' => '2 · Details & rhythm', 'body' => 'Business name and phone are required. Pay in full for 12 months, or spread it daily, weekly or monthly.'],
      ['target' => '[data-step="3"]', 'wiz' => 3, 'title' => '3 · Pay in seconds', 'body' => 'Enter your Mobile Money number, hit Pay, approve the prompt on your phone. Done.'],
      ['target' => '#tour-summary', 'title' => 'Review and pay', 'body' => 'The summary always shows exactly what is due today.'],
  ]])

  @auth
  @include('partials.onboard', ['key' => 'checkout', 'title' => 'How ordering works', 'body' => 'Pick a plan, tell us about your business, then pay with Mobile Money in seconds. You pay exactly what the summary shows — no hidden fees.', 'link' => route('packages.index'), 'linkLabel' => 'Review my packages →'])
  @endauth

  @guest
  <div id="checkout-auth" class="card card-pad mt-5">
    <h2 class="font-display font-bold text-xl text-zinc-950 dark:text-white">Create an account or log in</h2>
    <p class="mt-1 text-sm text-zinc-500">Your package choices below are saved automatically — nothing is lost when you sign in.</p>
    <div class="mt-4 inline-flex rounded-full bg-zinc-100 dark:bg-white/10 p-1" role="tablist" aria-label="Account">
      <button type="button" id="tab-login" class="rounded-full px-4 py-2 text-sm font-bold transition">Log in</button>
      <button type="button" id="tab-register" class="rounded-full px-4 py-2 text-sm font-bold transition">Create account</button>
    </div>
    <div class="mt-6 grid lg:grid-cols-2 gap-8">
      <div id="panel-login">
        <livewire:checkout.login-form />
      </div>
      <div id="panel-register" class="hidden">
        <livewire:checkout.register-form />
      </div>
      <div class="text-sm text-zinc-500 space-y-3">
        <p class="font-bold text-zinc-900 dark:text-white">Why an account?</p>
        <ul class="space-y-2">
          <li class="flex gap-2"><span class="text-brand-600 font-bold">✓</span> Track your order and payment status</li>
          <li class="flex gap-2"><span class="text-brand-600 font-bold">✓</span> Download invoices anytime</li>
          <li class="flex gap-2"><span class="text-brand-600 font-bold">✓</span> Manage renewals to keep your site running</li>
        </ul>
        <p id="auth-required-note" class="hidden font-semibold text-brand-700 dark:text-brand-300">Please log in or create an account first — then your order goes through with everything you selected.</p>
      </div>
    </div>
  </div>
  @endguest

  @if($errors->any())
  <div class="mt-6 rounded-xl border border-red-300 bg-red-50 dark:bg-red-500/10 px-5 py-4 text-sm text-red-700 dark:text-red-300">
    <p class="font-bold">Please fix the following:</p>
    <ul class="list-disc ml-5 mt-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
  </div>
  @endif
  <div id="pay-error" class="hidden mt-6 rounded-xl border border-red-300 bg-red-50 dark:bg-red-500/10 px-5 py-4 text-sm text-red-700 dark:text-red-300"></div>

  <form method="POST" action="{{ route('checkout.store') }}" id="checkout-form" class="mt-6 grid lg:grid-cols-[1fr_320px] gap-5 items-start" data-guest="{{ auth()->check() ? '0' : '1' }}" data-wiz-start="{{ $errors->any() ? '2' : '1' }}">
    @csrf
    <input type="hidden" name="payment_method" id="pay-method" value="iotec">
    <div class="space-y-6">
      <div id="wiz-nav">
        <div class="flex items-start gap-1 sm:gap-2" id="wiz-dots" role="tablist" aria-label="Checkout steps">
          @foreach([1 => 'Plan', 2 => 'Details', 3 => 'Pay'] as $n => $label)
          <button type="button" data-goto="{{ $n }}" class="wiz-dot flex-1 flex flex-col items-center gap-1.5" role="tab">
            <span class="wiz-num w-8 h-8 grid place-items-center rounded-full text-xs font-extrabold border-2 border-zinc-300 dark:border-white/15 text-zinc-400">{{ $n }}</span>
            <span class="text-[11px] font-bold text-zinc-500 dark:text-zinc-400">{{ $label }}</span>
          </button>
          @endforeach
        </div>
        <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-zinc-200 dark:bg-white/10"><div id="wiz-bar" class="h-full rounded-full bg-brand-600 transition-all duration-300" style="width:33%"></div></div>
      </div>
      <div class="wiz-step" data-step="1">
      <section>
        <h2 class="font-display font-bold text-lg text-zinc-950 dark:text-white">1. Package & domain</h2>
        @php $accents = ['START' => 'emerald', 'GROW' => 'brand', 'BUSINESS' => 'amber', 'CORPORATE' => 'blue']; @endphp
        <div class="mt-3 grid sm:grid-cols-2 gap-3">
          @foreach($packages as $name => $p)
          <label class="opt cursor-pointer" data-accent="{{ $accents[$name] ?? 'zinc' }}">
            <input type="radio" name="package" value="{{ $name }}" class="sr-only" {{ $selectedPackage === $name ? 'checked' : '' }}>
            <div class="h-full rounded-xl bg-white dark:bg-[#141416] p-5 transition">
              <div class="flex items-center justify-between gap-2">
                <p class="font-display font-bold text-sm tracking-wide text-zinc-500">{{ $name }}</p>
                <span class="tick w-6 h-6 grid place-items-center rounded-full border-2 border-zinc-300 dark:border-zinc-600 text-transparent">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                </span>
              </div>
              <p class="mt-1 font-display font-extrabold text-2xl text-zinc-950 dark:text-white">UGX {{ number_format($p['price']) }}</p>
              <p class="text-xs text-zinc-500 mt-1">{{ $p['deposit'] }} deposit · {{ $p['blurb'] }}</p>
            </div>
          </label>
          @endforeach
        </div>
      </section>
      <section class="mt-8">
        <h2 class="font-display font-bold text-lg text-zinc-950 dark:text-white">Your domain <span class="text-xs font-semibold text-zinc-500">— one-time fee, never billed again</span></h2>
        @php $domAccents = ['none' => 'zinc', 'subdomain' => 'brand', 'budget' => 'emerald', 'premium' => 'amber']; @endphp
        <div class="mt-3 grid gap-3 sm:grid-cols-2">
          @foreach($domains as $key => $d)
          <label class="opt cursor-pointer" data-accent="{{ $domAccents[$key] ?? 'zinc' }}">
            <input type="radio" name="domain" value="{{ $key }}" class="sr-only" {{ $selectedDomain === $key ? 'checked' : '' }}>
            <div class="h-full rounded-xl bg-white dark:bg-[#141416] p-5 transition">
              <div class="flex items-center justify-between gap-2">
                <p class="font-bold text-sm text-zinc-950 dark:text-white">{{ $d['label'] }}</p>
                <span class="tick w-6 h-6 grid place-items-center rounded-full border-2 border-zinc-300 dark:border-zinc-600 text-transparent shrink-0">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                </span>
              </div>
              <p class="mt-1 text-xl font-extrabold text-zinc-950 dark:text-white">{{ $d['fee'] === 0 ? 'Free' : 'UGX '.number_format($d['fee']).' once' }}</p>
              <p class="text-xs text-zinc-500 mt-1">{{ $key === 'none' ? 'Use a domain you already own' : ($key === 'subdomain' ? 'Free forever · we set it up on our server' : ($key === 'budget' ? '.xyz, .online or .shop — we register it for you' : '.com or .org — we register it for you')) }}</p>
            </div>
          </label>
          @endforeach
        </div>
        <div id="subdomain-row" class="mt-3 hidden max-w-md">
          <label class="text-xs font-semibold">Your free subdomain *</label>
          <div class="flex items-stretch gap-0">
            <input id="f-subdomain" name="subdomain_label" value="{{ old('subdomain_label') }}" placeholder="mybusiness" class="mt-1 w-full bg-white dark:bg-[#17171A] border border-zinc-200 dark:border-white/15 rounded-lg rounded-r-none! px-4 py-2 text-sm outline-none focus:border-brand-600 text-zinc-900 dark:text-white">
            <span class="mt-1 grid shrink-0 place-items-center rounded-r-lg border border-l-0 border-zinc-200 dark:border-white/15 bg-zinc-50 dark:bg-white/5 px-3 text-sm text-zinc-500">.{{ config('hosting.base_domain') }}</span>
          </div>
          <p class="mt-1 text-xs text-zinc-500">Letters, numbers and hyphens. Reserved names and taken subdomains are rejected.</p>
        </div>
      </section>
      <div class="mt-6 flex justify-end"><button type="button" data-next class="btn-dark">Continue →</button></div>
      </div>
      <div class="wiz-step hidden" data-step="2">
      <section>
        <h2 class="font-display font-bold text-lg text-zinc-950 dark:text-white">2. Your details</h2>
        <div class="mt-3 grid sm:grid-cols-2 gap-4">
          <div><label class="text-xs font-semibold">Business name *</label><input required id="f-business" name="business_name" value="{{ old('business_name') }}" placeholder="Savanna Build Ltd" class="mt-1 w-full bg-white dark:bg-[#17171A] border border-zinc-200 dark:border-white/15 rounded-lg px-4 py-2 text-sm outline-none focus:border-brand-600 text-zinc-900 dark:text-white"></div>
          <div><label class="text-xs font-semibold">Phone / WhatsApp *</label><input required id="f-phone" name="phone" value="{{ old('phone') }}" placeholder="+256 700 000000" class="mt-1 w-full bg-white dark:bg-[#17171A] border border-zinc-200 dark:border-white/15 rounded-lg px-4 py-2 text-sm outline-none focus:border-brand-600 text-zinc-900 dark:text-white"></div>
          <div><label class="text-xs font-semibold">Industry / niche</label><select id="f-niche" name="niche" class="mt-1 w-full bg-white dark:bg-[#17171A] border border-zinc-200 dark:border-white/15 rounded-lg px-4 py-2 text-sm outline-none focus:border-brand-600 text-zinc-900 dark:text-white"><option value="">Select…</option>@foreach(config('packages.niches') as $n)<option {{ old('niche', $selectedNiche) === $n ? 'selected' : '' }}>{{ $n }}</option>@endforeach</select></div>
          <div><label class="text-xs font-semibold">Notes (optional)</label><input id="f-notes" name="notes" value="{{ old('notes') }}" placeholder="Anything we should know" class="mt-1 w-full bg-white dark:bg-[#17171A] border border-zinc-200 dark:border-white/15 rounded-lg px-4 py-2 text-sm outline-none focus:border-brand-600 text-zinc-900 dark:text-white"></div>
        </div>
        <p id="wiz-err-3" class="hidden mt-3 text-sm font-semibold text-red-600 dark:text-red-400">Please add your business name and phone number to continue.</p>
      </section>
      <section class="mt-8">
        <h2 class="font-display font-bold text-lg text-zinc-950 dark:text-white">How you pay over time</h2>
        @php $freqAccents = ['full' => 'blue', 'monthly' => 'brand', 'weekly' => 'emerald', 'daily' => 'amber']; @endphp
        <div class="mt-3 grid sm:grid-cols-2 gap-3">
          @foreach([['full','Pay in full','fullLabel'],['monthly','Monthly','monthlyLabel'],['weekly','Weekly','weeklyLabel'],['daily','Daily','dailyLabel']] as [$val,$title,$lid])
          <label class="opt cursor-pointer" data-accent="{{ $freqAccents[$val] }}">
            <input type="radio" name="billing_frequency" value="{{ $val }}" class="sr-only" {{ $selectedBilling === $val ? 'checked' : '' }}>
            <div class="rounded-xl bg-white dark:bg-[#141416] px-5 py-4 transition flex items-center justify-between gap-2">
              <div><p class="font-bold text-zinc-950 dark:text-white">{{ $title }}</p>
              <p class="text-sm text-zinc-500" id="{{ $lid }}">{{ $title }}</p></div>
              <span class="tick w-6 h-6 grid place-items-center rounded-full border-2 border-zinc-300 dark:border-zinc-600 text-transparent shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
              </span>
            </div>
          </label>
          @endforeach
        </div>
      </section>
      <div class="mt-6 flex justify-between"><button type="button" data-back class="btn-ghost">← Back</button><button type="button" data-next class="btn-dark">Continue →</button></div>
      </div>
      <div class="wiz-step hidden" data-step="3">
      <section>
        <h2 class="font-display font-bold text-lg text-zinc-950 dark:text-white">3. Pay now</h2>
        <p class="text-sm text-zinc-500 mt-1">Your website stays running while your plan is active. Full upfront payment covers 12 months.</p>
        <select name="duration_months" id="duration" class="mt-3 w-full sm:w-72 bg-white dark:bg-[#17171A] border border-zinc-200 dark:border-white/15 rounded-lg px-4 py-2 text-sm outline-none focus:border-brand-600 text-zinc-900 dark:text-white">
          @foreach($durations as $d)
          <option value="{{ $d }}" {{ $d === 12 ? 'selected' : '' }}>{{ $d }} months</option>
          @endforeach
        </select>
      </section>
      <section class="mt-6 rounded-xl bg-white dark:bg-[#141416] p-5">
        <h3 class="font-display font-bold text-base text-zinc-950 dark:text-white">Mobile Money — approve on your phone</h3>
        <p class="mt-1 text-sm text-zinc-500">We send a payment prompt to this number. Enter your MoMo PIN on your handset and you are done — no codes, no waiting.</p>
        <div class="mt-3 max-w-md">
          <label class="text-xs font-semibold" for="pay-phone">Mobile Money number *</label>
          <input id="pay-phone" inputmode="tel" placeholder="0772 123456" class="mt-1 w-full bg-white dark:bg-[#17171A] border border-zinc-200 dark:border-white/15 rounded-lg px-4 py-2 text-sm outline-none focus:border-brand-600 text-zinc-900 dark:text-white">
        </div>
        <p id="pay-err" class="hidden mt-3 text-sm font-semibold text-red-600 dark:text-red-400"></p>
        <div id="pay-pending" class="hidden mt-4 rounded-xl bg-brand-50 dark:bg-brand-500/10 p-4">
          <p class="flex items-center gap-2 text-sm font-bold text-zinc-900 dark:text-white"><span class="size-4 animate-spin rounded-full border-2 border-brand-600 border-t-transparent"></span><span>Waiting for approval…</span></p>
          <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Approve the <strong id="pay-pending-amount">…</strong> prompt on <strong id="pay-pending-phone">your phone</strong>, then enter your MoMo PIN.</p>
          <button type="button" id="pay-cancel" class="mt-2 text-xs font-bold text-zinc-500 hover:underline">Cancel and try again</button>
        </div>
        @if($iotecEnabled)
        <div class="mt-4 flex flex-col sm:flex-row gap-3">
          <button type="button" id="pay-submit" class="btn-brand flex-1 py-4! text-base font-extrabold">Pay <span id="pay-amount">…</span> now</button>
          <button type="button" id="pay-card" class="btn-ghost">Pay by card</button>
        </div>
        <p class="mt-3 text-[11px] text-zinc-500">Secured by ioTec Pay. You pay exactly what is shown — no hidden fees.</p>
        @else
        <div class="mt-4 rounded-xl bg-amber-500/10 p-4 text-sm text-amber-800 dark:text-amber-200">Online payments are switching on soon. Place your order and confirm with us on WhatsApp — we will activate it immediately.</div>
        @endif
      </section>
      <div class="mt-6 flex justify-start"><button type="button" data-back class="btn-ghost">← Back</button></div>
      </div>

    </div>

    <aside id="tour-summary" class="lg:sticky lg:top-24 rounded-xl bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 p-6 shadow-xl h-fit">
      <p class="text-xs font-bold tracking-wide uppercase opacity-60">Order summary</p>
      <p class="font-display font-bold text-xl mt-2" id="sumPackage">GROW</p>
      <dl class="mt-4 space-y-2 text-sm">
        <div class="flex justify-between gap-2"><dt class="opacity-60">Package total</dt><dd class="font-semibold" id="sumPkgTotal">—</dd></div>
        <div class="flex justify-between gap-2"><dt class="opacity-60">Domain · one-time</dt><dd class="font-semibold" id="sumDomain">—</dd></div>
        <div class="flex justify-between gap-2"><dt class="opacity-60">Billing</dt><dd class="font-semibold" id="sumFreq">Monthly</dd></div>
        <div class="flex justify-between gap-2"><dt class="opacity-60">Term</dt><dd class="font-semibold" id="sumDuration">12 months</dd></div>
        <div class="flex justify-between gap-2"><dt class="opacity-60">Then</dt><dd class="font-semibold" id="sumRest">—</dd></div>
      </dl>
      <div class="mt-4 pt-4 border-t border-white/15 dark:border-zinc-950/10">
        <p class="text-xs opacity-60">First payment — due today</p>
        <p class="font-display font-extrabold text-3xl" id="sumDue">—</p>
        <p class="text-xs opacity-60 mt-1" id="sumDueNote">domain one-time fee + first installment</p>
        <p class="text-xs opacity-60 mt-2">Grand total over term: <strong id="sumTotal">—</strong></p>
      </div>

      <p class="mt-5 text-[11px] opacity-60">You pay exactly what is shown here — no hidden fees. Pay on the last step with Mobile Money or card.</p>
    </aside>
  </form>
</div>

<script>
const DATA = @json($packages);
const DOMAINS = @json($domains);
const IOTEC = @json($iotecEnabled);
const fmt = n => 'UGX ' + Number(n).toLocaleString('en-US');
const pkgInputs = [...document.querySelectorAll('input[name="package"]')];
const freqInputs = [...document.querySelectorAll('input[name="billing_frequency"]')];
const domInputs = [...document.querySelectorAll('input[name="domain"]')];
const durationSel = document.getElementById('duration');
const LS_KEY = 'ttryy-checkout-v1';
const cur = () => ({
  pkg: (pkgInputs.find(i => i.checked) || {}).value || 'GROW',
  freq: (freqInputs.find(i => i.checked) || {}).value || 'daily',
  dom: (domInputs.find(i => i.checked) || {}).value || 'none',
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
    const c = cur();
    localStorage.setItem(LS_KEY, JSON.stringify({ ...c,
      business: document.getElementById('f-business').value, phone: document.getElementById('f-phone').value,
      niche: document.getElementById('f-niche').value, notes: document.getElementById('f-notes').value,
      subdomain: document.getElementById('f-subdomain').value }));
    const q = new URLSearchParams({ package: c.pkg, billing: c.freq, domain: c.dom, duration: String(c.months) });
    const niche = document.getElementById('f-niche').value;
    if(niche) q.set('niche', niche);
    history.replaceState(null, '', location.pathname + '?' + q.toString());
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
  if(s.subdomain) document.getElementById('f-subdomain').value = s.subdomain;
  syncCo(durationSel); syncCo(document.getElementById('f-niche'));
  syncSubdomainRow();
  return true;
}
function syncSubdomainRow(){
  const show = ((domInputs.find(i => i.checked) || {}).value || 'none') === 'subdomain';
  document.getElementById('subdomain-row').classList.toggle('hidden', !show);
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
  const payAmt = document.getElementById('pay-amount');
  if(payAmt) payAmt.textContent = fmt(q.due);
  const payPhone = document.getElementById('pay-phone');
  const detailPhone = document.getElementById('f-phone');
  if(payPhone && !payPhone.value && detailPhone && detailPhone.value) payPhone.value = detailPhone.value;
  durationSel.disabled = freq === 'full';
  durationSel.classList.toggle('opacity-50', freq === 'full');
}
function showPayError(msg){
  const box = document.getElementById('pay-error');
  box.textContent = msg;
  box.classList.remove('hidden');
  box.scrollIntoView({ behavior: 'smooth', block: 'center' });
  toast(msg, 'danger');
}
async function createOrder(method){
  const form = document.getElementById('checkout-form');
  const data = new FormData(form);
  data.set('payment_method', method);
  const res = await fetch(form.action, { method: 'POST', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, body: data });
  if(res.status === 422){
    const j = await res.json().catch(() => ({}));
    const first = j.message || (j.errors ? Object.values(j.errors).flat()[0] : 'Please check the form and try again.');
    throw new Error(first);
  }
  if(!res.ok) throw new Error('Something went wrong. Please try again.');
  return res.json();
}
function closeAllCo(){ document.querySelectorAll('.cs-list').forEach(l=>l.classList.add('hidden')); document.querySelectorAll('.cs-btn').forEach(b=>b.setAttribute('aria-expanded','false')); }
function syncCo(sel){
  const wrap=sel.closest('.cs-wrap'); if(!wrap) return;
  const label=wrap.querySelector('.cs-label'), opt=sel.options[sel.selectedIndex];
  label.textContent=opt?opt.textContent:'Select…';
  wrap.querySelectorAll('.cs-list button').forEach(b=>{ const tick=b.querySelector('.cs-tick'); if(tick) tick.classList.toggle('hidden', b.dataset.value!==sel.value); });
}
function enhanceCoSelect(sel){
  if(!sel || sel.closest('.cs-wrap')) return;
  const wrap=document.createElement('div'); wrap.className='cs-wrap relative';
  sel.parentNode.insertBefore(wrap, sel); wrap.appendChild(sel);
  sel.classList.add('sr-only'); sel.tabIndex=-1; sel.setAttribute('aria-hidden','true');
  const btn=document.createElement('button');
  btn.type='button';
  btn.className='cs-btn w-full flex items-center justify-between gap-2 rounded-lg border border-zinc-200 dark:border-white/15 bg-white dark:bg-[#17171A] px-4 py-2 text-sm text-zinc-900 dark:text-white outline-none';
  btn.setAttribute('aria-expanded','false');
  btn.innerHTML='<span class="cs-label truncate">Select…</span><svg class="w-4 h-4 shrink-0 text-zinc-400" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>';
  const list=document.createElement('div');
  list.className='cs-list hidden absolute z-30 left-0 right-0 mt-2 rounded-xl border border-zinc-200 dark:border-white/15 bg-white dark:bg-[#141416] shadow-2xl p-1.5 max-h-60 overflow-y-auto';
  list.setAttribute('role','listbox');
  [...sel.options].forEach(o=>{
    const item=document.createElement('button');
    item.type='button'; item.dataset.value=o.value; item.setAttribute('role','option');
    item.className='w-full flex items-center justify-between gap-2 text-left px-4 py-2 rounded-lg text-sm transition hover:bg-zinc-100 dark:hover:bg-white/10 '+(o.value===''?'text-zinc-400':'text-zinc-800 dark:text-zinc-200');
    item.innerHTML='<span class="truncate">'+o.textContent+'</span><svg class="cs-tick w-4 h-4 shrink-0 text-zinc-900 dark:text-white hidden" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>';
    item.addEventListener('click',()=>{ sel.value=o.value; sel.dispatchEvent(new Event('change',{bubbles:true})); syncCo(sel); closeAllCo(); save(); });
    list.appendChild(item);
  });
  btn.addEventListener('click',()=>{ const willOpen=list.classList.contains('hidden'); closeAllCo(); if(willOpen){ list.classList.remove('hidden'); btn.setAttribute('aria-expanded','true'); } });
  wrap.appendChild(btn); wrap.appendChild(list);
  syncCo(sel);
}
document.addEventListener('click',e=>{ if(!e.target.closest || !e.target.closest('.cs-wrap')) closeAllCo(); });
document.addEventListener('keydown',e=>{ if(e.key==='Escape') closeAllCo(); });
function wizDetailsOk(){
  const b = document.getElementById('f-business'), p = document.getElementById('f-phone'), err = document.getElementById('wiz-err-3');
  const ok = b && p && b.value.trim() !== '' && p.value.trim() !== '';
  if(err) err.classList.toggle('hidden', ok);
  if(!ok) [b, p].forEach(el => {
    if(!el || el.value.trim() !== '') return;
    el.style.borderColor = '#ef4444';
    const clear = () => { el.style.borderColor = ''; };
    el.addEventListener('input', clear, { once: true });
  });
  return ok;
}
function paintWiz(scroll){
  const W = window.__wiz; if(!W) return;
  document.querySelectorAll('.wiz-step').forEach(p => p.classList.toggle('hidden', +p.dataset.step !== W.step));
  document.querySelectorAll('#wiz-dots .wiz-dot').forEach(d => {
    const n = +d.dataset.goto, num = d.querySelector('.wiz-num');
    const isCur = n === W.step, isPast = n < W.step, seen = !!W.visited[n];
    const base = 'wiz-num w-8 h-8 grid place-items-center rounded-full text-xs font-extrabold border-2 ';
    num.className = base + (isCur ? 'bg-brand-600 border-brand-600 text-white shadow-lg'
      : isPast ? 'border-brand-600 text-brand-700 dark:text-brand-300'
      : seen ? 'border-zinc-400 dark:border-white/30 text-zinc-500 dark:text-zinc-300'
      : 'border-zinc-300 dark:border-white/15 text-zinc-400');
    d.classList.toggle('cursor-pointer', seen && !isCur);
    d.classList.toggle('opacity-60', !seen && !isCur);
  });
  const bar = document.getElementById('wiz-bar');
  if(bar) bar.style.width = Math.round(W.step / 3 * 100) + '%';
  if(scroll !== false){
    const form = document.getElementById('checkout-form');
    if(form) form.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
}
function initWizard(){
  const form = document.getElementById('checkout-form');
  const dots = [...document.querySelectorAll('#wiz-dots .wiz-dot')];
  if(!dots.length || !form) return;
  if(!window.__wiz){
    const start = Math.min(3, Math.max(1, parseInt(form.dataset.wizStart || '1', 10) || 1));
    window.__wiz = { step: start, visited: {} };
    window.__wiz.visited[start] = true;
    if(start > 1){ for(let s = 1; s < start; s++) window.__wiz.visited[s] = true; }
  }
  if(form.dataset.wizInit){ paintWiz(false); return; }
  form.dataset.wizInit = '1';
  const W = window.__wiz;
  const go = n => { W.step = Math.min(3, Math.max(1, n)); W.visited[W.step] = true; paintWiz(true); };
  window.__wizGo = go;
  document.querySelectorAll('[data-next]').forEach(b => b.addEventListener('click', () => {
    if(W.step === 2 && !wizDetailsOk()) return;
    go(W.step + 1);
  }));
  document.querySelectorAll('[data-back]').forEach(b => b.addEventListener('click', () => go(W.step - 1)));
  dots.forEach(d => d.addEventListener('click', () => {
    const n = parseInt(d.dataset.goto, 10);
    if(W.visited[n]) go(n);
  }));
  paintWiz(false);
}
function initCheckout(){
  initWizard();
  const formEl = document.getElementById('checkout-form');
  if(formEl.dataset.initDone){ refresh(); return; }
  formEl.dataset.initDone = '1';
  enhanceCoSelect(document.getElementById('duration'));
  enhanceCoSelect(document.getElementById('f-niche'));
  restore();
  refresh();
  [...pkgInputs, ...freqInputs, ...domInputs].forEach(i => i.addEventListener('change', () => { refresh(); save(); syncSubdomainRow(); }));
  syncSubdomainRow();
  durationSel.addEventListener('change', () => { refresh(); save(); });
  ['f-business','f-phone','f-niche','f-notes'].forEach(id => {
    const el = document.getElementById(id);
    if(el) el.addEventListener('input', save);
  });
  const tabLogin = document.getElementById('tab-login'), tabReg = document.getElementById('tab-register');
  const pLogin = document.getElementById('panel-login'), pReg = document.getElementById('panel-register');
  const paintTabs = login => {
    const on = 'rounded-full px-4 py-2 text-sm font-bold transition bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 shadow';
    const off = 'rounded-full px-4 py-2 text-sm font-bold transition text-zinc-500 bg-zinc-100 hover:text-zinc-900 dark:bg-white/5 dark:hover:text-white';
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
    toast('Please log in or create an account first — your choices are saved.', 'warning');
    const auth = document.getElementById('checkout-auth');
    if(auth) auth.scrollIntoView({ behavior: 'smooth', block: 'start' });
  });
  let payPollTimer = null;
  function payError(msg){
    const box = document.getElementById('pay-err');
    if(box){ box.textContent = msg; box.classList.remove('hidden'); }
    toast(msg, 'danger');
  }
  function setPayPending(on, amount, phone){
    const panel = document.getElementById('pay-pending');
    if(!panel) return;
    panel.classList.toggle('hidden', !on);
    if(on){
      const a = document.getElementById('pay-pending-amount'), p = document.getElementById('pay-pending-phone');
      if(a && amount) a.textContent = amount;
      if(p && phone) p.textContent = phone;
    }
  }
  function stopPayPoll(){
    if(payPollTimer){ clearInterval(payPollTimer); payPollTimer = null; }
  }
  async function postJSON(url, body){
    const token = form.querySelector('input[name=_token]').value;
    const res = await fetch(url, { method: 'POST',
      headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
      body: JSON.stringify(body || {}) });
    return res.json().catch(() => ({}));
  }
  function pollOrderStatus(reference, left){
    stopPayPoll();
    let tries = left || 40;
    payPollTimer = setInterval(async () => {
      tries--;
      try{
        const s = await (await fetch("{{ route('checkout.iotec.status') }}?reference=" + encodeURIComponent(reference), { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })).json();
        if(s.status === 'paid' && s.redirect){ stopPayPoll(); window.location.href = s.redirect; return; }
        if(s.status === 'failed'){ stopPayPoll(); setPayPending(false); payError(s.message || 'The payment was not approved. Try again.'); const b = document.getElementById('pay-submit'); if(b){ b.disabled = false; b.classList.remove('opacity-60'); } return; }
      }catch(e){}
      if(tries <= 0){ stopPayPoll(); setPayPending(false); payError('Still waiting on the provider — if you approved, check My packages in a minute. Your order is saved.'); const b = document.getElementById('pay-submit'); if(b){ b.disabled = false; b.classList.remove('opacity-60'); } }
    }, 3000);
  }
  const paySubmit = document.getElementById('pay-submit');
  if(paySubmit){
    paySubmit.addEventListener('click', async () => {
      if(form.dataset.guest === '1'){ form.requestSubmit(); return; }
      if(!form.checkValidity()){ form.reportValidity(); return; }
      const phoneEl = document.getElementById('pay-phone');
      const phone = (phoneEl && phoneEl.value || '').trim();
      if(!phone){ payError('Enter the Mobile Money number that will approve this payment.'); if(phoneEl) phoneEl.focus(); return; }
      document.getElementById('pay-err').classList.add('hidden');
      paySubmit.disabled = true; paySubmit.classList.add('opacity-60');
      try{
        const order = await createOrder('iotec');
        const pay = await postJSON("{{ route('checkout.iotec.pay') }}", { reference: order.reference, phone });
        if(pay.redirect){ window.location.href = pay.redirect; return; }
        if(!pay.tx_id) throw new Error(pay.message || 'Payment could not be started. Please try again.');
        setPayPending(true, fmt(order.due_today), phone);
        pollOrderStatus(order.reference, 40);
      }catch(err){ payError(err.message || 'Something went wrong. Please try again.'); paySubmit.disabled = false; paySubmit.classList.remove('opacity-60'); }
    });
  }
  const payCancel = document.getElementById('pay-cancel');
  if(payCancel) payCancel.addEventListener('click', () => {
    stopPayPoll(); setPayPending(false);
    const b = document.getElementById('pay-submit'); if(b){ b.disabled = false; b.classList.remove('opacity-60'); }
  });
  const payCard = document.getElementById('pay-card');
  if(payCard){
    payCard.addEventListener('click', async () => {
      if(form.dataset.guest === '1'){ form.requestSubmit(); return; }
      if(!form.checkValidity()){ form.reportValidity(); return; }
      payCard.disabled = true; payCard.classList.add('opacity-60');
      try{
        const order = await createOrder('iotec');
        const r = await postJSON("{{ route('checkout.iotec.card') }}", { reference: order.reference });
        if(r.redirect){ window.location.href = r.redirect; return; }
        throw new Error(r.message || 'Card payments could not be started. Please pay with Mobile Money.');
      }catch(err){ payError(err.message || 'Something went wrong. Please try again.'); }
      payCard.disabled = false; payCard.classList.remove('opacity-60');
    });
  }
}
if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initCheckout);
else initCheckout();
if(window.Livewire) document.addEventListener('livewire:navigated', initCheckout);
</script>
</x-layouts::checkout>
