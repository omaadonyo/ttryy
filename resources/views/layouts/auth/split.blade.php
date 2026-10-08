<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <script>(function(){try{var k='flux.appearance',t=localStorage.getItem(k);if(t!=='light'&&t!=='dark'){t=localStorage.getItem('ttryy-theme');if(t!=='light'&&t!=='dark'){t=window.matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light';}localStorage.setItem(k,t);}document.documentElement.classList.toggle('dark',t==='dark');}catch(e){}})();</script>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
        <div class="relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0">
            <div class="bg-muted relative hidden h-full flex-col p-10 text-white lg:flex dark:border-e dark:border-neutral-800">
                <div class="absolute inset-0 bg-zinc-950"></div>
                <a href="{{ route('home') }}" class="relative z-20 flex items-center text-lg font-medium" wire:navigate>
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-[#9e005d] text-white font-black text-xl mr-2">T</span>
                    Ttryy
                </a>

                <div class="relative z-20 mt-auto">
                    <p class="font-display font-extrabold text-3xl leading-tight">We build your website. We find the customers. You close them.</p>
                    <ul class="mt-5 space-y-2 text-sm text-zinc-300">
                        <li class="flex gap-2"><span class="font-bold">01 —</span> Professional website, no WordPress</li>
                        <li class="flex gap-2"><span class="font-bold">02 —</span> 5,000 niche prospects + opportunities</li>
                        <li class="flex gap-2"><span class="font-bold">03 —</span> From UGX 700/day</li>
                    </ul>
                </div>
            </div>
            <div class="w-full lg:p-8">
                <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px]">
                    <a href="{{ route('home') }}" class="z-20 flex flex-col items-center gap-2 font-medium lg:hidden" wire:navigate>
                        <span class="flex h-9 w-9 items-center justify-center rounded-md">
                            <x-app-logo-icon class="size-9 fill-current text-black dark:text-white" />
                        </span>

                        <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
                    </a>
                    {{ $slot }}
                </div>
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
