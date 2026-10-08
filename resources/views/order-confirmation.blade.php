<x-layouts::app :title="__('Order :ref', ['ref' => $order->reference])">
    <div class="flex h-full w-full flex-1 flex-col gap-4 max-w-3xl">
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-8 text-center">
            <div class="w-16 h-16 mx-auto grid place-items-center rounded-full bg-[#9e005d] text-white">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
            </div>
            <flux:heading size="xl" class="mt-4">Order received.</flux:heading>
            <flux:text class="mt-2">Reference <strong class="font-mono">{{ $order->reference }}</strong>. A Ttryy representative will contact <strong>{{ $order->phone }}</strong> to confirm and collect payment.</flux:text>
        </div>

        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-8">
            <flux:heading>Your order</flux:heading>
            <dl class="mt-4 divide-y divide-neutral-200 dark:divide-neutral-700 text-sm">
                <div class="flex justify-between py-2.5"><dt class="text-zinc-500">Package</dt><dd class="font-bold">{{ $order->package }}</dd></div>
                <div class="flex justify-between py-2.5"><dt class="text-zinc-500">Billing</dt><dd class="font-bold">{{ ucfirst($order->billing_frequency) }}</dd></div>
                <div class="flex justify-between py-2.5"><dt class="text-zinc-500">Duration</dt><dd class="font-bold">{{ $order->duration_months }} months ({{ $order->periods }} payments)</dd></div>
                <div class="flex justify-between py-2.5"><dt class="text-zinc-500">Rate</dt><dd class="font-bold">UGX {{ number_format($order->amount_per_period) }}</dd></div>
                <div class="flex justify-between py-2.5"><dt class="text-zinc-500">Domain · one-time fee</dt><dd class="font-bold">UGX {{ number_format($order->domain_fee) }} ({{ $order->domain }})</dd></div>
                <div class="flex justify-between py-2.5"><dt class="text-zinc-500">Business</dt><dd class="font-bold">{{ $order->business_name }}</dd></div>
                @if($order->niche)<div class="flex justify-between py-2.5"><dt class="text-zinc-500">Niche</dt><dd class="font-bold">{{ $order->niche }}</dd></div>@endif
                <div class="flex justify-between py-2.5"><dt class="text-zinc-500">Status</dt><dd><flux:badge size="sm">{{ $order->status }}</flux:badge></dd></div>
                <div class="flex justify-between py-3"><dt class="font-bold">Due today <span class="font-normal text-zinc-500">(domain + first payment)</span></dt><dd class="text-2xl font-extrabold text-[#9e005d]">UGX {{ number_format($order->due_today) }}</dd></div>
                <div class="flex justify-between py-2.5"><dt class="text-zinc-500">Total over term</dt><dd class="font-bold">UGX {{ number_format($order->total_amount) }}</dd></div>
            </dl>
            <flux:button :href="'https://wa.me/256700000000?text='.urlencode('Hi Ttryy! I just placed order '.$order->reference.' ('.$order->package.', '.ucfirst($order->billing_frequency).', due today UGX '.number_format($order->due_today).'). How do I pay?')" target="_blank" variant="primary" class="mt-6 w-full !bg-[#9e005d] hover:!bg-[#7e0049]">Confirm on WhatsApp</flux:button>
            <div class="mt-3 flex flex-col sm:flex-row gap-3">
                <flux:button :href="route('packages.index')" variant="ghost" class="flex-1" wire:navigate>My packages</flux:button>
                <flux:button :href="route('dashboard')" variant="ghost" class="flex-1" wire:navigate>Dashboard</flux:button>
            </div>
        </div>
    </div>
<script>try{ localStorage.removeItem('ttryy-checkout-v1'); }catch(e){}</script>
</x-layouts::app>
