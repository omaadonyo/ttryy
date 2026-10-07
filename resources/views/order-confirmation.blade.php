<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Order {{ $order->reference }} — Ttryy</title>
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
</style>
</head>
<body class="bg-zinc-50 text-zinc-900 dark:bg-[#0A0A0B] dark:text-zinc-300 min-h-screen">
<header class="sticky top-0 z-50 bg-white/90 dark:bg-[#0A0A0B]/85 backdrop-blur-xl border-b border-zinc-200 dark:border-white/10">
  <nav class="max-w-3xl mx-auto px-4 sm:px-6 flex items-center justify-between h-16">
    <a href="{{ route('home') }}" class="flex items-center gap-2">
      <span class="w-9 h-9 rounded-full bg-[#9e005d] text-white grid place-items-center font-black text-xl font-display">T</span>
      <span class="font-display font-extrabold text-2xl tracking-tight text-zinc-950 dark:text-white">Ttryy</span>
    </a>
    <a href="{{ route('packages.index') }}" class="text-sm font-semibold text-zinc-600 dark:text-zinc-300 hover:text-zinc-950 dark:hover:text-white px-3 py-2">My packages</a>
  </nav>
</header>

<main class="max-w-3xl mx-auto px-4 sm:px-6 py-10">
  <div class="rounded-xl bg-white dark:bg-[#141416] shadow-[0_12px_32px_-12px_rgba(10,10,12,.28)] p-8 text-center">
    <div class="w-16 h-16 mx-auto grid place-items-center rounded-full bg-[#9e005d] text-white">
      <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
    </div>
    <h1 class="font-display font-extrabold text-3xl mt-4 text-zinc-950 dark:text-white">Order received.</h1>
    <p class="text-zinc-600 dark:text-zinc-400 mt-2">Reference <strong class="font-mono text-zinc-950 dark:text-white">{{ $order->reference }}</strong>. A Ttryy representative will contact <strong>{{ $order->phone }}</strong> to confirm and collect payment.</p>
  </div>

  <div class="mt-6 rounded-xl bg-white dark:bg-[#141416] shadow-[0_12px_32px_-12px_rgba(10,10,12,.28)] p-8">
    <h2 class="font-display font-bold text-lg text-zinc-950 dark:text-white">Your order</h2>
    <dl class="mt-4 divide-y divide-zinc-100 dark:divide-white/10 text-sm">
      <div class="flex justify-between py-2.5"><dt class="text-zinc-500">Package</dt><dd class="font-bold text-zinc-950 dark:text-white">{{ $order->package }}</dd></div>
      <div class="flex justify-between py-2.5"><dt class="text-zinc-500">Billing</dt><dd class="font-bold text-zinc-950 dark:text-white">{{ ucfirst($order->billing_frequency) }}</dd></div>
      <div class="flex justify-between py-2.5"><dt class="text-zinc-500">Duration</dt><dd class="font-bold text-zinc-950 dark:text-white">{{ $order->duration_months }} months ({{ $order->periods }} payments)</dd></div>
      <div class="flex justify-between py-2.5"><dt class="text-zinc-500">Rate</dt><dd class="font-bold text-zinc-950 dark:text-white">UGX {{ number_format($order->amount_per_period) }}</dd></div>
      <div class="flex justify-between py-2.5"><dt class="text-zinc-500">Business</dt><dd class="font-bold text-zinc-950 dark:text-white">{{ $order->business_name }}</dd></div>
      @if($order->niche)<div class="flex justify-between py-2.5"><dt class="text-zinc-500">Niche</dt><dd class="font-bold text-zinc-950 dark:text-white">{{ $order->niche }}</dd></div>@endif
      <div class="flex justify-between py-2.5"><dt class="text-zinc-500">Status</dt><dd><span class="text-[11px] font-bold px-3 py-1 rounded-full bg-zinc-100 dark:bg-white/10 text-zinc-600 dark:text-zinc-300 uppercase">{{ $order->status }}</span></dd></div>
      <div class="flex justify-between py-3"><dt class="font-bold text-zinc-950 dark:text-white">Total</dt><dd class="font-display font-extrabold text-2xl text-zinc-950 dark:text-white">UGX {{ number_format($order->total_amount) }}</dd></div>
    </dl>
    <a target="_blank" href="https://wa.me/256700000000?text={{ urlencode('Hi Ttryy! I just placed order '.$order->reference.' ('.$order->package.', '.ucfirst($order->billing_frequency).', UGX '.number_format($order->total_amount).'). How do I pay?') }}" class="mt-6 block text-center bg-[#9e005d] hover:bg-[#7e0049] text-white font-bold py-3 rounded-full transition">Confirm on WhatsApp</a>
    <div class="mt-3 flex flex-col sm:flex-row gap-3">
      <a href="{{ route('packages.index') }}" class="flex-1 text-center border border-zinc-300 dark:border-white/20 hover:border-zinc-950 dark:hover:border-white font-bold py-3 rounded-full transition text-sm">View my packages</a>
      <a href="{{ route('home') }}" class="flex-1 text-center border border-zinc-300 dark:border-white/20 hover:border-zinc-950 dark:hover:border-white font-bold py-3 rounded-full transition text-sm">Back home</a>
    </div>
  </div>
</main>
</body>
</html>
