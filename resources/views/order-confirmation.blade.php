<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Order {{ $order->reference }} — Ttryy</title>
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
  .sqgrid{ background-image:linear-gradient(to right, rgba(17,17,19,.05) 1px, transparent 1px), linear-gradient(to bottom, rgba(17,17,19,.05) 1px, transparent 1px); background-size:30px 30px; background-attachment:fixed; -webkit-mask-image:radial-gradient(ellipse 90% 85% at 50% 32%, black 20%, transparent 75%); mask-image:radial-gradient(ellipse 90% 85% at 50% 32%, black 20%, transparent 75%); }
  .dark .sqgrid{ background-image:linear-gradient(to right, rgba(255,255,255,.05) 1px, transparent 1px), linear-gradient(to bottom, rgba(255,255,255,.05) 1px, transparent 1px); background-attachment:fixed; }
</style>
</head>
<body class="bg-white text-zinc-900 dark:bg-[#0A0A0B] dark:text-zinc-300 min-h-screen">

<header class="sticky top-0 z-50 bg-white/90 dark:bg-[#0A0A0B]/85 backdrop-blur-xl border-b border-zinc-200 dark:border-white/10">
  <nav class="max-w-3xl mx-auto px-4 sm:px-6 flex items-center justify-between h-16">
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

