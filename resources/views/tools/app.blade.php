<x-layouts::tools :title="__('Business Tools')">
@php
$icons = [
    'grid' => '<path d="M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM13 13h7v7h-7z"/>',
    'cart' => '<circle cx="9" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/><path d="M2 3h3l2.4 12.2a2 2 0 002 1.8h8.4a2 2 0 002-1.6L21 7H6"/>',
    'quote' => '<path d="M7 4h7l4 4v12a1 1 0 01-1 1H7a1 1 0 01-1-1V5a1 1 0 011-1z"/><path d="M14 4v5h4M9 13h6M9 17h4"/>',
    'invoice' => '<path d="M7 4h7l4 4v12a1 1 0 01-1 1H7a1 1 0 01-1-1V5a1 1 0 011-1z"/><path d="M14 4v5h4M9 13l2 2 4-4"/>',
    'receipt' => '<path d="M6 3h12v18l-3-2-3 2-3-2-3 2z"/><path d="M9 8h6M9 12h6"/>',
    'cube' => '<path d="M12 3l8 4.5v9L12 21l-8-4.5v-9z"/><path d="M12 12l8-4.5M12 12v9M12 12L4 7.5"/>',
    'users' => '<circle cx="9" cy="8" r="3.2"/><path d="M3 20a6 6 0 0112 0M16 5.5a3 3 0 010 5.6M21 20a5 5 0 00-4-4.9"/>',
    'truck' => '<path d="M3 6h11v9H3zM14 9h4l3 3v3h-7"/><circle cx="7" cy="18" r="1.6"/><circle cx="17" cy="18" r="1.6"/>',
    'cash' => '<rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2.4"/><path d="M6 12h.01M18 12h.01"/>',
    'chart' => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
    'flow' => '<path d="M7 7h11l-3-3M17 17H6l3 3"/>',
    'scale' => '<path d="M12 4v16M6 8h12M6 8l-3 6a3 3 0 006 0zM18 8l3 6a3 3 0 01-6 0z"/>',
    'cog' => '<circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3M5 5l2 2M17 17l2 2M19 5l-2 2M7 17l-2 2"/>',
    'bell' => '<path d="M18 8a6 6 0 10-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 01-3.4 0"/>',
    'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/>',
    'send' => '<path d="M22 2L11 13M22 2l-7 20-4-9-9-4z"/>',
    'shield' => '<path d="M12 3l8 3v6c0 5-3.4 8-8 9-4.6-1-8-4-8-9V6z"/><path d="M9 12l2 2 4-4"/>',
    'wallet' => '<path d="M3 7a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><path d="M16 12h.01"/>',
];
$nav = [
    'overview' => ['Overview', 'grid'],
    'pos' => ['POS', 'cart'],
    'quotations' => ['Quotations', 'quote'],
    'salesorders' => ['Sales Orders', 'invoice'],
    'deliverynotes' => ['Delivery Notes', 'truck'],
    'invoices' => ['Invoicing', 'invoice'],
    'receipts' => ['Receipts', 'receipt'],
    'creditnotes' => ['Credit Notes', 'receipt'],
    'purchasequotes' => ['Purchase Quotes', 'quote'],
    'purchaseorders' => ['Purchase Orders', 'cart'],
    'purchaseinvoices' => ['Purchase Invoices', 'invoice'],
    'purchasepayments' => ['Purchase Payments', 'cash'],
    'inventory' => ['Inventory', 'cube'],
    'customers' => ['Customers', 'users'],
    'suppliers' => ['Suppliers', 'truck'],
    'expenses' => ['Expenses', 'cash'],
    'accounts' => ['Chart of Accounts', 'scale'],
    'bank' => ['Bank &amp; Cash', 'wallet'],
    'employees' => ['Employees', 'users'],
    'payslips' => ['Payslips', 'cash'],
    'journal' => ['Journal', 'clock'],
    'reminders' => ['Reminders', 'send'],
    'reports' => ['Reports', 'chart'],
    'cashflow' => ['Cash Flow', 'flow'],
    'pnl' => ['P&amp;L', 'chart'],
    'balancesheet' => ['Balance Sheet', 'scale'],
    'staff' => ['Team', 'shield'],
    'activity' => ['Activity', 'clock'],
    'notifications' => ['Notifications', 'bell'],
    'settings' => ['Settings', 'cog'],
];
@endphp
<style>
    [x-cloak]{ display:none !important; }
    .bt-scroll{ scrollbar-width:none; -ms-overflow-style:none; }
    .bt-scroll::-webkit-scrollbar{ display:none; }
    .bt-panel{ display:none; }
    .bt-panel.is-active{ display:block; }
    .bt-fade-in{ animation:btFade .22s ease; }
    @keyframes btFade{ from{ opacity:0; transform:translateY(6px);} to{ opacity:1; transform:none;} }
    .bt-paper{ background:#fff; color:#18181b; }
    /* Slim custom scrollbars scoped to the app */
    [data-bt-app] *{ scrollbar-width:thin; scrollbar-color:rgba(113,113,122,.38) transparent; }
    [data-bt-app] *::-webkit-scrollbar{ width:8px; height:8px; }
    [data-bt-app] *::-webkit-scrollbar-track{ background:transparent; }
    [data-bt-app] *::-webkit-scrollbar-thumb{ background:rgba(113,113,122,.35); border-radius:999px; border:2px solid transparent; background-clip:content-box; }
    [data-bt-app] *::-webkit-scrollbar-thumb:hover{ background:rgba(113,113,122,.6); background-clip:content-box; }
    .dark [data-bt-app] *::-webkit-scrollbar-thumb{ background:rgba(161,161,170,.35); background-clip:content-box; }
    .dark [data-bt-app] *::-webkit-scrollbar-thumb:hover{ background:rgba(161,161,170,.6); background-clip:content-box; }
    [data-bt-app] .bt-scroll::-webkit-scrollbar{ display:none; }
    /* Privacy mode — blur every money figure for shoulder-surfing-proof screenshots */
    [data-bt-app].bt-obscure [x-text*="money("]{ filter:blur(7px); transition:filter .15s ease; }
    [data-bt-app].bt-obscure [x-text*="(stats.cash)"],
    [data-bt-app].bt-obscure .bt-cash{ filter:blur(7px); }
</style>

<div x-data="btTools(@js($boot))" x-init="init()" data-bt-app :class="obscure && 'bt-obscure'" class="min-h-screen">
    <div x-show="busy" x-cloak class="fixed inset-x-0 top-0 z-50 h-0.5 overflow-hidden bg-brand-100 dark:bg-brand-900">
        <div class="h-full w-1/3 animate-[btSlide_1s_ease-in-out_infinite] bg-brand-600" style="animation:btSlide 1s ease-in-out infinite"></div>
    </div>

    {{-- ============================== HEADER ============================== --}}
    <header class="sticky top-0 z-30 w-full border-b border-zinc-200/90 bg-[#f4f4f5]/90 backdrop-blur-md dark:border-white/10 dark:bg-zinc-950/90">
        <div class="flex h-[64px] w-full items-center gap-3 px-4 lg:px-6">
            <a href="{{ route('dashboard') }}" class="btn-ghost btn-sm gap-1.5" title="Back to dashboard">
                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M15 6l-6 6 6 6"/></svg>
                <span class="hidden sm:inline">Dashboard</span>
            </a>
            <div class="relative min-w-0">
                <button type="button" @click="businessOpen = !businessOpen" class="flex min-w-0 items-center gap-2.5 rounded-xl px-1.5 py-1 text-left transition hover:bg-zinc-950/5 dark:hover:bg-white/10">
                    <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 text-white shadow-[0_10px_20px_-10px_rgba(158,0,93,.9)]">
                        <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M9 7h6M9 12h6M9 17h3M5 3h14a1 1 0 011 1v16a1 1 0 01-1 1H5a1 1 0 01-1-1V4a1 1 0 011-1z"/></svg>
                    </span>
                    <div class="min-w-0">
                        <p class="font-display truncate text-[15px] font-extrabold leading-none tracking-tight">Business Tools</p>
                        <p class="truncate text-[11px] text-zinc-500 dark:text-zinc-400" x-text="settings.business_name || 'Set your business profile'"></p>
                    </div>
                    <svg class="size-4 shrink-0 text-zinc-400 transition" :class="businessOpen && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
                </button>
                <div x-show="businessOpen" x-cloak @click.outside="businessOpen = false" x-transition.opacity
                    class="absolute left-0 z-40 mt-2 w-72 overflow-hidden rounded-2xl border border-zinc-200 bg-white p-1.5 shadow-2xl dark:border-white/10 dark:bg-zinc-900">
                    <p class="px-3 py-2 text-[10px] font-bold uppercase tracking-[0.16em] text-zinc-400">Your businesses</p>
                    <template x-for="b in businesses" :key="'biz'+b.id">
                        <button type="button" @click="switchBusiness(b)"
                            class="flex w-full items-center justify-between gap-2 rounded-xl px-3 py-2.5 text-left text-sm hover:bg-zinc-100 dark:hover:bg-white/10">
                            <span class="min-w-0">
                                <span class="block truncate font-bold" x-text="b.name"></span>
                                <span class="block text-[11px] capitalize text-zinc-400" x-text="b.role"></span>
                            </span>
                            <svg x-show="b.active" class="size-4 shrink-0 text-brand-600 dark:text-brand-300" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                        </button>
                    </template>
                    <button type="button" @click="businessOpen = false; openBusinessForm()"
                        class="mt-1 flex w-full items-center gap-2 rounded-xl border border-dashed border-zinc-300 px-3 py-2.5 text-sm font-bold text-brand-600 hover:bg-brand-50 dark:border-white/15 dark:text-brand-300 dark:hover:bg-white/5">
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                        New business
                    </button>
                </div>
            </div>

            <div class="relative ml-auto hidden md:block">
                <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-zinc-400" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                <input x-model="search" type="search" placeholder="Search documents, contacts, items…" class="field w-64 pl-9 lg:w-96" />
                <div x-show="quickResults.length" x-cloak class="absolute right-0 mt-2 w-96 overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl dark:border-white/10 dark:bg-zinc-900">
                    <template x-for="r in quickResults" :key="r.action + r.id">
                        <button type="button" @click="runQuick(r)" class="flex w-full items-center justify-between gap-3 px-4 py-2.5 text-left hover:bg-zinc-50 dark:hover:bg-white/5">
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-semibold" x-text="r.label"></span>
                                <span class="block truncate text-xs text-zinc-500" x-text="r.sub"></span>
                            </span>
                            <span class="shrink-0 text-[10px] font-bold uppercase tracking-wider text-zinc-400" x-text="r.action === 'doc' ? 'Open' : 'Jump'"></span>
                        </button>
                    </template>
                </div>
            </div>

            <div class="ml-auto flex items-center gap-1.5 md:ml-2">
                <button type="button" @click="openComposer('quotation')" class="btn-ghost btn-sm hidden lg:inline-flex">Quotation</button>
                <button type="button" @click="openComposer('invoice')" class="btn-brand btn-sm gap-1.5">
                    <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                    <span class="hidden sm:inline">New</span>
                </button>
                <div class="relative">
                    <button type="button" @click="notifOpen = !notifOpen; if (notifOpen) loadNotifications()" class="relative grid size-9 place-items-center rounded-full hover:bg-zinc-950/5 dark:hover:bg-white/10" aria-label="Notifications">
                        <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M18 8a6 6 0 10-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 01-3.4 0"/></svg>
                        <span x-show="notifications.unread > 0" x-cloak x-text="notifications.unread > 9 ? '9+' : notifications.unread"
                            class="absolute right-0.5 top-0.5 grid min-w-[16px] place-items-center rounded-full bg-brand-600 px-1 text-[9px] font-bold leading-4 text-white"></span>
                    </button>
                    <div x-show="notifOpen" x-cloak @click.outside="notifOpen = false" x-transition.opacity
                        class="absolute right-0 z-40 mt-2 w-80 overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl dark:border-white/10 dark:bg-zinc-900">
                        <div class="flex items-center justify-between border-b border-zinc-100 px-4 py-3 dark:border-white/10">
                            <p class="text-sm font-extrabold">Notifications</p>
                            <div class="flex items-center gap-3">
                                <button type="button" @click="markAllRead()" class="text-xs font-bold text-zinc-500 hover:text-brand-600">Mark all read</button>
                                <button type="button" @click="go('notifications'); notifOpen = false" class="text-xs font-bold text-brand-600 dark:text-brand-300">View all</button>
                            </div>
                        </div>
                        <div class="max-h-80 overflow-y-auto">
                            <template x-for="n in bellNotifications" :key="'nt'+n.id">
                                <div class="flex gap-3 border-b border-zinc-100 px-4 py-3 last:border-0 dark:border-white/5" :class="!n.read && 'bg-brand-50/60 dark:bg-brand-500/5'">
                                    <span class="mt-0.5 size-2 shrink-0 rounded-full" :class="notificationTone(n.kind)"></span>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-bold" x-text="n.title"></p>
                                        <p class="text-xs text-zinc-500" x-text="n.body"></p>
                                        <p class="mt-0.5 text-[10px] uppercase tracking-wider text-zinc-400" x-text="n.created_at"></p>
                                    </div>
                                </div>
                            </template>
                            <p x-show="!bellNotifications.length" class="px-4 py-10 text-center text-sm text-zinc-500">You're all caught up.</p>
                        </div>
                    </div>
                </div>
                <button type="button" @click="openUpgrade()" :title="planTitle()"
                    class="hidden items-center gap-2 rounded-full border bg-white px-3 py-1.5 text-left transition hover:border-brand-300 sm:flex dark:bg-zinc-900"
                    :class="plan.premium ? 'border-emerald-300 dark:border-emerald-500/40' : 'border-amber-300 dark:border-amber-500/40'">
                    <span class="grid size-5 shrink-0 place-items-center rounded-full"
                        :class="plan.premium ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300' : 'bg-amber-100 text-amber-600 dark:bg-amber-500/15 dark:text-amber-300'">
                        <svg x-show="plan.premium" class="size-3.5" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                        <svg x-show="!plan.premium" class="size-3.5" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V7a4 4 0 018 0v4"/></svg>
                    </span>
                    <span class="leading-tight">
                        <span class="block text-[11px] font-extrabold" x-text="plan.premium ? 'Premium' : 'Free plan'"></span>
                        <span class="block text-[10px] text-zinc-500" x-text="planSummaryLine()"></span>
                    </span>
                    <span class="h-1.5 w-14 shrink-0 overflow-hidden rounded-full bg-zinc-200 dark:bg-white/10">
                        <span class="block h-full rounded-full transition-all" :class="planTone()" :style="'width:' + planPercent() + '%'"></span>
                    </span>
                </button>
                <div x-show="boot.user.is_owner" class="flex items-center gap-1" role="group" aria-label="Display currency">
                    <div class="flex items-center rounded-full bg-zinc-950/5 p-0.5 text-[11px] font-bold dark:bg-white/10">
                        <button type="button" @click="setCurrency('UGX')" :class="displayCurrency === 'UGX' ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-700 dark:text-white' : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-white'" class="rounded-full px-2.5 py-1 transition">UGX</button>
                        <button type="button" @click="setCurrency('USD')" :class="displayCurrency === 'USD' ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-700 dark:text-white' : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-white'" class="rounded-full px-2.5 py-1 transition">USD</button>
                    </div>
                    <button type="button" @click="refreshFx()" :title="rateHint()" class="grid size-7 place-items-center rounded-full text-zinc-400 hover:bg-zinc-950/5 hover:text-zinc-700 dark:hover:bg-white/10 dark:hover:text-white" aria-label="Refresh exchange rates">
                        <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 12a9 9 0 11-3-6.7"/><path d="M21 3v6h-6"/></svg>
                    </button>
                </div>
                <button type="button" @click="toggleObscure()" :class="obscure && 'bg-brand-600 text-white hover:bg-brand-600'" class="grid size-9 place-items-center rounded-full hover:bg-zinc-950/5 dark:hover:bg-white/10" :title="obscure ? 'Show figures' : 'Hide figures'" aria-label="Toggle privacy mode">
                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/><path x-show="obscure" d="M4 4l16 16"/></svg>
                </button>
                <button type="button" onclick="toggleTheme()" class="grid size-9 place-items-center rounded-full hover:bg-zinc-950/5 dark:hover:bg-white/10" aria-label="Toggle dark mode">
                    <svg class="theme-icon-moon size-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20 13.5A8 8 0 0110.5 4 8 8 0 1020 13.5z"/></svg>
                    <svg class="theme-icon-sun size-5 hidden" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
                </button>
            </div>
        </div>
    </header>

    <div class="flex w-full">
        {{-- ============================== DESKTOP VERTICAL NAV ============================== --}}
        <aside :class="sidebarCollapsed ? 'w-[72px] px-2' : 'w-60 px-3'" class="sticky top-[64px] hidden h-[calc(100vh-64px)] shrink-0 flex-col gap-0.5 overflow-y-auto overflow-x-hidden border-e border-zinc-200/90 bg-white/60 py-4 transition-[width] duration-200 lg:flex dark:border-white/10 dark:bg-zinc-900/40">
            <div class="flex items-center justify-between px-1 pb-2">
                <p x-show="!sidebarCollapsed" class="px-2 text-[10px] font-bold uppercase tracking-[0.16em] text-zinc-400">Workspace</p>
                <button type="button" @click="toggleSidebar()" class="grid size-7 shrink-0 place-items-center rounded-lg text-zinc-400 transition hover:bg-zinc-950/5 hover:text-zinc-700 dark:hover:bg-white/10" :title="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'">
                    <svg class="size-4 transition-transform" :class="sidebarCollapsed && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M15 6l-6 6 6 6"/></svg>
                </button>
            </div>
            @foreach($nav as $key => [$label, $icon])
                <button type="button" data-bt-tab="{{ $key }}" @click="go('{{ $key }}')" title="{{ strip_tags($label) }}"
                    :class="[tab === '{{ $key }}' ? 'bg-zinc-950 text-white shadow-sm dark:bg-white dark:text-zinc-950' : 'text-zinc-600 hover:bg-zinc-950/5 dark:text-zinc-300 dark:hover:bg-white/5', sidebarCollapsed ? 'justify-center px-0' : '']"
                    class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-bold transition">
                    <svg class="size-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">{!! $icons[$icon] !!}</svg>
                    <span x-show="!sidebarCollapsed" class="flex-1 truncate">{!! $label !!}</span>
                    <svg x-show="!sidebarCollapsed && isLocked('{{ $key }}')" x-cloak class="size-3.5 shrink-0 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-label="Premium"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V7a4 4 0 018 0v4"/></svg>
                    <span x-show="!sidebarCollapsed && badgeCount('{{ $key }}') > 0" x-cloak x-text="badgeCount('{{ $key }}')"
                        :class="tab === '{{ $key }}' ? 'bg-white/20 text-white dark:bg-zinc-900/20 dark:text-zinc-900' : 'bg-brand-600 text-white'"
                        class="grid min-w-[20px] place-items-center rounded-full px-1.5 text-[10px] font-bold leading-5"></span>
                </button>
            @endforeach
            <div x-show="!sidebarCollapsed" class="mt-auto rounded-xl border border-zinc-200 p-3 text-xs text-zinc-500 dark:border-white/10">
                <p class="font-bold text-zinc-700 dark:text-zinc-200">Cash on hand</p>
                <p class="font-display mt-1 text-lg font-extrabold text-zinc-900 dark:text-white" x-text="money(stats.cash)"></p>
            </div>
        </aside>

        {{-- ============================== MOBILE CAROUSEL NAV ============================== --}}
        <div class="min-w-0 flex-1">
            <nav class="sticky top-[64px] z-20 border-b border-zinc-200/90 bg-[#f4f4f5]/95 backdrop-blur-md lg:hidden dark:border-white/10 dark:bg-zinc-950/95">
                <div class="relative flex items-center gap-2 px-3 py-2">
                    <button type="button" @click="scrollNav(-1)" class="grid size-8 shrink-0 place-items-center rounded-full border border-zinc-200 bg-white shadow dark:border-white/15 dark:bg-zinc-900" aria-label="Scroll left">
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M15 6l-6 6 6 6"/></svg>
                    </button>
                    <div x-ref="navtrack" class="bt-scroll flex flex-1 items-center gap-1.5 overflow-x-auto scroll-smooth">
                        @foreach($nav as $key => [$label, $icon])
                            <button type="button" data-bt-tab="{{ $key }}" @click="go('{{ $key }}')"
                                :class="tab === '{{ $key }}' ? 'bg-zinc-950 text-white dark:bg-white dark:text-zinc-950' : 'bg-white text-zinc-600 dark:bg-zinc-900 dark:text-zinc-300'"
                                class="flex shrink-0 items-center gap-2 rounded-full border border-zinc-200 px-3.5 py-1.5 text-xs font-bold whitespace-nowrap dark:border-white/10">
                                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">{!! $icons[$icon] !!}</svg>
                                {!! $label !!}
                                <svg x-show="isLocked('{{ $key }}')" x-cloak class="size-3 shrink-0 text-amber-500" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V7a4 4 0 018 0v4"/></svg>
                                <span x-show="badgeCount('{{ $key }}') > 0" x-cloak x-text="badgeCount('{{ $key }}')"
                                    class="grid min-w-[18px] place-items-center rounded-full bg-brand-600 px-1 text-[10px] font-bold leading-5 text-white"></span>
                            </button>
                        @endforeach
                    </div>
                    <button type="button" @click="scrollNav(1)" class="grid size-8 shrink-0 place-items-center rounded-full border border-zinc-200 bg-white shadow dark:border-white/15 dark:bg-zinc-900" aria-label="Scroll right">
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M9 6l6 6-6 6"/></svg>
                    </button>
                </div>
            </nav>

            <main class="w-full px-4 py-6 lg:px-8">

                {{-- ============================== OVERVIEW ============================== --}}
                <section class="bt-panel" :class="tab === 'overview' && 'is-active bt-fade-in'">
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <p class="eyebrow text-brand-600 dark:text-brand-300">Live snapshot</p>
                            <h1 class="page-title mt-1">Good to see you, <span x-text="(boot.user.name || '').split(' ')[0]"></span></h1>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="go('pos')" class="btn-dark btn-sm">Open POS</button>
                            <button type="button" @click="openComposer('invoice')" class="btn-brand btn-sm">New invoice</button>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        <template x-for="c in [
                            {k:'collected_month', t:'Collected this month', tone:'from-emerald-500 to-emerald-600', sub:'Receipts issued'},
                            {k:'receivable', t:'Owed to you', tone:'from-amber-500 to-amber-600', sub:'Unpaid invoices'},
                            {k:'expenses_month', t:'Spent this month', tone:'from-rose-500 to-rose-600', sub:'Paid expenses'},
                            {k:'cash', t:'Cash position', tone:'from-brand-500 to-brand-700', sub:'In minus out'},
                        ]" :key="'s'+c.k">
                            <div class="card card-pad relative overflow-hidden">
                                <span class="absolute -right-6 -top-6 size-24 rounded-full bg-gradient-to-br opacity-10" :class="c.tone"></span>
                                <p class="text-[11px] font-bold uppercase tracking-wider text-zinc-500" x-text="c.t"></p>
                                <p class="font-display mt-2 text-3xl font-extrabold tracking-tight" x-text="money(stats[c.k])"></p>
                                <p class="mt-1 text-xs text-zinc-400" x-text="c.sub"></p>
                            </div>
                        </template>
                    </div>

                    <div class="mt-4 grid gap-4 xl:grid-cols-3">
                        <div class="card card-pad xl:col-span-2">
                            <div class="flex items-center justify-between">
                                <h2 class="section-title">Money in / out</h2>
                                <div class="flex items-center gap-3 text-[11px] font-semibold">
                                    <span class="inline-flex items-center gap-1.5"><span class="size-2.5 rounded-full bg-emerald-500"></span>In</span>
                                    <span class="inline-flex items-center gap-1.5"><span class="size-2.5 rounded-full bg-brand-600"></span>Out</span>
                                </div>
                            </div>
                            <div class="mt-6 flex h-56 items-end gap-3">
                                <template x-for="m in months" :key="'ov'+m.label+m.year">
                                    <div class="flex flex-1 flex-col items-center gap-2">
                                        <div class="flex h-48 w-full items-end justify-center gap-1.5">
                                            <div class="w-1/3 rounded-t-md bg-gradient-to-t from-emerald-500 to-emerald-400" :style="'height:' + barH(m.in) + '%'" :title="money(m.in)"></div>
                                            <div class="w-1/3 rounded-t-md bg-gradient-to-t from-brand-600 to-brand-400" :style="'height:' + barH(m.out) + '%'" :title="money(m.out)"></div>
                                        </div>
                                        <span class="text-[11px] font-bold text-zinc-500" x-text="m.label"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="flex flex-col gap-4">
                            <div class="card card-pad">
                                <h2 class="section-title">Needs attention</h2>
                                <div class="mt-4 flex flex-col gap-2.5">
                                    <button type="button" @click="go('invoices')" class="flex items-center justify-between rounded-xl border border-zinc-200 px-3.5 py-3 text-left transition hover:border-brand-300 dark:border-white/10">
                                        <span class="text-sm font-semibold">Overdue invoices</span><span class="pill-red" x-text="stats.overdue"></span>
                                    </button>
                                    <button type="button" @click="go('inventory')" class="flex items-center justify-between rounded-xl border border-zinc-200 px-3.5 py-3 text-left transition hover:border-brand-300 dark:border-white/10">
                                        <span class="text-sm font-semibold">Low stock items</span><span class="pill-amber" x-text="stats.low_stock"></span>
                                    </button>
                                    <button type="button" @click="go('quotations')" class="flex items-center justify-between rounded-xl border border-zinc-200 px-3.5 py-3 text-left transition hover:border-brand-300 dark:border-white/10">
                                        <span class="text-sm font-semibold">Open quotations</span><span class="pill-zinc" x-text="stats.pending_quotes"></span>
                                    </button>
                                </div>
                            </div>
                            <div class="card card-pad">
                                <h2 class="section-title">Inventory value</h2>
                                <p class="font-display mt-3 text-2xl font-extrabold" x-text="money(stats.inventory_value)"></p>
                                <p class="mt-1 text-xs text-zinc-500"><span x-text="stats.documents"></span> documents · <span x-text="stats.customers"></span> customers</p>
                            </div>
                        </div>
                    </div>

                    <div class="card card-pad mt-4">
                        <div class="flex items-center justify-between">
                            <h2 class="section-title">Recent activity</h2>
                            <button type="button" @click="go('invoices')" class="text-xs font-bold text-brand-600 dark:text-brand-300">View all →</button>
                        </div>
                        <div class="mt-3 divide-y divide-zinc-100 dark:divide-white/5">
                            <template x-for="d in documents.slice(0, 8)" :key="'ra'+d.id">
                                <button type="button" @click="openDrawer(d.id)" class="flex w-full items-center justify-between gap-3 py-2.5 text-left">
                                    <span class="flex min-w-0 items-center gap-3">
                                        <span class="pill-zinc shrink-0 capitalize" x-text="d.type.slice(0, 3)"></span>
                                        <span class="min-w-0">
                                            <span class="block truncate text-sm font-semibold" x-text="d.number"></span>
                                            <span class="block truncate text-xs text-zinc-500" x-text="(d.contact_name || 'No contact') + ' · ' + d.issue_date"></span>
                                        </span>
                                    </span>
                                    <span class="shrink-0 text-sm font-bold" x-text="money(d.total)"></span>
                                </button>
                            </template>
                            <p x-show="!documents.length" class="py-8 text-center text-sm text-zinc-500">No documents yet.</p>
                        </div>
                    </div>
                </section>

                {{-- ============================== POS ============================== --}}
                <section class="bt-panel" :class="tab === 'pos' && 'is-active bt-fade-in'">
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <p class="eyebrow text-brand-600 dark:text-brand-300">Point of sale</p>
                            <h1 class="page-title mt-1">Terminal</h1>
                        </div>
                        <div class="flex items-center gap-2">
                            <label class="inline-flex items-center gap-2 text-sm font-semibold"><input type="checkbox" x-model="posInStock" class="rounded" /> In stock only</label>
                            <input x-model="posQuery" type="search" placeholder="Scan or search products…" class="field w-64" />
                        </div>
                    </div>

                    <div class="mt-5 grid gap-4 xl:grid-cols-[1.7fr_1fr]">
                        <div class="card card-pad">
                            <div class="grid max-h-[68vh] grid-cols-2 gap-3 overflow-y-auto sm:grid-cols-3 2xl:grid-cols-4">
                                <template x-for="it in posResults()" :key="'p'+it.id">
                                    <button type="button" @click="addToCart(it)" :disabled="it.stock <= 0"
                                        class="group relative overflow-hidden rounded-2xl border border-zinc-200 p-3 text-left transition hover:-translate-y-0.5 hover:border-brand-400 hover:shadow-lg disabled:cursor-not-allowed disabled:opacity-40 dark:border-white/10">
                                        <span class="grid size-11 place-items-center rounded-xl bg-gradient-to-br from-zinc-100 to-zinc-200 font-display text-base font-extrabold text-zinc-500 dark:from-zinc-800 dark:to-zinc-700 dark:text-zinc-300" x-text="(it.name || '?').slice(0,2).toUpperCase()"></span>
                                        <span class="mt-2 block text-[10px] font-bold uppercase tracking-wider text-zinc-400" x-text="it.sku || it.unit"></span>
                                        <span class="mt-0.5 line-clamp-2 block text-sm font-bold leading-tight" x-text="it.name"></span>
                                        <span class="mt-2 flex items-center justify-between">
                                            <span class="text-sm font-extrabold text-brand-600 dark:text-brand-300" x-text="money(it.sale_price)"></span>
                                            <span class="rounded-full px-2 py-0.5 text-[10px] font-bold" :class="it.stock <= 0 ? 'bg-red-100 text-red-700' : (it.low ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700')" x-text="it.stock + ' ' + it.unit"></span>
                                        </span>
                                    </button>
                                </template>
                                <p x-show="!posResults().length" class="col-span-full py-16 text-center text-sm text-zinc-500">No products match.</p>
                            </div>
                        </div>

                        <div class="card card-pad flex flex-col xl:sticky xl:top-[80px] xl:max-h-[80vh]">
                            <div class="flex items-center justify-between">
                                <h2 class="section-title">Current sale</h2>
                                <button type="button" @click="clearCart()" class="text-xs font-bold text-zinc-500 hover:text-red-600">Clear</button>
                            </div>

                            <div class="mt-3 flex-1 overflow-y-auto">
                                <template x-for="l in cart" :key="'c'+l.item_id">
                                    <div class="flex items-center gap-2 border-b border-zinc-100 py-2.5 dark:border-white/5">
                                        <div class="min-w-0 flex-1"><p class="truncate text-sm font-semibold" x-text="l.name"></p><p class="text-xs text-zinc-500" x-text="money(l.price)"></p></div>
                                        <div class="flex items-center gap-1.5">
                                            <button type="button" @click="decLine(l)" class="grid size-7 place-items-center rounded-lg border border-zinc-200 font-bold dark:border-white/15">−</button>
                                            <span class="w-7 text-center text-sm font-bold" x-text="l.qty"></span>
                                            <button type="button" @click="incLine(l)" class="grid size-7 place-items-center rounded-lg border border-zinc-200 font-bold dark:border-white/15">+</button>
                                        </div>
                                        <span class="w-24 text-right text-sm font-bold" x-text="money(l.qty * l.price)"></span>
                                    </div>
                                </template>
                                <p x-show="!cart.length" class="py-12 text-center text-sm text-zinc-500">Tap a product to start a sale.</p>
                            </div>

                            <div class="mt-4 border-t border-zinc-200 pt-4 dark:border-white/10">
                                <label class="text-[11px] font-bold uppercase tracking-wider text-zinc-500">Customer (optional)</label>
                                <x-tools.picker class="mt-1" x-model="posCustomer" options="contacts.filter(c => c.kind === 'customer')" track="id" display="o => o.name" placeholder="Walk-in customer" />

                                <div class="mt-3 grid grid-cols-4 gap-2">
                                    <template x-for="pm in ['cash','momo','card','bank']" :key="'pm'+pm">
                                        <button type="button" @click="posPay = pm"
                                            :class="posPay === pm ? 'border-brand-500 bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-200' : 'border-zinc-200 dark:border-white/15'"
                                            class="rounded-lg border px-2 py-2 text-[11px] font-bold uppercase tracking-wide" x-text="pm"></button>
                                    </template>
                                </div>

                                <div class="mt-4 flex items-center justify-between">
                                    <span class="font-semibold text-zinc-500">Total</span>
                                    <span class="font-display text-2xl font-extrabold" x-text="money(cartTotal)"></span>
                                </div>
                                <div class="mt-2 flex items-center justify-between gap-3">
                                    <label class="text-sm font-semibold text-zinc-500">Cash received</label>
                                    <input x-model.number="posTendered" type="number" min="0" class="field w-32 text-right" />
                                </div>
                                <div class="mt-1 flex items-center justify-between text-sm" x-show="posTendered > 0">
                                    <span class="font-semibold text-zinc-500">Change</span><span class="font-bold text-emerald-600" x-text="money(cartChange)"></span>
                                </div>
                                <button type="button" @click="checkout()" :disabled="saving || !cart.length" class="btn-brand mt-4 w-full justify-center">
                                    <svg x-show="saving" class="size-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" stroke-opacity=".25"/><path d="M21 12a9 9 0 00-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                                    Charge <span x-text="money(cartTotal)"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- ==================== DOCUMENTS (quotations / invoices / receipts) ==================== --}}
                <section class="bt-panel" :class="rollingTab && 'is-active bt-fade-in'">
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <p class="eyebrow text-brand-600 dark:text-brand-300" x-text="tabDocMeta.eyebrow"></p>
                            <h1 class="page-title mt-1" x-text="tabDocMeta.label"></h1>
                            <p class="mt-1 text-sm text-zinc-500" x-text="filterDocs(tabDocType).length + ' record(s)'"></p>
                        </div>
                        <div class="flex items-center gap-2">
                            <input x-model="search" type="search" placeholder="Search…" class="field w-48" />
                            <x-tools.picker class="w-40" x-model="docStatus" options="docStatuses(tabDocType).map(s => ({ id: s, name: s === 'all' ? 'All statuses' : ucfirst(s) }))" track="id" display="o => o.name" placeholder="All statuses" />
                            <button type="button" @click="openComposer(tabDocType)" class="btn-brand btn-sm">New <span x-text="tabDocMeta.label.replace(/s$/, '')"></span></button>
                        </div>
                    </div>

                    <div class="card mt-4 overflow-hidden">
                        <table class="tbl w-full" x-show="filterDocs(tabDocType).length">
                            <thead>
                                <tr>
                                    <th>Number</th><th>Contact</th><th>Date</th><th>Status</th>
                                    <th class="min-w-[8rem] text-right">Total</th><th class="min-w-[7rem] text-right">Balance</th><th class="min-w-[7rem] whitespace-nowrap">Settled</th><th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="d in filterDocs(tabDocType)" :key="'d'+d.id">
                                    <tr class="hover:bg-zinc-50 dark:hover:bg-white/5">
                                        <td class="font-bold whitespace-nowrap">
                                            <button type="button" @click="openDrawer(d.id)" class="hover:text-brand-600" x-text="d.number"></button>
                                        </td>
                                        <td x-text="d.contact_name || '—'"></td>
                                        <td class="whitespace-nowrap" x-text="d.issue_date"></td>
                                        <td>
                                            <span class="pill-zinc capitalize" x-text="d.status"></span>
                                            <span x-show="d.overdue" class="pill-red ml-1">overdue</span>
                                        </td>
                                        <td class="min-w-[8rem] text-right font-semibold whitespace-nowrap" x-text="money(d.total)"></td>
                                        <td class="min-w-[7rem] text-right whitespace-nowrap" x-text="d.is_bill ? money(d.balance) : '—'"></td>
                                        <td class="min-w-[7rem] whitespace-nowrap text-xs text-zinc-500" x-text="settledHint(d)"></td>
                                        <td class="text-right whitespace-nowrap">
                                            <div class="inline-flex items-center gap-3">
                                                <div x-show="convertOptions(d.type).length" x-data="{ open: false }" class="relative inline-block text-left">
                                                    <button type="button" @click="open = !open" class="text-xs font-bold text-emerald-600 hover:text-emerald-700">Convert ▾</button>
                                                    <div x-show="open" x-cloak @click.outside="open = false" class="absolute right-0 z-30 mt-1 w-44 overflow-hidden rounded-xl border border-zinc-200 bg-white py-1 shadow-xl dark:border-white/10 dark:bg-zinc-900">
                                                        <template x-for="t in convertOptions(d.type)" :key="'cv'+d.id+t">
                                                            <button type="button" @click="open = false; convertTo(d, t)" class="block w-full px-3 py-2 text-left text-xs font-semibold hover:bg-zinc-50 dark:hover:bg-white/5" x-text="'Convert to ' + typeLabel(t)"></button>
                                                        </template>
                                                    </div>
                                                </div>
                                                <button type="button" x-show="canClone(d.type)" @click="cloneDoc(d)" class="text-xs font-bold text-zinc-500 hover:text-brand-600">Duplicate</button>
                                                <button type="button" @click="openPreview({ id: d.id })" class="text-xs font-bold text-zinc-500 hover:text-brand-600">Preview</button>
                                                <button type="button" @click="openDrawer(d.id)" class="text-xs font-bold text-brand-600 dark:text-brand-300">Open</button>
                                                <button type="button" @click="deleteDoc(d)" class="text-xs font-bold text-red-600 hover:text-red-700">Delete</button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                        <x-tools.empty x-show="!filterDocs(tabDocType).length" x-cloak
                            icon="<path d='M7 4h7l4 4v12a1 1 0 01-1 1H7a1 1 0 01-1-1V5a1 1 0 011-1z'/><path d='M14 4v5h4M9 13h6'/>"
                            title="No documents yet"
                            description="Create your first document — it saves as a draft as you type."
                            action="openComposer(tabDocType)"
                            action-label="Create document" />
                    </div>
                </section>

                {{-- ============================== INVENTORY ============================== --}}
                <section class="bt-panel" :class="tab === 'inventory' && 'is-active bt-fade-in'">
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <p class="eyebrow text-brand-600 dark:text-brand-300">Stock control</p>
                            <h1 class="page-title mt-1">Inventory</h1>
                            <p class="mt-1 text-sm text-zinc-500" x-text="items.length + ' product(s) tracked · ' + money(stats.inventory_value) + ' of stock on hand'"></p>
                        </div>
                        <div class="flex items-center gap-2">
                            <input x-model="search" type="search" placeholder="Search items…" class="field w-48" />
                            <label class="inline-flex items-center gap-2 text-sm font-semibold"><input type="checkbox" x-model="itemLowOnly" class="rounded" /> Low only</label>
                            <button type="button" @click="openItem(null)" class="btn-brand btn-sm">Add item</button>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        <div class="card card-pad relative overflow-hidden">
                            <span class="absolute -right-6 -top-6 size-24 rounded-full bg-gradient-to-br from-brand-500 to-brand-700 opacity-10"></span>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-zinc-500">Products tracked</p>
                            <p class="font-display mt-2 text-3xl font-extrabold tracking-tight" x-text="items.length"></p>
                        </div>
                        <div class="card card-pad relative overflow-hidden">
                            <span class="absolute -right-6 -top-6 size-24 rounded-full bg-gradient-to-br from-emerald-500 to-emerald-600 opacity-10"></span>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-zinc-500">Stock value (cost)</p>
                            <p class="font-display mt-2 text-3xl font-extrabold tracking-tight" x-text="money(stats.inventory_value)"></p>
                        </div>
                        <div class="card card-pad relative overflow-hidden">
                            <span class="absolute -right-6 -top-6 size-24 rounded-full bg-gradient-to-br from-amber-500 to-amber-600 opacity-10"></span>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-zinc-500">Low stock</p>
                            <p class="font-display mt-2 text-3xl font-extrabold tracking-tight text-amber-600" x-text="lowStockCount"></p>
                        </div>
                        <div class="card card-pad relative overflow-hidden">
                            <span class="absolute -right-6 -top-6 size-24 rounded-full bg-gradient-to-br from-rose-500 to-rose-600 opacity-10"></span>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-zinc-500">Out of stock</p>
                            <p class="font-display mt-2 text-3xl font-extrabold tracking-tight text-rose-600" x-text="outOfStockCount"></p>
                        </div>
                    </div>

                    <div class="card mt-4 overflow-hidden">
                        <div class="flex items-center justify-between border-b border-zinc-100 px-4 py-3 dark:border-white/10">
                            <h2 class="text-sm font-bold">Stock ledger</h2>
                            <span class="text-xs text-zinc-500" x-text="filterItems().length + ' shown'"></span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="tbl w-full" x-show="filterItems().length">
                                <thead><tr>
                                    <th>Item</th><th>SKU</th>
                                    <th class="text-right">Cost</th><th class="text-right">Sell</th><th class="min-w-[5rem] text-right">Margin</th>
                                    <th class="min-w-[6rem] text-center">On hand</th><th class="min-w-[5rem] text-center">Reorder</th>
                                    <th class="min-w-[7rem] text-right">Value</th><th class="text-right">Actions</th>
                                </tr></thead>
                                <tbody>
                                    <template x-for="it in filterItems()" :key="'i'+it.id">
                                        <tr class="hover:bg-zinc-50 dark:hover:bg-white/5">
                                            <td>
                                                <div class="flex items-center gap-2.5">
                                                    <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-gradient-to-br from-zinc-100 to-zinc-200 font-display text-xs font-extrabold text-zinc-500 dark:from-zinc-800 dark:to-zinc-700 dark:text-zinc-300" x-text="(it.name || '?').slice(0,2).toUpperCase()"></span>
                                                    <span class="min-w-0">
                                                        <span class="block truncate font-semibold" x-text="it.name"></span>
                                                        <span class="block text-xs text-zinc-400" x-text="it.unit"></span>
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="text-zinc-500" x-text="it.sku || '—'"></td>
                                            <td class="text-right whitespace-nowrap" x-text="money(it.cost_price)"></td>
                                            <td class="text-right font-semibold whitespace-nowrap" x-text="money(it.sale_price)"></td>
                                            <td class="text-right whitespace-nowrap" :class="margin(it) >= 0 ? 'text-emerald-600' : 'text-red-600'" x-text="margin(it) + '%'"></td>
                                            <td class="text-center whitespace-nowrap"><span :class="stockPill(it)" x-text="it.stock + ' ' + it.unit"></span></td>
                                            <td class="text-center text-zinc-500" x-text="it.reorder_level"></td>
                                            <td class="text-right whitespace-nowrap" x-text="money(it.stock_value)"></td>
                                            <td class="text-right whitespace-nowrap">
                                                <button type="button" @click="openAdjust(it, 'add')" class="text-xs font-bold text-emerald-600 hover:text-emerald-700">Restock</button>
                                                <button type="button" @click="openAdjust(it, 'set')" class="ml-3 text-xs font-bold text-zinc-500 hover:text-brand-600">Adjust</button>
                                                <button type="button" @click="openHistory(it)" class="ml-3 text-xs font-bold text-zinc-500 hover:text-brand-600">History</button>
                                                <button type="button" @click="openItem(it)" class="ml-3 text-xs font-bold text-brand-600 dark:text-brand-300">Edit</button>
                                                <button type="button" @click="deleteItem(it)" class="ml-3 text-xs font-bold text-red-600">Delete</button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                            <x-tools.empty x-show="!filterItems().length" x-cloak
                                icon="<path d='M12 3l8 4.5v9L12 21l-8-4.5v-9z'/><path d='M12 12l8-4.5M12 12v9M12 12L4 7.5'/>"
                                title="No items yet"
                                description="Add products or services to track stock, set prices and sell from the POS."
                                action="openItem(null)"
                                action-label="Add item" />
                        </div>
                    </div>
                </section>

                {{-- ============================== CONTACTS ============================== --}}
                <section class="bt-panel" :class="(tab === 'customers' || tab === 'suppliers') && 'is-active bt-fade-in'">
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <p class="eyebrow text-brand-600 dark:text-brand-300">Directory</p>
                            <h1 class="page-title mt-1" x-text="tab === 'customers' ? 'Customers' : 'Suppliers'"></h1>
                            <p class="mt-1 text-sm text-zinc-500" x-text="filterContacts(tab === 'customers' ? 'customer' : 'supplier').length + ' contact(s)'"></p>
                        </div>
                        <div class="flex items-center gap-2">
                            <input x-model="search" type="search" placeholder="Search…" class="field w-48" />
                            <button type="button" @click="openContact(tab === 'customers' ? 'customer' : 'supplier', null)" class="btn-brand btn-sm" x-text="'Add ' + (tab === 'customers' ? 'customer' : 'supplier')"></button>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                        <template x-for="c in filterContacts(tab === 'customers' ? 'customer' : 'supplier')" :key="'ct'+c.id">
                            <div class="card card-pad">
                                <div class="flex items-start gap-3">
                                    <span class="grid size-11 shrink-0 place-items-center rounded-full bg-gradient-to-br from-brand-500 to-brand-700 font-display text-sm font-extrabold text-white" x-text="(c.name || '?').slice(0,2).toUpperCase()"></span>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-bold" x-text="c.name"></p>
                                        <p class="mt-0.5 truncate text-xs text-zinc-500" x-text="c.phone || c.email || 'No contact details'"></p>
                                    </div>
                                </div>
                                <div class="mt-3 flex items-center justify-between">
                                    <span x-show="c.kind === 'customer' && c.balance_owed > 0" class="pill-amber">Owed <span x-text="money(c.balance_owed)"></span></span>
                                    <span x-show="!(c.kind === 'customer' && c.balance_owed > 0)" class="text-xs text-zinc-400">—</span>
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="openContact(c.kind, c)" class="text-xs font-bold text-brand-600 dark:text-brand-300">Edit</button>
                                        <button type="button" @click="deleteContact(c)" class="text-xs font-bold text-red-600">Delete</button>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <div x-show="!filterContacts(tab === 'customers' ? 'customer' : 'supplier').length" x-cloak class="col-span-full">
                            <x-tools.empty
                                icon="<circle cx='9' cy='8' r='3.2'/><path d='M3 20a6 6 0 0112 0M16 5.5a3 3 0 010 5.6M21 20a5 5 0 00-4-4.9'/>"
                                title="No contacts yet"
                                description="Add the people and businesses you buy from and sell to."
                                action="openContact(tab === 'customers' ? 'customer' : 'supplier', null)"
                                action-label="Add contact" />
                        </div>
                    </div>
                </section>

                {{-- ============================== EXPENSES ============================== --}}
                <section class="bt-panel" :class="tab === 'expenses' && 'is-active bt-fade-in'">
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <p class="eyebrow text-brand-600 dark:text-brand-300">Outgoings</p>
                            <h1 class="page-title mt-1">Expenses</h1>
                            <p class="mt-1 text-sm text-zinc-500" x-text="filterExpenses().length + ' record(s)'"></p>
                        </div>
                        <div class="flex items-center gap-2">
                            <input x-model="search" type="search" placeholder="Search…" class="field w-48" />
                            <x-tools.picker class="w-32" x-model="expStatus" options="[{ id: 'all', name: 'All' }, { id: 'paid', name: 'Paid' }, { id: 'unpaid', name: 'Unpaid' }]" track="id" display="o => o.name" placeholder="All" />
                            <button type="button" @click="openExpense(null)" class="btn-brand btn-sm">Add expense</button>
                        </div>
                    </div>

                    <div class="card mt-4 overflow-hidden">
                        <table class="tbl w-full" x-show="filterExpenses().length">
                            <thead><tr><th>Date</th><th>Category</th><th>Supplier</th><th>Status</th><th class="min-w-[8rem] text-right">Amount</th><th class="text-right">Actions</th></tr></thead>
                            <tbody>
                                <template x-for="e in filterExpenses()" :key="'e'+e.id">
                                    <tr class="hover:bg-zinc-50 dark:hover:bg-white/5">
                                        <td class="whitespace-nowrap" x-text="e.date"></td>
                                        <td class="font-semibold" x-text="e.category"></td>
                                        <td x-text="e.supplier_name || '—'"></td>
                                        <td><span :class="e.status === 'paid' ? 'pill-green' : 'pill-amber'" class="capitalize" x-text="e.status"></span></td>
                                        <td class="min-w-[8rem] text-right font-semibold whitespace-nowrap" x-text="money(e.amount)"></td>
                                        <td class="text-right whitespace-nowrap">
                                            <button type="button" @click="openExpense(e)" class="text-xs font-bold text-brand-600 dark:text-brand-300">Edit</button>
                                            <button type="button" @click="deleteExpense(e)" class="ml-3 text-xs font-bold text-red-600">Delete</button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                        <x-tools.empty x-show="!filterExpenses().length" x-cloak
                            icon="<rect x='2' y='6' width='20' height='12' rx='2'/><circle cx='12' cy='12' r='2.4'/><path d='M6 12h.01M18 12h.01'/>"
                            title="No expenses recorded"
                            description="Track your spending to see real profit in the reports."
                            action="openExpense(null)"
                            action-label="Add expense" />
                    </div>
                </section>

                {{-- ============================== CHART OF ACCOUNTS ============================== --}}
                <section class="bt-panel" :class="tab === 'accounts' && 'is-active bt-fade-in'">
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <p class="eyebrow text-brand-600 dark:text-brand-300">Accounting</p>
                            <h1 class="page-title mt-1">Chart of accounts</h1>
                            <p class="mt-1 text-sm text-zinc-500">Every sale, purchase and payment posts here automatically.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <input x-model="search" type="search" placeholder="Search accounts…" class="field w-48" />
                            <button type="button" @click="openAccount(null)" class="btn-brand btn-sm">Add account</button>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-3 sm:grid-cols-3">
                        <div class="card card-pad"><p class="text-[11px] font-bold uppercase tracking-wider text-zinc-500">Total debits</p><p class="font-display mt-1 text-xl font-extrabold" x-text="money(accountTotals.debit)"></p></div>
                        <div class="card card-pad"><p class="text-[11px] font-bold uppercase tracking-wider text-zinc-500">Total credits</p><p class="font-display mt-1 text-xl font-extrabold" x-text="money(accountTotals.credit)"></p></div>
                        <div class="card card-pad"><p class="text-[11px] font-bold uppercase tracking-wider text-zinc-500">Trial balance</p><p class="font-display mt-1 text-xl font-extrabold" :class="accountTotals.diff === 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600'" x-text="accountTotals.diff === 0 ? 'Balanced' : money(accountTotals.diff)"></p></div>
                    </div>

                    <div class="card mt-4 overflow-hidden">
                        <table class="tbl w-full" x-show="filterAccounts().length">
                            <thead><tr><th>Code</th><th>Account</th><th>Type</th><th class="min-w-[7rem] text-right">Debit</th><th class="min-w-[7rem] text-right">Credit</th><th class="min-w-[8rem] text-right">Balance</th><th class="text-right">Actions</th></tr></thead>
                            <tbody>
                                <template x-for="a in filterAccounts()" :key="'ac'+a.id">
                                    <tr class="hover:bg-zinc-50 dark:hover:bg-white/5">
                                        <td class="font-mono text-xs text-zinc-500" x-text="a.code"></td>
                                        <td class="font-semibold"><span x-text="a.name"></span><span x-show="a.is_system" class="ml-2 text-[10px] font-bold uppercase tracking-wider text-zinc-400">System</span></td>
                                        <td><span class="pill-zinc capitalize" x-text="a.subtype || a.type"></span></td>
                                        <td class="min-w-[7rem] text-right whitespace-nowrap" x-text="money(a.debit)"></td>
                                        <td class="min-w-[7rem] text-right whitespace-nowrap" x-text="money(a.credit)"></td>
                                        <td class="min-w-[8rem] text-right font-semibold whitespace-nowrap" x-text="money(a.balance)"></td>
                                        <td class="text-right whitespace-nowrap">
                                            <button type="button" @click="openAccount(a)" class="text-xs font-bold text-brand-600 dark:text-brand-300" x-show="!a.is_system">Edit</button>
                                            <button type="button" @click="deleteAccount(a)" class="ml-3 text-xs font-bold text-red-600" x-show="!a.is_system">Delete</button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                        <x-tools.empty x-show="!filterAccounts().length" x-cloak
                            icon="<path d='M4 7h16M4 12h16M4 17h10'/>"
                            title="No accounts yet"
                            description="Your chart of accounts is seeded automatically when you start trading."
                            action="openAccount(null)"
                            action-label="Add account" />
                    </div>
                </section>

                {{-- ============================== BANK & CASH ============================== --}}
                <section class="bt-panel" :class="tab === 'bank' && 'is-active bt-fade-in'">
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <p class="eyebrow text-brand-600 dark:text-brand-300">Treasury</p>
                            <h1 class="page-title mt-1">Bank &amp; cash</h1>
                            <p class="mt-1 text-sm text-zinc-500">Balances, deposits and withdrawals across all your money accounts.</p>
                        </div>
                        <button type="button" @click="openBank(null)" class="btn-brand btn-sm">Add account</button>
                    </div>

                    <div class="mt-4 grid gap-4 lg:grid-cols-3">
                        <template x-for="b in bankAccounts" :key="'bk'+b.id">
                            <div class="card card-pad">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <p class="text-[11px] font-bold uppercase tracking-wider text-zinc-500" x-text="b.type"></p>
                                        <h3 class="font-display mt-0.5 text-base font-extrabold" x-text="b.name"></h3>
                                    </div>
                                    <span class="pill-zinc" x-text="(b.transactions_count || 0) + ' tx'"></span>
                                </div>
                                <p class="font-display mt-3 text-2xl font-extrabold" x-text="money(b.balance)"></p>
                                <p class="mt-0.5 text-xs text-zinc-500" x-text="b.account_number || 'No number on file'"></p>
                                <div class="mt-3 flex items-center gap-2">
                                    <button type="button" @click="openBankTx(b, 'credit')" class="btn-ghost btn-sm">Deposit</button>
                                    <button type="button" @click="openBankTx(b, 'debit')" class="btn-ghost btn-sm">Withdraw</button>
                                    <button type="button" @click="openBank(b)" class="btn-ghost btn-sm">Edit</button>
                                    <button type="button" @click="deleteBank(b)" class="ml-auto text-xs font-bold text-red-600">Delete</button>
                                </div>
                            </div>
                        </template>
                        <x-tools.empty x-show="!bankAccounts.length" x-cloak class="lg:col-span-3"
                            icon="<rect x='3' y='6' width='18' height='12' rx='2'/><circle cx='12' cy='12' r='2'/>"
                            title="No bank or cash accounts"
                            description="Add a cash box, bank account or mobile money wallet to track your treasury."
                            action="openBank(null)"
                            action-label="Add account" />
                    </div>

                    <div class="card mt-4 overflow-hidden">
                        <div class="flex items-center justify-between border-b border-zinc-200 px-4 py-3 dark:border-white/10">
                            <h2 class="section-title">Transactions</h2>
                            <x-tools.picker class="w-48" x-model="bankTxFilter" options="bankAccounts" track="id" display="o => o.name" placeholder="All accounts" />
                        </div>
                        <table class="tbl w-full" x-show="filteredBankTx.length">
                            <thead><tr><th>Date</th><th>Account</th><th>Description</th><th>Reference</th><th class="min-w-[8rem] text-right">Amount</th><th class="text-right">Actions</th></tr></thead>
                            <tbody>
                                <template x-for="t in filteredBankTx" :key="'tx'+t.id">
                                    <tr class="hover:bg-zinc-50 dark:hover:bg-white/5">
                                        <td class="whitespace-nowrap" x-text="t.date"></td>
                                        <td x-text="bankName(t.bank_account_id)"></td>
                                        <td class="font-semibold" x-text="t.description"></td>
                                        <td class="text-zinc-500" x-text="t.reference || '—'"></td>
                                        <td class="min-w-[8rem] text-right font-semibold whitespace-nowrap" :class="t.amount < 0 ? 'text-red-600' : 'text-emerald-600 dark:text-emerald-400'" x-text="money(t.amount)"></td>
                                        <td class="text-right"><button type="button" @click="deleteBankTx(t)" class="text-xs font-bold text-red-600">Delete</button></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                        <p x-show="!filteredBankTx.length" class="py-8 text-center text-sm text-zinc-500">No transactions yet.</p>
                    </div>
                </section>

                {{-- ============================== EMPLOYEES ============================== --}}
                <section class="bt-panel" :class="tab === 'employees' && 'is-active bt-fade-in'">
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <p class="eyebrow text-brand-600 dark:text-brand-300">People</p>
                            <h1 class="page-title mt-1">Employees</h1>
                            <p class="mt-1 text-sm text-zinc-500" x-text="employees.filter(e => e.is_active).length + ' active'"></p>
                        </div>
                        <div class="flex items-center gap-2">
                            <input x-model="search" type="search" placeholder="Search…" class="field w-48" />
                            <button type="button" @click="openEmployee(null)" class="btn-brand btn-sm">Add employee</button>
                        </div>
                    </div>

                    <div class="card mt-4 overflow-hidden">
                        <table class="tbl w-full" x-show="filterEmployees().length">
                            <thead><tr><th>Name</th><th>Position</th><th>Type</th><th class="min-w-[7rem] text-right">Base salary</th><th>Frequency</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
                            <tbody>
                                <template x-for="e in filterEmployees()" :key="'emp'+e.id">
                                    <tr class="hover:bg-zinc-50 dark:hover:bg-white/5">
                                        <td class="font-semibold"><span x-text="e.name"></span><p class="text-xs font-normal text-zinc-500" x-text="e.email || e.phone || ''"></p></td>
                                        <td x-text="e.position || '—'"></td>
                                        <td><span class="pill-zinc capitalize" x-text="e.employment_type"></span></td>
                                        <td class="min-w-[7rem] text-right whitespace-nowrap" x-text="money(e.base_salary)"></td>
                                        <td class="capitalize" x-text="e.pay_frequency"></td>
                                        <td><span :class="e.is_active ? 'pill-green' : 'pill-zinc'" x-text="e.is_active ? 'Active' : 'Inactive'"></span></td>
                                        <td class="text-right whitespace-nowrap">
                                            <button type="button" @click="openPayslipFor(e)" class="text-xs font-bold text-brand-600 dark:text-brand-300">Pay</button>
                                            <button type="button" @click="openEmployee(e)" class="ml-3 text-xs font-bold text-brand-600 dark:text-brand-300">Edit</button>
                                            <button type="button" @click="deleteEmployee(e)" class="ml-3 text-xs font-bold text-red-600">Delete</button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                        <x-tools.empty x-show="!filterEmployees().length" x-cloak
                            icon="<circle cx='12' cy='8' r='3.2'/><path d='M5 20a7 7 0 0114 0'/>"
                            title="No employees yet"
                            description="Add your team to run payroll and issue payslips."
                            action="openEmployee(null)"
                            action-label="Add employee" />
                    </div>
                </section>

                {{-- ============================== PAYSLIPS ============================== --}}
                <section class="bt-panel" :class="tab === 'payslips' && 'is-active bt-fade-in'">
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <p class="eyebrow text-brand-600 dark:text-brand-300">Payroll</p>
                            <h1 class="page-title mt-1">Payslips</h1>
                            <p class="mt-1 text-sm text-zinc-500" x-text="payslips.length + ' slip(s)'"></p>
                        </div>
                        <button type="button" @click="openPayslip(null)" class="btn-brand btn-sm" :disabled="!employees.length">Create payslip</button>
                    </div>

                    <div class="card mt-4 overflow-hidden">
                        <table class="tbl w-full" x-show="payslips.length">
                            <thead><tr><th>Number</th><th>Employee</th><th>Period</th><th class="min-w-[7rem] text-right">Gross</th><th class="min-w-[6rem] text-right">Deductions</th><th class="min-w-[7rem] text-right">Net</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
                            <tbody>
                                <template x-for="p in payslips" :key="'ps'+p.id">
                                    <tr class="hover:bg-zinc-50 dark:hover:bg-white/5">
                                        <td class="font-mono text-xs" x-text="p.number"></td>
                                        <td class="font-semibold" x-text="p.employee_name || '—'"></td>
                                        <td class="whitespace-nowrap text-xs text-zinc-500" x-text="p.period_start + ' → ' + p.period_end"></td>
                                        <td class="min-w-[7rem] text-right whitespace-nowrap" x-text="money(p.gross)"></td>
                                        <td class="min-w-[6rem] text-right whitespace-nowrap" x-text="money(p.deductions)"></td>
                                        <td class="min-w-[7rem] text-right font-semibold whitespace-nowrap" x-text="money(p.net)"></td>
                                        <td><span :class="p.status === 'paid' ? 'pill-green' : 'pill-amber'" class="capitalize" x-text="p.status"></span></td>
                                        <td class="text-right whitespace-nowrap">
                                            <button type="button" @click="openPayslip(p)" class="text-xs font-bold text-brand-600 dark:text-brand-300">Edit</button>
                                            <button type="button" @click="deletePayslip(p)" class="ml-3 text-xs font-bold text-red-600">Delete</button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                        <x-tools.empty x-show="!payslips.length" x-cloak
                            icon="<path d='M6 3h9l5 5v13H6z'/><path d='M14 3v6h6'/>"
                            title="No payslips yet"
                            description="Create a payslip to record salaries and post them to your ledger."
                            action="openPayslip(null)"
                            action-label="Create payslip" />
                    </div>
                </section>

                {{-- ============================== JOURNAL ============================== --}}
                <section class="bt-panel" :class="tab === 'journal' && 'is-active bt-fade-in'">
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <p class="eyebrow text-brand-600 dark:text-brand-300">Double-entry</p>
                            <h1 class="page-title mt-1">Journal</h1>
                            <p class="mt-1 text-sm text-zinc-500" x-text="journal.length + ' entr(ies) — every posting must balance.'"></p>
                        </div>
                        <input x-model="search" type="search" placeholder="Search memo…" class="field w-48" />
                    </div>

                    <div class="mt-4 flex flex-col gap-3">
                        <template x-for="j in filterJournal()" :key="'j'+j.id">
                            <div class="card overflow-hidden">
                                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-zinc-200 px-4 py-3 dark:border-white/10">
                                    <div>
                                        <p class="text-sm font-bold" x-text="j.memo || j.reference || 'Journal entry'"></p>
                                        <p class="text-xs text-zinc-500"><span x-text="j.date"></span> · <span class="capitalize" x-text="(j.source_type || 'manual').replace('_', ' ')"></span></p>
                                    </div>
                                    <span class="font-display font-extrabold" x-text="money(j.total)"></span>
                                </div>
                                <table class="tbl w-full">
                                    <thead><tr><th>Account</th><th class="min-w-[7rem] text-right">Debit</th><th class="min-w-[7rem] text-right">Credit</th></tr></thead>
                                    <tbody>
                                        <template x-for="(l, i) in j.lines" :key="'jl'+j.id+i">
                                            <tr>
                                                <td><span class="font-mono text-xs text-zinc-500" x-text="l.code"></span> <span x-text="l.account"></span></td>
                                                <td class="min-w-[7rem] text-right whitespace-nowrap" x-text="l.debit ? money(l.debit) : ''"></td>
                                                <td class="min-w-[7rem] text-right whitespace-nowrap" x-text="l.credit ? money(l.credit) : ''"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </template>
                        <x-tools.empty x-show="!filterJournal().length" x-cloak
                            icon="<path d='M4 6h16M4 12h16M4 18h16'/>"
                            title="No journal entries yet"
                            description="Invoices, bills, payments and payroll post here automatically."
                            action="go('invoices')"
                            action-label="Create an invoice" />
                    </div>
                </section>

                {{-- ============================== REPORTS ============================== --}}
                <section class="bt-panel" :class="tab === 'reports' && 'is-active bt-fade-in'">
                    <x-tools.premium tab="reports" description="Revenue, profit and cash-flow analytics are part of Business Tools Premium.">
                    @include('tools.panels.report-header', ['title' => 'Reports', 'eyebrow' => 'Analytics'])
                    <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        <div class="card card-pad"><p class="text-[11px] font-bold uppercase tracking-wider text-zinc-500">Revenue</p><p class="font-display mt-2 text-2xl font-extrabold" x-text="money(report.pnl.revenue)"></p></div>
                        <div class="card card-pad"><p class="text-[11px] font-bold uppercase tracking-wider text-zinc-500">Gross profit</p><p class="font-display mt-2 text-2xl font-extrabold" x-text="money(report.pnl.gross)"></p></div>
                        <div class="card card-pad"><p class="text-[11px] font-bold uppercase tracking-wider text-zinc-500">Operating expenses</p><p class="font-display mt-2 text-2xl font-extrabold" x-text="money(report.pnl.expense_total)"></p></div>
                        <div class="card card-pad"><p class="text-[11px] font-bold uppercase tracking-wider text-zinc-500">Net profit</p><p class="font-display mt-2 text-2xl font-extrabold" :class="report.pnl.net >= 0 ? 'text-emerald-600' : 'text-red-600'" x-text="money(report.pnl.net)"></p></div>
                    </div>
                    <div class="mt-4 grid gap-4 xl:grid-cols-2">
                        <div class="card card-pad">
                            <h2 class="section-title">Expenses by category</h2>
                            <div class="mt-4 flex flex-col gap-3">
                                <template x-for="row in report.pnl.by_category" :key="'cat'+row.category">
                                    <div>
                                        <div class="flex items-center justify-between text-sm"><span class="font-semibold" x-text="row.category"></span><span class="font-bold" x-text="money(row.amount)"></span></div>
                                        <div class="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-white/10"><div class="h-full rounded-full bg-gradient-to-r from-brand-500 to-brand-700" :style="'width:' + catWidth(row.amount) + '%'"></div></div>
                                    </div>
                                </template>
                                <p x-show="!report.pnl.by_category.length" class="py-6 text-center text-sm text-zinc-500">No expenses in this period.</p>
                            </div>
                        </div>
                        <div class="card card-pad">
                            <h2 class="section-title">Cash flow summary</h2>
                            <div class="mt-4 flex items-end gap-6">
                                <div><p class="text-xs font-bold uppercase tracking-wider text-zinc-500">In</p><p class="font-display mt-1 text-2xl font-extrabold text-emerald-600" x-text="money(report.cashflow.in_total)"></p></div>
                                <div><p class="text-xs font-bold uppercase tracking-wider text-zinc-500">Out</p><p class="font-display mt-1 text-2xl font-extrabold text-red-600" x-text="money(report.cashflow.out_total)"></p></div>
                                <div class="ml-auto text-right"><p class="text-xs font-bold uppercase tracking-wider text-zinc-500">Net</p><p class="font-display mt-1 text-2xl font-extrabold" x-text="money(report.cashflow.net)"></p></div>
                            </div>
                            <div class="mt-5 flex h-40 items-end gap-3">
                                <template x-for="m in months" :key="'rp'+m.label+m.year">
                                    <div class="flex flex-1 flex-col items-center gap-2">
                                        <div class="flex h-32 w-full items-end justify-center gap-1.5">
                                            <div class="w-1/3 rounded-t-md bg-emerald-500/85" :style="'height:' + barH(m.in) + '%'"></div>
                                            <div class="w-1/3 rounded-t-md bg-brand-600/85" :style="'height:' + barH(m.out) + '%'"></div>
                                        </div>
                                        <span class="text-[11px] font-bold text-zinc-500" x-text="m.label"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                    </x-tools.premium>
                </section>

                {{-- ============================== CASH FLOW ============================== --}}
                <section class="bt-panel" :class="tab === 'cashflow' && 'is-active bt-fade-in'">
                    <x-tools.premium tab="cashflow" description="See every receipt in and payment out, with running net movement.">
                    @include('tools.panels.report-header', ['title' => 'Cash Flow', 'eyebrow' => 'Liquidity'])
                    <div class="mt-5 grid gap-4 lg:grid-cols-2">
                        <div class="card card-pad">
                            <div class="flex items-center justify-between"><h2 class="section-title">Money in</h2><span class="pill-green" x-text="money(report.cashflow.in_total)"></span></div>
                            <div class="mt-3 divide-y divide-zinc-100 dark:divide-white/5">
                                <template x-for="r in report.cashflow.inflows" :key="'in'+r.number">
                                    <div class="flex items-center justify-between py-2 text-sm"><span><span class="font-semibold" x-text="r.number"></span> <span class="text-zinc-500" x-text="r.contact || ''"></span> <span class="text-xs text-zinc-400" x-text="r.date"></span></span><span class="font-bold text-emerald-600" x-text="money(r.amount)"></span></div>
                                </template>
                                <p x-show="!report.cashflow.inflows.length" class="py-6 text-center text-sm text-zinc-500">No receipts in this period.</p>
                            </div>
                        </div>
                        <div class="card card-pad">
                            <div class="flex items-center justify-between"><h2 class="section-title">Money out</h2><span class="pill-red" x-text="money(report.cashflow.out_total)"></span></div>
                            <div class="mt-3 divide-y divide-zinc-100 dark:divide-white/5">
                                <template x-for="(r, i) in report.cashflow.outflows" :key="'out'+i">
                                    <div class="flex items-center justify-between py-2 text-sm"><span><span class="font-semibold" x-text="r.category"></span> <span class="text-xs text-zinc-400" x-text="r.date"></span></span><span class="font-bold text-red-600" x-text="money(r.amount)"></span></div>
                                </template>
                                <p x-show="!report.cashflow.outflows.length" class="py-6 text-center text-sm text-zinc-500">No paid expenses in this period.</p>
                            </div>
                        </div>
                    </div>
                    <div class="card card-pad mt-4 flex items-center justify-between">
                        <span class="font-semibold">Net movement</span><span class="font-display text-2xl font-extrabold" x-text="money(report.cashflow.net)"></span>
                    </div>
                    </x-tools.premium>
                </section>

                {{-- ============================== P&L ============================== --}}
                <section class="bt-panel" :class="tab === 'pnl' && 'is-active bt-fade-in'">
                    <x-tools.premium tab="pnl" description="Track revenue, cost of goods sold and margins in one profit &amp; loss statement.">
                    @include('tools.panels.report-header', ['title' => 'Profit &amp; Loss', 'eyebrow' => 'Performance'])
                    <div class="mt-5 grid gap-4 xl:grid-cols-2">
                        <div class="card card-pad">
                            <div class="divide-y divide-zinc-100 dark:divide-white/5">
                                <div class="flex items-center justify-between py-3"><span class="font-semibold">Revenue (receipts)</span><span class="font-bold" x-text="money(report.pnl.revenue)"></span></div>
                                <div class="flex items-center justify-between py-3"><span class="font-semibold">Cost of goods sold</span><span class="font-bold text-red-600" x-text="'− ' + money(report.pnl.cogs)"></span></div>
                                <div class="flex items-center justify-between py-3"><span class="font-semibold">Gross profit</span><span class="font-bold" x-text="money(report.pnl.gross)"></span></div>
                                <div class="flex items-center justify-between py-3"><span class="font-semibold">Operating expenses</span><span class="font-bold text-red-600" x-text="'− ' + money(report.pnl.expense_total)"></span></div>
                                <div class="flex items-center justify-between py-3"><span class="font-display text-lg font-extrabold">Net profit</span><span class="font-display text-lg font-extrabold" :class="report.pnl.net >= 0 ? 'text-emerald-600' : 'text-red-600'" x-text="money(report.pnl.net)"></span></div>
                            </div>
                        </div>
                        <div class="card card-pad">
                            <h2 class="section-title">Margin breakdown</h2>
                            <div class="mt-4 flex flex-col gap-3">
                                <div><div class="flex items-center justify-between text-sm"><span class="font-semibold">Gross margin</span><span class="font-bold" x-text="pct(report.pnl.gross, report.pnl.revenue) + '%'"></span></div><div class="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-white/10"><div class="h-full rounded-full bg-emerald-500" :style="'width:' + pct(report.pnl.gross, report.pnl.revenue) + '%'"></div></div></div>
                                <div><div class="flex items-center justify-between text-sm"><span class="font-semibold">Expense ratio</span><span class="font-bold" x-text="pct(report.pnl.expense_total, report.pnl.revenue) + '%'"></span></div><div class="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-white/10"><div class="h-full rounded-full bg-brand-600" :style="'width:' + pct(report.pnl.expense_total, report.pnl.revenue) + '%'"></div></div></div>
                                <div><div class="flex items-center justify-between text-sm"><span class="font-semibold">Net margin</span><span class="font-bold" x-text="pct(report.pnl.net, report.pnl.revenue) + '%'"></span></div><div class="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-white/10"><div class="h-full rounded-full bg-zinc-900 dark:bg-white" :style="'width:' + Math.max(0, pct(report.pnl.net, report.pnl.revenue)) + '%'"></div></div></div>
                            </div>
                        </div>
                    </div>
                    </x-tools.premium>
                </section>

                {{-- ============================== BALANCE SHEET ============================== --}}
                <section class="bt-panel" :class="tab === 'balancesheet' && 'is-active bt-fade-in'">
                    <x-tools.premium tab="balancesheet" description="Your assets, liabilities and equity, computed live from the ledgers.">
                    @include('tools.panels.report-header', ['title' => 'Balance Sheet', 'eyebrow' => 'Position'])
                    <div class="mt-5 grid gap-4 lg:grid-cols-2">
                        <div class="card card-pad">
                            <h2 class="section-title">Assets</h2>
                            <div class="mt-4 flex flex-col gap-3">
                                <template x-for="a in [
                                    {t:'Cash', v:report.balance.cash, c:'from-emerald-500 to-emerald-600'},
                                    {t:'Receivables', v:report.balance.receivables, c:'from-amber-500 to-amber-600'},
                                    {t:'Inventory', v:report.balance.inventory, c:'from-brand-500 to-brand-700'},
                                ]" :key="'as'+a.t">
                                    <div>
                                        <div class="flex items-center justify-between text-sm"><span class="font-semibold" x-text="a.t"></span><span class="font-bold" x-text="money(a.v)"></span></div>
                                        <div class="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-white/10"><div class="h-full rounded-full bg-gradient-to-r" :class="a.c" :style="'width:' + pct(a.v, report.balance.assets) + '%'"></div></div>
                                    </div>
                                </template>
                                <div class="mt-1 flex items-center justify-between border-t border-zinc-200 pt-3 dark:border-white/10"><span class="font-display font-extrabold">Total assets</span><span class="font-display font-extrabold" x-text="money(report.balance.assets)"></span></div>
                            </div>
                        </div>
                        <div class="card card-pad">
                            <h2 class="section-title">Liabilities &amp; equity</h2>
                            <div class="mt-4 divide-y divide-zinc-100 dark:divide-white/5">
                                <div class="flex items-center justify-between py-3"><span class="font-semibold">Payables</span><span class="font-bold text-red-600" x-text="money(report.balance.payables)"></span></div>
                                <div class="flex items-center justify-between py-3"><span class="font-display font-extrabold">Owner's equity</span><span class="font-display font-extrabold" x-text="money(report.balance.equity)"></span></div>
                            </div>
                            <p class="mt-4 text-xs text-zinc-500">Assets = Liabilities + Equity, computed live from your ledgers.</p>
                        </div>
                    </div>
                    </x-tools.premium>
                </section>

                {{-- ============================== REMINDERS ============================== --}}
                <section class="bt-panel" :class="tab === 'reminders' && 'is-active bt-fade-in'">
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <p class="eyebrow text-brand-600 dark:text-brand-300">Collections</p>
                            <h1 class="page-title mt-1">Payment reminders</h1>
                            <p class="mt-1 text-sm text-zinc-500" x-text="openInvoices.length + ' invoice(s) awaiting payment'"></p>
                        </div>
                        <button type="button" @click="openReminder(null)" class="btn-brand btn-sm" :disabled="!openInvoices.length">Send reminder</button>
                    </div>

                    <div class="mt-5 grid gap-4 lg:grid-cols-2">
                        <div class="card card-pad">
                            <h2 class="section-title">Awaiting payment</h2>
                            <div class="mt-3 divide-y divide-zinc-100 dark:divide-white/5">
                                <template x-for="d in openInvoices" :key="'oi'+d.id">
                                    <div class="flex items-center justify-between gap-3 py-2.5">
                                        <span class="min-w-0">
                                            <button type="button" @click="openDrawer(d.id)" class="block truncate text-sm font-bold hover:text-brand-600" x-text="d.number"></button>
                                            <span class="block truncate text-xs text-zinc-500" x-text="(d.contact_name || 'No contact') + ' · due ' + (d.due_date || '—')"></span>
                                        </span>
                                        <div class="flex shrink-0 items-center gap-3">
                                            <span class="font-bold" x-text="money(d.balance)"></span>
                                            <button type="button" @click="openReminder(d)" class="btn-ghost btn-sm">Remind</button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <x-tools.empty x-show="!openInvoices.length" x-cloak icon="<path d='M20 6L9 17l-5-5'/>" title="Everything is settled" description="You have no unpaid invoices right now." />
                        </div>
                        <div class="card card-pad">
                            <h2 class="section-title">Sent reminders</h2>
                            <div class="mt-3 divide-y divide-zinc-100 dark:divide-white/5">
                                <template x-for="r in reminders" :key="'rm'+r.id">
                                    <div class="flex items-start gap-3 py-3">
                                        <span class="mt-0.5 grid size-8 shrink-0 place-items-center rounded-lg" :class="r.channel === 'whatsapp' ? 'bg-emerald-100 text-emerald-700' : 'bg-brand-100 text-brand-700 dark:bg-brand-500/15 dark:text-brand-200'">
                                            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4z"/></svg>
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-sm font-bold" x-text="(r.document_number || '') + ' → ' + (r.recipient || '')"></p>
                                            <p class="line-clamp-2 text-xs text-zinc-500" x-text="r.message"></p>
                                            <p class="mt-0.5 text-[10px] uppercase tracking-wider text-zinc-400" x-text="ucfirst(r.channel) + ' · ' + (r.sent_at || r.created_at) + (r.sent_by ? ' · ' + r.sent_by : '')"></p>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <x-tools.empty x-show="!reminders.length" x-cloak icon="<path d='M22 2L11 13M22 2l-7 20-4-9-9-4z'/>" title="No reminders sent" description="Chase unpaid invoices by email or WhatsApp in one click." />
                        </div>
                    </div>
                </section>

                {{-- ============================== TEAM ============================== --}}
                <section class="bt-panel" :class="tab === 'staff' && 'is-active bt-fade-in'">
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <p class="eyebrow text-brand-600 dark:text-brand-300">Access</p>
                            <h1 class="page-title mt-1">Team</h1>
                            <p class="mt-1 text-sm text-zinc-500" x-text="staff.length + ' member(s) can access this business'"></p>
                        </div>
                        <button type="button" @click="openStaff(null)" class="btn-brand btn-sm" x-show="boot.user.is_owner">Invite by email</button>
                    </div>

                    <div class="card mt-4 overflow-hidden" x-show="staff.length">
                        <table class="tbl w-full">
                            <thead><tr><th>Member</th><th>Role</th><th>Last login</th><th class="text-right">Actions</th></tr></thead>
                            <tbody>
                                <template x-for="m in staff" :key="'st'+m.id">
                                    <tr class="hover:bg-zinc-50 dark:hover:bg-white/5">
                                        <td>
                                            <div class="flex items-center gap-3">
                                                <span class="grid size-9 shrink-0 place-items-center rounded-full bg-gradient-to-br from-brand-500 to-brand-700 font-display text-xs font-extrabold text-white" x-text="(m.name || '?').slice(0,2).toUpperCase()"></span>
                                                <span class="min-w-0"><span class="block truncate text-sm font-bold" x-text="m.name"></span><span class="block truncate text-xs text-zinc-500" x-text="m.email"></span></span>
                                            </div>
                                        </td>
                                        <td><span class="pill-zinc capitalize" x-text="m.role"></span></td>
                                        <td class="text-xs text-zinc-500">
                                            <span x-text="m.last_login_at || 'Never'"></span>
                                            <span class="block" x-text="m.last_login_ip || ''"></span>
                                        </td>
                                        <td class="text-right whitespace-nowrap">
                                            <template x-if="boot.user.is_owner && !m.is_owner">
                                                <span>
                                                    <button type="button" @click="setStaffRole(m, m.role === 'owner' ? 'staff' : 'owner')" class="text-xs font-bold text-zinc-500 hover:text-brand-600" x-text="m.role === 'owner' ? 'Make staff' : 'Make owner'"></button>
                                                    <button type="button" @click="removeStaff(m)" class="ml-3 text-xs font-bold text-red-600">Remove</button>
                                                </span>
                                            </template>
                                            <span x-show="m.is_owner" class="text-xs text-zinc-400">Owner</span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <div class="card mt-4" x-show="!staff.length" x-cloak>
                        <x-tools.empty icon="<circle cx='9' cy='8' r='3.2'/><path d='M3 20a6 6 0 0112 0M16 5.5a3 3 0 010 5.6M21 20a5 5 0 00-4-4.9'/>" title="Just you so far" description="Invite teammates to share the same sales, stock and customers." action="openStaff(null)" action-label="Invite by email" />
                    </div>
                </section>

                {{-- ============================== ACTIVITY ============================== --}}
                <section class="bt-panel" :class="tab === 'activity' && 'is-active bt-fade-in'">
                    <x-tools.premium tab="activity" description="A full audit trail of every action, who did it and from where, is part of Premium.">
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <p class="eyebrow text-brand-600 dark:text-brand-300">Audit trail</p>
                            <h1 class="page-title mt-1">Activity log</h1>
                            <p class="mt-1 text-sm text-zinc-500">What happened, who did it, from where and when.</p>
                        </div>
                        <button type="button" @click="loadActivity()" class="btn-ghost btn-sm">Refresh</button>
                    </div>

                    <div class="card mt-4 overflow-hidden">
                        <div class="max-h-[70vh] overflow-y-auto divide-y divide-zinc-100 dark:divide-white/5">
                            <template x-for="a in filteredActivity" :key="'ac'+a.id">
                                <div class="flex items-start gap-3 px-4 py-3">
                                    <span class="mt-0.5 size-2 shrink-0 rounded-full bg-brand-500"></span>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-semibold" x-text="a.description"></p>
                                        <p class="mt-0.5 text-xs text-zinc-500">
                                            <span class="font-bold" x-text="a.user_name"></span>
                                            <span> · </span><span x-text="a.action"></span>
                                            <span> · </span><span x-text="a.ip || 'no ip'"></span>
                                        </p>
                                    </div>
                                    <span class="shrink-0 text-[10px] uppercase tracking-wider text-zinc-400" x-text="a.created_at"></span>
                                </div>
                            </template>
                        </div>
                        <x-tools.empty x-show="!filteredActivity.length" x-cloak icon="<circle cx='12' cy='12' r='9'/><path d='M12 7v5l3 3'/>" title="No activity yet" description="Actions you and your team take will appear here with the IP address." />
                    </div>
                    </x-tools.premium>
                </section>

                {{-- ============================== NOTIFICATIONS ============================== --}}
                <section class="bt-panel" :class="tab === 'notifications' && 'is-active bt-fade-in'">
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <p class="eyebrow text-brand-600 dark:text-brand-300">Inbox</p>
                            <h1 class="page-title mt-1">Notifications</h1>
                            <p class="mt-1 text-sm text-zinc-500" x-text="notifications.unread + ' unread · ' + notifications.items.length + ' total'"></p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="loadNotifications()" class="btn-ghost btn-sm">Refresh</button>
                            <button type="button" @click="markAllRead()" class="btn-brand btn-sm" :disabled="!notifications.unread">Mark all read</button>
                        </div>
                    </div>

                    <div class="card mt-4 overflow-hidden">
                        <div class="divide-y divide-zinc-100 dark:divide-white/5">
                            <template x-for="n in notifications.items" :key="'np'+n.id">
                                <div class="flex items-start gap-3 px-4 py-4" :class="!n.read && 'bg-brand-50/60 dark:bg-brand-500/5'">
                                    <span class="mt-1 size-2.5 shrink-0 rounded-full" :class="notificationTone(n.kind)"></span>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-bold" x-text="n.title"></p>
                                        <p class="text-sm text-zinc-500" x-text="n.body"></p>
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <p class="text-[10px] uppercase tracking-wider text-zinc-400" x-text="n.created_at"></p>
                                        <span x-show="!n.read" class="mt-1 inline-block rounded-full bg-brand-600 px-2 py-0.5 text-[10px] font-bold text-white">New</span>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <x-tools.empty x-show="!notifications.items.length" x-cloak
                            icon="<path d='M18 8a6 6 0 10-12 0c0 7-3 9-3 9h18s-3-2-3-9'/><path d='M13.7 21a2 2 0 01-3.4 0'/>"
                            title="No notifications yet"
                            description="Invoices issued, payments received and team changes show up here." />
                    </div>
                </section>

                {{-- ============================== SETTINGS ============================== --}}
                <section class="bt-panel" :class="tab === 'settings' && 'is-active bt-fade-in'">
                    <p class="eyebrow text-brand-600 dark:text-brand-300">Configuration</p>
                    <h1 class="page-title mt-1">Business profile</h1>
                    <p class="mt-1 text-sm text-zinc-500">These details print on every quotation, invoice and receipt.</p>
                    <div class="card card-pad mt-5 max-w-3xl">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Business name</span><input x-model="settings.business_name" class="field" /></label>
                            <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Phone</span><input x-model="settings.phone" class="field" /></label>
                            <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Email</span><input x-model="settings.email" type="email" class="field" /></label>
                            <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Currency</span><input x-model="settings.currency" class="field" placeholder="USD" /></label>
                            <label class="flex flex-col gap-1.5 sm:col-span-2"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Address</span><input x-model="settings.address" class="field" /></label>
                            <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Default tax rate (%)</span><input x-model.number="settings.tax_rate" type="number" min="0" max="100" class="field" /></label>
                            <label class="flex flex-col gap-1.5 sm:col-span-2"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Receipt footer</span><input x-model="settings.receipt_footer" class="field" placeholder="Thank you for your business" /></label>
                            <label class="flex flex-col gap-1.5 sm:col-span-2"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Quotation footer</span><input x-model="settings.quotation_footer" class="field" placeholder="Prices valid for 30 days" /></label>
                            <label class="flex flex-col gap-1.5 sm:col-span-2"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Invoice footer</span><input x-model="settings.invoice_footer" class="field" placeholder="Payment due within 15 days" /></label>
                        </div>
                        <div class="mt-5 flex justify-end"><button type="button" @click="saveSettings()" :disabled="saving" class="btn-brand">Save profile</button></div>
                    </div>
                </section>
            </main>
        </div>
    </div>

    {{-- ============================== COMPOSER ============================== --}}
    <div x-show="composeOpen" x-cloak class="fixed inset-0 z-40 flex justify-end bg-zinc-950/50" @keydown.escape.window="composeOpen = false">
        <div class="bt-fade-in flex h-full w-full flex-col bg-white shadow-2xl dark:bg-zinc-900" @click.outside="composeOpen = false">
            <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-4 dark:border-white/10">
                <div><h2 class="font-display text-lg font-extrabold" x-text="'New ' + composeLabel"></h2><p class="text-xs text-zinc-500">Draft saves automatically to this device.</p></div>
                <div class="flex items-center gap-2">
                    <button type="button" @click="previewDraft()" class="btn-ghost btn-sm">Preview</button>
                    <button type="button" @click="discardDraft()" class="btn-ghost btn-sm">Discard draft</button>
                    <button type="button" @click="composeOpen = false" class="grid size-9 place-items-center rounded-full hover:bg-zinc-100 dark:hover:bg-white/10">✕</button>
                </div>
            </div>

            <div class="flex-1 overflow-hidden">
                <template x-if="draft">
                    <div class="grid h-full gap-6 p-5 lg:grid-cols-2">
                    <div class="bt-scroll flex flex-col gap-4 overflow-y-auto pr-1">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Contact</span>
                                <x-tools.picker x-model="draft.contact_id" options="contacts" track="id" display="o => o.name + ' (' + o.kind + ')'" placeholder="— None —" />
                            </label>
                            <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Issue date</span><input type="date" x-model="draft.issue_date" class="field" /></label>
                            <template x-if="composeType === 'quotation' || composeType === 'purchase_quote'"><label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Valid until</span><input type="date" x-model="draft.valid_until" class="field" /></label></template>
                            <template x-if="composeType !== 'quotation' && composeType !== 'purchase_quote'"><label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Due date</span><input type="date" x-model="draft.due_date" class="field" /></label></template>
                        </div>

                        <template x-if="settlementDraft">
                            <div class="rounded-xl border border-zinc-200 p-4 dark:border-white/10">
                                <p class="text-sm font-bold" x-text="composeType === 'purchase_payment' ? 'Record a payment against an open supplier bill' : (composeType === 'credit_note' ? 'Raise a credit note against an open invoice' : 'Record a payment against an open invoice')"></p>
                                <div class="mt-3 grid gap-3 sm:grid-cols-3">
                                    <label class="flex flex-col gap-1.5 sm:col-span-2"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500" x-text="composeType === 'purchase_payment' ? 'Bill' : 'Invoice'"></span>
                                        <x-tools.picker x-model="draft.invoice_id" options="draftBills" track="id" display="o => o.number + ' · ' + (o.contact_name || '') + ' · ' + Number(o.balance).toLocaleString()" placeholder="— None —" />
                                    </label>
                                    <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Amount</span><input type="number" min="1" x-model.number="draft.amount" class="field" /></label>
                                    <label x-show="composeType !== 'credit_note'" class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Method</span><select x-model="draft.payment_method" class="field"><option value="cash">Cash</option><option value="momo">Mobile money</option><option value="card">Card</option><option value="bank">Bank</option></select></label>
                                    <label x-show="composeType === 'receipt' || composeType === 'purchase_payment'" class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Account</span>
                                        <x-tools.picker x-model="draft.bank_account_id" options="bankAccounts" track="id" display="o => o.name" placeholder="— Cash —" />
                                    </label>
                                </div>
                            </div>
                        </template>

                        <template x-if="!settlementDraft || !draft.invoice_id">
                            <div>
                                <div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Line items</span><button type="button" @click="addLine()" class="btn-ghost btn-sm">+ Add line</button></div>
                                <div class="mt-2 flex flex-col gap-2">
                                    <template x-for="(line, idx) in draft.lines" :key="'ln'+idx">
                                        <div class="grid grid-cols-12 items-center gap-2">
                                            <div class="col-span-3">
                                                <x-tools.picker x-model="line.item_id" options="items" track="id" display="o => o.name" placeholder="Custom line" />
                                            </div>
                                            <input x-model="line.description" placeholder="Description" class="field col-span-4" />
                                            <input type="number" min="0.01" step="0.01" x-model.number="line.qty" placeholder="Qty" class="field col-span-2 text-center" />
                                            <input type="number" min="0" x-model.number="line.unit_price" placeholder="Price" class="field col-span-2 text-right" />
                                            <button type="button" @click="removeLine(idx)" class="col-span-1 text-center text-lg text-zinc-400 hover:text-red-600">×</button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Notes</span><textarea x-model="draft.notes" rows="3" class="field"></textarea></label>
                            <div class="flex flex-col gap-2 text-sm" x-show="!settlementDraft || !draft.invoice_id">
                                <div class="flex items-center justify-between"><span class="text-zinc-500">Subtotal</span><span class="font-semibold" x-text="money(draftSubtotal)"></span></div>
                                <div class="flex items-center justify-between gap-3"><span class="text-zinc-500">Discount</span><input type="number" min="0" x-model.number="draft.discount" class="field h-8 w-32 text-right" /></div>
                                <div class="flex items-center justify-between gap-3"><span class="text-zinc-500">Tax %</span><input type="number" min="0" max="100" x-model.number="draft.tax_rate" class="field h-8 w-24 text-right" /></div>
                                <div class="flex items-center justify-between border-t border-zinc-200 pt-2 dark:border-white/10"><span class="font-bold">Total</span><span class="font-display text-lg font-extrabold" x-text="money(draftTotal)"></span></div>
                            </div>
                        </div>
                    </div>

                    <div class="bt-scroll hidden overflow-y-auto lg:block">
                        <div class="sticky top-0">
                            <p class="mb-2 flex items-center gap-2 text-[11px] font-bold uppercase tracking-wider text-zinc-400">
                                <span class="size-1.5 rounded-full bg-emerald-500"></span> Live preview
                            </p>
                            @include('tools.partials.document-body', ['bind' => 'draftPreviewDoc', 'showFooter' => false])
                        </div>
                    </div>
                    </div>
                </template>
            </div>

            <div class="flex items-center justify-between border-t border-zinc-200 px-5 py-4 dark:border-white/10">
                <span class="text-xs text-zinc-500" x-text="saving ? 'Saving…' : 'Ready'"></span>
                <div class="flex items-center gap-2">
                    <button type="button" @click="composeOpen = false" class="btn-ghost">Cancel</button>
                    <button type="button" @click="submitDraft()" :disabled="saving" class="btn-brand">
                        <svg x-show="saving" class="size-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" stroke-opacity=".25"/><path d="M21 12a9 9 0 00-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                        Save <span x-text="composeLabel" class="capitalize"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================== DOCUMENT DRAWER ============================== --}}
    <div x-show="drawerOpen" x-cloak class="fixed inset-0 z-40 flex justify-end bg-zinc-950/50" @keydown.escape.window="drawerOpen = false">
        <div class="bt-fade-in flex h-full w-full max-w-xl flex-col bg-white shadow-2xl dark:bg-zinc-900" @click.outside="drawerOpen = false">
            <template x-if="drawerDoc">
                <div class="flex h-full flex-col">
                    <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-4 dark:border-white/10">
                        <div><p class="text-[11px] font-bold uppercase tracking-wider text-zinc-500" x-text="drawerDoc.label"></p><h2 class="font-display text-lg font-extrabold" x-text="drawerDoc.number"></h2></div>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="openPreview(drawerDoc)" class="btn-ghost btn-sm">Preview</button>
                            <a :href="drawerDoc.pdf_url" class="btn-brand btn-sm">PDF</a>
                            <button type="button" @click="drawerOpen = false" class="grid size-9 place-items-center rounded-full hover:bg-zinc-100 dark:hover:bg-white/10">✕</button>
                        </div>
                    </div>
                    <div class="flex-1 overflow-y-auto px-5 py-5">
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div><span class="text-zinc-500">Contact</span><p class="font-semibold" x-text="drawerDoc.contact_name || '—'"></p></div>
                            <div><span class="text-zinc-500">Status</span><p><span class="pill-zinc capitalize" x-text="drawerDoc.status"></span></p></div>
                            <div><span class="text-zinc-500">Issued</span><p class="font-semibold" x-text="drawerDoc.issue_date"></p></div>
                            <div><span class="text-zinc-500">Total</span><p class="font-display font-extrabold" x-text="money(drawerDoc.total)"></p></div>
                            <div x-show="drawerDoc.is_bill"><span class="text-zinc-500">Last settled</span><p class="font-semibold" x-text="drawerDoc.last_receipt_date || 'No payments yet'"></p></div>
                            <div x-show="drawerDoc.is_bill"><span class="text-zinc-500">Balance</span><p class="font-semibold" x-text="money(drawerDoc.balance)"></p></div>
                        </div>
                        <div class="mt-5 overflow-hidden rounded-xl border border-zinc-200 dark:border-white/10">
                            <table class="tbl w-full"><thead><tr><th>Item</th><th class="text-center">Qty</th><th class="text-right">Price</th><th class="text-right">Total</th></tr></thead>
                                <tbody><template x-for="l in drawerDoc.lines" :key="'dl'+l.id"><tr><td x-text="l.description"></td><td class="text-center" x-text="l.qty"></td><td class="text-right" x-text="money(l.unit_price)"></td><td class="text-right font-semibold" x-text="money(l.line_total)"></td></tr></template></tbody>
                            </table>
                        </div>

                        <template x-if="drawerDoc.converted_from_number || (drawerDoc.chain_from)">
                            <p class="mt-3 text-xs text-zinc-500" x-show="drawerDoc.converted_from_number" x-text="'Converted from ' + drawerDoc.converted_from_number"></p>
                        </template>

                        <template x-if="docStatuses(drawerDoc.type).length > 1">
                            <div class="mt-5">
                                <p class="text-xs font-bold uppercase tracking-wider text-zinc-500" x-text="'Move ' + drawerDoc.label.toLowerCase()"></p>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <template x-for="s in docStatuses(drawerDoc.type).filter(x => x !== 'all')" :key="'qs'+s"><button type="button" @click="setDocStatus(drawerDoc, s)" :class="drawerDoc.status === s ? 'btn-dark btn-sm' : 'btn-ghost btn-sm'" class="capitalize" x-text="s"></button></template>
                                </div>
                            </div>
                        </template>

                        <div class="mt-5 flex flex-wrap items-center gap-2" x-show="convertOptions(drawerDoc.type).length || canClone(drawerDoc.type)">
                            <span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Convert &amp; copy</span>
                            <template x-for="t in convertOptions(drawerDoc.type)" :key="'dcv'+t">
                                <button type="button" @click="convertTo(drawerDoc, t)" class="btn-brand btn-sm" x-text="'Convert to ' + typeLabel(t)"></button>
                            </template>
                            <button type="button" x-show="canClone(drawerDoc.type)" @click="cloneDoc(drawerDoc)" class="btn-ghost btn-sm">Duplicate</button>
                        </div>

                        <template x-if="drawerDoc.is_bill && drawerDoc.balance > 0">
                            <div class="mt-5 rounded-xl border border-zinc-200 p-4 dark:border-white/10">
                                <p class="text-xs font-bold uppercase tracking-wider text-zinc-500" x-text="(drawerDoc.type === 'purchase_invoice' ? 'Record supplier payment ' : 'Record payment ') + '· balance ' + money(drawerDoc.balance)"></p>
                                <div class="mt-2 flex items-end gap-2">
                                    <input type="number" min="1" x-model.number="payForm.amount" class="field w-32" />
                                    <select x-show="drawerDoc.type !== 'purchase_invoice'" x-model="payForm.payment_method" class="field w-36"><option value="cash">Cash</option><option value="momo">Mobile money</option><option value="card">Card</option><option value="bank">Bank</option></select>
                                    <x-tools.picker x-show="drawerDoc.type === 'purchase_invoice'" class="w-40" x-model="payForm.bank_account_id" options="bankAccounts.map(b => ({ id: b.id, name: b.name }))" track="id" display="o => o.name" placeholder="Pay from account" />
                                    <button type="button" @click="recordPayment(drawerDoc)" class="btn-brand btn-sm">Add payment</button>
                                </div>
                            </div>
                        </template>

                        <template x-if="drawerDoc.is_bill && drawerDoc.balance <= 0 && drawerDoc.type === 'invoice'">
                            <p class="mt-5 text-sm font-semibold text-emerald-600">Paid in full.</p>
                        </template>

                        <template x-if="drawerDoc.receipts && drawerDoc.receipts.length">
                            <div class="mt-5">
                                <p class="text-xs font-bold uppercase tracking-wider text-zinc-500" x-text="drawerDoc.type === 'purchase_invoice' ? 'Payments' : 'Receipts'"></p>
                                <div class="mt-2 divide-y divide-zinc-100 dark:divide-white/5">
                                    <template x-for="r in drawerDoc.receipts" :key="'rc'+r.id">
                                        <div class="flex items-center justify-between py-2 text-sm">
                                            <span class="min-w-0">
                                                <span class="block font-semibold" x-text="r.number"></span>
                                                <span class="block text-xs text-zinc-500" x-text="r.issue_date + ' · ' + ucfirst(r.payment_method || '')"></span>
                                            </span>
                                            <span class="font-bold" x-text="money(r.total)"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <template x-if="drawerDoc.type === 'invoice'">
                            <div class="mt-5">
                                <p class="text-xs font-bold uppercase tracking-wider text-zinc-500">Credit note</p>
                                <div class="mt-2 flex items-end gap-2">
                                    <input type="number" min="1" x-model.number="creditForm.amount" class="field w-32" />
                                    <button type="button" @click="issueCreditNote(drawerDoc)" class="btn-ghost btn-sm">Issue credit note</button>
                                </div>
                            </div>
                        </template>
                    </div>
                    <div class="flex items-center justify-between border-t border-zinc-200 px-5 py-4 dark:border-white/10">
                        <button type="button" @click="deleteDoc(drawerDoc)" class="text-sm font-bold text-red-600">Delete</button>
                        <span class="text-xs text-zinc-500" x-text="drawerDoc.overdue ? 'Overdue' : 'On file'"></span>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- ============================== DOCUMENT PREVIEW (with QR) ============================== --}}
    <div x-show="previewOpen" x-cloak class="fixed inset-0 z-50 grid place-items-center overflow-y-auto bg-zinc-950/60 p-3 sm:p-6" @keydown.escape.window="previewOpen = false">
        <div class="bt-fade-in w-full max-w-3xl" @click.outside="previewOpen = false">
            <template x-if="previewDoc && previewDoc.lines">
                <div>
                    <div class="mb-3 flex items-center justify-between">
                        <span class="text-sm font-bold text-white/80" x-text="'Preview · ' + previewDoc.number"></span>
                        <div class="flex items-center gap-2">
                            <a :href="previewDoc.pdf_url" class="btn-brand btn-sm">Download PDF</a>
                            <button type="button" @click="previewOpen = false" class="grid size-9 place-items-center rounded-full bg-white/10 text-white hover:bg-white/20">✕</button>
                        </div>
                    </div>
                    <div x-show="previewDoc.kind === 'draft'" class="mb-2 rounded-xl bg-amber-400/20 px-4 py-2 text-xs font-bold text-amber-100">Live draft preview — save to generate the printable PDF.</div>
                    @include('tools.partials.document-body', ['bind' => 'previewDoc'])
                </div>
            </template>
        </div>
    </div>

    {{-- ============================== SALE RECEIPT (POS) ============================== --}}
    <div x-show="lastSale" x-cloak class="fixed inset-0 z-50 grid place-items-center bg-zinc-950/60 p-4" @keydown.escape.window="lastSale = null">
        <div class="bt-fade-in w-full max-w-sm rounded-2xl bg-white p-6 text-center shadow-2xl dark:bg-zinc-900" @click.outside="lastSale = null">
            <span class="mx-auto grid size-14 place-items-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15"><svg class="size-7" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg></span>
            <h2 class="font-display mt-3 text-xl font-extrabold">Sale complete</h2>
            <p class="text-sm text-zinc-500" x-text="lastSale ? lastSale.number : ''"></p>
            <p class="font-display mt-4 text-3xl font-extrabold" x-text="lastSale ? money(lastSale.total) : ''"></p>
            <p class="mt-1 text-sm text-zinc-500" x-show="lastChange > 0">Change: <span class="font-bold text-zinc-800 dark:text-zinc-100" x-text="money(lastChange)"></span></p>
            <div class="mt-5 flex items-center justify-center gap-3" x-show="lastSale && lastSale.qr">
                <img :src="lastSale ? lastSale.qr : ''" alt="QR code" class="size-28 rounded-lg border border-zinc-200 dark:border-white/10" />
            </div>
            <div class="mt-6 flex gap-2">
                <button type="button" @click="lastSale = null" class="btn-ghost flex-1 justify-center">New sale</button>
                <button type="button" @click="openPreview(lastSale)" class="btn-brand flex-1 justify-center">View receipt</button>
            </div>
        </div>
    </div>

    {{-- ============================== CONTACT MODAL ============================== --}}
    <div x-show="contactForm" x-cloak class="fixed inset-0 z-50 grid place-items-center overflow-y-auto bg-zinc-950/60 p-4" @keydown.escape.window="contactForm = null">
        <div class="bt-fade-in w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-zinc-900" @click.outside="contactForm = null">
            <template x-if="contactForm">
                <div>
                    <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-4 dark:border-white/10">
                        <h2 class="font-display text-lg font-extrabold capitalize" x-text="(contactForm.id ? 'Edit ' : 'New ') + contactForm.kind"></h2>
                        <button type="button" @click="contactForm = null" class="grid size-9 place-items-center rounded-full hover:bg-zinc-100 dark:hover:bg-white/10">✕</button>
                    </div>
                    <div class="grid gap-4 px-5 py-5 sm:grid-cols-2">
                        <label class="flex flex-col gap-1.5 sm:col-span-2"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Name</span><input x-model="contactForm.name" class="field" autofocus /></label>
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Phone</span><input x-model="contactForm.phone" class="field" placeholder="+256…" /></label>
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Email</span><input x-model="contactForm.email" type="email" class="field" /></label>
                        <label class="flex flex-col gap-1.5 sm:col-span-2"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Address</span><input x-model="contactForm.address" class="field" /></label>
                        <label class="flex flex-col gap-1.5 sm:col-span-2"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Notes</span><textarea x-model="contactForm.notes" rows="3" class="field"></textarea></label>
                    </div>
                    <div class="flex justify-end gap-2 border-t border-zinc-200 px-5 py-4 dark:border-white/10">
                        <button type="button" @click="contactForm = null" class="btn-ghost">Cancel</button>
                        <button type="button" @click="saveContact()" :disabled="saving" class="btn-brand">Save contact</button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- ============================== ITEM MODAL ============================== --}}
    <div x-show="itemForm" x-cloak class="fixed inset-0 z-50 grid place-items-center overflow-y-auto bg-zinc-950/60 p-4" @keydown.escape.window="itemForm = null">
        <div class="bt-fade-in w-full max-w-xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-zinc-900" @click.outside="itemForm = null">
            <template x-if="itemForm">
                <div>
                    <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-4 dark:border-white/10">
                        <h2 class="font-display text-lg font-extrabold" x-text="itemForm.id ? 'Edit item' : 'New item'"></h2>
                        <button type="button" @click="itemForm = null" class="grid size-9 place-items-center rounded-full hover:bg-zinc-100 dark:hover:bg-white/10">✕</button>
                    </div>
                    <div class="grid gap-4 px-5 py-5 sm:grid-cols-2">
                        <label class="flex flex-col gap-1.5 sm:col-span-2"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Name</span><input x-model="itemForm.name" class="field" autofocus /></label>
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">SKU</span><input x-model="itemForm.sku" class="field" /></label>
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Unit</span><input x-model="itemForm.unit" class="field" placeholder="pcs" /></label>
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Cost price</span><input type="number" min="0" x-model.number="itemForm.cost_price" class="field" /></label>
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Selling price</span><input type="number" min="0" x-model.number="itemForm.sale_price" class="field" /></label>
                        <template x-if="!itemForm.id">
                            <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Opening stock</span><input type="number" min="0" x-model.number="itemForm.stock" class="field" /></label>
                        </template>
                        <template x-if="itemForm.id">
                            <div class="flex flex-col gap-1.5">
                                <span class="text-xs font-bold uppercase tracking-wider text-zinc-500">On hand</span>
                                <div class="flex items-center justify-between rounded-md border border-zinc-200 px-3.5 py-2 text-sm dark:border-white/15">
                                    <span class="font-bold" x-text="itemForm.stock + ' ' + (itemForm.unit || '')"></span>
                                    <button type="button" @click="itemForm = null; openAdjust({ id: itemForm.id, name: itemForm.name, stock: itemForm.stock, unit: itemForm.unit }, 'add')" class="text-xs font-bold text-emerald-600">Restock</button>
                                </div>
                            </div>
                        </template>
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Reorder level</span><input type="number" min="0" x-model.number="itemForm.reorder_level" class="field" /></label>
                        <div class="flex items-center justify-between rounded-md bg-zinc-50 px-3.5 py-2 text-sm dark:bg-white/5">
                            <span class="text-zinc-500">Profit / unit</span>
                            <span class="font-bold" :class="itemMargin >= 0 ? 'text-emerald-600' : 'text-red-600'" x-text="money(Number(itemForm.sale_price || 0) - Number(itemForm.cost_price || 0)) + ' (' + itemMargin + '%)'"></span>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 border-t border-zinc-200 px-5 py-4 dark:border-white/10">
                        <button type="button" @click="itemForm = null" class="btn-ghost">Cancel</button>
                        <button type="button" @click="saveItem()" :disabled="saving" class="btn-brand">
                            <svg x-show="saving" class="size-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" stroke-opacity=".25"/><path d="M21 12a9 9 0 00-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                            Save item
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- ============================== ADJUST STOCK MODAL ============================== --}}
    <div x-show="adjustForm" x-cloak class="fixed inset-0 z-50 grid place-items-center overflow-y-auto bg-zinc-950/60 p-4" @keydown.escape.window="adjustForm = null">
        <div class="bt-fade-in w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-zinc-900" @click.outside="adjustForm = null">
            <template x-if="adjustForm">
                <div>
                    <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-4 dark:border-white/10">
                        <div><h2 class="font-display text-lg font-extrabold">Move stock</h2><p class="text-xs text-zinc-500" x-text="adjustForm.name"></p></div>
                        <button type="button" @click="adjustForm = null" class="grid size-9 place-items-center rounded-full hover:bg-zinc-100 dark:hover:bg-white/10">✕</button>
                    </div>
                    <div class="grid gap-4 px-5 py-5">
                        <div class="grid grid-cols-3 gap-1 rounded-xl bg-zinc-100 p-1 dark:bg-white/5">
                            <template x-for="m in [['add','Restock'],['remove','Reduce'],['set','Set count']]" :key="'am'+m[0]">
                                <button type="button" @click="adjustForm.mode = m[0]"
                                    :class="adjustForm.mode === m[0] ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-900 dark:text-white' : 'text-zinc-500'"
                                    class="rounded-lg px-2 py-1.5 text-xs font-bold" x-text="m[1]"></button>
                            </template>
                        </div>
                        <div class="flex items-center justify-between rounded-xl bg-zinc-50 px-4 py-3 text-sm dark:bg-white/5">
                            <span class="text-zinc-500">On hand now</span>
                            <span class="font-bold" x-text="adjustForm.stock + ' ' + (adjustForm.unit || '')"></span>
                        </div>
                        <label class="flex flex-col gap-1.5">
                            <span class="text-xs font-bold uppercase tracking-wider text-zinc-500" x-text="adjustForm.mode === 'add' ? 'Quantity received' : (adjustForm.mode === 'remove' ? 'Quantity removed' : 'New stock count')"></span>
                            <input type="number" min="0" x-model.number="adjustForm.qty" class="field" :placeholder="adjustForm.mode === 'set' ? String(adjustForm.stock) : '0'" />
                        </label>
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Reason</span><input x-model="adjustForm.reason" class="field" placeholder="Purchase, stock count, damage…" /></label>
                        <div class="flex items-center justify-between rounded-xl border border-zinc-200 px-4 py-3 text-sm dark:border-white/10">
                            <span class="text-zinc-500">New balance</span>
                            <span class="font-display text-lg font-extrabold" :class="adjustResult < 0 ? 'text-red-600' : 'text-zinc-900 dark:text-white'" x-text="adjustResult + ' ' + (adjustForm.unit || '')"></span>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 border-t border-zinc-200 px-5 py-4 dark:border-white/10">
                        <button type="button" @click="adjustForm = null" class="btn-ghost">Cancel</button>
                        <button type="button" @click="saveAdjust()" :disabled="saving || adjustQtyChange === 0" class="btn-brand">
                            <svg x-show="saving" class="size-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" stroke-opacity=".25"/><path d="M21 12a9 9 0 00-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                            Apply
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- ============================== STOCK HISTORY MODAL ============================== --}}
    <div x-show="historyOpen" x-cloak class="fixed inset-0 z-50 grid place-items-center overflow-y-auto bg-zinc-950/60 p-4" @keydown.escape.window="historyOpen = false">
        <div class="bt-fade-in w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-zinc-900" @click.outside="historyOpen = false">
            <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-4 dark:border-white/10">
                <div><h2 class="font-display text-lg font-extrabold">Stock history</h2><p class="text-xs text-zinc-500" x-text="historyItem ? historyItem.name : ''"></p></div>
                <button type="button" @click="historyOpen = false" class="grid size-9 place-items-center rounded-full hover:bg-zinc-100 dark:hover:bg-white/10">✕</button>
            </div>
            <div class="max-h-[60vh] overflow-y-auto px-5 py-4">
                <div x-show="historyLoading" class="py-10 text-center">
                    <svg class="mx-auto size-6 animate-spin text-brand-600" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" stroke-opacity=".25"/><path d="M21 12a9 9 0 00-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                </div>
                <div x-show="!historyLoading" class="divide-y divide-zinc-100 dark:divide-white/5">
                    <template x-for="m in historyMoves" :key="'mv'+m.id">
                        <div class="flex items-center justify-between gap-3 py-2.5">
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-semibold" x-text="m.reason"></span>
                                <span class="block text-xs text-zinc-500" x-text="m.created_at + (m.ref_type ? ' · ' + m.ref_type : '')"></span>
                            </span>
                            <span class="shrink-0 font-bold" :class="m.qty_change >= 0 ? 'text-emerald-600' : 'text-red-600'" x-text="(m.qty_change > 0 ? '+' : '') + m.qty_change"></span>
                        </div>
                    </template>
                    <p x-show="!historyMoves.length" class="py-10 text-center text-sm text-zinc-500">No stock movements recorded yet.</p>
                </div>
            </div>
            <div class="flex justify-end border-t border-zinc-200 px-5 py-4 dark:border-white/10">
                <button type="button" @click="historyOpen = false" class="btn-ghost">Close</button>
            </div>
        </div>
    </div>

    {{-- ============================== EXPENSE MODAL ============================== --}}
    <div x-show="expenseForm" x-cloak class="fixed inset-0 z-50 grid place-items-center overflow-y-auto bg-zinc-950/60 p-4" @keydown.escape.window="expenseForm = null">
        <div class="bt-fade-in w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-zinc-900" @click.outside="expenseForm = null">
            <template x-if="expenseForm">
                <div>
                    <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-4 dark:border-white/10">
                        <h2 class="font-display text-lg font-extrabold" x-text="expenseForm.id ? 'Edit expense' : 'New expense'"></h2>
                        <button type="button" @click="expenseForm = null" class="grid size-9 place-items-center rounded-full hover:bg-zinc-100 dark:hover:bg-white/10">✕</button>
                    </div>
                    <div class="grid gap-4 px-5 py-5 sm:grid-cols-2">
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Date</span><input type="date" x-model="expenseForm.date" class="field" /></label>
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Amount</span><input type="number" min="1" x-model.number="expenseForm.amount" class="field" /></label>
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Category</span><input x-model="expenseForm.category" class="field" placeholder="Rent, transport…" /></label>
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Status</span>
                            <x-tools.picker x-model="expenseForm.status" options="[{ id: 'paid', name: 'Paid' }, { id: 'unpaid', name: 'Unpaid' }]" track="id" display="o => o.name" placeholder="Paid" />
                        </label>
                        <label class="flex flex-col gap-1.5 sm:col-span-2"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Supplier (optional)</span>
                            <x-tools.picker x-model="expenseForm.contact_id" options="contacts.filter(c => c.kind === 'supplier')" track="id" display="o => o.name" placeholder="— None —" />
                        </label>
                        <label class="flex flex-col gap-1.5 sm:col-span-2"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Note</span><textarea x-model="expenseForm.note" rows="2" class="field"></textarea></label>
                    </div>
                    <div class="flex justify-end gap-2 border-t border-zinc-200 px-5 py-4 dark:border-white/10">
                        <button type="button" @click="expenseForm = null" class="btn-ghost">Cancel</button>
                        <button type="button" @click="saveExpense()" :disabled="saving" class="btn-brand">Save expense</button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- ============================== ACCOUNT MODAL ============================== --}}
    <div x-show="accountForm" x-cloak class="fixed inset-0 z-50 grid place-items-center overflow-y-auto bg-zinc-950/60 p-4" @keydown.escape.window="accountForm = null">
        <div class="bt-fade-in w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-zinc-900" @click.outside="accountForm = null">
            <template x-if="accountForm">
                <div>
                    <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-4 dark:border-white/10">
                        <h2 class="font-display text-lg font-extrabold" x-text="accountForm.id ? 'Edit account' : 'New account'"></h2>
                        <button type="button" @click="accountForm = null" class="grid size-9 place-items-center rounded-full hover:bg-zinc-100 dark:hover:bg-white/10">✕</button>
                    </div>
                    <div class="grid gap-4 px-5 py-5 sm:grid-cols-2">
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Code</span><input x-model="accountForm.code" class="field font-mono" placeholder="6000" /></label>
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Name</span><input x-model="accountForm.name" class="field" placeholder="Marketing" /></label>
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Type</span>
                            <select x-model="accountForm.type" class="field"><option value="asset">Asset</option><option value="liability">Liability</option><option value="equity">Equity</option><option value="income">Income</option><option value="expense">Expense</option></select>
                        </label>
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Opening balance</span><input type="number" x-model.number="accountForm.opening_balance" class="field" /></label>
                    </div>
                    <div class="flex justify-end gap-2 border-t border-zinc-200 px-5 py-4 dark:border-white/10">
                        <button type="button" @click="accountForm = null" class="btn-ghost">Cancel</button>
                        <button type="button" @click="saveAccount()" :disabled="saving" class="btn-brand">Save account</button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- ============================== BANK ACCOUNT MODAL ============================== --}}
    <div x-show="bankForm" x-cloak class="fixed inset-0 z-50 grid place-items-center overflow-y-auto bg-zinc-950/60 p-4" @keydown.escape.window="bankForm = null">
        <div class="bt-fade-in w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-zinc-900" @click.outside="bankForm = null">
            <template x-if="bankForm">
                <div>
                    <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-4 dark:border-white/10">
                        <h2 class="font-display text-lg font-extrabold" x-text="bankForm.id ? 'Edit account' : 'New bank / cash account'"></h2>
                        <button type="button" @click="bankForm = null" class="grid size-9 place-items-center rounded-full hover:bg-zinc-100 dark:hover:bg-white/10">✕</button>
                    </div>
                    <div class="grid gap-4 px-5 py-5 sm:grid-cols-2">
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Name</span><input x-model="bankForm.name" class="field" placeholder="Stanbic main" /></label>
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Type</span>
                            <select x-model="bankForm.type" class="field" :disabled="!!bankForm.id"><option value="bank">Bank</option><option value="cash">Cash</option><option value="momo">Mobile money</option></select>
                        </label>
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Account number</span><input x-model="bankForm.account_number" class="field" /></label>
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Opening balance</span><input type="number" x-model.number="bankForm.opening_balance" class="field" /></label>
                    </div>
                    <div class="flex justify-end gap-2 border-t border-zinc-200 px-5 py-4 dark:border-white/10">
                        <button type="button" @click="bankForm = null" class="btn-ghost">Cancel</button>
                        <button type="button" @click="saveBank()" :disabled="saving" class="btn-brand">Save account</button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- ============================== BANK TX MODAL ============================== --}}
    <div x-show="bankTxForm" x-cloak class="fixed inset-0 z-50 grid place-items-center overflow-y-auto bg-zinc-950/60 p-4" @keydown.escape.window="bankTxForm = null">
        <div class="bt-fade-in w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-zinc-900" @click.outside="bankTxForm = null">
            <template x-if="bankTxForm">
                <div>
                    <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-4 dark:border-white/10">
                        <h2 class="font-display text-lg font-extrabold" x-text="bankTxForm.type === 'credit' ? 'Deposit' : 'Withdrawal'"></h2>
                        <button type="button" @click="bankTxForm = null" class="grid size-9 place-items-center rounded-full hover:bg-zinc-100 dark:hover:bg-white/10">✕</button>
                    </div>
                    <div class="grid gap-4 px-5 py-5 sm:grid-cols-2">
                        <label class="flex flex-col gap-1.5 sm:col-span-2"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Account</span><input class="field" :value="bankTxName" disabled /></label>
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Date</span><input type="date" x-model="bankTxForm.date" class="field" /></label>
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Amount</span><input type="number" min="1" x-model.number="bankTxForm.amount" class="field" /></label>
                        <label class="flex flex-col gap-1.5 sm:col-span-2"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Description</span><input x-model="bankTxForm.description" class="field" placeholder="Cash sale, transfer…" /></label>
                        <label class="flex flex-col gap-1.5 sm:col-span-2"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Reference</span><input x-model="bankTxForm.reference" class="field" /></label>
                    </div>
                    <div class="flex justify-end gap-2 border-t border-zinc-200 px-5 py-4 dark:border-white/10">
                        <button type="button" @click="bankTxForm = null" class="btn-ghost">Cancel</button>
                        <button type="button" @click="saveBankTx()" :disabled="saving" class="btn-brand" x-text="bankTxForm.type === 'credit' ? 'Record deposit' : 'Record withdrawal'"></button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- ============================== EMPLOYEE MODAL ============================== --}}
    <div x-show="employeeForm" x-cloak class="fixed inset-0 z-50 grid place-items-center overflow-y-auto bg-zinc-950/60 p-4" @keydown.escape.window="employeeForm = null">
        <div class="bt-fade-in w-full max-w-xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-zinc-900" @click.outside="employeeForm = null">
            <template x-if="employeeForm">
                <div>
                    <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-4 dark:border-white/10">
                        <h2 class="font-display text-lg font-extrabold" x-text="employeeForm.id ? 'Edit employee' : 'New employee'"></h2>
                        <button type="button" @click="employeeForm = null" class="grid size-9 place-items-center rounded-full hover:bg-zinc-100 dark:hover:bg-white/10">✕</button>
                    </div>
                    <div class="grid gap-4 px-5 py-5 sm:grid-cols-2">
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Name</span><input x-model="employeeForm.name" class="field" /></label>
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Position</span><input x-model="employeeForm.position" class="field" placeholder="Sales assistant" /></label>
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Email</span><input type="email" x-model="employeeForm.email" class="field" /></label>
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Phone</span><input x-model="employeeForm.phone" class="field" /></label>
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Employment type</span>
                            <select x-model="employeeForm.employment_type" class="field"><option value="full_time">Full time</option><option value="part_time">Part time</option><option value="contract">Contract</option></select>
                        </label>
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Pay frequency</span>
                            <select x-model="employeeForm.pay_frequency" class="field"><option value="monthly">Monthly</option><option value="weekly">Weekly</option><option value="daily">Daily</option></select>
                        </label>
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Base salary</span><input type="number" min="0" x-model.number="employeeForm.base_salary" class="field" /></label>
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Hired on</span><input type="date" x-model="employeeForm.hired_on" class="field" /></label>
                        <label class="flex items-center gap-2 sm:col-span-2"><input type="checkbox" x-model="employeeForm.is_active" class="size-4 rounded border-zinc-300 text-brand-600" /><span class="text-sm font-semibold">Currently active</span></label>
                    </div>
                    <div class="flex justify-end gap-2 border-t border-zinc-200 px-5 py-4 dark:border-white/10">
                        <button type="button" @click="employeeForm = null" class="btn-ghost">Cancel</button>
                        <button type="button" @click="saveEmployee()" :disabled="saving" class="btn-brand">Save employee</button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- ============================== PAYSLIP MODAL ============================== --}}
    <div x-show="payslipForm" x-cloak class="fixed inset-0 z-50 grid place-items-center overflow-y-auto bg-zinc-950/60 p-4" @keydown.escape.window="payslipForm = null">
        <div class="bt-fade-in w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-zinc-900" @click.outside="payslipForm = null">
            <template x-if="payslipForm">
                <div>
                    <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-4 dark:border-white/10">
                        <h2 class="font-display text-lg font-extrabold" x-text="payslipForm.id ? 'Edit payslip' : 'New payslip'"></h2>
                        <button type="button" @click="payslipForm = null" class="grid size-9 place-items-center rounded-full hover:bg-zinc-100 dark:hover:bg-white/10">✕</button>
                    </div>
                    <div class="grid gap-4 px-5 py-5 sm:grid-cols-2">
                        <label class="flex flex-col gap-1.5 sm:col-span-2"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Employee</span>
                            <select x-model="payslipForm.employee_id" class="field"><option value="">— Select —</option><template x-for="e in employees" :key="'pe'+e.id"><option :value="e.id" x-text="e.name"></option></template></select>
                        </label>
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Period start</span><input type="date" x-model="payslipForm.period_start" class="field" /></label>
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Period end</span><input type="date" x-model="payslipForm.period_end" class="field" /></label>
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Gross</span><input type="number" min="0" x-model.number="payslipForm.gross" class="field" /></label>
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Allowances</span><input type="number" min="0" x-model.number="payslipForm.allowances" class="field" /></label>
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Deductions</span><input type="number" min="0" x-model.number="payslipForm.deductions" class="field" /></label>
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Status</span>
                            <select x-model="payslipForm.status" class="field"><option value="draft">Draft</option><option value="paid">Paid</option></select>
                        </label>
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Paid on</span><input type="date" x-model="payslipForm.paid_on" class="field" /></label>
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Paid from</span>
                            <x-tools.picker x-model="payslipForm.bank_account_id" options="bankAccounts" track="id" display="o => o.name" placeholder="— Cash —" />
                        </label>
                    </div>
                    <div class="flex items-center justify-between border-t border-zinc-200 px-5 py-4 dark:border-white/10">
                        <span class="text-sm text-zinc-500">Net pay <b class="font-display text-base font-extrabold text-zinc-900 dark:text-white" x-text="money(payslipNet)"></b></span>
                        <div class="flex gap-2">
                            <button type="button" @click="payslipForm = null" class="btn-ghost">Cancel</button>
                            <button type="button" @click="savePayslip()" :disabled="saving" class="btn-brand">Save payslip</button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
    {{-- ============================== STAFF MODAL ============================== --}}
    <div x-show="staffForm" x-cloak class="fixed inset-0 z-50 grid place-items-center overflow-y-auto bg-zinc-950/60 p-4" @keydown.escape.window="closeStaff()">
        <div class="bt-fade-in w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-zinc-900" @click.outside="closeStaff()">
            <template x-if="staffForm">
                <div>
                    <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-4 dark:border-white/10">
                        <h2 class="font-display text-lg font-extrabold">Invite a teammate</h2>
                        <button type="button" @click="closeStaff()" class="grid size-9 place-items-center rounded-full hover:bg-zinc-100 dark:hover:bg-white/10">✕</button>
                    </div>
                    <div class="grid gap-4 px-5 py-5">
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Email</span><input x-model="staffForm.email" type="email" class="field" placeholder="teammate@example.com" autofocus /></label>
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Name (optional)</span><input x-model="staffForm.name" class="field" /></label>
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Role</span>
                            <x-tools.picker x-model="staffForm.role" options="[{ id: 'staff', name: 'Staff' }, { id: 'owner', name: 'Owner' }]" track="id" display="o => o.name" placeholder="Staff" />
                        </label>
                        <div x-show="staffTemp" x-cloak class="rounded-xl border border-amber-300 bg-amber-50 p-3 text-sm dark:border-amber-500/30 dark:bg-amber-500/10">
                            <p class="font-bold text-amber-800 dark:text-amber-200">Account created</p>
                            <p class="mt-1 text-amber-700 dark:text-amber-200">Share this temporary password with the teammate:</p>
                            <p class="mt-2 rounded-lg bg-white px-3 py-2 font-mono text-sm font-bold text-zinc-900 dark:bg-zinc-950 dark:text-white" x-text="staffTemp"></p>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 border-t border-zinc-200 px-5 py-4 dark:border-white/10">
                        <button type="button" @click="closeStaff()" class="btn-ghost">Close</button>
                        <button type="button" @click="saveStaff()" :disabled="saving" class="btn-brand">Grant access</button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- ============================== REMINDER MODAL ============================== --}}
    <div x-show="reminderForm" x-cloak class="fixed inset-0 z-50 grid place-items-center overflow-y-auto bg-zinc-950/60 p-4" @keydown.escape.window="reminderForm = null">
        <div class="bt-fade-in w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-zinc-900" @click.outside="reminderForm = null">
            <template x-if="reminderForm">
                <div>
                    <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-4 dark:border-white/10">
                        <h2 class="font-display text-lg font-extrabold">Send a reminder</h2>
                        <button type="button" @click="reminderForm = null" class="grid size-9 place-items-center rounded-full hover:bg-zinc-100 dark:hover:bg-white/10">✕</button>
                    </div>
                    <div class="grid gap-4 px-5 py-5">
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Invoice</span>
                            <x-tools.picker x-model="reminderForm.document_id" options="openInvoices" track="id" display="o => o.number + ' · ' + (o.contact_name || '') + ' · ' + Number(o.balance).toLocaleString()" placeholder="Select an invoice" />
                        </label>
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Channel</span>
                            <x-tools.picker x-model="reminderForm.channel" options="[{ id: 'email', name: 'Email' }, { id: 'whatsapp', name: 'WhatsApp' }]" track="id" display="o => o.name" placeholder="Email" />
                        </label>
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Recipient (optional)</span><input x-model="reminderForm.recipient" class="field" placeholder="Defaults to the customer's contact details" /></label>
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Message</span><textarea x-model="reminderForm.message" rows="4" class="field" placeholder="Leave blank for a ready-made message"></textarea></label>
                    </div>
                    <div class="flex justify-end gap-2 border-t border-zinc-200 px-5 py-4 dark:border-white/10">
                        <button type="button" @click="reminderForm = null" class="btn-ghost">Cancel</button>
                        <button type="button" @click="sendReminder()" :disabled="saving" class="btn-brand">Send reminder</button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- ============================== BUSINESS MODAL ============================== --}}
    <div x-show="businessForm" x-cloak class="fixed inset-0 z-50 grid place-items-center overflow-y-auto bg-zinc-950/60 p-4" @keydown.escape.window="businessForm = null">
        <div class="bt-fade-in w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-zinc-900" @click.outside="businessForm = null">
            <template x-if="businessForm">
                <div>
                    <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-4 dark:border-white/10">
                        <h2 class="font-display text-lg font-extrabold">New business</h2>
                        <button type="button" @click="businessForm = null" class="grid size-9 place-items-center rounded-full hover:bg-zinc-100 dark:hover:bg-white/10">✕</button>
                    </div>
                    <div class="grid gap-4 px-5 py-5 sm:grid-cols-2">
                        <label class="flex flex-col gap-1.5 sm:col-span-2"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Business name</span><input x-model="businessForm.name" class="field" autofocus /></label>
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Phone</span><input x-model="businessForm.phone" class="field" /></label>
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Email</span><input x-model="businessForm.email" type="email" class="field" /></label>
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Currency</span><input x-model="businessForm.currency" class="field" placeholder="UGX" /></label>
                        <label class="flex flex-col gap-1.5 sm:col-span-2"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Address</span><input x-model="businessForm.address" class="field" /></label>
                    </div>
                    <div class="flex justify-end gap-2 border-t border-zinc-200 px-5 py-4 dark:border-white/10">
                        <button type="button" @click="businessForm = null" class="btn-ghost">Cancel</button>
                        <button type="button" @click="saveBusiness()" :disabled="saving" class="btn-brand">Create business</button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- ============================== PREMIUM / UPGRADE ============================== --}}
    <div x-show="upgradeOpen" x-cloak class="fixed inset-0 z-50 grid place-items-center overflow-y-auto bg-zinc-950/60 p-4" @keydown.escape.window="upgradeOpen = false">
        <div class="bt-fade-in w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-zinc-900" @click.outside="upgradeOpen = false">
            <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-4 dark:border-white/10">
                <div class="flex items-center gap-2">
                    <span class="grid size-9 place-items-center rounded-xl bg-gradient-to-br from-amber-400 to-amber-600 text-white">
                        <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V7a4 4 0 018 0v4"/></svg>
                    </span>
                    <div>
                        <h2 class="font-display text-lg font-extrabold leading-none">Business Tools Premium</h2>
                        <p class="text-[11px] text-zinc-500" x-text="plan.premium ? ('Active — ' + (plan.until_human || '')) : 'Unlock every feature and lift the caps'"></p>
                    </div>
                </div>
                <button type="button" @click="upgradeOpen = false" class="grid size-9 place-items-center rounded-full hover:bg-zinc-100 dark:hover:bg-white/10">✕</button>
            </div>

            <div class="px-5 py-5">
                <div class="grid gap-2.5">
                    <template x-for="row in planRows" :key="'pl'+row.key">
                        <div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold uppercase tracking-wider text-zinc-500" x-text="row.label"></span>
                                <span class="font-semibold" x-text="row.max === null ? (row.used + ' · unlimited') : (row.used + ' / ' + row.max)"></span>
                            </div>
                            <div class="mt-1 h-2 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-white/10">
                                <div class="h-full rounded-full transition-all" :class="row.max === null ? 'bg-emerald-500' : (row.used >= row.max ? 'bg-red-500' : 'bg-brand-600')"
                                    :style="'width:' + rowPercent(row) + '%'"></div>
                            </div>
                        </div>
                    </template>
                </div>

                <template x-if="!plan.premium">
                    <div class="mt-5">
                        <p class="text-xs font-bold uppercase tracking-wider text-zinc-500">Choose a plan</p>
                        <div class="mt-2 grid gap-2 sm:grid-cols-2">
                            <button type="button" @click="upgradeCycle = 'daily'" :class="upgradeCycle === 'daily' ? 'border-brand-500 bg-brand-50 dark:bg-brand-500/10' : 'border-zinc-200 dark:border-white/10'" class="rounded-xl border p-3 text-left">
                                <p class="text-sm font-extrabold">Daily</p>
                                <p class="text-xs text-zinc-500">UGX 800 per day</p>
                            </button>
                            <button type="button" @click="upgradeCycle = 'monthly'" :class="upgradeCycle === 'monthly' ? 'border-brand-500 bg-brand-50 dark:bg-brand-500/10' : 'border-zinc-200 dark:border-white/10'" class="rounded-xl border p-3 text-left">
                                <p class="text-sm font-extrabold">Monthly <span class="pill-green">Save</span></p>
                                <p class="text-xs text-zinc-500">UGX 10,000 per month</p>
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <div class="flex items-center justify-between gap-3 border-t border-zinc-200 px-5 py-4 dark:border-white/10">
                <div class="text-xs text-zinc-500">
                    Wallet: <span class="font-bold text-zinc-800 dark:text-zinc-200" x-text="'UGX ' + Number(plan.wallet || 0).toLocaleString()"></span>
                </div>
                <div class="flex items-center gap-2">
                    <a :href="routes.wallet" class="btn-ghost btn-sm">Top up</a>
                    <button type="button" @click="subscribe()" :disabled="saving" class="btn-brand btn-sm" x-text="plan.premium ? 'Extend plan' : ('Pay UGX ' + Number(plan.prices[upgradeCycle]).toLocaleString())"></button>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================== SUGGESTIONS (IDEAS & VOTING) ============================== --}}
    <div class="fixed bottom-5 right-5 z-40 flex flex-col items-end gap-3">
        <div x-show="ideasOpen" x-cloak x-transition.opacity
            class="flex max-h-[70vh] w-[22rem] flex-col overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl dark:border-white/10 dark:bg-zinc-900">
            <div class="flex items-center justify-between border-b border-zinc-100 px-4 py-3 dark:border-white/10">
                <div>
                    <p class="text-sm font-extrabold">Ideas &amp; voting</p>
                    <p class="text-[11px] text-zinc-500">Suggest a feature — popular ideas ship.</p>
                </div>
                <button type="button" @click="ideasOpen = false" class="grid size-8 place-items-center rounded-full hover:bg-zinc-100 dark:hover:bg-white/10">✕</button>
            </div>

            <div class="bt-scroll flex-1 overflow-y-auto px-3 py-3">
                <template x-for="s in suggestions" :key="'sg'+s.id">
                    <div class="mb-2 rounded-xl border border-zinc-100 p-3 dark:border-white/10">
                        <div class="flex items-start gap-3">
                            <button type="button" @click="voteSuggestion(s)"
                                :class="s.voted ? 'border-brand-500 bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-200' : 'border-zinc-200 text-zinc-500 dark:border-white/15'"
                                class="flex w-11 shrink-0 flex-col items-center rounded-lg border px-2 py-1.5 font-bold transition hover:border-brand-400">
                                <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M6 15l6-6 6 6"/></svg>
                                <span class="text-xs" x-text="s.votes"></span>
                            </button>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <p class="truncate text-sm font-bold" x-text="s.title"></p>
                                    <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide"
                                        :class="s.status === 'shipped' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' : (s.status === 'planned' ? 'bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-200' : 'bg-zinc-100 text-zinc-500 dark:bg-white/10 dark:text-zinc-300')"
                                        x-text="s.status"></span>
                                </div>
                                <p x-show="s.body" class="mt-0.5 line-clamp-2 text-xs text-zinc-500" x-text="s.body"></p>
                                <p class="mt-1 text-[10px] text-zinc-400"><span x-text="s.author"></span> · <span x-text="s.created_at"></span></p>
                                <p x-show="s.reply" class="mt-1 rounded-lg bg-emerald-50 px-2 py-1 text-[11px] text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300" x-text="'Reply: ' + s.reply"></p>
                            </div>
                        </div>
                    </div>
                </template>
                <p x-show="!suggestions.length" class="py-8 text-center text-sm text-zinc-500">No ideas yet — be the first!</p>
            </div>

            <form class="border-t border-zinc-100 px-3 py-3 dark:border-white/10" @submit.prevent="submitIdea()">
                <input x-model="ideaForm.title" maxlength="140" placeholder="I wish Business Tools could…" class="field" />
                <textarea x-model="ideaForm.body" rows="2" placeholder="Add detail (optional)" class="field mt-2"></textarea>
                <button type="submit" :disabled="saving || !ideaForm.title" class="btn-brand btn-sm mt-2 w-full">Share idea</button>
            </form>
        </div>

        <button type="button" @click="toggleIdeas()" class="relative grid size-12 place-items-center rounded-full bg-brand-600 text-white shadow-xl transition hover:bg-brand-700" aria-label="Ideas and voting">
            <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 3a6 6 0 00-4 10.5V17h8v-3.5A6 6 0 0012 3z"/><path d="M9 21h6"/></svg>
            <span x-show="!ideasOpen && suggestionCount" x-cloak x-text="suggestionCount"
                class="absolute -right-1 -top-1 grid min-w-[20px] place-items-center rounded-full bg-amber-500 px-1 text-[10px] font-bold leading-5 text-white"></span>
        </button>
    </div>

    {{-- ============================== SUBMIT PRELOADER ============================== --}}
    <div x-show="saving" x-cloak class="fixed inset-0 z-[70] grid place-items-center bg-zinc-950/30 backdrop-blur-[1px]">
        <div class="flex items-center gap-3 rounded-2xl bg-white px-6 py-4 shadow-2xl dark:bg-zinc-800">
            <svg class="size-6 animate-spin text-brand-600" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" stroke-opacity=".2"/><path d="M21 12a9 9 0 00-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
            <span class="text-sm font-bold">Working…</span>
        </div>
    </div>

    {{-- ============================== TOAST ============================== --}}
    <div x-show="toast.open" x-cloak x-transition.opacity class="fixed bottom-5 left-1/2 z-[60] -translate-x-1/2">
        <div class="flex items-center gap-3 rounded-full px-5 py-3 text-sm font-semibold text-white shadow-xl" :class="toast.kind === 'err' ? 'bg-red-600' : 'bg-zinc-900 dark:bg-white dark:text-zinc-900'"><span x-text="toast.msg"></span></div>
    </div>
