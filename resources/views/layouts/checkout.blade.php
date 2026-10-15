<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <script>(function(){try{var k='flux.appearance',t=localStorage.getItem(k);if(t!=='light'&&t!=='dark'){t=localStorage.getItem('ttryy-theme');if(t!=='light'&&t!=='dark'){t=window.matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light';}localStorage.setItem(k,t);}document.documentElement.classList.toggle('dark',t==='dark');}catch(e){}})();</script>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-[#f4f4f5] font-sans text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100">
        <header class="sticky top-0 z-20 border-b border-zinc-200/90 bg-[#f4f4f5]/85 backdrop-blur-md dark:border-white/10 dark:bg-zinc-950/85">
            <div class="mx-auto flex h-[68px] w-[92%] items-center gap-2 md:w-[80%]">
                <a href="{{ url('/') }}" class="flex min-w-0 items-center gap-2.5" title="ttryy home">
                    <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-brand-600 text-white shadow-[0_8px_16px_-8px_rgba(158,0,93,.8)]">
                        <x-app-logo-icon class="size-5 fill-current" />
                    </span>
                    <span class="font-display block truncate text-[17px] font-extrabold tracking-tight">ttryy</span>
                </a>
                <div class="ml-auto flex shrink-0 items-center gap-1.5">
                    <button onclick="toggleTheme()" class="grid size-10 place-items-center rounded-full hover:bg-zinc-950/5 dark:hover:bg-white/10" aria-label="Toggle dark mode">
                        <svg class="theme-icon-moon size-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20 13.5A8 8 0 0110.5 4 8 8 0 1020 13.5z"/></svg>
                        <svg class="theme-icon-sun size-5 hidden" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
                    </button>
                    @auth
                    <a href="{{ route('dashboard') }}" class="btn-ghost btn-sm">Dashboard</a>
                    @else
                    <a href="{{ route('login') }}" class="btn-dark btn-sm">Sign in</a>
                    @endauth
                </div>
            </div>
        </header>

        <div class="mx-auto w-[92%] pt-6 pb-16 md:w-[80%]">
            <main class="block">
                <div class="w-full">{{ $slot }}</div>
            </main>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        <script>
        function paintThemeIcons(){var d=document.documentElement.classList.contains('dark');document.querySelectorAll('.theme-icon-moon').forEach(function(e){e.classList.toggle('hidden',d);});document.querySelectorAll('.theme-icon-sun').forEach(function(e){e.classList.toggle('hidden',!d);});}
        function toggleTheme(){var h=document.documentElement,next=h.classList.contains('dark')?'light':'dark';h.classList.toggle('dark',next==='dark');try{localStorage.setItem('flux.appearance',next);localStorage.setItem('ttryy-theme',next);}catch(e){}paintThemeIcons();}
        paintThemeIcons();
        </script>
        @fluxScripts
    </body>
</html>
