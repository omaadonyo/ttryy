<x-layouts::app :title="__('Wallet')">
    <div class="flex h-full w-full flex-1 flex-col gap-4">
        <div class="flex items-end justify-between gap-3 flex-wrap">
            <div>
                <flux:heading size="xl">Wallet</flux:heading>
                <flux:text class="mt-1">Top up tokens and spend them on AI messages, group access and more.</flux:text>
            </div>
            <div class="inline-flex items-center gap-2 rounded-full bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 text-sm font-bold px-5 py-2.5">
                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 10h18M7 15h4"/></svg>
                <span id="wallet-balance">{{ number_format($balance) }}</span>&nbsp;tokens
            </div>
        </div>

        <div class="rounded-xl bg-white dark:bg-white/[.04] p-5 shadow-[0_12px_32px_-12px_rgba(10,10,12,.18)] dark:shadow-[0_12px_32px_-12px_rgba(0,0,0,.7)]">
            <flux:heading>Top up with Mobile Money or card</flux:heading>
            <flux:text class="mt-1 text-sm">Pay securely with Flutterwave. Tokens land instantly after verification.</flux:text>
            <div class="mt-4 grid sm:grid-cols-3 gap-3" id="pack-grid">
                @foreach($packs as $key => $pack)
                <div class="rounded-xl bg-zinc-50 dark:bg-white/5 p-5 text-center">
                    <p class="font-display font-bold">{{ $pack['label'] }}</p>
                    <p class="font-display font-extrabold text-2xl mt-1">UGX {{ number_format($pack['price']) }}</p>
                    <button data-topup="{{ $key }}" data-tokens="{{ $pack['tokens'] }}" data-price="{{ $pack['price'] }}" class="mt-3 w-full bg-[#9e005d] hover:bg-[#7e0049] text-white text-sm font-bold px-4 py-2.5 rounded-full transition">Buy {{ number_format($pack['tokens']) }} tokens</button>
                </div>
                @endforeach
            </div>
            <p id="topup-status" class="hidden mt-3 text-sm font-semibold"></p>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div class="rounded-xl bg-white dark:bg-white/[.04] p-5 shadow-[0_12px_32px_-12px_rgba(10,10,12,.18)] dark:shadow-[0_12px_32px_-12px_rgba(0,0,0,.7)]">
                <flux:heading>What tokens buy</flux:heading>
                <ul class="mt-3 space-y-2 text-sm">
                    <li class="flex justify-between gap-2"><span>AI sales message</span><strong>{{ $costs['ai_message'] }} tokens</strong></li>
                    <li class="flex justify-between gap-2"><span>WhatsApp group access</span><strong>{{ $costs['group_unlock'] }} tokens each</strong></li>
                </ul>
            </div>
            <div class="rounded-xl bg-white dark:bg-white/[.04] p-5 shadow-[0_12px_32px_-12px_rgba(10,10,12,.18)] dark:shadow-[0_12px_32px_-12px_rgba(0,0,0,.7)]">
                <flux:heading>Recent top-ups</flux:heading>
                @if($topups->isEmpty())
                <p class="text-sm text-zinc-500 mt-2">No top-ups yet.</p>
                @else
                <ul class="mt-2 space-y-2 text-sm">
                    @foreach($topups as $t)
                    <li class="flex justify-between gap-2"><span class="font-mono text-xs">{{ $t->reference }} · {{ $t->pack }}</span><span class="font-bold">+{{ number_format($t->tokens) }} ({{ $t->status }})</span></li>
                    @endforeach
                </ul>
                @endif
            </div>
        </div>

        <div class="rounded-xl bg-white dark:bg-white/[.04] p-5 shadow-[0_12px_32px_-12px_rgba(10,10,12,.18)] dark:shadow-[0_12px_32px_-12px_rgba(0,0,0,.7)]">
            <flux:heading>Token history</flux:heading>
            @if($ledger->isEmpty())
            <p class="text-sm text-zinc-500 mt-2">No token activity yet — top up or earn your welcome bonus.</p>
            @else
            <div class="mt-2 overflow-x-auto">
                <table class="w-full text-sm min-w-[480px]">
                    <tbody class="divide-y divide-neutral-200 dark:divide-white/10">
                        @foreach($ledger as $tx)
                        <tr>
                            <td class="py-2 pr-4 text-xs text-zinc-500">{{ $tx->created_at->format('d M Y H:i') }}</td>
                            <td class="py-2 pr-4">{{ $tx->description }}</td>
                            <td class="py-2 pr-4 font-bold {{ $tx->type === 'credit' ? 'text-emerald-600 dark:text-emerald-400' : '' }}">{{ $tx->type === 'credit' ? '+' : '−' }}{{ number_format($tx->amount) }}</td>
                            <td class="py-2 text-right text-xs text-zinc-500">bal {{ number_format($tx->balance_after) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

@if(config('services.flutterwave.public_key'))
<script src="https://checkout.flutterwave.com/v3.js"></script>
@endif
<script>
document.querySelectorAll('[data-topup]').forEach(b => b.addEventListener('click', async () => {
  const status = document.getElementById('topup-status');
  const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
  b.disabled = true; status.classList.remove('hidden'); status.textContent = 'Creating top-up…';
  try{
    const res = await fetch("{{ route('wallet.topup') }}", { method: 'POST',
      headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
      body: JSON.stringify({ pack: b.dataset.topup }) });
    const j = await res.json();
    if(!res.ok) throw new Error(j.message || 'Top-up failed.');
    @if(config('services.flutterwave.public_key'))
    if(typeof FlutterwaveCheckout === 'undefined') throw new Error('Payment popup failed to load. Check your connection.');
    FlutterwaveCheckout({
      public_key: j.public_key, tx_ref: j.reference + '-' + Date.now(), amount: j.amount_ugx, currency: 'UGX',
      payment_options: 'mobilemoney,card', customer: { email: j.email, name: j.name },
      customizations: { title: 'Ttryy', description: j.tokens + ' tokens top-up ' + j.reference },
      callback: async resp => {
        try{
          const v = await fetch("{{ route('wallet.verify') }}", { method: 'POST',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ transaction_id: resp.transaction_id || resp.id, tx_ref: j.reference }) });
          const r = await v.json();
          if(r.redirect) window.location.href = r.redirect;
          else { status.textContent = r.message || 'Verification failed.'; }
        }catch(e){ status.textContent = 'Verification failed. Contact support with reference ' + j.reference; }
      },
      onclose: () => location.reload(),
    });
    @else
    status.textContent = 'Online top-up is not connected yet — contact Ttryy on WhatsApp to top up manually.';
    @endif
  }catch(e){ status.textContent = e.message || 'Top-up failed.'; }
  b.disabled = false;
}));
</script>
</x-layouts::app>