</div>

<script>
function paintThemeIcons(){
    var d = document.documentElement.classList.contains('dark');
    document.querySelectorAll('.theme-icon-moon').forEach(function(e){ e.classList.toggle('hidden', d); });
    document.querySelectorAll('.theme-icon-sun').forEach(function(e){ e.classList.toggle('hidden', !d); });
}
function toggleTheme(){
    var h = document.documentElement, next = h.classList.contains('dark') ? 'light' : 'dark';
    h.classList.toggle('dark', next === 'dark');
    try { localStorage.setItem('flux.appearance', next); localStorage.setItem('ttryy-theme', next); } catch(e){}
    paintThemeIcons();
}
paintThemeIcons();

window.btPicker = function (cfg) {
    return {
        value: null,
        open: false,
        options: cfg.options || [],
        track: cfg.track || 'id',
        labelKey: cfg.labelKey || 'name',
        placeholder: cfg.placeholder || 'Select…',
        displayFn: cfg.display || null,
        label: function (o) {
            if (!o) return '';
            return this.displayFn ? this.displayFn(o) : o[this.labelKey];
        },
        selected: function () {
            var v = this.value;
            return (this.options || []).find(function (o) { return o[this.track] === v; }.bind(this)) || null;
        }
    };
};

window.btTools = function (boot) {
    var email = (boot.user && boot.user.email) ? boot.user.email : 'anon';

    return {
        boot: boot,
        tab: boot.startTab || 'overview',
        composeOpen: !!boot.compose,
        composeType: boot.composeType || 'invoice',
        drawerOpen: false,
        drawerDoc: null,
        previewOpen: false,
        previewDoc: null,
        lastSale: null,
        lastChange: 0,
        busy: false,
        saving: false,
        toast: { open: false, msg: '', kind: 'ok' },

        settings: boot.settings,
        contacts: boot.contacts,
        items: boot.items,
        expenses: boot.expenses,
        documents: boot.documents,
        stats: boot.stats,
        months: boot.months,
        report: boot.report,
        routes: boot.routes,
        fx: boot.fx || { base: 'USD', rates: { USD: 1 }, stale: true },
        displayCurrency: (boot.fx && boot.fx.base) || 'USD',
        conversions: boot.conversions || {},
        cloneable: boot.cloneable || [],
        businesses: boot.businesses || [],
        staff: boot.staff || [],
        reminders: boot.reminders || [],
        activity: boot.activity || [],
        notifications: boot.notifications || { unread: 0, items: [] },
        accounts: boot.accounts || [],
        bankAccounts: boot.bankAccounts || [],
        bankTransactions: boot.bankTransactions || [],
        employees: boot.employees || [],
        payslips: boot.payslips || [],
        journal: boot.journal || [],
        plan: boot.plan || { premium: false, plan: 'free', limits: {}, usage: {}, prices: { daily: 800, monthly: 10000 }, premium_tabs: [] },
        suggestions: boot.suggestions || [],
        upgradeOpen: false,
        upgradeCycle: 'monthly',
        ideasOpen: false,
        ideaForm: { title: '', body: '' },

        sidebarCollapsed: false,
        businessOpen: false,
        notifOpen: false,

        search: '',
        docStatus: 'all',
        itemLowOnly: false,
        expStatus: 'all',
        activityQuery: '',
        reportRange: { from: boot.report.from, to: boot.report.to },

        draft: null,
        contactForm: null,
        itemForm: null,
        expenseForm: null,
        adjustForm: null,
        historyOpen: false,
        historyItem: null,
        historyMoves: [],
        historyLoading: false,
        staffForm: null,
        staffTemp: null,
        reminderForm: null,
        businessForm: null,
        payForm: { amount: 0, payment_method: 'cash', bank_account_id: null },
        creditForm: { amount: 0 },
        accountForm: null,
        bankForm: null,
        bankTxForm: null,
        bankTxFor: null,
        bankTxFilter: null,
        employeeForm: null,
        payslipForm: null,
        obscure: false,

        cart: [],
        posQuery: '',
        posPay: 'cash',
        posTendered: 0,
        posCustomer: null,
        posInStock: false,

        init: function () {
            var self = this;
            try { this.sidebarCollapsed = localStorage.getItem('bt.sidebar') === '1'; } catch (e) {}
            try { this.obscure = localStorage.getItem('bt.obscure') === '1'; } catch (e) {}
            var saved = null;
            try { saved = localStorage.getItem('bt.display'); } catch (e) {}
            if (saved && this.fx.rates[saved]) this.displayCurrency = saved;
            if (boot.compose) { this.newDraft(this.composeType); this.persistDraft(); }
            if (boot.openDoc) { this.openDrawer(boot.openDoc); }
            this.$watch('draft', function () { self.persistDraft(); self.syncLines(); }, { deep: true });
        },

        toggleObscure: function () {
            this.obscure = !this.obscure;
            try { localStorage.setItem('bt.obscure', this.obscure ? '1' : '0'); } catch (e) {}
        },

        /* Display currency only — amounts stay in the business's base currency. */
        setCurrency: function (code) {
            this.displayCurrency = code;
            try { localStorage.setItem('bt.display', code); } catch (e) {}
        },

        /* Pull the latest rates on demand. */
        refreshFx: async function () {
            var res = await this.send(this.routes.fx + '?fresh=1', 'GET');
            if (res && res.fx) {
                this.fx = res.fx;
                this.notify('Exchange rates updated.', 'ok');
            }
        },

        toggleSidebar: function () {
            this.sidebarCollapsed = !this.sidebarCollapsed;
            try { localStorage.setItem('bt.sidebar', this.sidebarCollapsed ? '1' : '0'); } catch (e) {}
        },

        badgeCount: function (key) {
            var map = {
                invoices: this.stats.overdue,
                quotations: this.stats.pending_quotes,
                inventory: this.stats.low_stock,
                reminders: this.openInvoices.length,
                staff: this.stats.staff > 1 ? this.stats.staff : 0
            };
            return map[key] || 0;
        },

        notificationTone: function (kind) {
            return { invoice: 'bg-brand-500', payment: 'bg-emerald-500', team: 'bg-amber-500' }[kind] || 'bg-zinc-400';
        },

        /* ---------------- plans, premium gating, ideas ---------------- */
        isLocked: function (tab) {
            return !this.plan.premium && (this.plan.premium_tabs || []).indexOf(tab) !== -1;
        },
        openUpgrade: function () { this.upgradeOpen = true; },
        planRows: function () {
            var u = this.plan.usage || {}, l = this.plan.limits || {};
            return [
                { key: 'businesses', label: 'Businesses', used: u.businesses || 0, max: l.businesses === undefined ? null : l.businesses },
                { key: 'staff', label: 'Team members', used: u.staff || 0, max: l.staff === undefined ? null : l.staff },
                { key: 'items', label: 'Products', used: u.items || 0, max: l.items === undefined ? null : l.items },
                { key: 'documents', label: 'Documents', used: u.documents || 0, max: l.documents === undefined ? null : l.documents }
            ];
        },
        rowPercent: function (row) {
            if (row.max === null || row.max === undefined || row.max < 1) return 100;
            return Math.min(100, Math.round(row.used / row.max * 100));
        },
        planPercent: function () {
            if (this.plan.premium) return 100;
            var rows = this.planRows(), worst = 0;
            var self = this;
            rows.forEach(function (r) { if (r.max) worst = Math.max(worst, r.used / r.max); });
            return Math.min(100, Math.round(worst * 100));
        },
        planTone: function () {
            if (this.plan.premium) return 'bg-emerald-500';
            return this.planPercent() >= 100 ? 'bg-red-500' : (this.planPercent() >= 60 ? 'bg-amber-500' : 'bg-brand-600');
        },
        planSummaryLine: function () {
            if (this.plan.premium) return (this.plan.days_left || 0) + ' days left';
            return this.planPercent() + '% of free limits used';
        },
        planTitle: function () {
            var rows = this.planRows(), self = this;
            return rows.map(function (r) { return r.label + ': ' + r.used + (r.max === null ? ' (unlimited)' : ' / ' + r.max); }).join('\n');
        },
        subscribe: async function () {
            var d = await this.send(this.routes.subscribe, 'POST', { cycle: this.upgradeCycle });
            if (d) {
                if (d.state) this.applyState(d.state);
                this.upgradeOpen = false;
                this.notify('Premium activated.', 'ok');
            }
        },
        toggleIdeas: function () {
            this.ideasOpen = !this.ideasOpen;
            if (this.ideasOpen) this.loadSuggestions();
        },
        loadSuggestions: async function () {
            try {
                var res = await fetch(this.routes.suggestions, { headers: { 'Accept': 'application/json' } });
                if (res.ok) { var d = await res.json(); this.suggestions = d.suggestions || []; }
            } catch (e) {}
        },
        submitIdea: async function () {
            if (!this.ideaForm.title) return;
            var d = await this.send(this.routes.suggestionStore, 'POST', { title: this.ideaForm.title, body: this.ideaForm.body });
            if (d) {
                this.suggestions = d.suggestions || this.suggestions;
                this.ideaForm = { title: '', body: '' };
                this.notify('Idea shared — thanks!', 'ok');
            }
        },
        voteSuggestion: async function (s) {
            var d = await this.send(this.routes.suggestionVote.replace('_ID_', s.id), 'POST');
            if (d && d.suggestions) this.suggestions = d.suggestions;
        },
        get suggestionCount() { return this.suggestions.length; },

        get openInvoices() {
            return this.documents.filter(function (d) { return d.type === 'invoice' && d.balance > 0; });
        },
        get openBills() {
            return this.documents.filter(function (d) { return d.type === 'purchase_invoice' && d.balance > 0; });
        },
        get docTabs() {
            return {
                quotations: { type: 'quotation', label: 'Quotations', eyebrow: 'Sales documents' },
                salesorders: { type: 'sales_order', label: 'Sales Orders', eyebrow: 'Sales documents' },
                deliverynotes: { type: 'delivery_note', label: 'Delivery Notes', eyebrow: 'Sales documents' },
                invoices: { type: 'invoice', label: 'Invoicing', eyebrow: 'Sales documents' },
                receipts: { type: 'receipt', label: 'Receipts', eyebrow: 'Sales documents' },
                creditnotes: { type: 'credit_note', label: 'Credit Notes', eyebrow: 'Sales documents' },
                purchasequotes: { type: 'purchase_quote', label: 'Purchase Quotes', eyebrow: 'Purchase documents' },
                purchaseorders: { type: 'purchase_order', label: 'Purchase Orders', eyebrow: 'Purchase documents' },
                purchaseinvoices: { type: 'purchase_invoice', label: 'Purchase Invoices', eyebrow: 'Purchase documents' },
                purchasepayments: { type: 'purchase_payment', label: 'Purchase Payments', eyebrow: 'Purchase documents' }
            };
        },
        get settlementType() { return ['receipt', 'credit_note', 'purchase_payment'].indexOf(this.tabDocType) !== -1; },
        get isPurchaseTab() { return ['purchase_quote', 'purchase_order', 'purchase_invoice', 'purchase_payment'].indexOf(this.tabDocType) !== -1; },
        docStatuses: function (type) {
            var sets = {
                quotation: ['all', 'draft', 'sent', 'accepted', 'declined'],
                purchase_quote: ['all', 'draft', 'sent', 'accepted', 'declined'],
                sales_order: ['all', 'draft', 'sent', 'fulfilled', 'declined'],
                purchase_order: ['all', 'draft', 'sent', 'fulfilled', 'declined'],
                delivery_note: ['all', 'draft', 'posted', 'fulfilled'],
                invoice: ['all', 'unpaid', 'partial', 'paid'],
                purchase_invoice: ['all', 'unpaid', 'partial', 'paid']
            };
            return sets[type] || ['all', 'posted'];
        },
        convertOptions: function (type) {
            return (this.conversions && this.conversions[type]) || [];
        },
        canClone: function (type) {
            return (this.cloneable || []).indexOf(type) !== -1;
        },
        typeLabel: function (t) {
            return (t || '').split('_').map(function (w) { return w.charAt(0).toUpperCase() + w.slice(1); }).join(' ');
        },
        settledHint: function (d) {
            if (d.is_bill) return d.last_receipt_date || '—';
            if (['receipt', 'credit_note', 'purchase_payment'].indexOf(d.type) !== -1) return d.issue_date;
            return '—';
        },
        get filteredActivity() {
            var s = this.activityQuery.trim().toLowerCase();
            if (!s) return this.activity;
            return this.activity.filter(function (a) {
                return (a.description + ' ' + a.action + ' ' + a.user_name + ' ' + (a.ip || '')).toLowerCase().indexOf(s) !== -1;
            });
        },
        get bellNotifications() { return (this.notifications.items || []).slice(0, 20); },
        get lowStockCount() { return this.items.filter(function (i) { return i.stock > 0 && i.low; }).length; },
        get outOfStockCount() { return this.items.filter(function (i) { return i.stock <= 0; }).length; },

        margin: function (it) {
            var sell = Number(it.sale_price) || 0, cost = Number(it.cost_price) || 0;
            if (sell <= 0) return 0;
            return Math.round(((sell - cost) / sell) * 100);
        },
        stockPill: function (it) {
            if (it.stock <= 0) return 'pill-red';
            return it.low ? 'pill-amber' : 'pill-green';
        },
        get itemMargin() {
            var f = this.itemForm; if (!f) return 0;
            var sell = Number(f.sale_price) || 0, cost = Number(f.cost_price) || 0;
            if (sell <= 0) return 0;
            return Math.round(((sell - cost) / sell) * 100);
        },

        /* ---------------- utilities ---------------- */
        fmt: function (n, dec) { return Number(n || 0).toLocaleString('en-US', { minimumFractionDigits: dec || 0, maximumFractionDigits: dec || 0 }); },
        money: function (n) { return this.displayCurrency + ' ' + this.fmt(this.convert(n), this.moneyDecimals()); },
        convert: function (n) {
            n = Number(n) || 0;
            var base = (this.fx && this.fx.base) || 'USD';
            var rates = (this.fx && this.fx.rates) || {};
            var from = rates[base], to = rates[this.displayCurrency];
            if (!from || !to || base === this.displayCurrency) return n;
            return n / from * to;
        },
        moneyDecimals: function () {
            var base = (this.fx && this.fx.base) || 'USD';
            if (this.displayCurrency === base) return 0;
            return ['UGX', 'KES', 'TZS', 'JPY'].indexOf(this.displayCurrency) !== -1 ? 0 : 2;
        },
        rateHint: function () {
            var base = (this.fx && this.fx.base) || 'USD';
            var rates = (this.fx && this.fx.rates) || {};
            if (!rates[this.displayCurrency] || this.displayCurrency === base) return 'Amounts are stored in ' + base;
            return '1 ' + base + ' = ' + Number(rates[this.displayCurrency]).toLocaleString('en-US', { maximumFractionDigits: 2 }) + ' ' + this.displayCurrency;
        },
        footerFor: function (type) {
            if (type === 'quotation' || type === 'purchase_quote') return this.settings.quotation_footer || 'Prices valid for 30 days.';
            if (type === 'receipt' || type === 'purchase_payment') return this.settings.receipt_footer || 'Thank you for your business!';
            if (type === 'invoice' || type === 'purchase_invoice' || type === 'credit_note') return this.settings.invoice_footer || 'Payment is due as per the terms above.';
            return this.settings.receipt_footer || 'Thank you for your business!';
        },
        today: function () { return new Date().toISOString().slice(0, 10); },
        ucfirst: function (s) { return s ? s.charAt(0).toUpperCase() + s.slice(1) : ''; },
        pct: function (part, whole) { return whole > 0 ? Math.round((Number(part || 0) / whole) * 100) : 0; },
        barH: function (v) {
            var max = 1;
            this.months.forEach(function (m) { max = Math.max(max, m.in, m.out); });
            return Math.round((Number(v || 0) / max) * 100);
        },
        catWidth: function (v) {
            var max = 1;
            (this.report.pnl.by_category || []).forEach(function (r) { max = Math.max(max, r.amount); });
            return Math.round((Number(v || 0) / max) * 100);
        },
        scrollNav: function (dir) { if (this.$refs.navtrack) this.$refs.navtrack.scrollBy({ left: dir * 240, behavior: 'smooth' }); },
        notify: function (msg, kind) {
            var self = this;
            this.toast = { open: true, msg: msg, kind: kind || 'ok' };
            clearTimeout(this._toastTimer);
            this._toastTimer = setTimeout(function () { self.toast.open = false; }, 3400);
        },

        send: async function (url, method, body) {
            this.busy = true;
            var mutating = (method || 'POST') !== 'GET';
            if (mutating) this.saving = true;
            var csrf = document.querySelector('meta[name="csrf-token"]');
            try {
                var res = await fetch(url, {
                    method: method || 'POST',
                    headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf ? csrf.content : '' },
                    body: body ? JSON.stringify(body) : null
                });
                var data = {};
                try { data = await res.json(); } catch (e) {}
                if (!res.ok) {
                    var msg = data.message || 'Something went wrong.';
                    if (data.errors) { var k = Object.keys(data.errors)[0]; if (k) msg = data.errors[k][0]; }
                    this.notify(msg, 'err');
                    return null;
                }
                if (data.message) this.notify(data.message, 'ok');
                return data;
            } catch (e) {
                this.notify('Network error — please retry.', 'err');
                return null;
            } finally {
                this.busy = false;
                if (mutating) this.saving = false;
            }
        },

        applyState: function (d) {
            if (!d) return;
            if (d.user) this.boot.user = d.user;
            if (d.business) this.boot.business = d.business;
            if (d.settings) this.settings = d.settings;
            if (d.contacts) this.contacts = d.contacts;
            if (d.items) this.items = d.items;
            if (d.expenses) this.expenses = d.expenses;
            if (d.documents) this.documents = d.documents;
            if (d.stats) this.stats = d.stats;
            if (d.months) this.months = d.months;
            if (d.report) this.report = d.report;
            if (d.businesses) this.businesses = d.businesses;
            if (d.staff) this.staff = d.staff;
            if (d.reminders) this.reminders = d.reminders;
            if (d.activity) this.activity = d.activity;
            if (d.notifications) this.notifications = d.notifications;
            if (d.accounts) this.accounts = d.accounts;
            if (d.bankAccounts) this.bankAccounts = d.bankAccounts;
            if (d.bankTransactions) this.bankTransactions = d.bankTransactions;
            if (d.employees) this.employees = d.employees;
            if (d.payslips) this.payslips = d.payslips;
            if (d.journal) this.journal = d.journal;
            if (d.plan) this.plan = d.plan;
            if (d.suggestions) this.suggestions = d.suggestions;
        },

        refreshState: async function () {
            try {
                var res = await fetch(this.routes.state, { headers: { 'Accept': 'application/json' } });
                if (!res.ok) return;
                this.applyState(await res.json());
            } catch (e) {}
        },

        go: function (tab) {
            this.tab = tab;
            this.composeOpen = false;
            this.drawerOpen = false;
            if (['reports', 'cashflow', 'pnl', 'balancesheet'].indexOf(tab) !== -1) this.loadReport();
            if (tab === 'notifications') this.loadNotifications();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        get rollingTab() { return !!this.docTabs[this.tab]; },
        get tabDocType() { return (this.docTabs[this.tab] || {}).type || 'invoice'; },
        get tabDocMeta() { return this.docTabs[this.tab] || this.docTabs.invoices; },

        get quickResults() {
            var s = this.search.trim().toLowerCase();
            if (s.length < 2) return [];
            var docs = this.documents.filter(function (d) { return (d.number + ' ' + (d.contact_name || '')).toLowerCase().indexOf(s) !== -1; })
                .slice(0, 6).map(function (d) { return { action: 'doc', id: d.id, label: d.number, sub: d.label + ' · ' + (d.contact_name || 'No contact') }; });
            var cons = this.contacts.filter(function (c) { return c.name.toLowerCase().indexOf(s) !== -1; }).slice(0, 5)
                .map(function (c) { return { action: 'contact', id: c.id, label: c.name, sub: c.kind }; });
            return docs.concat(cons).slice(0, 10);
        },
        runQuick: function (r) {
            if (r.action === 'doc') { if (this.tab !== 'invoices') this.tab = 'invoices'; this.openDrawer(r.id); }
            else { var c = this.contacts.find(function (x) { return x.id === r.id; }); this.tab = (c && c.kind === 'supplier') ? 'suppliers' : 'customers'; }
            this.search = '';
        },

        /* ---------------- documents ---------------- */
        filterDocs: function (type) {
            var s = this.search.toLowerCase(), status = this.docStatus;
            return this.documents.filter(function (d) { return d.type === type; })
                .filter(function (d) { return status === 'all' || d.status === status; })
                .filter(function (d) { return !s || (d.number + ' ' + (d.contact_name || '') + ' ' + d.status).toLowerCase().indexOf(s) !== -1; });
        },
        openDrawer: async function (id) {
            var d = await this.send(this.routes.apiDoc.replace('_ID_', id), 'GET');
            if (d && d.document) {
                this.drawerDoc = d.document;
                this.payForm = { amount: d.document.balance, payment_method: 'cash', bank_account_id: null };
                this.creditForm = { amount: d.document.balance };
                this.drawerOpen = true;
            }
        },
        openPreview: async function (doc) {
            if (!doc) return;
            if (doc.lines && doc.qr) { this.previewDoc = doc; this.previewOpen = true; this.lastSale = null; return; }
            var d = await this.send(this.routes.apiDoc.replace('_ID_', doc.id), 'GET');
            if (d && d.document) { this.previewDoc = d.document; this.previewOpen = true; this.lastSale = null; }
        },
        setDocStatus: async function (doc, status) {
            var d = await this.send(this.routes.docStatus.replace('_ID_', doc.id), 'PATCH', { status: status });
            if (d && d.document) { this.drawerDoc = Object.assign({}, this.drawerDoc, d.document); await this.refreshState(); }
        },
        convertTo: async function (doc, target) {
            var d = await this.send(this.routes.docConvert.replace('_ID_', doc.id), 'POST', { target: target });
            if (d) {
                await this.refreshState();
                if (d.document) { this.drawerOpen = false; this.openPreview(d.document); }
            }
        },
        cloneDoc: async function (doc) {
            var d = await this.send(this.routes.docClone.replace('_ID_', doc.id), 'POST');
            if (d) {
                await this.refreshState();
                if (d.document) { this.drawerOpen = false; this.openDrawer(d.document.id); }
            }
        },
        deleteDoc: async function (doc) {
            if (!confirm('Delete ' + doc.number + '? This cannot be undone.')) return;
            var d = await this.send(this.routes.docDestroy.replace('_ID_', doc.id), 'DELETE');
            if (d) { this.drawerOpen = false; await this.refreshState(); }
        },
        recordPayment: async function (doc) {
            var isPurchase = doc.type === 'purchase_invoice';
            var d = await this.send(this.routes.docStore.replace('_TYPE_', isPurchase ? 'purchase_payments' : 'receipts'), 'POST', {
                issue_date: this.today(), invoice_id: doc.id, amount: Number(this.payForm.amount) || 0,
                payment_method: isPurchase ? (this.payForm.payment_method || 'bank') : this.payForm.payment_method,
                bank_account_id: this.payForm.bank_account_id || null
            });
            if (d) {
                await this.refreshState();
                var fresh = this.documents.find(function (x) { return x.id === doc.id; });
                if (fresh) { this.drawerDoc = Object.assign({}, this.drawerDoc, fresh); this.payForm.amount = fresh.balance; }
            }
        },
        issueCreditNote: async function (doc) {
            var d = await this.send(this.routes.docStore.replace('_TYPE_', 'credit_notes'), 'POST', {
                issue_date: this.today(), invoice_id: doc.id, amount: Number(this.creditForm.amount) || 0
            });
            if (d) {
                await this.refreshState();
                var fresh = this.documents.find(function (x) { return x.id === doc.id; });
                if (fresh) { this.drawerDoc = Object.assign({}, this.drawerDoc, fresh); }
            }
        },

        /* ---------------- composer + drafts ---------------- */
        draftKey: function () { return 'bt.draft.' + email + '.' + this.composeType; },
        newDraft: function (type) {
            this.composeType = type;
            var saved = null;
            try { saved = JSON.parse(localStorage.getItem('bt.draft.' + email + '.' + type) || 'null'); } catch (e) {}
            if (saved && (saved.invoice_id || (saved.lines && saved.lines.length))) { this.draft = Object.assign(this.draftDefaults(), saved); this.notify('Draft restored from this device.', 'ok'); return; }
            this.draft = this.draftDefaults();
        },
        draftDefaults: function () {
            return {
                contact_id: null, issue_date: this.today(), due_date: '', valid_until: '',
                discount: 0, tax_rate: this.settings.tax_rate || 0, notes: '',
                invoice_id: null, amount: 0, payment_method: 'cash', bank_account_id: null,
                lines: [{ item_id: null, description: '', qty: 1, unit_price: 0 }]
            };
        },
        openComposer: function (type) { this.composeOpen = true; this.drawerOpen = false; this.newDraft(type); },
        persistDraft: function () { if (!this.draft || !this.composeOpen) return; try { localStorage.setItem(this.draftKey(), JSON.stringify(this.draft)); } catch (e) {} },
        clearDraft: function () { try { localStorage.removeItem(this.draftKey()); } catch (e) {} },
        discardDraft: function () { this.clearDraft(); this.newDraft(this.composeType); this.clearDraft(); this.notify('Draft discarded.'); },
        addLine: function () { this.draft.lines.push({ item_id: null, description: '', qty: 1, unit_price: 0 }); },
        removeLine: function (i) { this.draft.lines.splice(i, 1); if (!this.draft.lines.length) this.addLine(); },
        pickItem: function (line) { var it = this.items.find(function (x) { return x.id === line.item_id; }); if (it) { line.description = it.name; line.unit_price = it.sale_price; } },
        lineTotal: function (l) { return Math.round((Number(l.qty) || 0) * (Number(l.unit_price) || 0)); },
        get composeLabel() { return (this.docTabs[this.tab] && this.docTabs[this.tab].type === this.composeType ? this.docTabs[this.tab].label.replace(/s$/, '') : this.ucfirst(this.composeType.replace(/_/g, ' '))); },
        get settlementDraft() { return ['receipt', 'credit_note', 'purchase_payment'].indexOf(this.composeType) !== -1; },
        get draftBills() {
            var type = this.composeType === 'purchase_payment' ? 'purchase_invoice' : 'invoice';
            return this.documents.filter(function (d) { return d.type === type && d.balance > 0; });
        },
        get draftSubtotal() { return this.draft ? this.draft.lines.reduce((s, l) => s + this.lineTotal(l), 0) : 0; },
        get draftTaxable() { return Math.max(0, this.draftSubtotal - (Number(this.draft && this.draft.discount) || 0)); },
        get draftTax() { return Math.round(this.draftTaxable * ((Number(this.draft && this.draft.tax_rate) || 0)) / 100); },
        get draftTotal() { return this.draftTaxable + this.draftTax; },
        submitDraft: async function () {
            if (!this.draft) return;
            var d;
            if (this.settlementDraft && this.draft.invoice_id) {
                d = await this.send(this.routes.docStore.replace('_TYPE_', this.composeType + 's'), 'POST', {
                    issue_date: this.draft.issue_date, invoice_id: this.draft.invoice_id,
                    amount: Number(this.draft.amount) || 0, payment_method: this.draft.payment_method,
                    bank_account_id: this.draft.bank_account_id || null, notes: this.draft.notes
                });
            } else {
                var lines = this.draft.lines.filter(function (l) { return (l.description || '').trim() !== '' || l.item_id; });
                d = await this.send(this.routes.docStore.replace('_TYPE_', this.composeType + 's'), 'POST', {
                    contact_id: this.draft.contact_id, issue_date: this.draft.issue_date,
                    due_date: this.draft.due_date || null, valid_until: this.draft.valid_until || null,
                    discount: Number(this.draft.discount) || 0, tax_rate: Number(this.draft.tax_rate) || 0,
                    notes: this.draft.notes, lines: lines
                });
            }
            if (d) { this.clearDraft(); this.composeOpen = false; await this.refreshState(); if (d.document && d.document.qr) this.openPreview(d.document); }
        },
        get draftPreviewDoc() {
            var d = this.draft || { lines: [] };
            var c = this.contacts.find(function (x) { return x.id === d.contact_id; });
            var lines = (d.lines || []).filter(function (l) { return (l.description || '').trim() !== '' || l.item_id; })
                .map(function (l) {
                    var qty = Number(l.qty) || 0, price = Number(l.unit_price) || 0;
                    return { description: l.description, item_id: l.item_id, qty: qty, unit_price: price, line_total: qty * price };
                });
            return {
                kind: 'draft', type: this.composeType, label: this.composeLabel,
                number: 'Preview', status: 'draft',
                contact_name: c ? c.name : '',
                issue_date: d.issue_date, due_date: d.due_date, valid_until: d.valid_until,
                payment_method: this.settlementDraft ? (d.payment_method || null) : null,
                lines: lines,
                subtotal: this.draftSubtotal, discount: Number(d.discount) || 0,
                tax_amount: this.draftTax, total: this.draftTotal,
                paid_amount: 0, balance: this.draftTotal,
                receipts: [], notes: d.notes, qr: null, verify_url: null
            };
        },
        previewDraft: function () {
            if (!this.draft) return;
            this.previewDoc = this.draftPreviewDoc;
            this.previewOpen = true;
        },

        /* ---------------- inventory ---------------- */
        filterItems: function () {
            var s = this.search.toLowerCase(), low = this.itemLowOnly;
            return this.items.filter(function (i) { return !low || i.low; })
                .filter(function (i) { return !s || (i.name + ' ' + (i.sku || '')).toLowerCase().indexOf(s) !== -1; });
        },
        openItem: function (item) { this.itemForm = item ? Object.assign({}, item) : { id: null, name: '', sku: '', unit: 'pcs', cost_price: 0, sale_price: 0, stock: 0, reorder_level: 0 }; },
        saveItem: async function () {
            var f = this.itemForm;
            var url = f.id ? this.routes.itemUpdate.replace('_ID_', f.id) : this.routes.itemStore;
            var d = await this.send(url, f.id ? 'PATCH' : 'POST', f);
            if (d) { this.itemForm = null; await this.refreshState(); }
        },
        deleteItem: async function (item) {
            if (!confirm('Delete ' + item.name + '?')) return;
            var d = await this.send(this.routes.itemDestroy.replace('_ID_', item.id), 'DELETE');
            if (d) await this.refreshState();
        },
        openAdjust: function (item, mode) {
            var stock = Number(item.stock) || 0;
            this.adjustForm = { item_id: item.id, name: item.name, stock: stock, unit: item.unit || '', mode: mode || 'set', qty: mode === 'set' ? stock : 0, reason: '' };
        },
        get adjustQtyChange() {
            var a = this.adjustForm; if (!a) return 0;
            var q = Number(a.qty) || 0;
            if (a.mode === 'add') return q;
            if (a.mode === 'remove') return -q;
            return q - (Number(a.stock) || 0);
        },
        get adjustResult() {
            return this.adjustForm ? (Number(this.adjustForm.stock) || 0) + this.adjustQtyChange : 0;
        },
        saveAdjust: async function () {
            var a = this.adjustForm;
            var change = this.adjustQtyChange;
            if (!change) { this.notify('Nothing to change.', 'err'); return; }
            if (this.adjustResult < 0) { this.notify('Stock cannot go below zero.', 'err'); return; }
            var d = await this.send(this.routes.itemAdjust.replace('_ID_', a.item_id), 'POST', { qty_change: change, reason: a.reason });
            if (d) { this.adjustForm = null; await this.refreshState(); }
        },
        openHistory: async function (item) {
            this.historyItem = item; this.historyMoves = []; this.historyOpen = true; this.historyLoading = true;
            try {
                var res = await fetch(this.routes.apiItem.replace('_ID_', item.id), { headers: { 'Accept': 'application/json' } });
                if (res.ok) { var d = await res.json(); this.historyMoves = d.moves || []; }
            } catch (e) {}
            this.historyLoading = false;
        },

        /* ---------------- contacts ---------------- */
        filterContacts: function (kind) {
            var s = this.search.toLowerCase();
            return this.contacts.filter(function (c) { return c.kind === kind; })
                .filter(function (c) { return !s || (c.name + ' ' + (c.phone || '') + ' ' + (c.email || '')).toLowerCase().indexOf(s) !== -1; });
        },
        openContact: function (kind, c) { this.contactForm = c ? Object.assign({}, c) : { id: null, kind: kind, name: '', phone: '', email: '', address: '', notes: '' }; },
        saveContact: async function () {
            var f = this.contactForm;
            var url = f.id ? this.routes.contactUpdate.replace('_ID_', f.id) : this.routes.contactStore;
            var d = await this.send(url, f.id ? 'PATCH' : 'POST', f);
            if (d) { this.contactForm = null; await this.refreshState(); }
        },
        deleteContact: async function (c) {
            if (!confirm('Delete ' + c.name + '?')) return;
            var d = await this.send(this.routes.contactDestroy.replace('_ID_', c.id), 'DELETE');
            if (d) await this.refreshState();
        },

        /* ---------------- expenses ---------------- */
        filterExpenses: function () {
            var s = this.search.toLowerCase(), status = this.expStatus;
            return this.expenses.filter(function (e) { return status === 'all' || e.status === status; })
                .filter(function (e) { return !s || (e.category + ' ' + (e.supplier_name || '') + ' ' + (e.note || '')).toLowerCase().indexOf(s) !== -1; });
        },
        openExpense: function (e) { this.expenseForm = e ? Object.assign({}, e) : { id: null, date: this.today(), category: '', amount: 0, status: 'paid', contact_id: null, note: '' }; },
        saveExpense: async function () {
            var f = this.expenseForm;
            var url = f.id ? this.routes.expenseUpdate.replace('_ID_', f.id) : this.routes.expenseStore;
            var d = await this.send(url, f.id ? 'PATCH' : 'POST', { date: f.date, category: f.category, amount: Number(f.amount) || 0, status: f.status, contact_id: f.contact_id, note: f.note });
            if (d) { this.expenseForm = null; await this.refreshState(); }
        },
        deleteExpense: async function (e) {
            if (!confirm('Delete this expense?')) return;
            var d = await this.send(this.routes.expenseDestroy.replace('_ID_', e.id), 'DELETE');
            if (d) await this.refreshState();
        },

        /* ---------------- chart of accounts ---------------- */
        get accountTotals() {
            var debit = 0, credit = 0;
            this.accounts.forEach(function (a) { debit += Number(a.debit) || 0; credit += Number(a.credit) || 0; });
            return { debit: debit, credit: credit, diff: debit - credit };
        },
        filterAccounts: function () {
            var s = this.search.toLowerCase();
            return this.accounts.slice().sort(function (a, b) { return String(a.code).localeCompare(String(b.code)); })
                .filter(function (a) { return !s || (a.name + ' ' + a.code + ' ' + a.type + ' ' + (a.subtype || '')).toLowerCase().indexOf(s) !== -1; });
        },
        openAccount: function (a) {
            this.accountForm = a ? Object.assign({}, a)
                : { id: null, code: '', name: '', type: 'expense', opening_balance: 0 };
        },
        saveAccount: async function () {
            var f = this.accountForm;
            var url = f.id ? this.routes.accountUpdate.replace('_ID_', f.id) : this.routes.accountStore;
            var d = await this.send(url, f.id ? 'PATCH' : 'POST', {
                code: f.code, name: f.name, type: f.type, opening_balance: Number(f.opening_balance) || 0,
            });
            if (d) { this.accountForm = null; await this.refreshState(); }
        },
        deleteAccount: async function (a) {
            if (a.is_system) { this.notify('System accounts cannot be deleted.'); return; }
            if (!confirm('Delete account "' + a.name + '"?')) return;
            var d = await this.send(this.routes.accountDestroy.replace('_ID_', a.id), 'DELETE');
            if (d) await this.refreshState();
        },

        /* ---------------- bank & cash ---------------- */
        get filteredBankTx() {
            var f = this.bankTxFilter;
            return this.bankTransactions.filter(function (t) { return !f || t.bank_account_id === f; });
        },
        get bankTxName() {
            var b = this.bankAccounts.find((x) => x.id === (this.bankTxFor && this.bankTxFor.id));
            return b ? b.name : '';
        },
        bankName: function (id) {
            var b = this.bankAccounts.find(function (x) { return x.id === id; });
            return b ? b.name : '—';
        },
        openBank: function (b) {
            this.bankForm = b ? Object.assign({}, b)
                : { id: null, name: '', type: 'bank', account_number: '', opening_balance: 0 };
        },
        saveBank: async function () {
            var f = this.bankForm;
            var url = f.id ? this.routes.bankUpdate.replace('_ID_', f.id) : this.routes.bankStore;
            var d = await this.send(url, f.id ? 'PATCH' : 'POST', {
                name: f.name, type: f.type, account_number: f.account_number || null, opening_balance: Number(f.opening_balance) || 0,
            });
            if (d) { this.bankForm = null; await this.refreshState(); }
        },
        deleteBank: async function (b) {
            if (!confirm('Delete "' + b.name + '" and its transactions?')) return;
            var d = await this.send(this.routes.bankDestroy.replace('_ID_', b.id), 'DELETE');
            if (d) await this.refreshState();
        },
        openBankTx: function (b, type) {
            this.bankTxFor = b;
            this.bankTxForm = { date: this.today(), description: '', amount: 0, type: type, reference: '' };
        },
        saveBankTx: async function () {
            var f = this.bankTxForm, b = this.bankTxFor;
            if (!b) return;
            var d = await this.send(this.routes.bankTxStore.replace('_ID_', b.id), 'POST', {
                date: f.date, description: f.description, amount: Number(f.amount) || 0, type: f.type, reference: f.reference || null,
            });
            if (d) { this.bankTxForm = null; this.bankTxFor = null; await this.refreshState(); }
        },
        deleteBankTx: async function (t) {
            if (!confirm('Delete this transaction?')) return;
            var d = await this.send(this.routes.bankTxDestroy.replace('_ID_', t.id), 'DELETE');
            if (d) await this.refreshState();
        },

        /* ---------------- employees & payslips ---------------- */
        filterEmployees: function () {
            var s = this.search.toLowerCase();
            return this.employees.filter(function (e) { return !s || (e.name + ' ' + (e.email || '') + ' ' + (e.position || '')).toLowerCase().indexOf(s) !== -1; });
        },
        openEmployee: function (e) {
            this.employeeForm = e ? Object.assign({}, e)
                : { id: null, name: '', email: '', phone: '', position: '', employment_type: 'full_time', pay_frequency: 'monthly', base_salary: 0, hired_on: this.today(), is_active: true };
        },
        saveEmployee: async function () {
            var f = this.employeeForm;
            var url = f.id ? this.routes.employeeUpdate.replace('_ID_', f.id) : this.routes.employeeStore;
            var d = await this.send(url, f.id ? 'PATCH' : 'POST', {
                name: f.name, email: f.email || null, phone: f.phone || null, position: f.position || null,
                employment_type: f.employment_type, base_salary: Number(f.base_salary) || 0, pay_frequency: f.pay_frequency,
                hired_on: f.hired_on || null, is_active: !!f.is_active,
            });
            if (d) { this.employeeForm = null; await this.refreshState(); }
        },
        deleteEmployee: async function (e) {
            if (!confirm('Remove employee "' + e.name + '"?')) return;
            var d = await this.send(this.routes.employeeDestroy.replace('_ID_', e.id), 'DELETE');
            if (d) await this.refreshState();
        },
        openPayslip: function (p) {
            if (p) {
                this.payslipForm = Object.assign({}, p, { bank_account_id: null });
                return;
            }
            var start = this.today().slice(0, 8) + '01';
            this.payslipForm = {
                id: null, employee_id: null, period_start: start, period_end: this.today(),
                gross: 0, allowances: 0, deductions: 0, status: 'draft', paid_on: '', bank_account_id: null,
            };
        },
        openPayslipFor: function (e) {
            this.openPayslip(null);
            this.payslipForm.employee_id = e.id;
            this.payslipForm.gross = Number(e.base_salary) || 0;
        },
        get payslipNet() {
            var f = this.payslipForm || {};
            return (Number(f.gross) || 0) + (Number(f.allowances) || 0) - (Number(f.deductions) || 0);
        },
        savePayslip: async function () {
            var f = this.payslipForm;
            var url = f.id ? this.routes.payslipUpdate.replace('_ID_', f.id) : this.routes.payslipStore;
            var d = await this.send(url, f.id ? 'PATCH' : 'POST', {
                employee_id: f.employee_id, period_start: f.period_start, period_end: f.period_end,
                gross: Number(f.gross) || 0, allowances: Number(f.allowances) || 0, deductions: Number(f.deductions) || 0,
                status: f.status, paid_on: f.paid_on || null, bank_account_id: f.bank_account_id || null,
            });
            if (d) { this.payslipForm = null; await this.refreshState(); }
        },
        deletePayslip: async function (p) {
            if (!confirm('Delete payslip ' + p.number + '?')) return;
            var d = await this.send(this.routes.payslipDestroy.replace('_ID_', p.id), 'DELETE');
            if (d) await this.refreshState();
        },

        /* ---------------- journal ---------------- */
        filterJournal: function () {
            var s = this.search.toLowerCase();
            return this.journal.filter(function (j) { return !s || ((j.memo || '') + ' ' + (j.reference || '') + ' ' + (j.source_type || '')).toLowerCase().indexOf(s) !== -1; });
        },

        /* ---------------- settings + reports ---------------- */
        saveSettings: async function () {
            var d = await this.send(this.routes.settings, 'PATCH', this.settings);
            if (d && d.settings) this.settings = d.settings;
        },
        loadReport: async function () {
            var url = this.routes.reports + '?from=' + (this.reportRange.from || '') + '&to=' + (this.reportRange.to || '');
            try { var res = await fetch(url, { headers: { 'Accept': 'application/json' } }); if (res.ok) this.report = await res.json(); } catch (e) {}
        },

        /* ---------------- businesses ---------------- */
        switchBusiness: async function (b) {
            this.businessOpen = false;
            if (b.active) return;
            var d = await this.send(this.routes.businessSwitch.replace('_ID_', b.id), 'POST');
            if (d && d.state) { this.applyState(d.state); this.tab = 'overview'; }
        },
        openBusinessForm: function () {
            this.businessForm = { name: '', phone: '', email: '', address: '', currency: this.settings.currency || 'USD' };
        },
        saveBusiness: async function () {
            var d = await this.send(this.routes.businessStore, 'POST', this.businessForm);
            if (d) { this.businessForm = null; if (d.state) { this.applyState(d.state); this.tab = 'overview'; } }
        },

        /* ---------------- staff ---------------- */
        openStaff: function () { this.staffTemp = null; this.staffForm = { name: '', email: '', role: 'staff' }; },
        closeStaff: function () { this.staffForm = null; this.staffTemp = null; },
        saveStaff: async function () {
            var d = await this.send(this.routes.staffStore, 'POST', this.staffForm);
            if (d) {
                if (d.staff) this.staff = d.staff;
                if (d.temporary_password) { this.staffTemp = d.temporary_password; }
                else { this.closeStaff(); }
            }
        },
        setStaffRole: async function (m, role) {
            var d = await this.send(this.routes.staffUpdate.replace('_ID_', m.id), 'PATCH', { role: role });
            if (d && d.staff) this.staff = d.staff;
        },
        removeStaff: async function (m) {
            if (!confirm('Remove ' + m.name + ' from this business?')) return;
            var d = await this.send(this.routes.staffDestroy.replace('_ID_', m.id), 'DELETE');
            if (d && d.staff) this.staff = d.staff;
        },

        /* ---------------- reminders ---------------- */
        openReminder: function (doc) {
            if (!this.openInvoices.length) { this.notify('No unpaid invoices to remind.', 'err'); return; }
            this.reminderForm = {
                document_id: doc ? doc.id : this.openInvoices[0].id,
                channel: 'email', recipient: '', message: ''
            };
        },
        sendReminder: async function () {
            var d = await this.send(this.routes.reminderStore, 'POST', this.reminderForm);
            if (d) {
                if (d.reminders) this.reminders = d.reminders;
                if (d.wa_url) window.open(d.wa_url, '_blank');
                this.reminderForm = null;
            }
        },

        /* ---------------- notifications ---------------- */
        loadNotifications: async function () {
            try {
                var res = await fetch(this.routes.notifications, { headers: { 'Accept': 'application/json' } });
                if (res.ok) { var d = await res.json(); this.notifications = d.notifications || this.notifications; }
            } catch (e) {}
        },
        markAllRead: async function () {
            var d = await this.send(this.routes.notificationsRead, 'POST');
            if (d && d.notifications) this.notifications = d.notifications;
        },

        /* ---------------- activity ---------------- */
        loadActivity: async function () {
            try {
                var res = await fetch(this.routes.activity, { headers: { 'Accept': 'application/json' } });
                if (res.ok) { var d = await res.json(); this.activity = d.activity || this.activity; }
            } catch (e) {}
        },

        /* ---------------- line autofill ---------------- */
        syncLines: function () {
            if (!this.draft || !this.draft.lines) return;
            var self = this;
            this.draft.lines.forEach(function (l) {
                if (l.item_id && l._picked !== l.item_id) {
                    l._picked = l.item_id;
                    var it = self.items.find(function (x) { return x.id === l.item_id; });
                    if (it) { l.description = it.name; l.unit_price = it.sale_price; }
                }
                if (!l.item_id) l._picked = null;
            });
        },

        /* ---------------- POS ---------------- */
        posResults: function () {
            var s = this.posQuery.toLowerCase(), inStock = this.posInStock;
            return this.items.filter(function (i) { return !inStock || i.stock > 0; })
                .filter(function (i) { return !s || (i.name + ' ' + (i.sku || '')).toLowerCase().indexOf(s) !== -1; });
        },
        addToCart: function (item) {
            var l = this.cart.find(function (x) { return x.item_id === item.id; });
            if (l) { if (l.qty < item.stock) l.qty++; }
            else { this.cart.push({ item_id: item.id, name: item.name, price: item.sale_price, qty: 1, stock: item.stock }); }
        },
        incLine: function (l) { if (l.qty < l.stock) l.qty++; },
        decLine: function (l) { l.qty--; if (l.qty <= 0) this.removeCart(l); },
        removeCart: function (l) { this.cart = this.cart.filter(function (x) { return x !== l; }); },
        clearCart: function () { this.cart = []; this.posTendered = 0; this.posCustomer = null; },
        get cartTotal() { return this.cart.reduce(function (s, l) { return s + l.qty * l.price; }, 0); },
        get cartChange() { return Math.max(0, (Number(this.posTendered) || 0) - this.cartTotal); },
        checkout: async function () {
            if (!this.cart.length) { this.notify('Cart is empty.', 'err'); return; }
            var d = await this.send(this.routes.posCheckout, 'POST', {
                payment_method: this.posPay, tendered: Number(this.posTendered) || 0, contact_id: this.posCustomer,
                lines: this.cart.map(function (l) { return { item_id: l.item_id, qty: l.qty }; })
            });
            if (d) { this.clearCart(); this.lastSale = d.document; this.lastChange = d.change || 0; await this.refreshState(); }
        }
    };
};
</script>
</x-layouts::tools>
