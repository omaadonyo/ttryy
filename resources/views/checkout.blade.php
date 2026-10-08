<x-layouts::app :title="__('Checkout')">
    @if($flwKey)
    <script src="https://checkout.flutterwave.com/v3.js"></script>
    @endif
    <style>
      .opt > div{ box-shadow:0 2px 12px -6px rgba(10,10,12,.14); }
      .dark .opt > div{ box-shadow:0 8px 24px -10px rgba(0,0,0,.75); }
      .opt .tick{ opacity:0; transform:scale(.4); transition:opacity .25s ease, transform .25s ease, background-color .25s ease; }
      .opt input:checked + div{ background:color-mix(in srgb, var(--ac) 8%, transparent); }
      .opt input:checked + div .tick{ opacity:1; transform:scale(1); background:var(--ac); border-color:var(--ac); color:#fff; }
      .opt input:focus-visible + div{ outline:2px solid var(--ac); outline-offset:2px; }
      .opt[data-accent="zinc"]{ --ac:#52525b; }
      .opt[data-accent="brand"]{ --ac:#9e005d; }
      .opt[data-accent="black"]{ --ac:#18181b; }
      .dark .opt[data-accent="black"]{ --ac:#e4e4e7; }
      .cs-wrap:focus-within .cs-btn{ border-color:#9e005d; }
      .cs-btn svg{ transition:transform .25s ease; }
      .cs-btn[aria-expanded="true"] svg{ transform:rotate(180deg); }
    </style>
    <div class="flex h-full w-full flex-1 flex-col gap-5">
  <p class="text-xs font-semibold tracking-wide uppercase text-zinc-500">Secure checkout</p>
  <h1 class="font-display font-extrabold text-3xl mt-1 text-zinc-950 dark:text-white">Choose how you pay</h1>
  <p class="text-zinc-600 dark:text-zinc-400 mt-2">Pick a package, a domain, a billing rhythm, and how long you want your website kept running. You pay exactly what is shown — no hidden fees.</p>

  <div class="mt-5 flex items-center gap-2 text-sm flex-wrap">
    @auth
    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 px-3 py-1 text-xs font-semibold">Signed in as {{ auth()->user()->name }}</span>
    @else
    <span class="inline-flex items-center gap-1.5 rounded-full bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 px-3 py-1 text-xs font-semibold">Sign in below to order</span>
    @endauth
    <span class="inline-flex items-center gap-1.5 rounded-full bg-zinc-100 dark:bg-white/10 text-zinc-600 dark:text-zinc-300 px-3 py-1 text-xs font-semibold">Package &amp; plan</span>
    <span class="text-zinc-300 dark:text-zinc-600">→</span>
    <span class="text-xs text-zinc-500">Confirm &amp; pay</span>
  </div>

  @guest
  <div id="checkout-auth" class="mt-5 rounded-xl bg-white dark:bg-[#141416] shadow-[0_12px_32px_-12px_rgba(10,10,12,.28)] p-5 sm:p-6">
    <h2 class="font-display font-bold text-xl text-zinc-950 dark:text-white">Create an account or log in</h2>
    <p class="mt-1 text-sm text-zinc-500">Your package choices below are saved automatically — nothing is lost when you sign in.</p>
    <div class="mt-4 inline-flex rounded-full bg-zinc-100 dark:bg-white/10 p-1" role="tablist" aria-label="Account">
      <button type="button" id="tab-login" class="rounded-full px-5 py-2 text-sm font-bold transition">Log in</button>
      <button type="button" id="tab-register" class="rounded-full px-5 py-2 text-sm font-bold transition">Create account</button>
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
          <li class="flex gap-2"><span class="text-[#9e005d] font-bold">✓</span> Track your order and payment status</li>
          <li class="flex gap-2"><span class="text-[#9e005d] font-bold">✓</span> Download invoices anytime</li>
          <li class="flex gap-2"><span class="text-[#9e005d] font-bold">✓</span> Manage renewals to keep your site running</li>
        </ul>
        <p id="auth-required-note" class="hidden font-semibold text-[#9e005d]">Please log in or create an account first — then your order goes through with everything you selected.</p>
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

  <form method="POST" action="{{ route('checkout.store') }}" id="checkout-form" class="mt-6 grid lg:grid-cols-[1fr_320px] gap-5 items-start" data-guest="{{ auth()->check() ? '0' : '1' }}">
    @csrf
    <input type="hidden" name="payment_method" id="pay-method" value="online">
    <div class="space-y-6">
      <section>
        <h2 class="font-display font-bold text-lg text-zinc-950 dark:text-white">1. Your package</h2>
        @php $accents = ['START' => 'black', 'GROW' => 'brand', 'BUSINESS' => 'black', 'CORPORATE' => 'black']; @endphp
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

      <section>
        <h2 class="font-display font-bold text-lg text-zinc-950 dark:text-white">2. Your domain <span class="text-xs font-semibold text-zinc-500">— one-time fee, never billed again</span></h2>
        @php $domAccents = ['none' => 'black', 'budget' => 'brand', 'premium' => 'black']; @endphp
        <div class="mt-3 grid sm:grid-cols-3 gap-3">
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
              <p class="text-xs text-zinc-500 mt-1">{{ $key === 'none' ? 'Use a domain you already own' : ($key === 'budget' ? '.xyz, .online or .shop — we register it for you' : '.com or .org — we register it for you') }}</p>
            </div>
          </label>
          @endforeach
        </div>
      </section>

      <section>
        <h2 class="font-display font-bold text-lg text-zinc-950 dark:text-white">3. How you pay</h2>
        @php $freqAccents = ['full' => 'black', 'monthly' => 'brand', 'weekly' => 'black', 'daily' => 'black']; @endphp
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

      <section>
        <h2 class="font-display font-bold text-lg text-zinc-950 dark:text-white">4. How long you pay</h2>
        <p class="text-sm text-zinc-500 mt-1">Your website stays running while your plan is active. Full upfront payment covers 12 months.</p>
        <select name="duration_months" id="duration" class="mt-3 w-full sm:w-72 bg-white dark:bg-[#17171A] border border-zinc-200 dark:border-white/15 rounded-full px-5 py-3 text-sm outline-none focus:border-[#9e005d] text-zinc-900 dark:text-white">
          @foreach($durations as $d)
          <option value="{{ $d }}" {{ $d === 12 ? 'selected' : '' }}>{{ $d }} months</option>
          @endforeach
        </select>
      </section>

      <section>
        <h2 class="font-display font-bold text-lg text-zinc-950 dark:text-white">5. Your business details</h2>
        <div class="mt-3 grid sm:grid-cols-2 gap-4">
          <div><label class="text-xs font-semibold">Business name *</label><input required id="f-business" name="business_name" value="{{ old('business_name') }}" placeholder="Savanna Build Ltd" class="mt-1 w-full bg-white dark:bg-[#17171A] border border-zinc-200 dark:border-white/15 rounded-full px-5 py-3 text-sm outline-none focus:border-[#9e005d] text-zinc-900 dark:text-white"></div>
          <div><label class="text-xs font-semibold">Phone / WhatsApp *</label><input required id="f-phone" name="phone" value="{{ old('phone') }}" placeholder="+256 700 000000" class="mt-1 w-full bg-white dark:bg-[#17171A] border border-zinc-200 dark:border-white/15 rounded-full px-5 py-3 text-sm outline-none focus:border-[#9e005d] text-zinc-900 dark:text-white"></div>
          <div><label class="text-xs font-semibold">Industry / niche</label><select id="f-niche" name="niche" class="mt-1 w-full bg-white dark:bg-[#17171A] border border-zinc-200 dark:border-white/15 rounded-full px-5 py-3 text-sm outline-none focus:border-[#9e005d] text-zinc-900 dark:text-white"><option value="">Select…</option>@foreach(config('packages.niches') as $n)<option {{ old('niche', $selectedNiche) === $n ? 'selected' : '' }}>{{ $n }}</option>@endforeach</select></div>
          <div><label class="text-xs font-semibold">Notes (optional)</label><input id="f-notes" name="notes" value="{{ old('notes') }}" placeholder="Anything we should know" class="mt-1 w-full bg-white dark:bg-[#17171A] border border-zinc-200 dark:border-white/15 rounded-full px-5 py-3 text-sm outline-none focus:border-[#9e005d] text-zinc-900 dark:text-white"></div>
        </div>
      </section>
    </div>

    <aside class="lg:sticky lg:top-24 rounded-xl bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 p-6 shadow-xl h-fit">
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

      <div class="mt-5">
        <p class="text-xs font-bold tracking-wide uppercase opacity-60">How do you want to pay?</p>
        <div class="mt-2 space-y-2" id="pay-choices">
          @if($flwKey)
          <label class="opt cursor-pointer block" data-accent="brand">
            <input type="radio" name="pay_choice" value="online" class="sr-only" checked>
            <div class="rounded-xl bg-white/10 dark:bg-zinc-950/5 p-4 transition flex items-center gap-3">
              <span class="tick w-6 h-6 grid place-items-center rounded-full border-2 border-white/40 dark:border-zinc-950/30 text-transparent shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
              </span>
              <span><span class="block font-bold text-sm">Pay online now</span><span class="block text-xs opacity-60">Card or Mobile Money, processed by Flutterwave</span></span>
            </div>
          </label>
          @endif
          <label class="opt cursor-pointer block" data-accent="black">
            <input type="radio" name="pay_choice" value="momo" class="sr-only" @if(!$flwKey) checked @endif>
            <div class="rounded-xl bg-white/10 dark:bg-zinc-950/5 p-4 transition flex items-center gap-3">
              <span class="tick w-6 h-6 grid place-items-center rounded-full border-2 border-white/40 dark:border-zinc-950/30 text-transparent shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
              </span>
              <span><span class="block font-bold text-sm">Mobile Money to {{ $momoCode }}</span><span class="block text-xs opacity-60">Send it yourself, we confirm on WhatsApp</span></span>
            </div>
          </label>
        </div>
      </div>

      <button type="button" id="pay-now" class="mt-5 w-full bg-[#9e005d] hover:bg-[#7e0049] text-white font-bold py-3 rounded-full transition">Pay <span id="pay-now-amount">now</span></button>

      <div id="momo-panel" class="hidden mt-3 rounded-xl bg-white/10 dark:bg-zinc-950/5 p-4 text-sm">
        <p class="font-bold">Send to MTN MoMo merchant code</p>
        <p class="font-display font-extrabold text-3xl tracking-widest mt-1">{{ $momoCode }}</p>
        <ol class="mt-2 space-y-1 text-[13px] opacity-80 list-decimal ml-4">
          <li>Dial *165# → Payments → Merchant payment</li>
          <li>Enter merchant code <strong>{{ $momoCode }}</strong> (Ttryy)</li>
          <li>Amount: <strong id="momo-amount">—</strong> (your first payment)</li>
          <li>Reference / narration: your order reference (shown after you continue)</li>
        </ol>
        <button type="button" id="momo-sent" class="mt-3 w-full bg-white dark:bg-zinc-950 text-zinc-950 dark:text-white font-bold py-2.5 rounded-full text-sm transition">I've sent the Mobile Money</button>
      </div>

      <p class="mt-3 text-[11px] opacity-60">You pay exactly what is shown here — no hidden fees. Secured by Flutterwave.</p>
    </aside>
  </form>
</div>

<script>
const DATA = @json($packages);
const DOMAINS = @json($domains);
const FLW_KEY = @json($flwKey);
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
    const c = cur();
    localStorage.setItem(LS_KEY, JSON.stringify({ ...c,
      business: document.getElementById('f-business').value, phone: document.getElementById('f-phone').value,
      niche: document.getElementById('f-niche').value, notes: document.getElementById('f-notes').value }));
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
  syncCo(durationSel); syncCo(document.getElementById('f-niche'));
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
  const payAmt = document.getElementById('pay-now-amount');
  if(payAmt) payAmt.textContent = fmt(q.due);
  const momoAmt = document.getElementById('momo-amount');
  if(momoAmt) momoAmt.textContent = fmt(q.due);
  durationSel.disabled = freq === 'full';
  durationSel.classList.toggle('opacity-50', freq === 'full');
}
function showPayError(msg){
  const box = document.getElementById('pay-error');
  box.textContent = msg;
  box.classList.remove('hidden');
  box.scrollIntoView({ behavior: 'smooth', block: 'center' });
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
  btn.className='cs-btn w-full flex items-center justify-between gap-2 rounded-full border border-zinc-200 dark:border-white/15 bg-white dark:bg-[#17171A] px-5 py-3 text-sm text-zinc-900 dark:text-white outline-none';
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
function initCheckout(){
  enhanceCoSelect(document.getElementById('duration'));
  enhanceCoSelect(document.getElementById('f-niche'));
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
  const payNow = document.getElementById('pay-now');
  if(payNow){
    payNow.addEventListener('click', async () => {
      if(form.dataset.guest === '1'){ form.requestSubmit(); return; }
      if(!form.checkValidity()){ form.reportValidity(); return; }
      payNow.disabled = true; payNow.classList.add('opacity-60');
      try{
        const order = await createOrder('online');
        if(typeof FlutterwaveCheckout === 'undefined') throw new Error('Payment popup failed to load. Please check your connection and try again.');
        FlutterwaveCheckout({
          public_key: order.public_key || FLW_KEY,
          tx_ref: order.reference,
          amount: order.due_today,
          currency: order.currency || 'UGX',
          payment_options: 'mobilemoney,card',
          customer: { email: order.email, name: order.name, phone_number: order.phone },
          customizations: { title: 'Ttryy', description: 'Website package order ' + order.reference, logo: location.origin + '/favicon.svg' },
          callback: async resp => {
            try{
              const token = form.querySelector('input[name=_token]').value;
              const v = await fetch("{{ route('checkout.verify') }}", { method: 'POST',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ transaction_id: resp.transaction_id || resp.id, tx_ref: order.reference }) });
              const j = await v.json();
              if(j.redirect) window.location.href = j.redirect;
              else showPayError(j.message || 'Payment could not be verified. Your order is saved — confirm on WhatsApp.');
            }catch(err){ showPayError('Payment could not be verified. Your order is saved — confirm on WhatsApp.'); }
          },
          onclose: () => { window.location.href = order.order_url; },
        });
      }catch(err){ showPayError(err.message || 'Something went wrong. Please try again.'); }
      payNow.disabled = false; payNow.classList.remove('opacity-60');
    });
  }
  const payChoiceInputs = [...document.querySelectorAll('input[name="pay_choice"]')];
  const momoPanel = document.getElementById('momo-panel');
  function syncPayChoice(){
    const choice = (payChoiceInputs.find(i => i.checked) || {}).value || 'momo';
    if(payNow) payNow.classList.toggle('hidden', choice !== 'online');
    if(momoPanel) momoPanel.classList.toggle('hidden', choice !== 'momo');
  }
  payChoiceInputs.forEach(i => i.addEventListener('change', syncPayChoice));
  syncPayChoice();
  const momoSent = document.getElementById('momo-sent');
  if(momoSent) momoSent.addEventListener('click', () => {
    const hidden = document.createElement('input');
    hidden.type = 'hidden'; hidden.name = 'payment_method'; hidden.value = 'momo_manual';
    form.appendChild(hidden);
    form.requestSubmit();
  });
}
if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initCheckout);
else initCheckout();
if(window.Livewire) document.addEventListener('livewire:navigated', initCheckout);
</script>
    </div>
</x-layouts::app>
