<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <script>(function(){try{var k='flux.appearance',t=localStorage.getItem(k);if(t!=='light'&&t!=='dark'){t=localStorage.getItem('ttryy-theme');if(t!=='light'&&t!=='dark'){t=window.matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light';}localStorage.setItem(k,t);}document.documentElement.classList.toggle('dark',t==='dark');}catch(e){}})();</script>
        <script>(function(){try{if(localStorage.getItem('ttryy-nav')==='mini')document.documentElement.dataset.nav='mini';}catch(e){}})();</script>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-[#f4f4f5] font-sans text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100">
        @php
        $navSections = [
            'Growth' => [
                ['label' => 'Prospect scraper', 'icon' => 'magnifying-glass', 'href' => route('scraper.index'), 'active' => request()->routeIs('scraper.*')],
                ['label' => 'Prospects', 'icon' => 'building-office-2', 'href' => route('prospects.index'), 'active' => request()->routeIs('prospects.*')],
                ['label' => 'Saved contacts', 'icon' => 'bookmark', 'href' => route('contacts.index'), 'active' => request()->routeIs('contacts.*')],
                ['label' => 'Marketing tool', 'icon' => 'megaphone', 'href' => route('marketing.index'), 'active' => request()->routeIs('marketing.*')],
                ['label' => 'Analytics', 'icon' => 'presentation-chart-line', 'href' => route('analytics.index'), 'active' => request()->routeIs('analytics.*')],
            ],
            'Overview' => [
                ['label' => 'Dashboard', 'icon' => 'home', 'href' => route('dashboard'), 'active' => request()->routeIs('dashboard')],
            ],
            'Business' => [
                ['label' => 'New order', 'icon' => 'plus', 'href' => route('orders.new'), 'active' => request()->routeIs('orders.new') || request()->routeIs('checkout*')],
                ['label' => 'My packages', 'icon' => 'shopping-bag', 'href' => route('packages.index'), 'active' => request()->routeIs('packages.*')],
                ['label' => 'Payments', 'icon' => 'banknotes', 'href' => route('payments.index'), 'active' => request()->routeIs('payments.*')],
                ['label' => 'Wallet', 'icon' => 'wallet', 'href' => route('wallet.index'), 'active' => request()->routeIs('wallet.*')],
            ],
        ];
        if(auth()->user()?->is_admin){
            $navSections['Administration'] = [
                ['label' => 'Overview', 'icon' => 'chart-bar', 'href' => route('admin.dashboard'), 'active' => request()->routeIs('admin.dashboard')],
                ['label' => 'Orders', 'icon' => 'shopping-bag', 'href' => route('admin.orders'), 'active' => request()->routeIs('admin.orders*')],
                ['label' => 'Payments', 'icon' => 'banknotes', 'href' => route('admin.payments'), 'active' => request()->routeIs('admin.payments')],
                ['label' => 'Users', 'icon' => 'users', 'href' => route('admin.users'), 'active' => request()->routeIs('admin.users*')],
                ['label' => 'Packages', 'icon' => 'squares-2x2', 'href' => route('admin.packages'), 'active' => request()->routeIs('admin.packages')],
            ];
        }
        $initials = collect(explode(' ', trim(auth()->user()?->name ?? '?')))->map(fn($p) => mb_substr($p, 0, 1))->take(2)->implode('');
        @endphp

        <div id="nav-overlay" class="fixed inset-0 z-30 hidden bg-zinc-950/50 opacity-0 lg:hidden" onclick="closeNav()"></div>

        <aside id="dash-sidebar" class="border-e border-zinc-200/90 bg-white dark:border-white/10 dark:bg-zinc-900">
            <div class="flex h-full flex-col">
                <div class="flex h-[68px] shrink-0 items-center gap-2.5 px-5">
                    <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-2.5" title="Ttryy dashboard">
                        <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-brand-600 text-white shadow-[0_8px_16px_-8px_rgba(158,0,93,.8)]">
                            <x-app-logo-icon class="size-5 fill-current" />
                        </span>
                        <span id="nav-brand-text" class="min-w-0">
                            <span class="font-display block truncate text-[17px] font-extrabold tracking-tight">Ttryy</span>
                            <span class="block text-[11px] font-semibold text-zinc-400">Business growth</span>
                        </span>
                    </a>
                    <button onclick="closeNav()" class="ml-auto grid size-9 shrink-0 place-items-center rounded-full text-zinc-400 hover:bg-zinc-950/5 hover:text-zinc-700 lg:hidden dark:hover:bg-white/10 dark:hover:text-white" aria-label="Close menu">
                        <flux:icon name="x-mark" class="size-5" />
                    </button>
                </div>

                <nav class="dash-scroll min-h-0 flex-1 overflow-y-auto px-3 pb-4" aria-label="Dashboard">
                    @foreach($navSections as $section => $links)
                    <p class="nav-section px-3.5 pt-5 pb-1.5 text-[11px] font-bold tracking-[0.14em] text-zinc-400 uppercase dark:text-zinc-500">{{ $section }}</p>
                    <div class="flex flex-col gap-0.5">
                        @foreach($links as $link)
                        <a href="{{ $link['href'] }}" title="{{ $link['label'] }}" class="nav-link {{ $link['active'] ? 'nav-link-active' : '' }}">
                            <flux:icon name="{{ $link['icon'] }}" class="size-5 shrink-0" />
                            <span class="nav-label truncate">{{ $link['label'] }}</span>
                            @if($link['active'])<span class="nav-caret ml-auto size-1.5 shrink-0 rounded-full bg-current"></span>@endif
                        </a>
                        @endforeach
                    </div>
                    @endforeach
                </nav>

                <div class="shrink-0 border-t border-zinc-200/90 p-3 dark:border-white/10">
                    @auth
                    <p id="nav-foot-note" class="px-3.5 pt-1 pb-2 text-[11px] leading-relaxed text-zinc-400">Signed in as<br><strong class="text-zinc-600 dark:text-zinc-300">{{ auth()->user()->email }}</strong></p>
                    @endauth
                    <button id="nav-collapse" onclick="toggleNav()" class="nav-link hidden lg:flex" aria-label="Collapse sidebar">
                        <svg class="size-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M15 6l-6 6 6 6"/></svg>
                        <span class="nav-label">Collapse</span>
                    </button>
                </div>
            </div>
        </aside>

        <div id="dash-main">
            <header class="sticky top-0 z-20 border-b border-zinc-200/90 bg-[#f4f4f5]/85 backdrop-blur-md dark:border-white/10 dark:bg-zinc-950/85">
                <div class="mx-auto flex h-[68px] w-full max-w-6xl items-center gap-2 px-4 sm:px-6">
                    <button onclick="openNav()" class="grid size-10 shrink-0 place-items-center rounded-full hover:bg-zinc-950/5 lg:hidden dark:hover:bg-white/10" aria-label="Open menu">
                        <flux:icon name="bars-3" class="size-5" />
                    </button>
                    <p class="font-display min-w-0 truncate text-[17px] font-bold tracking-tight">{{ $title ?? config('app.name', 'Ttryy') }}</p>
                    <div class="ml-auto flex shrink-0 items-center gap-1.5">
                        <a href="{{ url('/') }}" class="hidden items-center gap-1.5 rounded-full px-3.5 py-2 text-[13px] font-bold text-zinc-500 transition hover:bg-zinc-950/5 hover:text-zinc-900 sm:inline-flex dark:text-zinc-400 dark:hover:bg-white/10 dark:hover:text-white">
                            <flux:icon name="globe-alt" class="size-4" />
                            View site
                        </a>
                        @auth
                        <div class="relative" id="bell-chip">
                            <button onclick="toggleBell(event)" class="relative grid size-10 place-items-center rounded-full transition hover:bg-zinc-950/5 dark:hover:bg-white/10" aria-label="Notifications">
                                <flux:icon name="bell" class="size-5" />
                                @if(($navUnreadCount ?? 0) > 0)
                                <span class="absolute top-1 right-1 grid min-w-4.5 h-4.5 place-items-center rounded-full bg-brand-600 px-1 text-[10px] font-extrabold text-white">{{ $navUnreadCount > 9 ? '9+' : $navUnreadCount }}</span>
                                @endif
                            </button>
                            <div id="bell-menu" class="card absolute top-full right-0 z-50 mt-2 hidden w-80 overflow-hidden p-1.5">
                                <div class="flex items-center justify-between px-3 py-2">
                                    <p class="text-sm font-extrabold">Notifications</p>
                                    @if(($navUnreadCount ?? 0) > 0)
                                    <form method="POST" action="{{ route('notifications.read-all') }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-xs font-bold text-brand-700 hover:underline dark:text-brand-300">Mark all read</button>
                                    </form>
                                    @endif
                                </div>
                                <div class="max-h-80 overflow-y-auto">
                                    @forelse($navNotifications ?? [] as $n)
                                    <a href="{{ route('notifications.open', $n->id) }}" class="flex gap-2.5 rounded-lg px-3 py-2.5 transition hover:bg-zinc-950/5 dark:hover:bg-white/10 {{ $n->read_at ? 'opacity-60' : '' }}">
                                        <span class="mt-1.5 size-2 shrink-0 rounded-full {{ ($n->data['kind'] ?? '') === 'payment' ? 'bg-emerald-500' : ((($n->data['kind'] ?? '') === 'tokens') ? 'bg-amber-500' : ((($n->data['kind'] ?? '') === 'email') ? 'bg-sky-500' : 'bg-brand-600')) }}"></span>
                                        <span class="min-w-0">
                                            <span class="block truncate text-[13px] font-bold">{{ $n->data['title'] ?? 'Notification' }}</span>
                                            @if(!empty($n->data['body']))<span class="block truncate text-xs text-zinc-500">{{ $n->data['body'] }}</span>@endif
                                            <span class="block text-[11px] text-zinc-400">{{ $n->created_at->diffForHumans() }}</span>
                                        </span>
                                    </a>
                                    @empty
                                    <p class="px-3 py-6 text-center text-[13px] text-zinc-500">No notifications yet — orders, payments and campaigns will show up here.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                        @endauth
                        <button onclick="toggleTheme()" class="grid size-10 place-items-center rounded-full hover:bg-zinc-950/5 dark:hover:bg-white/10" aria-label="Toggle dark mode">
                            <svg class="theme-icon-moon size-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20 13.5A8 8 0 0110.5 4 8 8 0 1020 13.5z"/></svg>
                            <svg class="theme-icon-sun size-5 hidden" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
                        </button>
                        <div class="relative" id="user-chip">
                            @auth
                            <button onclick="toggleUserMenu(event)" class="flex items-center gap-2 rounded-full py-1 pr-1 pl-1 transition hover:bg-zinc-950/5 dark:hover:bg-white/10" aria-label="Account menu" aria-haspopup="true">
                                <span class="grid size-9 place-items-center rounded-full bg-brand-600 text-[13px] font-extrabold text-white">{{ $initials }}</span>
                                <flux:icon name="chevron-down" class="size-4 text-zinc-400" />
                            </button>
                            <div id="user-menu" class="card absolute top-full right-0 z-50 mt-2 hidden w-64 overflow-hidden p-1.5">
                                <div class="px-3.5 py-3">
                                    <p class="truncate text-sm font-bold">{{ auth()->user()->name }}</p>
                                    <p class="truncate text-xs text-zinc-500">{{ auth()->user()->email }}</p>
                                </div>
                                <div class="border-t border-zinc-100 dark:border-white/10"></div>
                                <a href="{{ route('profile.edit') }}" class="nav-link mt-1.5"><flux:icon name="user" class="size-5 shrink-0" /><span class="nav-label">Profile settings</span></a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="nav-link w-full text-left text-red-600 hover:bg-red-500/10 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"><flux:icon name="arrow-right-start-on-rectangle" class="size-5 shrink-0" /><span class="nav-label">Log out</span></button>
                                </form>
                            </div>
                            @else
                            <a href="{{ route('login') }}" class="btn-dark btn-sm">Sign in</a>
                            @endauth
                        </div>
                    </div>
                </div>
            </header>

            <div class="mx-auto w-full max-w-6xl px-4 pt-6 pb-16 sm:px-6">
                {{ $slot }}
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        <script>
        function openNav(){
            document.getElementById('dash-sidebar').classList.add('open');
            const o = document.getElementById('nav-overlay');
            o.classList.remove('hidden');
            requestAnimationFrame(() => o.classList.remove('opacity-0'));
        }
        function closeNav(){
            document.getElementById('dash-sidebar').classList.remove('open');
            const o = document.getElementById('nav-overlay');
            o.classList.add('opacity-0');
            setTimeout(() => o.classList.add('hidden'), 250);
        }
        function toggleNav(){
            const html = document.documentElement;
            const mini = html.dataset.nav !== 'mini';
            html.dataset.nav = mini ? 'mini' : '';
            try{ localStorage.setItem('ttryy-nav', mini ? 'mini' : 'full'); }catch(e){}
        }
        function toggleUserMenu(e){
            if(e) e.stopPropagation();
            document.getElementById('user-menu').classList.toggle('hidden');
        }
        function toggleBell(e){
            if(e) e.stopPropagation();
            document.getElementById('bell-menu')?.classList.toggle('hidden');
        }
        document.addEventListener('click', e => {
            const chip = document.getElementById('user-chip');
            if(chip && !chip.contains(e.target)) document.getElementById('user-menu')?.classList.add('hidden');
            const bell = document.getElementById('bell-chip');
            if(bell && !bell.contains(e.target)) document.getElementById('bell-menu')?.classList.add('hidden');
        });
        document.addEventListener('keydown', e => {
            if(e.key === 'Escape'){ document.getElementById('user-menu')?.classList.add('hidden'); document.getElementById('bell-menu')?.classList.add('hidden'); closeNav(); }
        });
        function paintThemeIcons(){var d=document.documentElement.classList.contains('dark');document.querySelectorAll('.theme-icon-moon').forEach(function(e){e.classList.toggle('hidden',d);});document.querySelectorAll('.theme-icon-sun').forEach(function(e){e.classList.toggle('hidden',!d);});}
        function toggleTheme(){var h=document.documentElement,next=h.classList.contains('dark')?'light':'dark';h.classList.toggle('dark',next==='dark');try{localStorage.setItem('flux.appearance',next);localStorage.setItem('ttryy-theme',next);}catch(e){}paintThemeIcons();}
        paintThemeIcons();
        </script>
        @fluxScripts
    </body>
</html>
