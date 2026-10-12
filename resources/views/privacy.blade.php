<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Privacy Policy — Ttryy</title>
<meta name="description" content="How Ttryy collects, uses and protects your personal data, and your rights under applicable data protection law.">
<link rel="canonical" href="{{ route('privacy') }}">
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
  <h1 class="font-display font-extrabold text-3xl sm:text-4xl tracking-tight mt-3 text-zinc-950 dark:text-white">Privacy Policy</h1>
  <div class="mt-8 space-y-8 text-[15px] leading-relaxed">
    <section><h2 class="font-display font-bold text-xl text-zinc-950 dark:text-white">1. Who we are</h2>
      <p class="mt-2 text-zinc-600 dark:text-zinc-400">Ttryy ("we", "us") provides affordable business websites, prospect data and marketing tools for Ugandan businesses. Contact our data team at {{ config('packages.admin_email') }} for any privacy question.</p></section>
    <section><h2 class="font-display font-bold text-xl text-zinc-950 dark:text-white">2. Data we collect</h2>
      <ul class="mt-2 list-disc ml-5 space-y-1.5 text-zinc-600 dark:text-zinc-400">
        <li><strong class="text-zinc-900 dark:text-white">Account data:</strong> name, email, password (stored hashed), phone and business details you give us when ordering.</li>
        <li><strong class="text-zinc-900 dark:text-white">Order & payment data:</strong> package, billing plan, amounts, references and payment confirmations. Card and Mobile Money details are processed by Flutterwave — we never see or store full card numbers.</li>
        <li><strong class="text-zinc-900 dark:text-white">Marketing data:</strong> contacts you save, email addresses you send campaigns to, and email open events (via a 1px tracking image) used only for your campaign reports.</li>
        <li><strong class="text-zinc-900 dark:text-white">Usage data:</strong> prospect searches, token purchases and feature usage used to run your account and improve the product.</li>
      </ul></section>
    <section><h2 class="font-display font-bold text-xl text-zinc-950 dark:text-white">3. Why we use it</h2>
      <p class="mt-2 text-zinc-600 dark:text-zinc-400">To create and run your account, deliver websites and prospect lists, process payments, send order and campaign emails you request, prevent fraud, and meet legal obligations. We do not sell your personal data, and we never share your saved contacts or campaign recipients with third parties for their marketing.</p></section>
    <section><h2 class="font-display font-bold text-xl text-zinc-950 dark:text-white">4. Cookies & local storage</h2>
      <p class="mt-2 text-zinc-600 dark:text-zinc-400">We use strictly necessary storage only: login session, security tokens, and on-device preferences (theme, sidebar state, draft messages). Payment pages load Flutterwave's secure checkout; email images in campaigns you send register opens for your own reports. There is no third-party advertising tracking on Ttryy.</p></section>
    <section><h2 class="font-display font-bold text-xl text-zinc-950 dark:text-white">5. Your rights</h2>
      <p class="mt-2 text-zinc-600 dark:text-zinc-400">You may request a copy of your data, correction, or deletion of your account and personal data at any time by emailing {{ config('packages.admin_email') }}. Deletion removes your account, orders history, contacts and campaigns, except records we must keep by law (e.g. payment receipts). Marketing emails you send must comply with anti-spam law — only email people who expect to hear from you, and honor opt-outs immediately.</p></section>
    <section><h2 class="font-display font-bold text-xl text-zinc-950 dark:text-white">6. Security & retention</h2>
      <p class="mt-2 text-zinc-600 dark:text-zinc-400">Passwords are hashed, sessions expire, and access to customer data is limited to staff who need it. We keep account data while your account is active and for a reasonable period afterwards for tax and dispute purposes, then delete or anonymize it.</p></section>
    <section><h2 class="font-display font-bold text-xl text-zinc-950 dark:text-white">7. Changes</h2>
      <p class="mt-2 text-zinc-600 dark:text-zinc-400">If this policy changes materially, we will note the new update date here and, where appropriate, notify you by email or in your dashboard.</p></section>
  </div>
</main>
<footer class="mx-auto w-[92%] md:w-[80%] max-w-3xl pb-10 text-xs text-zinc-500 flex flex-col sm:flex-row justify-between gap-2">
  <p>© 2026 Ttryy. All rights reserved.</p>
  <p><a href="{{ route('terms') }}" class="hover:underline">Terms</a> · <a href="{{ route('sitemap.page') }}" class="hover:underline">Sitemap</a></p>
</footer>
</body>
</html>
