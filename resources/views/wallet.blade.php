<x-layouts::app :title="__('Wallet')">
    @include('partials.toast')
    <div class="flex w-full flex-col gap-5">
        <x-page-head title="Wallet" sub="Top up tokens and spend them on AI messages, group access and more.">
            <x-slot:actions>
                <div class="inline-flex items-center gap-2 rounded-full bg-zinc-950 px-5 py-2.5 text-sm font-bold text-white dark:bg-white dark:text-zinc-950">
                    <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 10h18M7 15h4"/></svg>
                    <span id="wallet-balance">{{ number_format($balance) }}</span>&nbsp;tokens
                </div>
                <button onclick="startTour_wallet()" class="btn-ghost btn-sm">Take tour</button>
            </x-slot:actions>
        </x-page-head>

        @include('partials.tour', ['key' => 'wallet', 'steps' => [
            ['target' => '#tour-topup', 'title' => 'Top up in seconds', 'body' => 'Pick a pack and pay with Mobile Money or card. Tokens land instantly after verification.'],
            ['target' => '#tour-flow', 'title' => 'Token flow', 'body' => 'Earned vs used per day for the last 30 days — spot heavy spending weeks.'],
            ['target' => '#tour-ledger', 'title' => 'Every token accounted for', 'body' => 'Full ledger with running balances, plus recent top-up receipts.'],
        ]])

        @include('partials.onboard', ['key' => 'wallet', 'title' => 'Tokens run the growth engine', 'body' => 'AI sales messages and community join-links spend tokens. Top up with Mobile Money or card — credit lands instantly after verification.', 'link' => route('marketing.index'), 'linkLabel' => 'Use tokens in marketing →'])

        <div id="tour-topup" class="card card-pad">
            <h2 class="section-title">Top up with Mobile Money or card</h2>
            <p class="page-sub">Pay securely with Flutterwave. Tokens land instantly after verification.</p>
            <div class="mt-4 grid gap-3 sm:grid-cols-3" id="pack-grid">
                @foreach($packs as $key => $pack)
                <div class="rounded-xl border border-zinc-200/80 bg-zinc-50 p-5 text-center dark:border-white/10 dark:bg-white/5">
                    <p class="font-display text-sm font-bold tracking-wide text-zinc-500 uppercase dark:text-zinc-400">{{ $pack['label'] }}</p>
                    <p class="stat-num mt-1">UGX {{ number_format($pack['price']) }}</p>
                    <button data-topup="{{ $key }}" data-tokens="{{ $pack['tokens'] }}" data-price="{{ $pack['price'] }}" class="btn-brand btn-sm mt-3 w-full">Buy {{ number_format($pack['tokens']) }} tokens</button>
                </div>
                @endforeach
            </div>
            <p id="topup-status" class="mt-3 hidden text-sm font-semibold"></p>
        </div>

        <div class="grid auto-rows-min gap-4 sm:grid-cols-2">
            <x-stat label="Tokens used · all time" :value="number_format($spentTotal)" hint="AI messages and group unlocks" />
            <x-stat label="Top-ups" :value="count($topups)" hint="Recent purchases" />
        </div>

        <div id="tour-flow" class="card card-pad">
            <h2 class="section-title">Token flow · last 30 days</h2>
            <p class="page-sub">Earned vs used per day.</p>
            <div class="mt-3 h-52"><canvas id="wallet-flow-chart"></canvas></div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="card card-pad">
                <h2 class="section-title">What tokens buy</h2>
                <ul class="mt-3 space-y-2 text-sm">
                    <li class="flex justify-between gap-2"><span class="text-zinc-500">AI sales message</span><strong>{{ $costs['ai_message'] }} tokens</strong></li>
                    <li class="flex justify-between gap-2"><span class="text-zinc-500">WhatsApp group access</span><strong>{{ $costs['group_unlock'] }} tokens each</strong></li>
                    <li class="flex justify-between gap-2"><span class="text-zinc-500">Pro prospect search (fresh pull)</span><strong>{{ $costs['scraper_search'] }} tokens</strong></li>
                </ul>
            </div>
            <div class="card card-pad">
                <h2 class="section-title">Recent top-ups</h2>
                @if($topups->isEmpty())
                <p class="mt-2 text-sm text-zinc-500">No top-ups yet.</p>
                @else
                <ul class="mt-2 space-y-2 text-sm">
                    @foreach($topups as $t)
                    <li class="flex justify-between gap-2"><span class="font-mono text-xs text-zinc-500">{{ $t->reference }} · {{ $t->pack }}</span><span class="font-bold">+{{ number_format($t->tokens) }} ({{ $t->status }})</span></li>
                    @endforeach
                </ul>
                @endif
            </div>
        </div>

        <div id="tour-ledger" class="card card-pad">
            <h2 class="section-title">Token history</h2>
            @if($ledger->isEmpty())
            <p class="mt-2 text-sm text-zinc-500">No token activity yet — top up or earn your welcome bonus.</p>
            @else
            <div class="tbl-wrap mt-3 rounded-none! border-0! shadow-none!">
                <table class="tbl" style="min-width:480px">
                    <tbody class="divide-y divide-neutral-200 dark:divide-white/10">
                        @foreach($ledger as $tx)
                        <tr>
                            <td class="text-xs whitespace-nowrap text-zinc-500">{{ $tx->created_at->format('d M Y H:i') }}</td>
                            <td>{{ $tx->description }}</td>
                            <td class="font-bold {{ $tx->type === 'credit' ? 'text-emerald-600 dark:text-emerald-400' : '' }}">{{ $tx->type === 'credit' ? '+' : '−' }}{{ number_format($tx->amount) }}</td>
                            <td class="text-right text-xs whitespace-nowrap text-zinc-500">bal {{ number_format($tx->balance_after) }}</td>
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
toastStoredFlash();
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
      customizations: { title: 'ttryy', description: j.tokens + ' tokens top-up ' + j.reference },
      callback: async resp => {
        try{
          const v = await fetch("{{ route('wallet.verify') }}", { method: 'POST',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ transaction_id: resp.transaction_id || resp.id, tx_ref: j.reference }) });
          const r = await v.json();
          if(r.redirect){ flashToast('Top-up confirmed — tokens added to your wallet.'); window.location.href = r.redirect; }
          else { status.textContent = r.message || 'Verification failed.'; toast(r.message || 'Verification failed.', 'danger'); }
        }catch(e){ status.textContent = 'Verification failed. Contact support with reference ' + j.reference; toast('Verification failed. Contact support with reference ' + j.reference + '.', 'danger'); }
      },
      onclose: () => location.reload(),
    });
    @else
    status.textContent = 'Online top-up is not connected yet — contact ttryy on WhatsApp to top up manually.';
    toast('Online top-up is not connected yet — contact ttryy on WhatsApp.', 'warning');
    @endif
  }catch(e){ status.textContent = e.message || 'Top-up failed.'; toast(e.message || 'Top-up failed.', 'danger'); }
  b.disabled = false;
}));
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function(){
  const el = document.getElementById('wallet-flow-chart');
  if(!el || typeof Chart === 'undefined') return;
  new Chart(el, { type: 'bar',
    data: { labels: @json($usage30['labels']),
      datasets: [
        { label: 'Earned', data: @json($usage30['earned']), backgroundColor: '#10b981', borderRadius: 4 },
        { label: 'Used', data: @json($usage30['used']), backgroundColor: '#9e005d', borderRadius: 4 },
      ] },
    options: { responsive: true, maintainAspectRatio: false,
      plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 16 } } },
      scales: { y: { beginAtZero: true, ticks: { precision: 0 }, stacked: true }, x: { stacked: true, ticks: { maxTicksLimit: 10 } } } } });
})();
</script>
</x-layouts::app>
