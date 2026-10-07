<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>My packages — Ttryy</title>
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
  h1,h2,.font-display{ font-family:'Sora',sans-serif; }
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
      <a href="{{ route('checkout') }}" class="bg-[#9e005d] hover:bg-[#7e0049] text-white text-sm font-bold px-5 py-2 rounded-full transition">New order</a>
      <a href="{{ route('home') }}" class="text-sm font-semibold text-zinc-600 dark:text-zinc-300 hover:text-zinc-950 dark:hover:text-white px-3 py-2">← Home</a>
    </div>
  </nav>
</header>

<main class="max-w-5xl mx-auto px-4 sm:px-6 py-10">
  <p class="text-xs font-semibold tracking-wide uppercase text-zinc-500">Your account</p>
  <h1 class="font-display font-extrabold text-3xl sm:text-4xl mt-2 text-zinc-950 dark:text-white">My packages</h1>
  <p class="text-zinc-600 dark:text-zinc-400 mt-2">Orders for <strong class="text-zinc-900 dark:text-white">{{ auth()->user()->name }}</strong>. Your website stays running while your plan is active.</p>

  @if($orders->isEmpty())
  <div class="mt-8 rounded-xl bg-white dark:bg-[#141416] shadow-[0_12px_32px_-12px_rgba(10,10,12,.28)] p-10 text-center">
    <p class="font-display font-bold text-xl text-zinc-950 dark:text-white">No orders yet.</p>
    <p class="text-sm text-zinc-500 mt-2">Choose a package to get your website and prospect list started.</p>
    <a href="{{ route('checkout') }}" class="inline-block mt-5 bg-[#9e005d] hover:bg-[#7e0049] text-white font-bold px-8 py-3 rounded-full transition">Choose a package</a>
  </div>
  @else
  <div class="mt-8 grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($orders as $order)
    <div class="rounded-xl bg-white dark:bg-[#141416] shadow-[0_12px_32px_-12px_rgba(10,10,12,.28)] p-6">
      <div class="flex items-center justify-between gap-2">
        <p class="font-mono text-xs text-zinc-500">{{ $order->reference }}</p>
        <span class="text-[11px] font-bold px-2.5 py-1 rounded-full bg-zinc-100 dark:bg-white/10 text-zinc-600 dark:text-zinc-300 uppercase">{{ $order->status }}</span>
      </div>
      <p class="font-display font-extrabold text-2xl mt-2 text-zinc-950 dark:text-white">{{ $order->package }}</p>
      <p class="text-sm text-zinc-500 mt-1">{{ ucfirst($order->billing_frequency) }} · {{ $order->duration_months }} months · {{ $order->periods }} payments</p>
      <p class="text-sm text-zinc-500">{{ $order->business_name }}@if($order->niche) · {{ $order->niche }}@endif</p>
      <p class="font-display font-extrabold text-xl mt-3 text-zinc-950 dark:text-white">UGX {{ number_format($order->total_amount) }}</p>
      <p class="text-[11px] text-zinc-400 mt-1">Ordered {{ $order->created_at->format('d M Y') }}</p>
    </div>
    @endforeach
  </div>
  @endif
</main>
</body>
</html>
