<x-layouts::app :title="__('My packages')">
    <div class="flex h-full w-full flex-1 flex-col gap-4">
        <div>
            <flux:heading size="xl">My packages</flux:heading>
            <flux:text class="mt-1">Orders for <strong>{{ auth()->user()->name }}</strong>. Your website stays running while your plan is active.</flux:text>
        </div>

        @if($orders->isEmpty())
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-10 text-center">
            <flux:heading>No orders yet.</flux:heading>
            <flux:text class="mt-2">Choose a package to get your website and prospect list started.</flux:text>
            <div class="mt-5"><flux:button :href="route('checkout')" variant="primary" wire:navigate>Choose a package</flux:button></div>
        </div>
        @else
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($orders as $order)
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
                <div class="flex items-center justify-between gap-2">
                    <p class="font-mono text-xs text-zinc-500">{{ $order->reference }}</p>
                    <flux:badge size="sm">{{ $order->status }}</flux:badge>
                </div>
                <p class="text-2xl font-extrabold mt-2">{{ $order->package }}</p>
                <p class="text-sm text-zinc-500 mt-1">{{ ucfirst($order->billing_frequency) }} · {{ $order->duration_months }} months · {{ $order->periods }} payments</p>
                <p class="text-sm text-zinc-500">Domain: {{ $order->domain }} (UGX {{ number_format($order->domain_fee) }})</p>
                <p class="text-sm text-zinc-500">{{ $order->business_name }}@if($order->niche) · {{ $order->niche }}@endif</p>
                <p class="text-xl font-extrabold mt-3">UGX {{ number_format($order->total_amount) }}</p>
                <p class="text-xs text-zinc-500 mt-1">Due today was UGX {{ number_format($order->due_today) }} · Ordered {{ $order->created_at->format('d M Y') }}</p>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</x-layouts::app>
