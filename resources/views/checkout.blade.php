<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Checkout — Ttryy</title>
<meta name="theme-color" media="(prefers-color-scheme: light)" content="#ffffff">
<meta name="theme-color" media="(prefers-color-scheme: dark)" content="#0A0A0B">
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='50' fill='%239e005d'/><text x='50' y='70' font-size='54' font-family='Arial Black' font-weight='900' fill='white' text-anchor='middle'>T</text></svg>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<script>(function(){try{var t=localStorage.getItem('ttryy-theme');if(!t){t=window.matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light';}if(t==='dark'){document.documentElement.classList.add('dark');}}catch(e){}})();</script>
@vite(['resources/css/app.css', 'resources/js/app.js'])
<style>
  html{ -webkit-font-smoothing:antialiased; color-scheme:light; }
  html.dark{ color-scheme:dark; }
  body{ font-family:'Inter',system-ui,sans-serif; }
  h1,h2,h3,.font-display{ font-family:'Sora',sans-serif; }
  .pkg-card input:checked + div{ border-color:#9e005d; box-shadow:0 12px 32px -12px rgba(158,0,93,.35); }
  .freq-card input:checked + div{ border-color:#9e005d; background:rgba(158,0,93,.06); }
  .dark .freq-card input:checked + div{ background:rgba(158,0,93,.15); }
</style>
</head>
<body class="bg-zinc-50 text-zinc-900 dark:bg-[#0A0A0B] dark:text-zinc-300 min-h-screen">

<header class="sticky top-0 z-50 bg-white/90 dark:bg-[#0A0A0B]/85 backdrop-blur-xl border-b border-zinc-200 dark:border-white/10">
  <nav class="max-w-5xl mx-auto px-4 sm:px-6 flex items-center justify-between h-16">
    <a href="{{ route('home') }}" class="flex items-center gap-2">
      <span class="w-9 h-9 rounded-full bg-[#9e005d] text-white grid place-items-center font-black text-xl font-display">T</span>
      <span class="font-display font-extrabold text-2xl tracking-tight text-zinc-950 dark:text-white">Ttryy</span>
    </a>
    <div class="flex items-center gap-1.5">
      <button onclick="document.documentElement.classList.toggle('dark');try{localStorage.setItem('ttryy-theme',document.documentElement.classList.contains('dark')?'dark':'light');}catch(e){}" class="w-10 h-10 grid place-items-center rounded-full hover:bg-zinc-100 dark:hover:bg-white/10 text-zinc-600 dark:text-zinc-300" aria-label="Toggle dark mode">
        <svg class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
        <svg class="w-5 h-5 dark:hidden" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20 13.5A8 8 0 0110.5 4 8 8 0 1020 13.5z"/></svg>
      </button>
      <a href="{{ route('packages.index') }}" class="text-sm font-semibold text-zinc-600 dark:text-zinc-300 hover:text-zinc-950 dark:hover:text-white px-3 py-2">My packages</a>
      <a href="{{ route('home') }}" class="text-sm font-semibold text-zinc-600 dark:text-zinc-300 hover:text-zinc-950 dark:hover:text-white px-3 py-2">← Home</a>
    </div>
  </nav>
</header>

<main class="max-w-5xl mx-auto px-4 sm:px-6 py-10">
  <p class="text-xs font-semibold tracking-wide uppercase text-[#9e005d]">Checkout</p>
  <h1 class="font-display font-extrabold text-3xl sm:text-4xl mt-2 text-zinc-950 dark:text-white">Choose how you pay</h1>
  <p class="text-zinc-600 dark:text-zinc-400 mt-2">Signed in as <strong class="text-zinc-900 dark:text-white">{{ auth()->user()->name }}</strong> ({{ auth()->user()->email }}). Pick a package, a billing rhythm, and how long you want your website kept running.</p>

  @if($errors->any())
  <div class="mt-6 rounded-xl border border-red-300 bg-red-50 dark:bg-red-500/10 px-5 py-4 text-sm text-red-700 dark:text-red-300">
    <p class="font-bold">Please fix the following:</p>
    <ul class="list-disc ml-5 mt-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
  </div>
  @endif

  <form method="POST" action="{{ route('checkout.store') }}" class="mt-8 grid lg:grid-cols-[1fr_320px] gap-6 items-start">
    @csrf
    <div class="space-y-8">
      <!-- PACKAGE -->
      <section>
        <h2 class="font-display font-bold text-lg text-zinc-950 dark:text-white">1. Your package</h2>
        <div class="mt-3 grid sm:grid-cols-3 gap-3">
          @foreach($packages as $name => $p)
          <label class="pkg-card cursor-pointer">
            <input type="radio" name="package" value="{{ $name }}" class="sr-only" {{ $selectedPackage === $name ? 'checked' : '' }}>
            <div class="h-full rounded-xl bg-white dark:bg-[#141416] border-2 border-zinc-200 dark:border-white/10 p-5 transition">
              <p class="font-display font-bold text-sm tracking-wide {{ $name === 'GROW' ? 'text-[#9e005d]' : 'text-zinc-500' }}">{{ $name }}</p>
              <p class="mt-1 font-display font-extrabold text-2xl text-zinc-950 dark:text-white">UGX {{ number_format($p['price']) }}</p>
              <p class="text-xs text-zinc-500 mt-1">{{ $p['deposit'] }} deposit · {{ $p['blurb'] }}</p>
            </div>
          </label>
          @endforeach
        </div>
      </section>

      <!-- BILLING -->
      <section>
        <h2 class="font-display font-bold text-lg text-zinc-950 dark:text-white">2. How you pay</h2>
        <div class="mt-3 grid sm:grid-cols-2 gap-3" id="freqGrid">
          <label class="freq-card cursor-pointer">
            <input type="radio" name="billing_frequency" value="full" class="sr-only" checked>
            <div class="rounded-xl border-2 border-zinc-200 dark:border-white/10 bg-white dark:bg-[#141416] px-5 py-4 transition">
              <p class="font-bold text-zinc-950 dark:text-white">Pay in full</p>
              <p class="text-sm text-zinc-500" id="fullLabel">One payment · website runs 12 months</p>
            </div>
          </label>
          <label class="freq-card cursor-pointer">
            <input type="radio" name="billing_frequency" value="monthly" class="sr-only">
            <div class="rounded-xl border-2 border-zinc-200 dark:border-white/10 bg-white dark:bg-[#141416] px-5 py-4 transition">
              <p class="font-bold text-zinc-950 dark:text-white">Monthly</p>
              <p class="text-sm text-zinc-500" id="monthlyLabel">per month</p>
            </div>
          </label>
          <label class="freq-card cursor-pointer">
            <input type="radio" name="billing_frequency" value="weekly" class="sr-only">
            <div class="rounded-xl border-2 border-zinc-200 dark:border-white/10 bg-white dark:bg-[#141416] px-5 py-4 transition">
              <p class="font-bold text-zinc-950 dark:text-white">Weekly</p>
              <p class="text-sm text-zinc-500" id="weeklyLabel">per week</p>
            </div>
          </label>
          <label class="freq-card cursor-pointer">
            <input type="radio" name="billing_frequency" value="daily" class="sr-only">
            <div class="rounded-xl border-2 border-zinc-200 dark:border-white/10 bg-white dark:bg-[#141416] px-5 py-4 transition">
              <p class="font-bold text-zinc-950 dark:text-white">Daily</p>
              <p class="text-sm text-zinc-500" id="dailyLabel">per day</p>
            </div>
          </label>
        </div>
      </section>

      <!-- DURATION -->
      <section>
        <h2 class="font-display font-bold text-lg text-zinc-950 dark:text-white">3. How long you pay</h2>
        <p class="text-sm text-zinc-500 mt-1">Your website stays running while your plan is active. Full upfront payment covers 12 months.</p>
        <select name="duration_months" id="duration" class="mt-3 w-full sm:w-72 bg-white dark:bg-[#17171A] border border-zinc-200 dark:border-white/15 rounded-full px-5 py-3 text-sm outline-none focus:border-[#9e005d] text-zinc-900 dark:text-white">
          @foreach($durations as $d)
          <option value="{{ $d }}" {{ $d === 12 ? 'selected' : '' }}>{{ $d }} months</option>
          @endforeach
        </select>
      </section>

      <!-- CONTACT -->
      <section>
        <h2 class="font-display font-bold text-lg text-zinc-950 dark:text-white">4. Your business details</h2>
        <div class="mt-3 grid sm:grid-cols-2 gap-4">
          <div><label class="text-xs font-semibold">Business name *</label><input required name="business_name" value="{{ old('business_name') }}" placeholder="Savanna Build Ltd" class="mt-1 w-full bg-white dark:bg-[#17171A] border border-zinc-200 dark:border-white/15 rounded-full px-5 py-3 text-sm outline-none focus:border-[#9e005d] text-zinc-900 dark:text-white"></div>
          <div><label class="text-xs font-semibold">Phone / WhatsApp *</label><input required name="phone" value="{{ old('phone') }}" placeholder="+256 700 000000" class="mt-1 w-full bg-white dark:bg-[#17171A] border border-zinc-200 dark:border-white/15 rounded-full px-5 py-3 text-sm outline-none focus:border-[#9e005d] text-zinc-900 dark:text-white"></div>
          <div><label class="text-xs font-semibold">Industry / niche</label><select name="niche" class="mt-1 w-full bg-white dark:bg-[#17171A] border border-zinc-200 dark:border-white/15 rounded-full px-5 py-3 text-sm outline-none focus:border-[#9e005d] text-zinc-900 dark:text-white"><option value="">Select…</option>@foreach(config('packages.niches') as $n)<option {{ old('niche', $selectedNiche) === $n ? 'selected' : '' }}>{{ $n }}</option>@endforeach</select></div>
          <div><label class="text-xs font-semibold">Notes (optional)</label><input name="notes" value="{{ old('notes') }}" placeholder="Anything we should know" class="mt-1 w-full bg-white dark:bg-[#17171A] border border-zinc-200 dark:border-white/15 rounded-full px-5 py-3 text-sm outline-none focus:border-[#9e005d] text-zinc-900 dark:text-white"></div>
        </div>
      </section>
    </div>

    <!-- SUMMARY -->
    <aside class="lg:sticky lg:top-24 rounded-xl bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 p-6 shadow-xl">
      <p class="text-xs font-bold tracking-wide uppercase opacity-60">Order summary</p>
      <p class="font-display font-bold text-xl mt-2" id="sumPackage">GROW</p>
      <dl class="mt-4 space-y-2 text-sm">
        <div class="flex justify-between gap-2"><dt class="opacity-60">Billing</dt><dd class="font-semibold" id="sumFreq">One-time</dd></div>
        <div class="flex justify-between gap-2"><dt class="opacity-60">Duration</dt><dd class="font-semibold" id="sumDuration">12 months</dd></div>
        <div class="flex justify-between gap-2"><dt class="opacity-60">Rate</dt><dd class="font-semibold" id="sumRate">—</dd></div>
        <div class="flex justify-between gap-2"><dt class="opacity-60">Payments</dt><dd class="font-semibold" id="sumPeriods">1</dd></div>
      </dl>
      <div class="mt-4 pt-4 border-t border-white/15 dark:border-zinc-950/10">
        <p class="text-xs opacity-60">Total to pay</p>
        <p class="font-display font-extrabold text-3xl" id="sumTotal">UGX 350,000</p>
      </div>
      <button class="mt-5 w-full bg-[#9e005d] hover:bg-[#7e0049] dark:bg-[#9e005d] dark:hover:bg-[#7e0049] dark:!text-white text-white font-bold py-3 rounded-full transition">Place order</button>
      <p class="mt-3 text-[11px] opacity-60">No payment is taken now. We confirm your order on WhatsApp and share Mobile Money payment details.</p>
    </aside>
  </form>
</main>

<script>
const DATA = @json($packages);
const fmt = n => 'UGX ' + Number(n).toLocaleString('en-US');
const pkgInputs = [...document.querySelectorAll('input[name="package"]')];
const freqInputs = [...document.querySelectorAll('input[name="billing_frequency"]')];
const durationSel = document.getElementById('duration');
const cur = () => ({
  pkg: (pkgInputs.find(i => i.checked) || {}).value || 'GROW',
  freq: (freqInputs.find(i => i.checked) || {}).value || 'full',
  months: parseInt(durationSel.value, 10) || 12,
});
function calc(){
  const { pkg, freq, months } = cur(), d = DATA[pkg];
  if(freq === 'full') return { periods: 1, rate: d.price, total: d.price, rateLabel: fmt(d.price) + ' once', freqLabel: 'One-time', durText: '12 months' };
  if(freq === 'monthly') return { periods: months, rate: d.monthly, total: d.monthly * months, rateLabel: fmt(d.monthly) + '/mo', freqLabel: 'Monthly', durText: months + ' months' };
  if(freq === 'weekly'){ const p = Math.round(months * 52 / 12); return { periods: p, rate: d.weekly, total: d.weekly * p, rateLabel: fmt(d.weekly) + '/wk', freqLabel: 'Weekly', durText: months + ' months' }; }
  const p = Math.round(months * 365 / 12); return { periods: p, rate: d.daily, total: d.daily * p, rateLabel: fmt(d.daily) + '/day', freqLabel: 'Daily', durText: months + ' months' };
}
function refresh(){
  const { pkg, freq } = cur(), d = DATA[pkg], q = calc();
  document.getElementById('sumPackage').textContent = pkg;
  document.getElementById('sumFreq').textContent = q.freqLabel;
  document.getElementById('sumDuration').textContent = q.durText;
  document.getElementById('sumRate').textContent = q.rateLabel;
  document.getElementById('sumPeriods').textContent = q.periods;
  document.getElementById('sumTotal').textContent = fmt(q.total);
  document.getElementById('monthlyLabel').textContent = fmt(d.monthly) + ' per month';
  document.getElementById('weeklyLabel').textContent = fmt(d.weekly) + ' per week';
  document.getElementById('dailyLabel').textContent = fmt(d.daily) + ' per day';
  document.getElementById('fullLabel').textContent = fmt(d.price) + ' once · website runs 12 months';
  durationSel.disabled = freq === 'full';
  durationSel.classList.toggle('opacity-50', freq === 'full');
}
[...pkgInputs, ...freqInputs].forEach(i => i.addEventListener('change', refresh));
durationSel.addEventListener('change', refresh);
refresh();
</script>
</body>
</html>
