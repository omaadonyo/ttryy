<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $doc->label() }} {{ $doc->number }} — verified</title>
<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-zinc-100 min-h-screen grid place-items-center p-6">
  <div class="w-full max-w-md rounded-2xl bg-white p-8 shadow-xl text-center">
    <p class="inline-flex items-center gap-2 rounded-full bg-emerald-500/15 px-4 py-1.5 text-xs font-bold text-emerald-700"><span class="size-2 rounded-full bg-emerald-500"></span>VERIFIED DOCUMENT</p>
    <p class="mt-4 text-xs font-bold uppercase tracking-widest text-zinc-400">{{ $business?->business_name ?? 'Ttryy Business Tools' }}</p>
    <h1 class="mt-1 text-3xl font-extrabold">{{ $doc->label() }} {{ $doc->number }}</h1>
    <dl class="mt-6 space-y-2 text-left text-sm">
      <div class="flex justify-between border-b border-zinc-100 pb-2"><dt class="text-zinc-500">Issued to</dt><dd class="font-bold">{{ $doc->contact?->name ?? 'Walk-in customer' }}</dd></div>
      <div class="flex justify-between border-b border-zinc-100 pb-2"><dt class="text-zinc-500">Date</dt><dd class="font-bold">{{ $doc->issue_date->format('d M Y') }}</dd></div>
      <div class="flex justify-between border-b border-zinc-100 pb-2"><dt class="text-zinc-500">Total</dt><dd class="font-bold">UGX {{ number_format($doc->total) }}</dd></div>
      @if($doc->type === 'invoice')
      <div class="flex justify-between border-b border-zinc-100 pb-2"><dt class="text-zinc-500">Paid</dt><dd class="font-bold">UGX {{ number_format($doc->paid_amount) }}</dd></div>
      <div class="flex justify-between"><dt class="text-zinc-500">Balance</dt><dd class="font-bold">UGX {{ number_format($doc->balance()) }}</dd></div>
      @endif
      <div class="flex justify-between"><dt class="text-zinc-500">Status</dt><dd class="font-bold uppercase">{{ $doc->status }}</dd></div>
    </dl>
    <p class="mt-6 text-xs text-zinc-400">This page confirms the document above is genuine and was issued through Ttryy Business Tools.</p>
  </div>
</body>
</html>