<main class="relative overflow-hidden">
  <div class="absolute inset-0 sqgrid pointer-events-none" aria-hidden="true"></div>
  <div class="relative max-w-3xl mx-auto px-4 sm:px-6 py-12 lg:py-16">

    <div class="text-center">
      <div class="w-20 h-20 mx-auto grid place-items-center rounded-full {{ $order->status === 'paid' ? 'bg-emerald-600' : 'bg-[#9e005d]' }} text-white shadow-2xl">
        <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
      </div>
      <p class="mt-5 text-xs font-semibold tracking-wide uppercase {{ $order->status === 'paid' ? 'text-emerald-700 dark:text-emerald-400' : 'text-[#9e005d]' }}">
        {{ $order->status === 'paid' ? 'Payment confirmed' : 'Order received' }}
      </p>
      <h1 class="font-display font-extrabold text-3xl sm:text-4xl mt-2 text-zinc-950 dark:text-white">
        {{ $order->status === 'paid' ? 'You’re all set.' : 'Almost there.' }}
      </h1>
      <p class="text-zinc-600 dark:text-zinc-400 mt-3 max-w-xl mx-auto">
        @if($order->status === 'paid')
        Your first payment went through. Our team starts on your website right away.
        @else
        A Ttryy representative will contact <strong class="text-zinc-900 dark:text-white">{{ $order->phone }}</strong> shortly to confirm and collect payment.
        @endif
      </p>
      <button onclick="navigator.clipboard.writeText('{{ $order->reference }}');this.querySelector('span').textContent='Copied!';setTimeout(()=>this.querySelector('span').textContent='Copy reference',1500)" class="mt-4 inline-flex items-center gap-2 rounded-full bg-zinc-100 dark:bg-white/10 hover:bg-zinc-200 dark:hover:bg-white/20 px-5 py-2.5 font-mono text-sm font-bold text-zinc-900 dark:text-white transition">
        {{ $order->reference }} <span class="font-sans text-[11px] font-semibold text-zinc-500">Copy reference</span>
      </button>
    </div>

    <div class="mt-10 rounded-xl bg-white dark:bg-[#141416] shadow-[0_12px_32px_-12px_rgba(10,10,12,.28)] p-6 sm:p-8">
      <h2 class="font-display font-bold text-lg text-zinc-950 dark:text-white">What happens next</h2>
      <ol class="mt-4 space-y-4">
        <li class="flex gap-4">
          <span class="font-display font-extrabold text-xl text-zinc-300 dark:text-zinc-600 w-7 shrink-0">01</span>
          <div><p class="font-bold text-[15px] text-zinc-900 dark:text-white">We confirm your order</p><p class="text-sm text-zinc-500">On WhatsApp at {{ $order->phone }} — usually within a few hours.</p></div>
        </li>
        <li class="flex gap-4">
          <span class="font-display font-extrabold text-xl text-zinc-300 dark:text-zinc-600 w-7 shrink-0">02</span>
          <div><p class="font-bold text-[15px] text-zinc-900 dark:text-white">You pay UGX {{ number_format($order->due_today) }} to start</p><p class="text-sm text-zinc-500">Domain one-time fee plus your first {{ strtolower($order->billing_frequency) === 'full' ? 'and only' : '' }} installment — via Mobile Money, card, or cash confirmation.</p></div>
        </li>
        <li class="flex gap-4">
          <span class="font-display font-extrabold text-xl text-zinc-300 dark:text-zinc-600 w-7 shrink-0">03</span>
          <div><p class="font-bold text-[15px] text-zinc-900 dark:text-white">Your website goes live within 5 days</p><p class="text-sm text-zinc-500">Plus your {{ $order->package }} prospect list and opportunity resources.</p></div>
        </li>
      </ol>
    </div>

    <div class="mt-6 rounded-xl bg-white dark:bg-[#141416] shadow-[0_12px_32px_-12px_rgba(10,10,12,.28)] p-6 sm:p-8">
      <div class="flex items-center justify-between gap-3">
        <h2 class="font-display font-bold text-lg text-zinc-950 dark:text-white">Your order</h2>
        <span class="text-[11px] font-bold px-3 py-1 rounded-full uppercase {{ $order->status === 'paid' ? 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-400' : 'bg-zinc-100 dark:bg-white/10 text-zinc-600 dark:text-zinc-300' }}">{{ $order->status }}</span>
      </div>
      <dl class="mt-4 divide-y divide-zinc-100 dark:divide-white/10 text-sm">
        <div class="flex justify-between py-2.5"><dt class="text-zinc-500">Package</dt><dd class="font-bold text-zinc-950 dark:text-white">{{ $order->package }}</dd></div>
        <div class="flex justify-between py-2.5"><dt class="text-zinc-500">Billing</dt><dd class="font-bold text-zinc-950 dark:text-white">{{ ucfirst($order->billing_frequency) }} · {{ $order->duration_months }} months ({{ $order->periods }} payments)</dd></div>
        <div class="flex justify-between py-2.5"><dt class="text-zinc-500">Rate</dt><dd class="font-bold text-zinc-950 dark:text-white">UGX {{ number_format($order->amount_per_period) }}</dd></div>
        <div class="flex justify-between py-2.5"><dt class="text-zinc-500">Domain · one-time fee</dt><dd class="font-bold text-zinc-950 dark:text-white">UGX {{ number_format($order->domain_fee) }} ({{ $order->domain }})</dd></div>
        <div class="flex justify-between py-2.5"><dt class="text-zinc-500">Business</dt><dd class="font-bold text-zinc-950 dark:text-white">{{ $order->business_name }}</dd></div>
        @if($order->niche)<div class="flex justify-between py-2.5"><dt class="text-zinc-500">Niche</dt><dd class="font-bold text-zinc-950 dark:text-white">{{ $order->niche }}</dd></div>@endif
        <div class="flex justify-between py-2.5"><dt class="text-zinc-500">Total over term</dt><dd class="font-bold text-zinc-950 dark:text-white">UGX {{ number_format($order->total_amount) }}</dd></div>
      </dl>
      <div class="mt-4 rounded-xl {{ $order->status === 'paid' ? 'bg-emerald-600' : 'bg-zinc-950 dark:bg-white' }} text-white dark:text-zinc-950 p-5 flex items-center justify-between gap-3">
        <div>
          <p class="text-xs font-semibold uppercase tracking-wide opacity-70">{{ $order->status === 'paid' ? 'Paid' : 'Due today' }}</p>
          <p class="font-display font-extrabold text-3xl">UGX {{ number_format($order->status === 'paid' ? $order->paid_amount : $order->due_today) }}</p>
        </div>
        <a href="{{ route('orders.invoice', $order) }}" class="shrink-0 text-xs font-bold px-4 py-2.5 rounded-full {{ $order->status === 'paid' ? 'bg-white/20 hover:bg-white/30 text-white' : 'bg-white/10 hover:bg-white/20 dark:bg-zinc-950/5 dark:hover:bg-zinc-950/10 text-white dark:text-zinc-950' }} transition">Download invoice</a>
      </div>
      <a target="_blank" href="https://wa.me/256700000000?text={{ urlencode('Hi Ttryy! I just placed order '.$order->reference.' ('.$order->package.', '.ucfirst($order->billing_frequency).', due today UGX '.number_format($order->due_today).'). How do I pay?') }}" class="mt-4 block text-center bg-[#9e005d] hover:bg-[#7e0049] text-white font-bold py-3 rounded-full transition shadow-lg shadow-[#9e005d]/25">Confirm on WhatsApp</a>
      <div class="mt-3 flex flex-col sm:flex-row gap-3">
        <a href="{{ route('packages.index') }}" class="flex-1 text-center border border-zinc-300 dark:border-white/20 hover:border-zinc-950 dark:hover:border-white font-bold py-3 rounded-full transition text-sm">View my packages</a>
        <a href="{{ route('dashboard') }}" class="flex-1 text-center border border-zinc-300 dark:border-white/20 hover:border-zinc-950 dark:hover:border-white font-bold py-3 rounded-full transition text-sm">Go to dashboard</a>
      </div>
    </div>
  </div>
</main>

<footer class="max-w-3xl mx-auto px-4 sm:px-6 pb-10 text-xs text-zinc-500 flex flex-col sm:flex-row justify-between gap-2">
  <p>© 2026 Ttryy. All rights reserved.</p>
  <p>Please quote <strong>{{ $order->reference }}</strong> in all correspondence.</p>
</footer>

<script>try{localStorage.removeItem('ttryy-checkout-v1');}catch(e){}</script>
</body>
</html>
