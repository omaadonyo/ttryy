{{-- Manual Mobile Money fallback: shown when the online gateway is down,
     or inside the "gateway trouble" expander. --}}
<div class="mt-3 rounded-xl border border-zinc-200/80 bg-zinc-50 p-4 text-sm dark:border-white/10 dark:bg-white/5">
  <p class="font-bold text-zinc-950 dark:text-white">Pay with MTN Mobile Money, then submit</p>
  <ol class="mt-2 list-decimal space-y-1 text-[13px] text-zinc-600 ml-4 dark:text-zinc-400">
    <li>Dial *165# → Payments → Merchant payment</li>
    <li>Merchant code <strong>{{ $momoCode }}</strong> (ttryy)</li>
    <li>Amount: <strong class="manual-amount">…</strong> (your first payment)</li>
    <li>Reference / narration: use your phone number — we match it after you submit</li>
  </ol>
  <button type="button" class="manual-submit btn-dark mt-3 w-full">Submit order manually</button>
  <p class="mt-2 text-[11px] text-zinc-500">Your order is saved and we confirm on WhatsApp, usually within a few hours.</p>
</div>
