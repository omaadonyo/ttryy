<x-layouts::checkout :title="__('Checkout')">
    @include('partials.toast')
    <style>
      .opt{ display:block; cursor:pointer; }
      .opt > div{ height:100%; border:1px solid rgb(228 228 231); border-radius:.5rem; background:#fff; transition:border-color .18s ease, box-shadow .18s ease, background-color .18s ease; }
      .dark .opt > div{ border-color:rgb(255 255 255 / .12); background:#141416; }
      .opt:hover > div{ border-color:rgb(161 161 170); }
      .dark .opt:hover > div{ border-color:rgb(255 255 255 / .28); }
      .opt input:checked + div{ border-color:#9e005d; background:#fdf2f7; box-shadow:0 0 0 1px #9e005d; }
      .dark .opt input:checked + div{ background:color-mix(in srgb, #9e005d 18%, transparent); }
      .opt input:focus-visible + div{ outline:2px solid #9e005d; outline-offset:2px; }
      .opt .tick{ opacity:0; transform:scale(.5); transition:opacity .2s ease, transform .2s ease, background-color .2s ease, border-color .2s ease; }
      .opt input:checked + div .tick{ opacity:1; transform:scale(1); background:#9e005d; border-color:#9e005d; color:#fff; }
      .cs-wrap:focus-within .cs-btn{ border-color:#9e005d; }
      .cs-btn svg{ transition:transform .25s ease; }
      .cs-btn[aria-expanded="true"] svg{ transform:rotate(180deg); }
      .sec-num{ display:grid; width:1.5rem; height:1.5rem; flex:none; place-items:center; border-radius:9999px; background:var(--color-brand-600); color:#fff; font-size:11px; font-weight:800; }
    </style>

    <div class="flex h-full w-full flex-1 flex-col gap-4">
      <div>
        <p class="text-xs font-semibold tracking-wide uppercase text-zinc-500">Secure checkout</p>
        <h1 class="font-display font-extrabold text-2xl sm:text-3xl mt-1 text-zinc-950 dark:text-white">Complete your order</h1>
        <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-2 max-w-2xl">Choose a package, pick your domain and pay with Mobile Money in seconds. You pay exactly what the summary shows — no hidden fees.</p>
      </div>

      <div class="flex items-center gap-2 text-xs flex-wrap">
        @auth
        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 px-3 py-1 font-semibold">Signed in as {{ auth()->user()->name }}</span>
        @else
        <span class="inline-flex items-center gap-1.5 rounded-full bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 px-3 py-1 font-semibold">Sign in below to order</span>
        @endauth
        <span class="inline-flex items-center gap-1.5 rounded-full bg-zinc-100 dark:bg-white/10 text-zinc-600 dark:text-zinc-300 px-3 py-1 font-semibold">Secure payment</span>
        <span class="inline-flex items-center gap-1.5 rounded-full bg-zinc-100 dark:bg-white/10 text-zinc-600 dark:text-zinc-300 px-3 py-1 font-semibold">Live in 5 days</span>
        <button onclick="startTour_checkout()" class="ml-auto font-bold text-brand-700 hover:underline dark:text-brand-300">Take the tour</button>
      </div>

      @include('partials.tour', ['key' => 'checkout', 'steps' => [
          ['target' => '#sec-package', 'title' => '1 · Package', 'body' => 'START, GROW, BUSINESS or CORPORATE. Pick the one that fits where you are.'],
          ['target' => '#sec-domain', 'title' => '2 · Domain', 'body' => 'A free yourname.ttryy.com subdomain, or a paid domain — a one-time fee included in your order.'],
          ['target' => '#sec-details', 'title' => '3 · Details & rhythm', 'body' => 'Your business name and phone, plus how you want to pay over time: full, monthly, weekly or daily.'],
          ['target' => '#sec-pay', 'title' => '4 · Pay', 'body' => 'Enter your Mobile Money number, hit Pay, approve the prompt on your phone. Done.'],
          ['target' => '#tour-summary', 'title' => 'Review and pay', 'body' => 'The summary always shows exactly what is due today.'],
      ]])

      @auth
      @include('partials.onboard', ['key' => 'checkout', 'title' => 'How ordering works', 'body' => 'Pick a plan, tell us about your business, then pay with Mobile Money in seconds. You pay exactly what the summary shows — no hidden fees.', 'link' => route('packages.index'), 'linkLabel' => 'Review my packages →'])
      @endauth

      @guest
      <div id="checkout-auth" class="card card-pad">
        <h2 class="font-display font-bold text-xl text-zinc-950 dark:text-white">Create an account or log in</h2>
        <p class="mt-1 text-sm text-zinc-500">Your choices below are saved automatically — nothing is lost when you sign in.</p>
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
      <div class="rounded-lg border border-red-300 bg-red-50 dark:bg-red-500/10 px-5 py-4 text-sm text-red-700 dark:text-red-300">
        <p class="font-bold">Please fix the following:</p>
        <ul class="list-disc ml-5 mt-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
      </div>
      @endif
      <div id="pay-error" class="hidden rounded-lg border border-red-300 bg-red-50 dark:bg-red-500/10 px-5 py-4 text-sm text-red-700 dark:text-red-300"></div>

      <form method="POST" action="{{ route('checkout.store') }}" id="checkout-form" class="grid lg:grid-cols-[1fr_336px] gap-5 items-start" data-guest="{{ auth()->check() ? '0' : '1' }}">
        @csrf
        <input type="hidden" name="payment_method" id="pay-method" value="iotec">

        <div class="space-y-4 min-w-0">
          {{-- 1 · PACKAGE --}}
          <section id="sec-package" class="card card-pad">
            <div class="flex items-center gap-2.5">
              <span class="sec-num">1</span>
              <h2 class="font-display font-bold text-base text-zinc-950 dark:text-white">Choose your package</h2>
            </div>
            <div class="mt-4 grid sm:grid-cols-2 gap-3">
              @foreach($packages as $name => $p)
              <label class="opt">
                <input type="radio" name="package" value="{{ $name }}" class="sr-only" {{ $selectedPackage === $name ? 'checked' : '' }}>
                <div class="p-4">
                  <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                      <p class="font-display font-extrabold text-base tracking-wide text-zinc-950 dark:text-white">{{ $name }}</p>
                      <p class="mt-0.5 text-xs leading-snug text-zinc-500">{{ $p['blurb'] }}</p>
                    </div>
                    <span class="tick grid size-5 shrink-0 place-items-center rounded-full border-2 border-zinc-300 dark:border-zinc-600 text-transparent">
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                    </span>
                  </div>
                  <p class="mt-3 font-display font-extrabold text-xl text-zinc-950 dark:text-white">UGX {{ number_format($p['price']) }}</p>
                  <p class="mt-1 text-xs text-zinc-500">or from <strong class="text-zinc-700 dark:text-zinc-300">UGX {{ number_format($dailyFrom[$name]) }}/day</strong> · paid off within the term</p>
                </div>
              </label>
              @endforeach
            </div>
          </section>

          {{-- 2 · DOMAIN --}}
          <section id="sec-domain" class="card card-pad">
            <div class="flex items-center gap-2.5">
              <span class="sec-num">2</span>
              <h2 class="font-display font-bold text-base text-zinc-950 dark:text-white">Your domain</h2>
              <span class="ml-auto text-[11px] font-semibold text-zinc-500">One-time fee · only paid once</span>
            </div>
            <div class="mt-4 grid sm:grid-cols-2 gap-3">
              @foreach($domains as $key => $d)
              <label class="opt">
                <input type="radio" name="domain" value="{{ $key }}" class="sr-only" {{ $selectedDomain === $key ? 'checked' : '' }}>
                <div class="p-4">
                  <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                      <p class="font-bold text-sm text-zinc-950 dark:text-white">{{ $d['label'] }}</p>
                      <p class="mt-0.5 text-xs leading-snug text-zinc-500">{{ $key === 'none' ? 'Use a domain you already own' : ($key === 'subdomain' ? 'Free forever · we set it up on our server' : ($key === 'budget' ? '.xyz, .online or .shop — we register it for you' : '.com or .org — we register it for you')) }}</p>
                    </div>
                    <span class="tick grid size-5 shrink-0 place-items-center rounded-full border-2 border-zinc-300 dark:border-zinc-600 text-transparent">
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                    </span>
                  </div>
                  <p class="mt-2 text-sm font-bold {{ $d['fee'] === 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-brand-700 dark:text-brand-300' }}">{{ $d['fee'] === 0 ? 'Free · included' : 'UGX '.number_format($d['fee']).' one-time' }}</p>
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
            <div id="existing-domain-row" class="mt-3 hidden max-w-md">
              <label class="text-xs font-semibold" for="f-existing">Your existing domain</label>
              <input id="f-existing" name="custom_domain" value="{{ old('custom_domain') }}" placeholder="yourbusiness.com" class="mt-1 w-full bg-white dark:bg-[#17171A] border border-zinc-200 dark:border-white/15 rounded-lg px-4 py-2 text-sm outline-none focus:border-brand-600 text-zinc-900 dark:text-white">
              <p class="mt-1 text-xs text-zinc-500">The domain you already own. Our team points it at your new site and sets up SSL — usually within 24 hours.</p>
            </div>
          </section>

          {{-- 3 · DETAILS + RHYTHM --}}
          <section id="sec-details" class="card card-pad">
            <div class="flex items-center gap-2.5">
              <span class="sec-num">3</span>
              <h2 class="font-display font-bold text-base text-zinc-950 dark:text-white">Your details &amp; payment rhythm</h2>
            </div>
            <div class="mt-4 grid sm:grid-cols-2 gap-4">
              <div><label class="text-xs font-semibold">Business name *</label><input required id="f-business" name="business_name" value="{{ old('business_name') }}" placeholder="Savanna Build Ltd" class="mt-1 w-full bg-white dark:bg-[#17171A] border border-zinc-200 dark:border-white/15 rounded-lg px-4 py-2 text-sm outline-none focus:border-brand-600 text-zinc-900 dark:text-white"></div>
              <div><label class="text-xs font-semibold">Phone / WhatsApp *</label><input required id="f-phone" name="phone" value="{{ old('phone') }}" placeholder="+256 700 000000" class="mt-1 w-full bg-white dark:bg-[#17171A] border border-zinc-200 dark:border-white/15 rounded-lg px-4 py-2 text-sm outline-none focus:border-brand-600 text-zinc-900 dark:text-white"></div>
              <div><label class="text-xs font-semibold">Industry / niche</label><select id="f-niche" name="niche" class="mt-1 w-full bg-white dark:bg-[#17171A] border border-zinc-200 dark:border-white/15 rounded-lg px-4 py-2 text-sm outline-none focus:border-brand-600 text-zinc-900 dark:text-white"><option value="">Select…</option>@foreach(config('packages.niches') as $n)<option {{ old('niche', $selectedNiche) === $n ? 'selected' : '' }}>{{ $n }}</option>@endforeach</select></div>
              <div><label class="text-xs font-semibold">Notes (optional)</label><input id="f-notes" name="notes" value="{{ old('notes') }}" placeholder="Anything we should know" class="mt-1 w-full bg-white dark:bg-[#17171A] border border-zinc-200 dark:border-white/15 rounded-lg px-4 py-2 text-sm outline-none focus:border-brand-600 text-zinc-900 dark:text-white"></div>
            </div>

            <p class="mt-6 text-xs font-semibold uppercase tracking-wide text-zinc-500">How you pay over time</p>
            <div class="mt-2 grid grid-cols-2 sm:grid-cols-4 gap-2.5">
              @foreach([['full','Pay in full','fullLabel'],['monthly','Monthly','monthlyLabel'],['weekly','Weekly','weeklyLabel'],['daily','Daily','dailyLabel']] as [$val,$title,$lid])
              <label class="opt">
                <input type="radio" name="billing_frequency" value="{{ $val }}" class="sr-only" {{ $selectedBilling === $val ? 'checked' : '' }}>
                <div class="p-3.5">
                  <div class="flex items-center justify-between gap-1">
                    <p class="font-bold text-sm text-zinc-950 dark:text-white">{{ $title }}</p>
                    <span class="tick grid size-4 shrink-0 place-items-center rounded-full border-2 border-zinc-300 dark:border-zinc-600 text-transparent">
                      <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                    </span>
                  </div>
                  <p class="mt-1 text-[11px] text-zinc-500" id="{{ $lid }}">{{ $title }}</p>
                </div>
              </label>
              @endforeach
            </div>
            <div class="mt-3 flex items-center gap-3 flex-wrap">
              <label class="text-xs font-semibold text-zinc-500">Term</label>
              <select name="duration_months" id="duration" class="w-40 bg-white dark:bg-[#17171A] border border-zinc-200 dark:border-white/15 rounded-lg px-4 py-2 text-sm outline-none focus:border-brand-600 text-zinc-900 dark:text-white">
                @foreach($durations as $d)
                <option value="{{ $d }}" {{ $d === 12 ? 'selected' : '' }}>{{ $d }} months</option>
                @endforeach
              </select>
              <span class="text-xs text-zinc-500">Website runs for the full term. Full payment covers 12 months.</span>
            </div>
          </section>

          {{-- 4 · PAY --}}
          <section id="sec-pay" class="card card-pad">
            <div class="flex items-center gap-2.5">
              <span class="sec-num">4</span>
              <h2 class="font-display font-bold text-base text-zinc-950 dark:text-white">Pay now</h2>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
              <span class="text-sm text-zinc-500">Due today</span>
              <span class="font-display font-extrabold text-2xl text-zinc-950 dark:text-white" id="pay-due-inline">—</span>
              <span class="text-xs text-zinc-500" id="pay-due-note">minimum first payment · covers at least 10 days</span>
            </div>

            <div class="mt-4 rounded-lg border border-zinc-200 dark:border-white/10 p-4">
              <h3 class="font-bold text-sm text-zinc-950 dark:text-white">Mobile Money — approve on your phone</h3>
              <p class="mt-1 text-xs text-zinc-500">We send a payment prompt to this number. Enter your MoMo PIN on your handset and you are done — no codes, no waiting.</p>
              <div class="mt-3 max-w-md">
                <label class="text-xs font-semibold" for="pay-phone">Mobile Money number *</label>
                <input id="pay-phone" inputmode="tel" placeholder="0772 123456" class="mt-1 w-full bg-white dark:bg-[#17171A] border border-zinc-200 dark:border-white/15 rounded-lg px-4 py-2 text-sm outline-none focus:border-brand-600 text-zinc-900 dark:text-white">
              </div>
              <p id="pay-err" class="hidden mt-3 text-sm font-semibold text-red-600 dark:text-red-400"></p>
              <div id="pay-pending" class="hidden mt-4 rounded-lg bg-brand-50 dark:bg-brand-500/10 p-4">
                <p class="flex items-center gap-2 text-sm font-bold text-zinc-900 dark:text-white"><span class="size-4 animate-spin rounded-full border-2 border-brand-600 border-t-transparent"></span><span>Waiting for approval…</span></p>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Approve the <strong id="pay-pending-amount">…</strong> prompt on <strong id="pay-pending-phone">your phone</strong>, then enter your MoMo PIN.</p>
                <button type="button" id="pay-cancel" class="mt-2 text-xs font-bold text-zinc-500 hover:underline">Cancel and try again</button>
              </div>
              @if($iotecEnabled)
              <div class="mt-4 flex flex-col sm:flex-row gap-3">
                <button type="button" id="pay-submit" class="btn-brand flex-1 py-4! text-base font-extrabold">
                  <span data-spin class="hidden size-4 rounded-full border-2 border-white/70 border-t-transparent animate-spin"></span>
                  <span>Pay <span id="pay-amount">…</span> now</span>
                </button>
                <button type="button" id="pay-card" class="btn-ghost py-4!">
                  <span data-spin class="hidden size-4 rounded-full border-2 border-current border-t-transparent animate-spin"></span>
                  <span>Pay by card</span>
                </button>
              </div>
              <p class="mt-3 text-[11px] text-zinc-500">Secured by ioTec Pay. You pay exactly what is shown — no hidden fees.</p>
              <details class="mt-3">
                <summary class="cursor-pointer text-xs font-bold text-zinc-500 hover:underline">Gateway trouble? Place the order manually instead</summary>
                @include('partials.manual-order')
              </details>
              @else
              <div class="mt-4 rounded-lg bg-amber-500/10 p-4 text-sm text-amber-800 dark:text-amber-200">Online payments are switching on soon. Place your order manually below and confirm with us on WhatsApp — we will activate it immediately.</div>
              @include('partials.manual-order')
              @endif
            </div>
          </section>
        </div>

        {{-- SUMMARY --}}
        <aside id="tour-summary" class="lg:sticky lg:top-24 rounded-lg bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 p-5 shadow-xl h-fit max-lg:fixed max-lg:inset-x-3 max-lg:bottom-3 max-lg:z-40 max-lg:p-4 max-lg:max-h-[72vh] max-lg:overflow-y-auto">
          <button type="button" id="sum-toggle" class="w-full items-center justify-between gap-2 hidden max-lg:flex" aria-expanded="false" aria-controls="sum-more">
            <span class="text-xs font-bold tracking-wide uppercase opacity-60">Order summary · tap for details</span>
            <span class="flex items-center gap-1.5"><span class="font-display font-extrabold text-lg" id="sumDueMini">—</span><svg id="sum-chev" class="size-4 opacity-60 transition-transform" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M6 15l6-6 6 6"/></svg></span>
          </button>
          <div id="sum-more" class="max-lg:hidden lg:block">
            <p class="text-xs font-bold tracking-wide uppercase opacity-60 max-lg:hidden">Order summary</p>
            <p class="font-display font-extrabold text-xl mt-1" id="sumPackage">GROW</p>
            <dl class="mt-4 space-y-2 text-sm">
              <div class="flex justify-between gap-2"><dt class="opacity-60">Package total</dt><dd class="font-semibold" id="sumPkgTotal">—</dd></div>
              <div class="flex justify-between gap-2"><dt class="opacity-60">Domain · one-time</dt><dd class="font-semibold" id="sumDomain">—</dd></div>
              <div class="flex justify-between gap-2"><dt class="opacity-60">Billing</dt><dd class="font-semibold" id="sumFreq">Daily</dd></div>
              <div class="flex justify-between gap-2"><dt class="opacity-60">Term</dt><dd class="font-semibold" id="sumDuration">12 months</dd></div>
              <div class="flex justify-between gap-2"><dt class="opacity-60">Then</dt><dd class="font-semibold text-right" id="sumRest">—</dd></div>
            </dl>
            <div class="mt-4 pt-4 border-t border-white/15 dark:border-zinc-950/10">
              <p class="text-xs opacity-60">First payment — due today</p>
              <p class="font-display font-extrabold text-3xl" id="sumDue">—</p>
              <p class="text-xs opacity-60 mt-1" id="sumDueNote">minimum first payment · covers at least 10 days</p>
              <p class="text-xs opacity-60 mt-2">Grand total over term: <strong id="sumTotal">—</strong></p>
            </div>
            <p class="mt-4 text-[11px] opacity-60">You pay exactly what is shown here — no hidden fees. Pay with Mobile Money or card.</p>
          </div>
        </aside>
        <div class="h-24 lg:hidden" aria-hidden="true"></div>
      </form>
    </div>

    <div id="pay-overlay" class="hidden fixed inset-0 z-[80] flex-col items-center justify-center gap-3 bg-white/75 backdrop-blur-sm dark:bg-zinc-950/80">
      <span class="size-11 rounded-full border-[3px] border-brand-600 border-t-transparent animate-spin"></span>
      <p id="pay-overlay-msg" class="font-display font-bold text-zinc-900 dark:text-white">Processing your payment…</p>
      <p class="text-xs text-zinc-500">Please don't close this page.</p>
    </div>

<script>
const DATA = @json($packages);
const QUOTES = @json($quotes);
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
function freqLabel(f){ return f[0].toUpperCase() + f.slice(1); }
function calc(){
  const { pkg, freq, dom, months } = cur();
  const fee = (DOMAINS[dom] || {fee:0}).fee;
  const q = QUOTES[pkg] && QUOTES[pkg][months] && QUOTES[pkg][months][freq];
  if(!q) return { pkgTotal: 0, total: fee, due: fee, rest: '—', rateLabel: '—', freqLabel: freqLabel(freq), durText: months + ' months', periods: 0, rate: 0 };
  const rest = freq === 'full' ? 'Nothing further — paid in full'
    : (q.periods - 1) + ' × ' + fmt(q.rate) + ' ' + q.per;
  return { pkgTotal: q.total, total: q.total + fee, due: q.due + fee, rest,
    rateLabel: fmt(q.rate) + q.per, freqLabel: q.freq_label,
    durText: (freq === 'full' ? 12 : months) + ' months', periods: q.periods, rate: q.rate };
}
function save(){
  try{
    const c = cur();
    localStorage.setItem(LS_KEY, JSON.stringify({ ...c,
      business: document.getElementById('f-business').value, phone: document.getElementById('f-phone').value,
      niche: document.getElementById('f-niche').value, notes: document.getElementById('f-notes').value,
      subdomain: document.getElementById('f-subdomain').value,
      existing: (document.getElementById('f-existing') || {}).value }));
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
  if(s.existing){ const ex = document.getElementById('f-existing'); if(ex) ex.value = s.existing; }
  syncCo(durationSel); syncCo(document.getElementById('f-niche'));
  syncSubdomainRow(); syncExistingDomainRow();
  return true;
}
function syncSubdomainRow(){
  const show = ((domInputs.find(i => i.checked) || {}).value || 'none') === 'subdomain';
  document.getElementById('subdomain-row').classList.toggle('hidden', !show);
}
function syncExistingDomainRow(){
  const show = ((domInputs.find(i => i.checked) || {}).value || 'none') === 'none';
  const row = document.getElementById('existing-domain-row');
  if(row) row.classList.toggle('hidden', !show);
}
function refresh(){
  const { pkg, freq, dom, months } = cur(), q = calc();
  const qM = (QUOTES[pkg] && QUOTES[pkg][months]) || {};
  document.getElementById('sumPackage').textContent = pkg;
  document.getElementById('sumFreq').textContent = q.freqLabel;
  document.getElementById('sumDuration').textContent = q.durText;
  document.getElementById('sumPkgTotal').textContent = fmt(q.pkgTotal);
  document.getElementById('sumDomain').textContent = (DOMAINS[dom] || {fee:0}).fee ? fmt((DOMAINS[dom] || {fee:0}).fee) + ' once' : 'Free';
  document.getElementById('sumRest').textContent = q.rest;
  document.getElementById('sumDue').textContent = fmt(q.due);
  const sumDueMini = document.getElementById('sumDueMini');
  if(sumDueMini) sumDueMini.textContent = fmt(q.due);
  document.getElementById('sumTotal').textContent = fmt(q.total);
  const inlineDue = document.getElementById('pay-due-inline');
  if(inlineDue) inlineDue.textContent = fmt(q.due);
  document.getElementById('monthlyLabel').textContent = qM.monthly ? fmt(qM.monthly.rate) + ' per month' : '—';
  document.getElementById('weeklyLabel').textContent = qM.weekly ? fmt(qM.weekly.rate) + ' per week' : '—';
  document.getElementById('dailyLabel').textContent = qM.daily ? fmt(qM.daily.rate) + ' per day' : '—';
  document.getElementById('fullLabel').textContent = fmt(DATA[pkg].price) + ' once · 12 months';
  const payAmt = document.getElementById('pay-amount');
  if(payAmt) payAmt.textContent = fmt(q.due);
  document.querySelectorAll('.manual-amount').forEach(el => { el.textContent = fmt(q.due); });
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
function showOverlay(msg){
  const o = document.getElementById('pay-overlay');
  if(!o) return;
  if(msg) document.getElementById('pay-overlay-msg').textContent = msg;
  o.classList.remove('hidden'); o.classList.add('flex');
}
function hideOverlay(){
  const o = document.getElementById('pay-overlay');
  if(!o) return;
  o.classList.add('hidden'); o.classList.remove('flex');
}
function setBtnLoading(id, on){
  const b = document.getElementById(id); if(!b) return;
  b.disabled = on; b.classList.toggle('opacity-70', on);
  const s = b.querySelector('[data-spin]'); if(s) s.classList.toggle('hidden', !on);
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
  if(!sel) return;
  const wrap=sel.closest('.cs-wrap'); if(!wrap) return;
  const label=wrap.querySelector('.cs-label'), opt=sel.options[sel.selectedIndex];
  if(label) label.textContent=opt?opt.textContent:'Select…';
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
  list.className='cs-list hidden absolute z-30 left-0 right-0 mt-2 rounded-lg border border-zinc-200 dark:border-white/15 bg-white dark:bg-[#141416] shadow-2xl p-1.5 max-h-60 overflow-y-auto';
  list.setAttribute('role','listbox');
  [...sel.options].forEach(o=>{
    const item=document.createElement('button');
    item.type='button'; item.dataset.value=o.value; item.setAttribute('role','option');
    item.className='w-full flex items-center justify-between gap-2 text-left px-4 py-2 rounded-lg text-sm transition hover:bg-zinc-100 dark:hover:bg-white/10 '+(o.value===''?'text-zinc-400':'text-zinc-800 dark:text-zinc-200');
    item.innerHTML='<span class="truncate">'+o.textContent+'</span><svg class="cs-tick w-4 h-4 shrink-0 text-brand-600 hidden" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>';
    item.addEventListener('click',()=>{ sel.value=o.value; sel.dispatchEvent(new Event('change',{bubbles:true})); syncCo(sel); closeAllCo(); save(); });
    list.appendChild(item);
  });
  btn.addEventListener('click',()=>{ const willOpen=list.classList.contains('hidden'); closeAllCo(); if(willOpen){ list.classList.remove('hidden'); btn.setAttribute('aria-expanded','true'); } });
  wrap.appendChild(btn); wrap.appendChild(list);
  syncCo(sel);
}
document.addEventListener('click',e=>{ if(!e.target.closest || !e.target.closest('.cs-wrap')) closeAllCo(); });
document.addEventListener('keydown',e=>{ if(e.key==='Escape') closeAllCo(); });

function initCheckout(){
  const formEl = document.getElementById('checkout-form');
  if(!formEl || formEl.dataset.initDone){ refresh(); return; }
  formEl.dataset.initDone = '1';
  enhanceCoSelect(document.getElementById('duration'));
  enhanceCoSelect(document.getElementById('f-niche'));
  restore();
  refresh();
  [...pkgInputs, ...freqInputs, ...domInputs].forEach(i => i.addEventListener('change', () => { refresh(); save(); syncSubdomainRow(); syncExistingDomainRow(); }));
  syncSubdomainRow();
  syncExistingDomainRow();
  durationSel.addEventListener('change', () => { refresh(); save(); });
  ['f-business','f-phone','f-niche','f-notes','f-subdomain','f-existing'].forEach(id => {
    const el = document.getElementById(id);
    if(el) el.addEventListener('input', save);
  });

  const tabLogin = document.getElementById('tab-login'), tabReg = document.getElementById('tab-register');
  const pLogin = document.getElementById('panel-login'), pReg = document.getElementById('panel-register');
  const paintTabs = login => {
    const on = 'rounded-full px-4 py-2 text-sm font-bold transition bg-brand-600 text-white shadow';
    const off = 'rounded-full px-4 py-2 text-sm font-bold transition text-zinc-500 bg-zinc-100 hover:text-zinc-900 dark:bg-white/5 dark:hover:text-white';
    if(!tabLogin) return;
    tabLogin.className = login ? on : off;
    tabReg.className = login ? off : on;
    pLogin.classList.toggle('hidden', !login);
    pReg.classList.toggle('hidden', login);
  };
  if(tabLogin){ tabLogin.addEventListener('click', () => paintTabs(true)); tabReg.addEventListener('click', () => paintTabs(false)); paintTabs(true); }

  formEl.addEventListener('submit', e => {
    if(formEl.dataset.guest === '1'){
      e.preventDefault();
      const note = document.getElementById('auth-required-note');
      if(note) note.classList.remove('hidden');
      toast('Please log in or create an account first — your choices are saved.', 'warning');
      const auth = document.getElementById('checkout-auth');
      if(auth) auth.scrollIntoView({ behavior: 'smooth', block: 'start' });
      return;
    }
    showOverlay('Submitting your order…');
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
    const token = formEl.querySelector('input[name=_token]').value;
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
        if(s.status === 'failed'){ stopPayPoll(); setPayPending(false); payError(s.message || 'The payment was not approved. Try again.'); setBtnLoading('pay-submit', false); return; }
      }catch(e){}
      if(tries <= 0){ stopPayPoll(); setPayPending(false); payError('Still waiting on the provider — if you approved, check My packages in a minute. Your order is saved.'); setBtnLoading('pay-submit', false); }
    }, 3000);
  }
  const paySubmit = document.getElementById('pay-submit');
  if(paySubmit){
    paySubmit.addEventListener('click', async () => {
      if(formEl.dataset.guest === '1'){ formEl.requestSubmit(); return; }
      if(!formEl.checkValidity()){ formEl.reportValidity(); return; }
      const phoneEl = document.getElementById('pay-phone');
      const phone = (phoneEl && phoneEl.value || '').trim();
      if(!phone){ payError('Enter the Mobile Money number that will approve this payment.'); if(phoneEl) phoneEl.focus(); return; }
      document.getElementById('pay-err').classList.add('hidden');
      setBtnLoading('pay-submit', true);
      showOverlay('Starting your payment…');
      try{
        const order = await createOrder('iotec');
        const pay = await postJSON("{{ route('checkout.iotec.pay') }}", { reference: order.reference, phone });
        if(pay.redirect){ window.location.href = pay.redirect; return; }
        if(!pay.tx_id) throw new Error(pay.message || 'Payment could not be started. Please try again.');
        hideOverlay();
        setPayPending(true, fmt(order.due_today), phone);
        pollOrderStatus(order.reference, 40);
      }catch(err){ hideOverlay(); payError(err.message || 'Something went wrong. Please try again.'); setBtnLoading('pay-submit', false); }
    });
  }
  const payCancel = document.getElementById('pay-cancel');
  if(payCancel) payCancel.addEventListener('click', () => {
    stopPayPoll(); setPayPending(false);
    setBtnLoading('pay-submit', false);
  });
  const payCard = document.getElementById('pay-card');
  if(payCard){
    payCard.addEventListener('click', async () => {
      if(formEl.dataset.guest === '1'){ formEl.requestSubmit(); return; }
      if(!formEl.checkValidity()){ formEl.reportValidity(); return; }
      setBtnLoading('pay-card', true);
      showOverlay('Starting your payment…');
      try{
        const order = await createOrder('iotec');
        const r = await postJSON("{{ route('checkout.iotec.card') }}", { reference: order.reference });
        if(r.redirect){ window.location.href = r.redirect; return; }
        throw new Error(r.message || 'Card payments could not be started. Please pay with Mobile Money.');
      }catch(err){ hideOverlay(); payError(err.message || 'Something went wrong. Please try again.'); }
      setBtnLoading('pay-card', false);
    });
  }
  document.querySelectorAll('.manual-submit').forEach(b => b.addEventListener('click', () => {
    if(formEl.dataset.guest === '1'){ formEl.requestSubmit(); return; }
    if(!formEl.checkValidity()){ formEl.reportValidity(); return; }
    document.getElementById('pay-method').value = 'momo_manual';
    showOverlay('Submitting your order…');
    formEl.requestSubmit();
  }));
  const sumToggle = document.getElementById('sum-toggle');
  if(sumToggle) sumToggle.addEventListener('click', () => {
    const more = document.getElementById('sum-more');
    const hidden = more.classList.toggle('max-lg:hidden');
    sumToggle.setAttribute('aria-expanded', hidden ? 'false' : 'true');
    const chev = document.getElementById('sum-chev');
    if(chev) chev.classList.toggle('rotate-180', !hidden);
  });
}
if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initCheckout);
else initCheckout();
if(window.Livewire) document.addEventListener('livewire:navigated', initCheckout);
</script>
</x-layouts::checkout>
