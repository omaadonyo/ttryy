<div class="flex flex-wrap items-end justify-between gap-3">
    <div>
        <p class="eyebrow text-brand-600 dark:text-brand-300">{{ $eyebrow ?? 'Report' }}</p>
        <h1 class="page-title mt-1">{!! $title !!}</h1>
        <p class="mt-1 text-sm text-zinc-500">Figures for the selected period, computed live.</p>
    </div>
    <div class="flex items-center gap-2">
        <input type="date" x-model="reportRange.from" @change="loadReport()" class="field w-40" />
        <span class="text-sm text-zinc-400">to</span>
        <input type="date" x-model="reportRange.to" @change="loadReport()" class="field w-40" />
        <button type="button" @click="loadReport()" class="btn-ghost btn-sm">Refresh</button>
    </div>
</div>
