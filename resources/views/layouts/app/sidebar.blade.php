<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <script>(function(){try{var k='flux.appearance',t=localStorage.getItem(k);if(t!=='light'&&t!=='dark'){t=localStorage.getItem('ttryy-theme');if(t!=='light'&&t!=='dark'){t=window.matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light';}localStorage.setItem(k,t);}document.documentElement.classList.toggle('dark',t==='dark');}catch(e){}})();</script>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Platform')" class="grid">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="shopping-bag" :href="route('packages.index')" :current="request()->routeIs('packages.*')" wire:navigate>
                        {{ __('My packages') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="magnifying-glass" :href="route('scraper.index')" :current="request()->routeIs('scraper.*')" wire:navigate>
                        {{ __('Prospect scraper') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="plus" :href="route('checkout')" :current="request()->routeIs('checkout*')" wire:navigate>
                        {{ __('New order') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
                @if(auth()->user()?->is_admin)
                <flux:sidebar.group :heading="__('Administration')" class="grid">
                    <flux:sidebar.item icon="chart-bar" :href="route('admin.dashboard')" :current="request()->routeIs('admin.dashboard')" wire:navigate>
                        {{ __('Overview') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="shopping-bag" :href="route('admin.orders')" :current="request()->routeIs('admin.orders*')" wire:navigate>
                        {{ __('Orders') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="banknotes" :href="route('admin.payments')" :current="request()->routeIs('admin.payments')" wire:navigate>
                        {{ __('Payments') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="users" :href="route('admin.users')" :current="request()->routeIs('admin.users*')" wire:navigate>
                        {{ __('Users') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="squares-2x2" :href="route('admin.packages')" :current="request()->routeIs('admin.packages')" wire:navigate>
                        {{ __('Packages') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
                @endif
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav>
                <flux:sidebar.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank">
                    {{ __('Repository') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire" target="_blank">
                    {{ __('Documentation') }}
                </flux:sidebar.item>
            </flux:sidebar.nav>

            <div class="px-2 pb-2">
                <button onclick="toggleTheme()" class="flex w-full items-center justify-center rounded-lg px-3 py-2 text-zinc-600 hover:bg-zinc-800/5 dark:text-zinc-300 dark:hover:bg-white/10" aria-label="Toggle dark mode">
                    <svg class="theme-icon-moon size-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20 13.5A8 8 0 0110.5 4 8 8 0 1020 13.5z"/></svg>
                    <svg class="theme-icon-sun size-5 hidden" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
                </button>
            </div>
            @auth
            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
            @else
            <flux:button :href="route('login')" variant="ghost" class="hidden lg:block">Sign in</flux:button>
            @endauth
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <button onclick="toggleTheme()" class="grid size-10 shrink-0 place-items-center rounded-lg text-zinc-600 hover:bg-zinc-800/5 dark:text-zinc-300 dark:hover:bg-white/10 lg:hidden" aria-label="Toggle dark mode">
                <svg class="theme-icon-moon size-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20 13.5A8 8 0 0110.5 4 8 8 0 1020 13.5z"/></svg>
                <svg class="theme-icon-sun size-5 hidden" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
            </button>
            @auth
            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
            @else
            <flux:button :href="route('login')" variant="primary">Sign in</flux:button>
            @endauth
        </flux:header>

        {{ $slot }}

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
