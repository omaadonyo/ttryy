<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Ttryy — We Build Your Website. You Find the Customers.</title>
<meta name="description" content="Get a professional, SEO-ready business website plus targeted potential customers and business opportunities specific to your niche. Starting from UGX 250,000.">
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='22' fill='%23059669'/><text x='50' y='68' font-size='52' font-family='Arial Black' font-weight='900' fill='white' text-anchor='middle'>T</text></svg>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
@vite(['resources/css/app.css', 'resources/js/app.js'])
<style>
  :root{ --brand:#059669; --brand-dark:#047857; --ink:#0A1628; --gold:#F59E0B; }
  html{ -webkit-font-smoothing:antialiased; }
  body{ font-family:'Inter',system-ui,sans-serif; color:#0A1628; background:#fff; }
  h1,h2,h3,.font-display{ font-family:'Sora',sans-serif; }
  .reveal{ opacity:0; transform:translateY(24px); transition:opacity .7s ease, transform .7s ease; }
  .reveal.visible{ opacity:1; transform:none; }
  .hero-grid{ background-image:radial-gradient(circle at 1px 1px, rgba(255,255,255,.12) 1px, transparent 0); background-size:28px 28px; }
  .light-grid{ background-image:radial-gradient(circle at 1px 1px, rgba(10,22,40,.08) 1px, transparent 0); background-size:26px 26px; }
  .marquee{ animation:marquee 28s linear infinite; }
  @keyframes marquee{ to{ transform:translateX(-50%);} }
  @keyframes floaty{ 0%,100%{transform:translateY(0)} 50%{transform:translateY(-10px)} }
  .floaty{ animation:floaty 5s ease-in-out infinite; }
  details.faq summary::-webkit-details-marker{ display:none; }
  .niche-card{ transition:transform .25s ease, box-shadow .25s ease; }
  .niche-card:hover{ transform:translateY(-4px); box-shadow:0 20px 40px -18px rgba(10,22,40,.25); }
  .btn-shine{ position:relative; overflow:hidden; }
  .btn-shine::after{ content:''; position:absolute; top:0; left:-60%; width:40%; height:100%; background:linear-gradient(100deg,transparent,rgba(255,255,255,.5),transparent); transform:skewX(-20deg); transition:left .6s; }
  .btn-shine:hover::after{ left:130%; }
  .scrollbar-none::-webkit-scrollbar{ display:none; } .scrollbar-none{ scrollbar-width:none; }
</style>
</head>
<body class="bg-white text-slate-900 overflow-x-hidden">

<!-- TOP BAR -->
<div class="bg-[#0A1628] text-white text-xs sm:text-sm">
  <div class="max-w-7xl mx-auto px-4 py-2 flex items-center justify-center sm:justify-between gap-2">
    <p class="truncate">🔥 <strong>Launch offer:</strong> Professional website + 5,000 niche prospects — starting <strong class="text-amber-400">UGX 250,000</strong></p>
    <button onclick="openLead('START')" class="hidden sm:inline-flex shrink-0 bg-emerald-500 hover:bg-emerald-400 text-[#0A1628] font-bold px-4 py-1 rounded-full transition">Claim Offer →</button>
  </div>
</div>

<!-- NAV -->
<header id="nav" class="sticky top-0 z-50 bg-white/90 backdrop-blur-xl border-b border-slate-100 transition-shadow">
  <nav class="max-w-7xl mx-auto px-4 sm:px-6 flex items-center justify-between h-16 lg:h-[72px]">
    <a href="#home" class="flex items-center gap-2">
      <span class="w-9 h-9 rounded-xl bg-emerald-600 grid place-items-center text-white font-black text-xl font-display shadow-lg shadow-emerald-600/25">T</span>
      <span class="font-display font-800 font-extrabold text-2xl tracking-tight">Ttryy<span class="text-emerald-600">.</span></span>
    </a>
    <div class="hidden lg:flex items-center gap-7 text-[15px] font-medium text-slate-600">
      <a href="#home" class="hover:text-emerald-700">Home</a>
      <a href="#how" class="hover:text-emerald-700">How It Works</a>
      <a href="#niches" class="hover:text-emerald-700">Niches</a>
      <a href="#work" class="hover:text-emerald-700">Our Work</a>
      <a href="#pricing" class="hover:text-emerald-700">Pricing</a>
      <a href="#tools" class="hover:text-emerald-700">Business Tools</a>
      <a href="#faq" class="hover:text-emerald-700">FAQ</a>
    </div>
    <div class="flex items-center gap-2">
      <a href="https://wa.me/256700000000?text=Hi%20Ttryy!%20I%20want%20a%20website%20for%20my%20business." target="_blank" class="hidden md:inline-flex items-center gap-1.5 text-sm font-semibold text-slate-700 hover:text-emerald-700 px-3 py-2">
        <svg class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Zm5.2 14.2c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .2-3.4-.7-2.9-1.2-4.7-4.1-4.9-4.3-.1-.2-1.1-1.5-1.1-2.9s.7-2 1-2.3c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5s.8 1.9.8 2c.1.1.1.3 0 .5-.3.6-.6.8-.4 1.1.7 1.2 1.6 2 2.8 2.6.3.2.5 0 .7-.2l.7-.8c.2-.3.4-.2.7-.1l2 1c.3.1.5.2.6.4 0 .1 0 .6-.5 1.3Z"/></svg>
        WhatsApp
      </a>
      <button onclick="openLead('')" class="btn-shine bg-[#0A1628] hover:bg-emerald-700 text-white text-sm font-bold px-5 py-2.5 rounded-full transition">Get Started</button>
      <button id="menuBtn" class="lg:hidden p-2 rounded-lg hover:bg-slate-100" aria-label="Menu">
        <svg id="menuOpen" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
        <svg id="menuClose" class="w-6 h-6 hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
      </button>
    </div>
  </nav>
  <div id="mobileMenu" class="lg:hidden hidden border-t border-slate-100 bg-white px-4 py-4 space-y-1 text-[15px] font-medium">
    <a href="#home" class="block px-3 py-2.5 rounded-lg hover:bg-slate-50">Home</a>
    <a href="#how" class="block px-3 py-2.5 rounded-lg hover:bg-slate-50">How It Works</a>
    <a href="#niches" class="block px-3 py-2.5 rounded-lg hover:bg-slate-50">Niches</a>
    <a href="#work" class="block px-3 py-2.5 rounded-lg hover:bg-slate-50">Our Work</a>
    <a href="#pricing" class="block px-3 py-2.5 rounded-lg hover:bg-slate-50">Pricing</a>
    <a href="#tools" class="block px-3 py-2.5 rounded-lg hover:bg-slate-50">Business Tools</a>
    <a href="#faq" class="block px-3 py-2.5 rounded-lg hover:bg-slate-50">FAQ</a>
    <button onclick="openLead('')" class="w-full mt-2 bg-emerald-600 text-white font-bold py-3 rounded-xl">Get Started</button>
  </div>
</header>

<!-- HERO -->
<section id="home" class="relative bg-[#0A1628] text-white overflow-hidden">
  <div class="absolute inset-0 hero-grid opacity-60"></div>
  <div class="absolute -top-32 -right-32 w-[500px] h-[500px] rounded-full bg-emerald-500/20 blur-[120px]"></div>
  <div class="absolute -bottom-40 -left-32 w-[500px] h-[500px] rounded-full bg-amber-500/15 blur-[120px]"></div>
  <div class="relative max-w-7xl mx-auto px-4 sm:px-6 pt-12 pb-10 lg:pt-20 lg:pb-16 grid lg:grid-cols-2 gap-12 items-center">
    <div class="reveal visible">
      <div class="inline-flex items-center gap-2 bg-white/10 border border-white/15 rounded-full pl-1.5 pr-4 py-1.5 text-xs sm:text-sm mb-6">
        <span class="bg-amber-400 text-[#0A1628] font-bold px-2.5 py-0.5 rounded-full">NEW</span>
        <span class="text-slate-200">Website + Prospects + Opportunities — in one package</span>
      </div>
      <h1 class="font-display font-extrabold text-4xl sm:text-5xl lg:text-[3.6rem] leading-[1.05] tracking-tight">
        We Build Your Website.<br>
        <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-amber-400">You Find the Customers.</span>
      </h1>
      <p class="mt-5 text-slate-300 text-base sm:text-lg leading-relaxed max-w-xl">
        Get a professional, SEO-ready business website plus targeted potential customers and business opportunities specific to your niche. We help you get online and find where the business is. <strong class="text-white">Your job is to close the client.</strong>
      </p>
      <div class="mt-8 flex flex-col sm:flex-row gap-3">
        <button onclick="openLead('')" class="btn-shine bg-emerald-500 hover:bg-emerald-400 text-[#0A1628] font-extrabold px-8 py-4 rounded-2xl text-base transition shadow-xl shadow-emerald-500/25">Get Your Website →</button>
        <a href="#pricing" class="inline-flex justify-center items-center border border-white/25 hover:border-white/60 hover:bg-white/5 font-bold px-8 py-4 rounded-2xl text-base transition">View Packages</a>
      </div>
      <p class="mt-4 text-amber-400 font-bold">Starting from UGX 250,000 <span class="text-slate-400 font-medium">· No WordPress · Full CMS · SEO included</span></p>
      <div class="mt-6 flex flex-wrap gap-2 text-xs font-semibold">
        @foreach(['Custom-built websites','Full CMS','SEO included','No WordPress','Niche-specific prospecting','Opportunity resources'] as $t)
        <span class="inline-flex items-center gap-1.5 bg-white/8 border border-white/12 bg-white/5 rounded-full px-3 py-1.5 text-slate-200"><svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>{{ $t }}</span>
        @endforeach
      </div>
    </div>
    <!-- Workflow visual -->
    <div class="reveal visible">
      <div class="bg-white/[.06] border border-white/10 rounded-3xl p-6 sm:p-8 backdrop-blur">
        <p class="text-xs font-bold tracking-widest text-emerald-400 uppercase mb-5">How Ttryy works for you</p>
        <div class="space-y-2" id="flowSteps">
          @php $flow=[['Your Business','Tell us what you sell','🏢'],['Ttryy Website','Pro site + CMS + SEO','🌐'],['Targeted Prospects','5,000 niche contacts','🎯'],['Opportunities','Tenders · Grants · Buyers','💼'],['You Close','Contact · Quote · Win','🤝']]; @endphp
          @foreach($flow as $i=>$f)
          <div class="flex items-center gap-4 bg-white rounded-2xl p-3.5 text-[#0A1628] {{ $i===4 ? '!bg-gradient-to-r !from-emerald-500 !to-emerald-600 !text-white !border-0' : '' }} border border-white/40 shadow-lg floaty" style="animation-delay:{{ $i*0.6 }}s">
            <span class="w-11 h-11 shrink-0 grid place-items-center text-xl rounded-xl {{ $i===4 ? 'bg-white/20' : 'bg-slate-100' }}">{{ $f[2] }}</span>
            <div class="min-w-0">
              <p class="font-bold text-[15px] leading-tight">{{ $f[0] }}</p>
              <p class="text-xs opacity-70">{{ $f[1] }}</p>
            </div>
            @if($i<4)<span class="ml-auto text-emerald-600 font-black pr-1">↓</span>@else<span class="ml-auto font-black pr-1">★</span>@endif
          </div>
          @endforeach
        </div>
        <div class="mt-5 grid grid-cols-3 gap-3 text-center">
          <div class="bg-white/5 border border-white/10 rounded-2xl py-3"><p class="font-display font-extrabold text-2xl text-amber-400">5K</p><p class="text-[11px] text-slate-300">Niche prospects</p></div>
          <div class="bg-white/5 border border-white/10 rounded-2xl py-3"><p class="font-display font-extrabold text-2xl text-amber-400">2K</p><p class="text-[11px] text-slate-300">Opportunities</p></div>
          <div class="bg-white/5 border border-white/10 rounded-2xl py-3"><p class="font-display font-extrabold text-2xl text-amber-400">14+</p><p class="text-[11px] text-slate-300">Industries</p></div>
        </div>
      </div>
    </div>
  </div>
  <!-- logo strip -->
  <div class="relative border-t border-white/10 bg-black/20">
    <div class="max-w-7xl mx-auto px-4 py-4 flex items-center gap-6 overflow-hidden">
      <span class="text-[11px] font-bold tracking-widest text-slate-400 uppercase shrink-0">Built for African business:</span>
      <div class="flex gap-8 text-slate-300 text-sm font-semibold whitespace-nowrap overflow-hidden"><div class="flex gap-8 marquee shrink-0">
        <span>NGOs & Charities</span><span>Construction</span><span>IT & Software</span><span>Marketing</span><span>Cleaning</span><span>Security</span><span>Catering</span><span>Printing</span><span>Solar</span><span>Logistics</span><span>Agriculture</span><span>Medical</span>
        <span>NGOs & Charities</span><span>Construction</span><span>IT & Software</span><span>Marketing</span><span>Cleaning</span><span>Security</span><span>Catering</span><span>Printing</span><span>Solar</span><span>Logistics</span><span>Agriculture</span><span>Medical</span>
      </div></div>
    </div>
  </div>
</section>

<!-- PROBLEM -->
<section class="py-16 lg:py-24 bg-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 grid lg:grid-cols-2 gap-10 items-center">
    <div class="reveal">
      <p class="text-emerald-700 font-bold text-xs tracking-[0.2em] uppercase">The problem</p>
      <h2 class="font-display font-extrabold text-3xl sm:text-4xl lg:text-[2.75rem] leading-tight mt-3">A Website Alone<br>Doesn't Bring You Business.</h2>
      <p class="mt-4 text-slate-600 text-lg">Many businesses have websites that simply sit online. Beautiful — but silent. The real questions are:</p>
      <div class="mt-6 space-y-3">
        @foreach(['Who are your potential customers?','Where can you find them?','Which organisations buy your services?','Which tenders & funding opportunities exist?','What should your sales team approach this week?','What opportunities can you apply for right now?'] as $q)
        <div class="flex items-start gap-3 bg-red-50/70 border border-red-100 rounded-xl px-4 py-3 text-[15px]"><span class="text-red-500 font-black">✕</span><span class="font-medium text-slate-700">{{ $q }}</span></div>
        @endforeach
      </div>
    </div>
    <div class="reveal">
      <div class="bg-[#0A1628] text-white rounded-3xl p-8 sm:p-10 relative overflow-hidden">
        <div class="absolute -top-20 -right-20 w-72 h-72 bg-emerald-500/25 blur-[80px] rounded-full"></div>
        <p class="text-emerald-400 font-bold text-xs tracking-[0.2em] uppercase">The Ttryy answer</p>
        <h3 class="font-display font-bold text-2xl sm:text-3xl mt-3 leading-snug">We help you get online and identify where the business is. <span class="text-amber-400">You focus on closing it.</span></h3>
        <div class="mt-6 grid sm:grid-cols-2 gap-3 text-sm">
          <div class="bg-white/5 border border-white/10 rounded-2xl p-4"><p class="font-bold">🌐 Get online</p><p class="text-slate-300 text-[13px] mt-1">Professional website + CMS + SEO that makes you look credible.</p></div>
          <div class="bg-white/5 border border-white/10 rounded-2xl p-4"><p class="font-bold">🎯 Find the market</p><p class="text-slate-300 text-[13px] mt-1">Niche prospects, directories & opportunity resources.</p></div>
          <div class="bg-white/5 border border-white/10 rounded-2xl p-4"><p class="font-bold">📨 Reach out</p><p class="text-slate-300 text-[13px] mt-1">Profiles, proposals & quotation templates included.</p></div>
          <div class="bg-white/5 border border-white/10 rounded-2xl p-4"><p class="font-bold">🏆 Close deals</p><p class="text-slate-300 text-[13px] mt-1">You contact, negotiate and win. We give you the head start.</p></div>
        </div>
        <button onclick="openLead('')" class="mt-6 w-full bg-emerald-500 hover:bg-emerald-400 text-[#0A1628] font-extrabold py-4 rounded-2xl transition">Get Your Website →</button>
      </div>
    </div>
  </div>
</section>

<!-- HOW IT WORKS -->
<section id="how" class="py-16 lg:py-24 bg-slate-50 light-grid">
  <div class="max-w-7xl mx-auto px-4 sm:px-6">
    <div class="text-center max-w-2xl mx-auto reveal">
      <p class="text-emerald-700 font-bold text-xs tracking-[0.2em] uppercase">How it works</p>
      <h2 class="font-display font-extrabold text-3xl sm:text-4xl lg:text-5xl mt-3">From Website to Potential Customer</h2>
      <p class="text-slate-600 mt-3 text-lg">Four simple steps. Zero guesswork.</p>
    </div>
    <div class="mt-12 grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
      @php $steps=[['01','Tell Us Your Niche','Tell Ttryy what your business sells and who you want to reach.','🎯','bg-amber-100'],['02','We Build Your Website','Professional, mobile-friendly website with custom CMS and SEO foundation.','🛠️','bg-emerald-100'],['03','We Identify Your Market','Niche-specific potential customers, business contacts, directories & opportunities.','🔍','bg-sky-100'],['04','You Close the Deal','Contact prospects, submit applications and win business with our resources.','🤝','bg-violet-100']]; @endphp
      @foreach($steps as $s)
      <div class="reveal bg-white rounded-3xl p-7 border border-slate-100 shadow-sm hover:shadow-xl transition relative overflow-hidden">
        <span class="font-display font-extrabold text-6xl text-slate-100 absolute top-3 right-5">{{ $s[0] }}</span>
        <span class="w-12 h-12 grid place-items-center text-2xl rounded-2xl {{ $s[4] }} relative">{{ $s[3] }}</span>
        <p class="text-xs font-bold text-emerald-700 mt-5 relative">{{ $s[0] }} — STEP</p>
        <h3 class="font-display font-bold text-xl mt-1 relative">{{ $s[1] }}</h3>
        <p class="text-slate-600 text-[15px] mt-2 relative">{{ $s[2] }}</p>
      </div>
      @endforeach
    </div>
    <div class="text-center mt-10 reveal"><button onclick="openLead('')" class="btn-shine bg-[#0A1628] hover:bg-emerald-700 text-white font-bold px-10 py-4 rounded-2xl transition">Start Building My Business →</button></div>
  </div>
</section>

<!-- WHAT YOU GET -->
<section class="py-16 lg:py-24 bg-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6">
    <div class="text-center max-w-2xl mx-auto reveal">
      <p class="text-emerald-700 font-bold text-xs tracking-[0.2em] uppercase">What you get</p>
      <h2 class="font-display font-extrabold text-3xl sm:text-4xl lg:text-5xl mt-3">More Than a Website</h2>
      <p class="text-slate-600 mt-3 text-lg">One package: <strong>Website + SEO + Prospecting + Opportunities + Business Tools.</strong></p>
    </div>
    <div class="mt-12 grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
      @php $gets=[['🌐','Professional Website','A modern website designed around your business, products and services.'],['🧩','Custom CMS','Manage your website content without depending on WordPress.'],['🚀','SEO','SEO foundations included to help you become discoverable online.'],['🎯','Potential Customers','Targeted business prospects based on your niche.'],['📇','Business Directories','Businesses and organisations that may need your products or services.'],['💼','Opportunities','Tenders, procurement, grants, applications and business opportunities.'],['📝','Sales Resources','Company profiles, proposals & quotation templates to look professional.'],['🧰','Business Tools','Free tools for accounting, CRM, inventory, POS, invoicing & more.']]; @endphp
      @foreach($gets as $g)
      <div class="reveal group bg-slate-50 hover:bg-[#0A1628] border border-slate-100 rounded-3xl p-6 transition duration-300">
        <span class="w-12 h-12 grid place-items-center text-2xl rounded-2xl bg-white shadow-sm group-hover:scale-110 transition">{{ $g[0] }}</span>
        <h3 class="font-display font-bold text-lg mt-4 group-hover:text-white">{{ $g[1] }}</h3>
        <p class="text-sm text-slate-600 mt-1.5 group-hover:text-slate-300">{{ $g[2] }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

<!-- NICHES -->
<section id="niches" class="py-16 lg:py-24 bg-[#0A1628] text-white relative overflow-hidden">
  <div class="absolute inset-0 hero-grid opacity-40"></div>
  <div class="relative max-w-7xl mx-auto px-4 sm:px-6">
    <div class="text-center max-w-3xl mx-auto reveal">
      <p class="text-emerald-400 font-bold text-xs tracking-[0.2em] uppercase">Niche-specific lead engine</p>
      <h2 class="font-display font-extrabold text-3xl sm:text-4xl lg:text-5xl mt-3">We Don't Give You Random Contacts.</h2>
      <p class="text-slate-300 mt-3 text-lg">Your potential customers are selected around <strong class="text-white">what you actually sell.</strong></p>
      <div class="mt-6 flex flex-col sm:flex-row gap-3 max-w-xl mx-auto">
        <div class="relative flex-1">
          <svg class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="M20 20l-3.5-3.5"/></svg>
          <input id="nicheSearch" type="text" placeholder="Search your industry… e.g. construction, NGO, solar" class="w-full bg-white/10 border border-white/15 rounded-2xl pl-11 pr-4 py-3.5 text-white placeholder:text-slate-400 outline-none focus:border-emerald-400">
        </div>
        <button onclick="openLead('')" class="bg-emerald-500 hover:bg-emerald-400 text-[#0A1628] font-bold px-6 py-3.5 rounded-2xl transition shrink-0">Tell Us Your Niche</button>
      </div>
    </div>
    <div id="nicheGrid" class="mt-10 grid sm:grid-cols-2 lg:grid-cols-3 gap-5"></div>
    <p class="text-center text-slate-400 text-xs mt-8 max-w-3xl mx-auto">Prospecting resources provide potential customers, business contacts and opportunity pointers relevant to your niche. Ttryy does not guarantee customers, contracts, grants or funding — you contact prospects and close the business.</p>
  </div>
</section>

<!-- MODEL -->
<section class="py-16 lg:py-24 bg-white">
  <div class="max-w-6xl mx-auto px-4 sm:px-6">
    <div class="text-center reveal"><p class="text-emerald-700 font-bold text-xs tracking-[0.2em] uppercase">The model</p>
      <h2 class="font-display font-extrabold text-3xl sm:text-4xl lg:text-5xl mt-3">We Find the Market.<br>You Close the Business.</h2></div>
    <div class="mt-10 grid md:grid-cols-2 gap-5">
      <div class="reveal bg-emerald-50 border-2 border-emerald-200 rounded-3xl p-8">
        <p class="inline-flex items-center gap-2 bg-emerald-600 text-white text-xs font-bold px-3 py-1.5 rounded-full">✓ TTRYY DOES</p>
        <ul class="mt-5 space-y-3 text-[15px] font-medium text-slate-800">
          @foreach(['Build your website','Make it SEO-ready','Identify potential customers','Organise business contacts','Identify relevant opportunities','Provide application resources','Provide sales resources'] as $t)
          <li class="flex gap-2.5"><span class="w-6 h-6 shrink-0 grid place-items-center bg-emerald-600 text-white rounded-full text-xs font-black">✓</span>{{ $t }}</li>
          @endforeach
        </ul>
      </div>
      <div class="reveal bg-amber-50 border-2 border-amber-200 rounded-3xl p-8">
        <p class="inline-flex items-center gap-2 bg-amber-500 text-[#0A1628] text-xs font-bold px-3 py-1.5 rounded-full">★ YOU DO</p>
        <ul class="mt-5 space-y-3 text-[15px] font-medium text-slate-800">
          @foreach(['Contact prospects','Build relationships','Send quotations','Attend meetings','Submit applications','Negotiate','Close the deal'] as $t)
          <li class="flex gap-2.5"><span class="w-6 h-6 shrink-0 grid place-items-center bg-amber-500 text-[#0A1628] rounded-full text-xs font-black">★</span>{{ $t }}</li>
          @endforeach
        </ul>
      </div>
    </div>
    <div class="reveal mt-6 bg-slate-100 border border-slate-200 rounded-2xl px-6 py-5 text-center text-slate-700 text-[15px]">We don't promise that every lead will become a customer. <strong>We give you a targeted starting point and the tools to pursue the opportunity.</strong></div>
  </div>
</section>

<!-- PRICING -->
<section id="pricing" class="py-16 lg:py-24 bg-slate-50 light-grid">
  <div class="max-w-7xl mx-auto px-4 sm:px-6">
    <div class="text-center max-w-2xl mx-auto reveal">
      <p class="text-emerald-700 font-bold text-xs tracking-[0.2em] uppercase">Pricing</p>
      <h2 class="font-display font-extrabold text-3xl sm:text-4xl lg:text-5xl mt-3">Choose Your Growth Package</h2>
      <p class="text-slate-600 mt-3 text-lg">Every package includes a professional website, custom CMS, SEO and a niche-specific business development resource.</p>
    </div>
    <div class="mt-12 grid lg:grid-cols-3 gap-6 items-stretch max-w-6xl mx-auto">
      <!-- START -->
      <div class="reveal bg-white rounded-3xl border border-slate-200 p-8 flex flex-col shadow-sm hover:shadow-xl transition">
        <p class="font-display font-extrabold tracking-wide text-slate-500">START</p>
        <p class="mt-2"><span class="font-display font-extrabold text-4xl">UGX 250,000</span></p>
        <p class="text-xs font-bold text-slate-500 mt-1 bg-slate-100 inline-block px-2.5 py-1 rounded-full w-fit">100% deposit</p>
        <p class="text-sm text-slate-600 mt-3">For businesses getting online and starting their prospecting journey.</p>
        <ul class="mt-6 space-y-2.5 text-sm flex-1">
          @foreach(['Full professional website','Custom CMS','Mobile responsive','SEO included','5,000 niche-specific potential customers','Lead/resource PDF','100 application/opportunity resources','Contact/lead forms','WhatsApp integration','No WordPress'] as $f)
          <li class="flex gap-2"><svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg><span>{{ $f }}</span></li>
          @endforeach
        </ul>
        <button onclick="openLead('START')" class="mt-7 w-full border-2 border-[#0A1628] hover:bg-[#0A1628] hover:text-white font-bold py-3.5 rounded-2xl transition">Get Started</button>
      </div>
      <!-- GROW -->
      <div class="reveal relative bg-[#0A1628] text-white rounded-3xl p-8 flex flex-col shadow-2xl shadow-slate-900/25 lg:scale-[1.04] border-2 border-emerald-500">
        <span class="absolute -top-3.5 left-1/2 -translate-x-1/2 bg-gradient-to-r from-emerald-500 to-amber-400 text-[#0A1628] text-xs font-extrabold px-4 py-1.5 rounded-full whitespace-nowrap">MOST POPULAR ★</span>
        <p class="font-display font-extrabold tracking-wide text-emerald-400">GROW</p>
        <p class="mt-2"><span class="font-display font-extrabold text-4xl">UGX 350,000</span></p>
        <p class="text-xs font-bold mt-1 bg-white/10 inline-block px-2.5 py-1 rounded-full w-fit text-amber-300">60% deposit</p>
        <p class="text-sm text-slate-300 mt-3">For businesses ready to actively pursue customers and opportunities.</p>
        <p class="text-xs font-bold text-slate-400 mt-4 uppercase tracking-wider">Everything in START, plus:</p>
        <ul class="mt-3 space-y-2.5 text-sm flex-1">
          @foreach(['5,000 niche-specific potential customers','200 application/opportunity resources','Enhanced prospecting resources','Professional business profile resources','Proposal/quotation resources','Full CMS','SEO'] as $f)
          <li class="flex gap-2"><svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg><span>{{ $f }}</span></li>
          @endforeach
        </ul>
        <button onclick="openLead('GROW')" class="btn-shine mt-7 w-full bg-emerald-500 hover:bg-emerald-400 text-[#0A1628] font-extrabold py-4 rounded-2xl transition">Choose Grow →</button>
      </div>
      <!-- BUSINESS -->
      <div class="reveal bg-white rounded-3xl border border-slate-200 p-8 flex flex-col shadow-sm hover:shadow-xl transition">
        <p class="font-display font-extrabold tracking-wide text-slate-500">BUSINESS</p>
        <p class="mt-2"><span class="font-display font-extrabold text-4xl">UGX 650,000</span></p>
        <p class="text-xs font-bold text-slate-500 mt-1 bg-slate-100 inline-block px-2.5 py-1 rounded-full w-fit">70% deposit</p>
        <p class="text-sm text-slate-600 mt-3">For businesses that want a larger business-development database.</p>
        <p class="text-xs font-bold text-slate-400 mt-4 uppercase tracking-wider">Everything in GROW, plus:</p>
        <ul class="mt-3 space-y-2.5 text-sm flex-1">
          @foreach(['5,000 niche-specific potential customers','2,000 business directory contacts','2,000 applications/opportunities','Advanced business-development resources','Advanced CMS','Advanced SEO setup'] as $f)
          <li class="flex gap-2"><svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg><span>{{ $f }}</span></li>
          @endforeach
        </ul>
        <button onclick="openLead('BUSINESS')" class="mt-7 w-full border-2 border-[#0A1628] hover:bg-[#0A1628] hover:text-white font-bold py-3.5 rounded-2xl transition">Choose Business</button>
      </div>
    </div>
    <!-- comparison table -->
    <div class="reveal mt-12 bg-white rounded-3xl border border-slate-200 overflow-hidden max-w-6xl mx-auto">
      <div class="overflow-x-auto">
      <table class="w-full text-sm min-w-[640px]">
        <thead><tr class="bg-[#0A1628] text-white text-left"><th class="px-6 py-4 font-bold">Feature</th><th class="px-6 py-4 font-bold">Start</th><th class="px-6 py-4 font-bold text-emerald-400">Grow ★</th><th class="px-6 py-4 font-bold">Business</th></tr></thead>
        <tbody class="divide-y divide-slate-100">
          @php $rows=[['Price','UGX 250K','UGX 350K','UGX 650K'],['Deposit','100%','60%','70%'],['Professional website','✓','✓','✓'],['Custom CMS','✓','✓','✓'],['WordPress','No','No','No'],['SEO','✓','✓','✓'],['Mobile responsive','✓','✓','✓'],['Niche potential customers','5,000','5,000','5,000'],['Application/opportunity resources','100','200','2,000'],['Business directory contacts','—','—','2,000'],['Lead/resource PDF','✓','✓','✓'],['Contact forms','✓','✓','✓'],['WhatsApp integration','✓','✓','✓'],['Proposal resources','✓','✓','✓'],['Quotation resources','✓','✓','✓']]; @endphp
          @foreach($rows as $r)
          <tr class="hover:bg-slate-50"><td class="px-6 py-3 font-medium text-slate-700">{{ $r[0] }}</td><td class="px-6 py-3">{{ $r[1] }}</td><td class="px-6 py-3 bg-emerald-50/60 font-semibold">{{ $r[2] }}</td><td class="px-6 py-3">{{ $r[3] }}</td></tr>
          @endforeach
        </tbody>
      </table>
      </div>
    </div>
  </div>
</section>

<!-- APPLICATION SUPPORT -->
<section class="py-16 lg:py-20 bg-white">
  <div class="max-w-6xl mx-auto px-4 sm:px-6">
    <div class="reveal bg-gradient-to-br from-emerald-700 via-emerald-800 to-[#0A1628] text-white rounded-[2rem] p-8 sm:p-12 grid lg:grid-cols-2 gap-8 items-center relative overflow-hidden">
      <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-amber-400/20 blur-[100px] rounded-full"></div>
      <div class="relative">
        <p class="text-emerald-300 font-bold text-xs tracking-[0.2em] uppercase">Application support</p>
        <h2 class="font-display font-extrabold text-3xl sm:text-4xl mt-3">Found an Opportunity? We Can Help You Apply.</h2>
        <p class="text-emerald-50/90 mt-3">Ttryy can provide paid application and preparation services for selected opportunities.</p>
        <div class="mt-5 flex flex-wrap gap-2 text-xs font-semibold">
          @foreach(['Grant applications','Tender preparation','RFP responses','Proposal writing','Concept notes','Company profiles','Technical proposals','Financial proposal formatting','Document preparation','Bid compliance checklist'] as $s)
          <span class="bg-white/10 border border-white/15 rounded-full px-3 py-1.5">{{ $s }}</span>
          @endforeach
        </div>
        <button onclick="openLead('', 'Application support')" class="mt-6 bg-amber-400 hover:bg-amber-300 text-[#0A1628] font-extrabold px-8 py-3.5 rounded-2xl transition">Request Application Support →</button>
        <p class="mt-4 text-[11px] text-emerald-100/70 leading-relaxed">Application support does not guarantee funding, contract awards or successful applications. Eligibility and final submission decisions remain with the relevant funder, procuring organisation or institution.</p>
      </div>
      <div class="relative bg-white text-slate-900 rounded-3xl p-6 sm:p-8 shadow-2xl">
        <p class="font-display font-bold text-lg">📄 Sample: what we prepare</p>
        <div class="mt-4 space-y-3 text-sm">
          <div><div class="flex justify-between text-xs font-bold mb-1"><span>Compliance checklist</span><span class="text-emerald-600">92%</span></div><div class="h-2 bg-slate-100 rounded-full"><div class="h-2 bg-emerald-500 rounded-full" style="width:92%"></div></div></div>
          <div><div class="flex justify-between text-xs font-bold mb-1"><span>Technical proposal</span><span class="text-emerald-600">Complete</span></div><div class="h-2 bg-slate-100 rounded-full"><div class="h-2 bg-emerald-500 rounded-full" style="width:100%"></div></div></div>
          <div><div class="flex justify-between text-xs font-bold mb-1"><span>Financial proposal</span><span class="text-amber-600">In review</span></div><div class="h-2 bg-slate-100 rounded-full"><div class="h-2 bg-amber-400 rounded-full" style="width:70%"></div></div></div>
        </div>
        <div class="mt-5 bg-slate-50 rounded-2xl p-4 text-xs text-slate-600">✅ Company profile &nbsp; ✅ Concept note &nbsp; ✅ Cover letter &nbsp; ✅ Attachments pack</div>
      </div>
    </div>
  </div>
</section>

<!-- OUR WORK -->
<section id="work" class="py-16 lg:py-24 bg-slate-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 reveal">
      <div><p class="text-emerald-700 font-bold text-xs tracking-[0.2em] uppercase">Portfolio</p>
        <h2 class="font-display font-extrabold text-3xl sm:text-4xl lg:text-5xl mt-3">Websites We've Built</h2>
        <p class="text-slate-600 mt-2">Real businesses. Real websites. Ready to win customers.</p></div>
      <button onclick="openLead('')" class="shrink-0 border-2 border-[#0A1628] hover:bg-[#0A1628] hover:text-white font-bold px-6 py-3 rounded-2xl transition text-sm">Get One Like This →</button>
    </div>
    <div class="mt-10 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
      @php $works=[['Savanna Build Ltd','Construction','Company site + tender-ready profile + contractor prospect pack.','#0A1628','🏗️'],['BrightSmile Clinics','Medical','Clinic network site with booking, SEO + hospital buyer directory.','#047857','🏥'],['EduCare Foundation','NGO & Charity','Donor-ready NGO site + 100+ grant platform resource pack.','#7C3AED','🤝'],['VoltAfrica Solar','Solar & Energy','Solar installer site + institutional buyer prospect pack.','#B45309','☀️'],['FreshPlate Catering','Catering','Catering site with quote forms + corporate & events directory.','#BE123C','🍽️'],['SwiftLine Logistics','Logistics','Logistics site with tracking pages + importer/exporter directory.','#0369A1','🚚']]; @endphp
      @foreach($works as $w)
      <div class="reveal group bg-white rounded-3xl overflow-hidden border border-slate-200 hover:shadow-2xl transition">
        <div class="h-44 relative overflow-hidden" style="background:{{ $w[3] }}">
          <div class="absolute inset-0 hero-grid opacity-50"></div>
          <div class="absolute inset-x-6 top-6 bottom-0 bg-white rounded-t-2xl p-4 shadow-xl group-hover:-translate-y-1 transition">
            <div class="flex gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-red-400"></span><span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span><span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span></div>
            <div class="mt-3 text-4xl">{{ $w[4] }}</div>
            <div class="mt-2 h-2.5 bg-slate-900 rounded w-2/3"></div>
            <div class="mt-2 h-2 bg-slate-200 rounded w-full"></div>
            <div class="mt-1.5 h-2 bg-slate-200 rounded w-5/6"></div>
            <div class="mt-3 flex gap-2"><span class="h-6 w-20 rounded-full" style="background:{{ $w[3] }}"></span><span class="h-6 w-20 rounded-full border border-slate-200"></span></div>
          </div>
        </div>
        <div class="p-6">
          <span class="text-[11px] font-bold bg-emerald-100 text-emerald-800 px-2.5 py-1 rounded-full">{{ $w[1] }}</span>
          <h3 class="font-display font-bold text-lg mt-2">{{ $w[0] }}</h3>
          <p class="text-sm text-slate-600 mt-1">{{ $w[2] }}</p>
          <button onclick="openLead('')" class="mt-4 text-sm font-bold text-emerald-700 hover:gap-3 gap-2 inline-flex items-center transition-all">View Website <span>→</span></button>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>

<!-- WHY TTRYY -->
<section class="py-16 lg:py-24 bg-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 text-center">
    <div class="reveal"><p class="text-emerald-700 font-bold text-xs tracking-[0.2em] uppercase">Why Ttryy</p>
      <h2 class="font-display font-extrabold text-3xl sm:text-4xl lg:text-5xl mt-3">We Think Beyond the Website</h2></div>
    <div class="mt-10 grid sm:grid-cols-2 lg:grid-cols-4 gap-5 text-left">
      @php $why=[['🌐','Website','A professional online presence that makes buyers trust you.'],['👁️','Visibility','SEO and discoverability foundations so customers can find you.'],['🎯','Prospecting','Potential customers relevant to your niche, organised and ready.'],['💼','Opportunities','Business, procurement, funding and application resources.']]; @endphp
      @foreach($why as $w)
      <div class="reveal bg-gradient-to-b from-slate-50 to-white border border-slate-200 rounded-3xl p-7 hover:border-emerald-300 hover:shadow-xl transition">
        <span class="text-4xl">{{ $w[0] }}</span>
        <h3 class="font-display font-bold text-xl mt-4">{{ $w[1] }}</h3>
        <p class="text-slate-600 text-[15px] mt-2">{{ $w[2] }}</p>
      </div>
      @endforeach
    </div>
    <p class="reveal mt-10 font-display font-bold text-xl sm:text-2xl">Your website is the front door. <span class="text-emerald-700">Your prospect database is where the business starts.</span></p>
    <button onclick="openLead('')" class="reveal mt-6 btn-shine bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-10 py-4 rounded-2xl transition">Get Your Website →</button>
  </div>
</section>

<!-- BUSINESS TOOLS -->
<section id="tools" class="py-16 lg:py-24 bg-[#0A1628] text-white relative overflow-hidden">
  <div class="absolute -top-32 left-1/3 w-[500px] h-[500px] bg-emerald-500/15 blur-[130px] rounded-full"></div>
  <div class="relative max-w-7xl mx-auto px-4 sm:px-6">
    <div class="text-center max-w-2xl mx-auto reveal">
      <span class="text-[11px] font-bold bg-white/10 border border-white/15 px-3 py-1.5 rounded-full tracking-widest uppercase text-slate-300">Coming soon · Secondary preview</span>
      <h2 class="font-display font-extrabold text-3xl sm:text-4xl mt-4">And We're Building the Tools to Run Your Business</h2>
      <p class="text-slate-300 mt-3">Ttryy is building a free business platform to help you manage day-to-day operations.</p>
    </div>
    <div class="mt-10 grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
      @php $tools=[['📒','Accounting',['Income','Expenses','P&L','Balance Sheet','Cash Flow']],['💰','Sales',['Quotations','Invoices','Receipts','Sales Orders']],['🤝','CRM',['Customers','Leads','Follow-ups','Customer history']],['📦','Inventory',['Products','Stock','Suppliers','Purchases']],['🧾','POS',['Point of sale','Receipts','Inventory updates','Sales tracking']],['📊','Business Management',['Tasks','Documents','Projects','Reports']]]; @endphp
      @foreach($tools as $t)
      <div class="reveal bg-white/5 border border-white/10 rounded-3xl p-6 hover:bg-white/10 transition">
        <span class="text-3xl">{{ $t[0] }}</span>
        <h3 class="font-display font-bold text-lg mt-3">{{ $t[1] }}</h3>
        <div class="mt-3 flex flex-wrap gap-1.5">@foreach($t[2] as $f)<span class="text-xs bg-white/10 rounded-full px-2.5 py-1 text-slate-200">{{ $f }}</span>@endforeach</div>
      </div>
      @endforeach
    </div>
    <p class="reveal text-center mt-8 font-display font-bold text-lg text-amber-300">Get customers. Manage customers. Get paid. Run your business.</p>
    <div class="text-center mt-4 reveal"><button onclick="openLead('', 'Business tools early access')" class="border border-white/25 hover:bg-white hover:text-[#0A1628] font-bold px-8 py-3.5 rounded-2xl transition">Explore Ttryy Business Tools</button></div>
  </div>
</section>

<!-- FAQ -->
<section id="faq" class="py-16 lg:py-24 bg-white">
  <div class="max-w-3xl mx-auto px-4 sm:px-6">
    <div class="text-center reveal"><p class="text-emerald-700 font-bold text-xs tracking-[0.2em] uppercase">FAQ</p>
      <h2 class="font-display font-extrabold text-3xl sm:text-4xl mt-3">Questions? Answered.</h2></div>
    <div class="mt-8 space-y-3" id="faqList">
      @php $faqs=[['Do I get a website?','Yes. Every package includes a professional website and custom CMS.'],['Do you use WordPress?','No. Ttryy websites use a custom CMS rather than WordPress — faster, more secure and easier to manage.'],['Are the 5,000 contacts guaranteed customers?','No. They are potential customers and prospects relevant to your selected niche. Ttryy provides the prospecting resource; you contact and close the customers.'],['What kind of contacts do I receive?','It depends on your niche. They can include businesses, organisations, institutions, procurement contacts, potential buyers, donors, funders, partners and other relevant prospects.'],['Can you help me apply for opportunities?','Yes. Application and proposal support can be offered as an additional paid service — including tenders, grants, RFPs, concept notes and company profiles.'],['Does Ttryy guarantee grants or tenders?','No. Ttryy helps identify relevant opportunities and can assist with applications, but final decisions belong to the funder, buyer or procuring organisation.'],['Can I manage my website myself?','Yes. Your website includes a CMS for managing supported content — no developer needed for everyday updates.'],['Is SEO included?','Yes. SEO foundations are included in all packages to help your website become discoverable online.'],['Can you build websites for any industry?','Yes. Tell us what your business does and we can create a niche-specific website and prospecting package — even if your industry isn\u2019t listed.']]; @endphp
      @foreach($faqs as $f)
      <details class="faq reveal group bg-slate-50 border border-slate-200 rounded-2xl open:bg-white open:shadow-lg transition">
        <summary class="cursor-pointer list-none flex items-center justify-between gap-4 px-5 sm:px-6 py-4 font-bold text-[15px]">{{ $f[0] }}<span class="faq-icon w-8 h-8 shrink-0 grid place-items-center rounded-full bg-[#0A1628] text-white text-lg leading-none transition-transform group-open:rotate-45">+</span></summary>
        <p class="px-5 sm:px-6 pb-5 text-slate-600 text-[15px] leading-relaxed">{{ $f[1] }}</p>
      </details>
      @endforeach
    </div>
  </div>
</section>

<!-- FINAL CTA -->
<section class="relative bg-[#0A1628] text-white overflow-hidden">
  <div class="absolute inset-0 hero-grid opacity-50"></div>
  <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[700px] h-[300px] bg-emerald-500/20 blur-[120px] rounded-full"></div>
  <div class="relative max-w-4xl mx-auto px-4 py-16 lg:py-24 text-center">
    <h2 class="reveal font-display font-extrabold text-3xl sm:text-5xl leading-tight">Your Next Customer Is<br>Already Out There.</h2>
    <p class="reveal text-slate-300 text-lg mt-4 max-w-2xl mx-auto">We help you get online, identify the right prospects and find opportunities. You focus on turning them into customers.</p>
    <div class="reveal mt-8 flex flex-col sm:flex-row justify-center gap-3">
      <button onclick="openLead('')" class="btn-shine bg-emerald-500 hover:bg-emerald-400 text-[#0A1628] font-extrabold px-10 py-4 rounded-2xl transition">Get My Website →</button>
      <a href="https://wa.me/256700000000?text=Hi%20Ttryy!%20I%20want%20to%20talk%20about%20growing%20my%20business." target="_blank" class="border border-white/25 hover:bg-white/10 font-bold px-10 py-4 rounded-2xl transition">Talk to Ttryy</a>
    </div>
    <p class="reveal mt-4 text-amber-400 font-bold">Starting from UGX 250,000</p>
  </div>
</section>

<!-- FOOTER -->
<footer class="bg-[#060D1A] text-slate-300">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 py-14">
    <div class="grid md:grid-cols-2 lg:grid-cols-6 gap-10">
      <div class="lg:col-span-1">
        <div class="flex items-center gap-2"><span class="w-9 h-9 rounded-xl bg-emerald-600 grid place-items-center text-white font-black text-xl">T</span><span class="font-display font-extrabold text-2xl text-white">Ttryy.</span></div>
        <p class="text-sm mt-4 leading-relaxed">Websites, customers, opportunities and business tools — built for businesses that want to grow.</p>
        <div class="mt-5 space-y-2 text-sm">
          <a href="https://wa.me/256700000000" target="_blank" class="flex items-center gap-2 hover:text-emerald-400">📱 WhatsApp: +256 700 000000</a>
          <a href="mailto:hello@ttryy.com" class="flex items-center gap-2 hover:text-emerald-400">✉️ hello@ttryy.com</a>
          <span class="flex items-center gap-2">📞 +256 700 000000</span>
        </div>
        <div class="mt-4 flex gap-2">
          @foreach(['𝕏','in','f','IG','YT'] as $s)<a href="#" class="w-9 h-9 grid place-items-center rounded-full bg-white/10 hover:bg-emerald-600 hover:text-white text-xs font-bold transition">{{ $s }}</a>@endforeach
        </div>
      </div>
      <div><p class="text-white font-bold text-sm tracking-wide">Website Services</p><ul class="mt-4 space-y-2.5 text-sm">@foreach(['Business Websites','Custom CMS','SEO','Website Maintenance','Website Portfolio'] as $l)<li><a href="#work" class="hover:text-emerald-400">{{ $l }}</a></li>@endforeach</ul></div>
      <div><p class="text-white font-bold text-sm tracking-wide">Find Business</p><ul class="mt-4 space-y-2.5 text-sm">@foreach(['Potential Customers','Business Directories','Tenders','Grants','Procurement Opportunities','Funding Opportunities','Business Opportunities'] as $l)<li><a href="#niches" class="hover:text-emerald-400">{{ $l }}</a></li>@endforeach</ul></div>
      <div><p class="text-white font-bold text-sm tracking-wide">Business Tools</p><ul class="mt-4 space-y-2.5 text-sm">@foreach(['Accounting','CRM','Quotations','Invoicing','Receipts','POS','Inventory','Customers','Suppliers','Expenses','Reports','Cash Flow','P&L','Balance Sheet'] as $l)<li><a href="#tools" class="hover:text-emerald-400">{{ $l }}</a></li>@endforeach</ul></div>
      <div><p class="text-white font-bold text-sm tracking-wide">Resources</p><ul class="mt-4 space-y-2.5 text-sm">@foreach(['Business Templates','Proposal Templates','Company Profiles','Tender Resources','Grant Resources','Business Plans','Marketing Resources','PDF Tools','Business Guides'] as $l)<li><a href="#" class="hover:text-emerald-400">{{ $l }}</a></li>@endforeach</ul>
        <p class="text-white font-bold text-sm tracking-wide mt-6">Company</p><ul class="mt-3 space-y-2.5 text-sm">@foreach(['About Ttryy','Our Work','Contact','FAQ','Terms','Privacy Policy'] as $l)<li><a href="#" class="hover:text-emerald-400">{{ $l }}</a></li>@endforeach</ul></div>
      <div><p class="text-white font-bold text-sm tracking-wide">Industries</p><ul class="mt-4 space-y-2.5 text-sm">@foreach(['NGOs & Charities','Construction','IT & Software','Marketing','Cleaning','Security','Catering','Printing','Furniture','Accounting','Logistics','Agriculture','Solar','Medical','Retail','Professional Services'] as $l)<li><a href="#niches" class="hover:text-emerald-400">{{ $l }}</a></li>@endforeach</ul></div>
    </div>
    <div class="mt-12 pt-6 border-t border-white/10 flex flex-col sm:flex-row justify-between gap-3 text-xs text-slate-500">
      <p>© 2026 Ttryy. All rights reserved.</p>
      <p>Get online. Find opportunities. Win customers. Run your business.</p>
    </div>
  </div>
</footer>

<!-- WhatsApp float + sticky mobile CTA -->
<a href="https://wa.me/256700000000?text=Hi%20Ttryy!%20I%20want%20a%20website%20for%20my%20business." target="_blank" class="fixed bottom-20 sm:bottom-6 right-4 z-40 w-14 h-14 grid place-items-center rounded-full bg-[#25D366] shadow-2xl hover:scale-110 transition" aria-label="Chat on WhatsApp">
  <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Zm5.2 14.2c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .2-3.4-.7-2.9-1.2-4.7-4.1-4.9-4.3-.1-.2-1.1-1.5-1.1-2.9s.7-2 1-2.3c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5s.8 1.9.8 2c.1.1.1.3 0 .5-.3.6-.6.8-.4 1.1.7 1.2 1.6 2 2.8 2.6.3.2.5 0 .7-.2l.7-.8c.2-.3.4-.2.7-.1l2 1c.3.1.5.2.6.4 0 .1 0 .6-.5 1.3Z"/></svg>
</a>
<div class="sm:hidden fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur border-t border-slate-200 px-4 py-3 flex gap-2">
  <button onclick="openLead('')" class="flex-1 bg-emerald-600 text-white font-bold py-3 rounded-xl text-sm">Get Your Website</button>
  <a href="#pricing" class="flex-1 text-center border-2 border-[#0A1628] font-bold py-2.5 rounded-xl text-sm">See Packages</a>
</div>
<div class="h-16 sm:hidden"></div>

<!-- LEAD MODAL -->
<div id="leadModal" class="fixed inset-0 z-[60] hidden items-center justify-center p-4">
  <div class="absolute inset-0 bg-[#0A1628]/70 backdrop-blur-sm" onclick="closeLead()"></div>
  <div class="relative bg-white rounded-3xl w-full max-w-2xl max-h-[92vh] overflow-y-auto shadow-2xl">
    <div class="sticky top-0 bg-white/95 backdrop-blur px-6 sm:px-8 pt-6 pb-4 border-b border-slate-100 flex items-start justify-between rounded-t-3xl">
      <div><h3 class="font-display font-extrabold text-2xl">Get Started</h3><p class="text-sm text-slate-500">Request your Ttryy package — we reply fast on WhatsApp.</p></div>
      <button onclick="closeLead()" class="w-9 h-9 grid place-items-center rounded-full bg-slate-100 hover:bg-slate-200 text-xl">×</button>
    </div>
    <form id="leadForm" class="px-6 sm:px-8 py-6 grid sm:grid-cols-2 gap-4">
      <div><label class="text-xs font-bold">Full name *</label><input required name="name" class="mt-1 w-full border border-slate-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" placeholder="Jane Nakato"></div>
      <div><label class="text-xs font-bold">Business name *</label><input required name="business" class="mt-1 w-full border border-slate-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" placeholder="Savanna Build Ltd"></div>
      <div><label class="text-xs font-bold">Phone / WhatsApp *</label><input required name="phone" class="mt-1 w-full border border-slate-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" placeholder="+256 700 000000"></div>
      <div><label class="text-xs font-bold">Email</label><input type="email" name="email" class="mt-1 w-full border border-slate-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" placeholder="you@business.com"></div>
      <div><label class="text-xs font-bold">Country</label><input name="country" value="Uganda" class="mt-1 w-full border border-slate-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"></div>
      <div><label class="text-xs font-bold">Business location</label><input name="location" class="mt-1 w-full border border-slate-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" placeholder="Kampala"></div>
      <div class="sm:col-span-2"><label class="text-xs font-bold">What does your business sell? *</label><input required name="sells" id="leadSells" class="mt-1 w-full border border-slate-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" placeholder="e.g. construction services, school supplies…"></div>
      <div><label class="text-xs font-bold">Industry / niche *</label><select required name="niche" id="leadNiche" class="mt-1 w-full border border-slate-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-emerald-500 bg-white"><option value="">Select…</option></select></div>
      <div><label class="text-xs font-bold">Preferred package</label><select name="package" id="leadPackage" class="mt-1 w-full border border-slate-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-emerald-500 bg-white"><option value="">Not sure yet</option><option>START — UGX 250,000</option><option>GROW — UGX 350,000</option><option>BUSINESS — UGX 650,000</option></select></div>
      <div class="sm:col-span-2"><label class="text-xs font-bold">Website / social media (if existing)</label><input name="existing" class="mt-1 w-full border border-slate-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" placeholder="https://…"></div>
      <div class="sm:col-span-2"><label class="text-xs font-bold">Message</label><textarea name="message" id="leadMsg" rows="3" class="mt-1 w-full border border-slate-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" placeholder="Tell us about your goals…"></textarea></div>
      <button class="sm:col-span-2 btn-shine bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold py-4 rounded-2xl transition">Request My Ttryy Package →</button>
      <p class="sm:col-span-2 text-center text-xs text-slate-400">Or chat now: <a class="text-emerald-700 font-bold" target="_blank" href="https://wa.me/256700000000">WhatsApp Ttryy</a></p>
    </form>
    <div id="leadSuccess" class="hidden px-8 py-12 text-center">
      <div class="w-16 h-16 mx-auto grid place-items-center rounded-full bg-emerald-100 text-3xl">✓</div>
      <h3 class="font-display font-extrabold text-2xl mt-4">Thanks! We've received your request.</h3>
      <p class="text-slate-600 mt-2">A Ttryy representative will contact you to confirm your niche, website requirements and package.</p>
      <button onclick="closeLead()" class="mt-6 bg-[#0A1628] text-white font-bold px-8 py-3 rounded-2xl">Done</button>
    </div>
  </div>
</div>

<script>
const NICHES = [
 {icon:'🤝',name:'NGOs & Charities',tags:'ngo charity donor grant foundation',prospects:['Grant makers','Foundations','Donors','Development agencies','UN organisations','Corporate foundations','CSR programs','Philanthropic orgs','International NGOs','Government programs','Research funders','NGO partners'],opps:['Grants','Funding opportunities','Fellowships','NGO partnerships','Procurement','Program funding','Research grants','CSR funding'],note:'Example: 100+ relevant donor and grant platforms for NGOs in Africa/Uganda'},
 {icon:'🏗️',name:'Construction',tags:'construction building contractor developer',prospects:['Property developers','Real estate companies','Government institutions','NGOs','Schools','Hospitals','Hotels','Factories','Commercial owners','Architects','Engineering firms','Quantity surveyors','Property managers'],opps:['Construction tenders','Building projects','Renovation contracts','Government procurement','NGO projects','Consultancy']},
 {icon:'💻',name:'IT & Software',tags:'it software tech computer digital',prospects:['SMEs','NGOs','Schools','Hospitals','Banks','SACCOs','Government institutions','Hotels','Manufacturers','Logistics companies','Professional firms'],opps:['IT tenders','Website projects','Software contracts','ERP projects','IT support contracts','Digital transformation','Data projects']},
 {icon:'📣',name:'Marketing & Advertising',tags:'marketing advertising branding media',prospects:['Hotels','Restaurants','Banks','NGOs','Schools','Real estate','Retailers','Manufacturers','Telecom','Professional firms','Events companies'],opps:['Marketing contracts','Advertising contracts','Branding projects','Social media contracts','Event campaigns','Corporate campaigns']},
 {icon:'🧹',name:'Cleaning & Facility Management',tags:'cleaning facility hygiene',prospects:['Offices','Hotels','Schools','Hospitals','Shopping malls','Factories','Churches','Government institutions','NGOs','Apartments','Property managers'],opps:['Office cleaning contracts','Hotel contracts','Facility-management contracts','Government tenders','NGO procurement']},
 {icon:'🛡️',name:'Security',tags:'security guard cctv',prospects:['Banks','Hotels','Schools','Hospitals','Factories','Warehouses','Construction companies','Shopping centres','Offices','NGOs','Residential estates'],opps:['Security tenders','Guarding contracts','CCTV projects','Access-control projects','Facility contracts']},
 {icon:'🍽️',name:'Catering & Food Services',tags:'catering food restaurant events',prospects:['Corporates','NGOs','Schools','Universities','Hospitals','Hotels','Event companies','Churches','Government agencies'],opps:['Corporate catering','Event catering','Conference catering','NGO workshop catering','School meals','Government events']},
 {icon:'🖨️',name:'Printing & Branding',tags:'printing branding design signage',prospects:['Schools','NGOs','Corporates','Government agencies','Churches','Hotels','Events companies','Publishers','Ad agencies'],opps:['Annual reports','Brochures','Banners','Books','Stationery','Merchandise','Event materials']},
 {icon:'🪑',name:'Office Furniture',tags:'furniture office interior',prospects:['Companies','Schools','Hospitals','Hotels','NGOs','Government institutions','Banks','Offices','Co-working spaces','Property developers'],opps:['Office furnishing','School furniture','Hospital furniture','Hotel furniture','Government & NGO procurement']},
 {icon:'📊',name:'Accounting & Professional Services',tags:'accounting audit tax finance',prospects:['SMEs','NGOs','Schools','Churches','SACCOs','Startups','Companies','Cooperatives','Associations'],opps:['Audit contracts','Accounting contracts','Bookkeeping','Tax consultancy','Payroll','Financial reporting']},
 {icon:'🚚',name:'Logistics & Transport',tags:'logistics transport delivery fleet',prospects:['Importers','Exporters','Manufacturers','Retailers','Wholesalers','NGOs','Construction companies','Distributors','E-commerce'],opps:['Delivery contracts','Transport contracts','Fleet contracts','Logistics tenders','Freight & distribution']},
 {icon:'🌾',name:'Agriculture & Agribusiness',tags:'agriculture farming agro',prospects:['Farmers','Commercial farms','Cooperatives','NGOs','Government programs','Agro-processors','Distributors','Agri projects'],opps:['Agri procurement','Seed & fertilizer supply','Equipment supply','Agri projects','Government & NGO programs']},
 {icon:'☀️',name:'Solar & Renewable Energy',tags:'solar energy renewable power',prospects:['Schools','Hospitals','Hotels','Farms','Factories','NGOs','Homes','Government institutions','Offices','Telecom companies'],opps:['Solar installations','Rural electrification','NGO projects','Government tenders','Institutional installs']},
 {icon:'🏥',name:'Medical Suppliers',tags:'medical health pharma hospital',prospects:['Hospitals','Clinics','Pharmacies','NGOs','Government agencies','Laboratories','Health programs','Medical centres'],opps:['Medical equipment tenders','Pharma procurement','Lab equipment','Health project procurement']},
];
function nicheCard(n){
  return `<div class="niche-card niche-item bg-white/[.06] border border-white/10 rounded-3xl p-6 text-left" data-search="${(n.name+' '+n.tags+' '+n.prospects.join(' ')+' '+n.opps.join(' ')).toLowerCase()}">
    <div class="flex items-center gap-3"><span class="w-11 h-11 grid place-items-center text-2xl rounded-2xl bg-white/10">${n.icon}</span>
    <h3 class="font-display font-bold text-lg leading-tight">${n.name}</h3></div>
    <p class="text-[11px] font-bold tracking-widest text-emerald-400 uppercase mt-5">Potential customers</p>
    <div class="mt-2 flex flex-wrap gap-1.5">${n.prospects.slice(0,8).map(p=>`<span class="text-xs bg-white/10 rounded-full px-2.5 py-1 text-slate-200">${p}</span>`).join('')}<span class="text-xs text-slate-400">+${n.prospects.length-8} more</span></div>
    <p class="text-[11px] font-bold tracking-widest text-amber-400 uppercase mt-4">Opportunities</p>
    <div class="mt-2 flex flex-wrap gap-1.5">${n.opps.slice(0,4).map(p=>`<span class="text-xs bg-amber-400/15 text-amber-200 border border-amber-400/20 rounded-full px-2.5 py-1">${p}</span>`).join('')}</div>
    ${n.note?`<p class="mt-4 text-xs bg-emerald-500/15 border border-emerald-400/25 text-emerald-200 rounded-xl px-3 py-2.5">⭐ ${n.note}</p>`:''}
    <button onclick="openLead('', '${n.name} package')" class="mt-4 w-full bg-white/10 hover:bg-emerald-500 hover:text-[#0A1628] border border-white/15 font-bold py-2.5 rounded-xl text-sm transition">Get ${n.name} Package →</button>
  </div>`;
}
function renderNiches(filter=''){
  const grid = document.getElementById('nicheGrid');
  const f = filter.toLowerCase().trim();
  const list = NICHES.filter(n => !f || (n.name+' '+n.tags+' '+n.prospects.join(' ')+' '+n.opps.join(' ')).toLowerCase().includes(f));
  grid.innerHTML = list.map(nicheCard).join('') + (list.length?`<div class="niche-card bg-gradient-to-br from-emerald-600 to-emerald-800 border border-emerald-400/30 rounded-3xl p-6 flex flex-col justify-center">
    <h3 class="font-display font-bold text-xl">Don't See Your Industry?</h3>
    <p class="text-sm text-emerald-50/90 mt-2">Tell us what you sell and we'll build a prospecting package around your market.</p>
    <button onclick="openLead('', 'Custom niche')" class="mt-4 bg-amber-400 hover:bg-amber-300 text-[#0A1628] font-bold py-2.5 rounded-xl text-sm transition">Tell Us Your Niche →</button></div>`:'<p class="text-slate-300 col-span-full text-center py-8">No industries match — <button class="underline text-emerald-400" onclick="openLead()">tell us your niche</button> and we\'ll build around it.</p>');
}
renderNiches();
document.getElementById('nicheSearch').addEventListener('input', e=>renderNiches(e.target.value));
// populate niche select
const sel = document.getElementById('leadNiche');
NICHES.forEach(n=>{ const o=document.createElement('option'); o.textContent=n.name; sel.appendChild(o); });
const other=document.createElement('option'); other.textContent='Other / Not listed'; sel.appendChild(other);
// modal
const modal=document.getElementById('leadModal');
function openLead(pkg='', msg=''){
  modal.classList.remove('hidden'); modal.classList.add('flex'); document.body.style.overflow='hidden';
  if(pkg){ const map={START:'START — UGX 250,000',GROW:'GROW — UGX 350,000',BUSINESS:'BUSINESS — UGX 650,000'}; document.getElementById('leadPackage').value = map[pkg]||pkg; }
  if(msg){ document.getElementById('leadMsg').value = 'I am interested in: '+msg; }
  document.getElementById('leadForm').classList.remove('hidden'); document.getElementById('leadSuccess').classList.add('hidden');
}
function closeLead(){ modal.classList.add('hidden'); modal.classList.remove('flex'); document.body.style.overflow=''; }
document.getElementById('leadForm').addEventListener('submit', e=>{ e.preventDefault(); document.getElementById('leadForm').classList.add('hidden'); document.getElementById('leadSuccess').classList.remove('hidden'); });
document.addEventListener('keydown', e=>{ if(e.key==='Escape') closeLead(); });
// mobile menu
const menuBtn=document.getElementById('menuBtn'), mobileMenu=document.getElementById('mobileMenu'), mO=document.getElementById('menuOpen'), mC=document.getElementById('menuClose');
menuBtn.addEventListener('click', ()=>{ const open=mobileMenu.classList.toggle('hidden'); mO.classList.toggle('hidden',!open); mC.classList.toggle('hidden',open); });
mobileMenu.querySelectorAll('a').forEach(a=>a.addEventListener('click',()=>{ mobileMenu.classList.add('hidden'); mO.classList.remove('hidden'); mC.classList.add('hidden'); }));
// reveal on scroll
const io=new IntersectionObserver(es=>es.forEach(e=>{ if(e.isIntersecting){ e.target.classList.add('visible'); io.unobserve(e.target);} }),{threshold:.12});
document.querySelectorAll('.reveal').forEach(el=>io.observe(el));
// nav shadow
window.addEventListener('scroll',()=>{ document.getElementById('nav').style.boxShadow = window.scrollY>10 ? '0 8px 30px -12px rgba(10,22,40,.15)' : 'none'; });
</script>
</body>
</html>
