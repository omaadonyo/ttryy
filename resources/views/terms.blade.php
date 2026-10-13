<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Terms of Service — Ttryy</title>
<meta name="description" content="The terms governing Ttryy website packages, payments, delivery and acceptable use.">
<link rel="canonical" href="{{ route('terms') }}">
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
      <span class="font-display font-extrabold text-2xl tracking-tight text-zinc-950 dark:text-white">Ttryy</span>
    </a>
    <a href="{{ route('home') }}" class="text-sm font-semibold text-zinc-600 dark:text-zinc-300 hover:text-zinc-950 dark:hover:text-white px-3 py-2">← Home</a>
  </nav>
</header>
<main class="mx-auto w-[92%] md:w-[80%] py-12 lg:py-16 max-w-3xl">
  <p class="text-xs font-bold uppercase tracking-[0.14em] text-zinc-500">Legal · Updated October 2026</p>
  <h1 class="font-display font-extrabold text-3xl sm:text-4xl tracking-tight mt-3 text-zinc-950 dark:text-white">Terms of Service</h1>
  <div class="mt-8 space-y-8 text-[15px] leading-relaxed">
    <section><h2 class="font-display font-bold text-xl text-zinc-950 dark:text-white">1. What Ttryy provides</h2>
      <p class="mt-2 text-zinc-600 dark:text-zinc-400">Website design, hosting and maintenance on installment plans, business prospect data, and marketing tools (WhatsApp outreach, email campaigns, templates, communities, token wallet). Package prices, deposits and billing rhythms are shown on the pricing page and locked in your order summary at checkout.</p></section>
    <section><h2 class="font-display font-bold text-xl text-zinc-950 dark:text-white">2. Payments & ownership</h2>
      <p class="mt-2 text-zinc-600 dark:text-zinc-400">You pay a deposit plus daily, weekly or monthly installments until the plan completes, or the full amount upfront. Your website stays running while your plan is active. Domain registration fees are one-time and non-recurring. cPanel and credentials are handed over once your account is in good standing. Manual Mobile Money payments must quote your order reference and are confirmed by our team, usually within a few hours.</p></section>
    <section><h2 class="font-display font-bold text-xl text-zinc-950 dark:text-white">3. Delivery</h2>
      <p class="mt-2 text-zinc-600 dark:text-zinc-400">We target going live within 5 days of your first confirmed payment, provided you supply required content (logo, text, photos) promptly. Delays caused by missing content extend delivery by the same period.</p></section>
    <section><h2 class="font-display font-bold text-xl text-zinc-950 dark:text-white">4. Refunds</h2>
      <p class="mt-2 text-zinc-600 dark:text-zinc-400">Deposits and domain fees are non-refundable once domain registration or design work has started. If we fail to deliver without cause, you may request a refund of unused installments by emailing {{ config('packages.admin_email') }}. Token top-ups are non-refundable once tokens are spent, but unused balances stay on your wallet indefinitely.</p></section>
    <section><h2 class="font-display font-bold text-xl text-zinc-950 dark:text-white">5. Acceptable use</h2>
      <ul class="mt-2 list-disc ml-5 space-y-1.5 text-zinc-600 dark:text-zinc-400">
        <li>Only send marketing emails to recipients who consented or have an existing business relationship with you, and honor opt-outs immediately.</li>
        <li>No spam, fraud, phishing, harassment, or unlawful content through our tools or hosted websites.</li>
        <li>Prospect data is licensed to you for your own outreach — do not resell or redistribute it.</li>
        <li>We may suspend accounts that breach these terms, with notice where practical.</li>
      </ul></section>
    <section><h2 class="font-display font-bold text-xl text-zinc-950 dark:text-white">6. Liability</h2>
      <p class="mt-2 text-zinc-600 dark:text-zinc-400">We provide the service with reasonable skill and care, but to the maximum extent permitted by law our liability is limited to amounts you paid in the 12 months before a claim. We are not liable for business you do or do not close from prospects, or for third-party services (payment providers, WhatsApp, email delivery).</p></section>
    <section><h2 class="font-display font-bold text-xl text-zinc-950 dark:text-white">7. Changes & contact</h2>
      <p class="mt-2 text-zinc-600 dark:text-zinc-400">We may update these terms; material changes will be flagged here and, where appropriate, by email or dashboard notice. Questions: {{ config('packages.admin_email') }}.</p></section>
  </div>
</main>
<footer class="mx-auto w-[92%] md:w-[80%] max-w-3xl pb-10 text-xs text-zinc-500 flex flex-col sm:flex-row justify-between gap-2">
  <p>© 2026 Ttryy. All rights reserved.</p>
  <p><a href="{{ route('privacy') }}" class="hover:underline">Privacy Policy</a> · <a href="{{ route('sitemap.page') }}" class="hover:underline">Sitemap</a></p>
</footer>
</body>
</html>
