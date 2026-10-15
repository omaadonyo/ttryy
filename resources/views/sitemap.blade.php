<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sitemap — ttryy</title>
<meta name="description" content="Every page on ttryy: packages, tools, account, legal and machine-readable sitemap.">
<link rel="canonical" href="{{ route('sitemap.page') }}">
<meta name="robots" content="index, follow">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<script>(function(){try{var k='flux.appearance',t=localStorage.getItem(k);if(t!=='light'&&t!=='dark'){t=localStorage.getItem('ttryy-theme');if(t!=='light'&&t!=='dark'){t=window.matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light';}localStorage.setItem(k,t);}document.documentElement.classList.toggle('dark',t==='dark');}catch(e){}})();</script>
@vite(['resources/css/app.css', 'resources/js/app.js'])
<style>body{font-family:'Inter',system-ui,sans-serif}h1,h2,.font-display{font-family:'Sora',system-ui,sans-serif}</style>
</head>
<body class="bg-white text-zinc-900 dark:bg-[#0A0A0B] dark:text-zinc-300 min-h-screen">
<header class="sticky top-0 z-50 bg-white/90 dark:bg-[#0A0A0B]/85 backdrop-blur-xl border-b border-zinc-200 dark:border-white/10">
  <nav class="mx-auto w-[92%] md:w-[80%] flex items-center justify-between h-16">
    <a href="{{ route('home') }}" class="flex items-center gap-2">
      <span class="w-9 h-9 rounded-full bg-[#9e005d] text-white grid place-items-center font-black text-xl">T</span>
      <span class="font-display font-extrabold text-2xl tracking-tight text-zinc-950 dark:text-white">ttryy</span>
    </a>
    <a href="{{ route('home') }}" class="text-sm font-semibold text-zinc-600 dark:text-zinc-300 hover:text-zinc-950 dark:hover:text-white px-3 py-2">← Home</a>
  </nav>
</header>
<main class="mx-auto w-[92%] md:w-[80%] py-12 lg:py-16 max-w-3xl">
  <p class="text-xs font-bold uppercase tracking-[0.14em] text-zinc-500">Find everything</p>
  <h1 class="font-display font-extrabold text-3xl sm:text-4xl tracking-tight mt-3 text-zinc-950 dark:text-white">Sitemap</h1>
  <div class="mt-8 grid sm:grid-cols-2 gap-8 text-[15px]">
    <section>
      <h2 class="font-display font-bold text-lg text-zinc-950 dark:text-white">Website</h2>
      <ul class="mt-3 space-y-2.5 text-zinc-600 dark:text-zinc-400">
        <li><a class="hover:text-[#9e005d] hover:underline" href="{{ route('home') }}">Home</a></li>
        <li><a class="hover:text-[#9e005d] hover:underline" href="{{ url('/#pricing') }}">Packages & pricing</a></li>
        <li><a class="hover:text-[#9e005d] hover:underline" href="{{ url('/#how') }}">How it works</a></li>
        <li><a class="hover:text-[#9e005d] hover:underline" href="{{ url('/#niches') }}">Niches</a></li>
        <li><a class="hover:text-[#9e005d] hover:underline" href="{{ url('/#work') }}">Our work</a></li>
        <li><a class="hover:text-[#9e005d] hover:underline" href="{{ url('/#faq') }}">FAQ</a></li>
        <li><a class="hover:text-[#9e005d] hover:underline" href="{{ route('checkout') }}">Checkout</a></li>
      </ul>
    </section>
    <section>
      <h2 class="font-display font-bold text-lg text-zinc-950 dark:text-white">Your account</h2>
      <ul class="mt-3 space-y-2.5 text-zinc-600 dark:text-zinc-400">
        <li><a class="hover:text-[#9e005d] hover:underline" href="{{ route('dashboard') }}">Dashboard</a></li>
        <li><a class="hover:text-[#9e005d] hover:underline" href="{{ route('checkout') }}">New order</a></li>
        <li><a class="hover:text-[#9e005d] hover:underline" href="{{ route('packages.index') }}">My packages</a></li>
        <li><a class="hover:text-[#9e005d] hover:underline" href="{{ route('scraper.index') }}">Prospect scraper</a></li>
        <li><a class="hover:text-[#9e005d] hover:underline" href="{{ route('prospects.index') }}">Prospect catalogue</a></li>
        <li><a class="hover:text-[#9e005d] hover:underline" href="{{ route('marketing.index') }}">Marketing tool</a></li>
        <li><a class="hover:text-[#9e005d] hover:underline" href="{{ route('wallet.index') }}">Token wallet</a></li>
      </ul>
    </section>
    <section>
      <h2 class="font-display font-bold text-lg text-zinc-950 dark:text-white">Legal</h2>
      <ul class="mt-3 space-y-2.5 text-zinc-600 dark:text-zinc-400">
        <li><a class="hover:text-[#9e005d] hover:underline" href="{{ route('privacy') }}">Privacy Policy</a></li>
        <li><a class="hover:text-[#9e005d] hover:underline" href="{{ route('terms') }}">Terms of Service</a></li>
      </ul>
    </section>
    <section>
      <h2 class="font-display font-bold text-lg text-zinc-950 dark:text-white">For search engines</h2>
      <ul class="mt-3 space-y-2.5 text-zinc-600 dark:text-zinc-400">
        <li><a class="hover:text-[#9e005d] hover:underline font-mono text-sm" href="{{ route('sitemap.xml') }}">sitemap.xml</a></li>
        <li><a class="hover:text-[#9e005d] hover:underline" href="{{ route('login') }}">Log in</a></li>
        <li><a class="hover:text-[#9e005d] hover:underline" href="{{ route('register') }}">Create account</a></li>
      </ul>
    </section>
  </div>
</main>
<footer class="mx-auto w-[92%] md:w-[80%] max-w-3xl pb-10 text-xs text-zinc-500 flex flex-col sm:flex-row justify-between gap-2">
  <p>© 2026 ttryy. All rights reserved.</p>
  <p><a href="{{ route('privacy') }}" class="hover:underline">Privacy</a> · <a href="{{ route('terms') }}" class="hover:underline">Terms</a></p>
</footer>
</body>
</html>
