<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>{{ $doc->label() }} {{ $doc->number }}</title>
<style>
  @page { margin: 0; }
  body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #18181b; margin: 0; }
  .bar { height: 8px; background: #9e005d; }
  .wrap { padding: 30px 34px; }
  .brand { color: #9e005d; }
  .muted { color: #71717a; }
  table { border-collapse: collapse; width: 100%; }
  .head td { vertical-align: top; }
  .biz { font-size: 22px; font-weight: bold; }
  .doc-title { font-size: 24px; font-weight: bold; text-align: right; color: #9e005d; text-transform: uppercase; }
  .doc-no { font-size: 15px; font-weight: bold; text-align: right; }
  .status { display: inline-block; border: 1px solid #d4d4d8; border-radius: 10px; padding: 3px 10px; font-size: 10px; font-weight: bold; text-transform: uppercase; color: #52525b; }
  .label { font-size: 10px; font-weight: bold; text-transform: uppercase; color: #a1a1aa; }
  .lines th { background: #18181b; color: #fff; text-align: left; padding: 8px 10px; font-size: 11px; }
  .lines th.c, .lines td.c { text-align: center; }
  .lines th.r, .lines td.r { text-align: right; }
  .lines td { border-bottom: 1px solid #e4e4e7; padding: 8px 10px; }
  .totals td { padding: 4px 10px; }
  .totals .k { color: #71717a; }
  .grand td { border-top: 2px solid #18181b; font-size: 15px; font-weight: bold; padding-top: 8px; }
  .paid { color: #059669; }
  .foot { margin-top: 24px; border-top: 1px solid #e4e4e7; padding-top: 12px; font-size: 10px; color: #52525b; }
</style>
@php
  $fallback = 'Thank you for your business!';
  $footer = match ($doc->type) {
      'quotation', 'purchase_quote' => $settings->quotation_footer ?: $fallback,
      'invoice', 'purchase_invoice', 'credit_note' => $settings->invoice_footer ?: $fallback,
      'receipt', 'purchase_payment' => $settings->receipt_footer ?: $fallback,
      default => $settings->receipt_footer ?: $fallback,
  };
@endphp
</head>
<body>
<div class="bar"></div>
<div class="wrap">
  <table class="head">
    <tr>
      <td style="width:60%;">
        <div class="biz brand">{{ $settings->business_name ?? 'My Business' }}</div>
        <div class="muted">{{ $settings->address ?? '' }}</div>
        <div class="muted">{{ $settings->phone ?? '' }}{{ $settings->email ? ' · '.$settings->email : '' }}</div>
      </td>
      <td style="width:40%;">
        <div class="doc-title">{{ $doc->label() }}</div>
        <div class="doc-no">{{ $doc->number }}</div>
        <div style="margin-top:6px;"><span class="status">{{ $doc->status }}</span></div>
      </td>
    </tr>
  </table>

  <table style="margin-top:22px;">
    <tr>
      <td style="width:55%;">
        <div class="label">Billed to</div>
        <div style="font-weight:bold;margin-top:4px;">{{ $doc->contact?->name ?? 'Walk-in customer' }}</div>
        @if($doc->contact?->address)<div class="muted">{{ $doc->contact->address }}</div>@endif
        @if($doc->contact?->phone || $doc->contact?->email)<div class="muted">{{ $doc->contact?->phone }}{{ $doc->contact?->email ? ' · '.$doc->contact->email : '' }}</div>@endif
      </td>
      <td style="text-align:right;">
        <div class="muted">Issued: <strong style="color:#3f3f46;">{{ $doc->issue_date->format('d M Y') }}</strong></div>
        @if($doc->due_date)<div class="muted">Due: <strong style="color:#3f3f46;">{{ $doc->due_date->format('d M Y') }}</strong></div>@endif
        @if($doc->valid_until)<div class="muted">Valid until: <strong style="color:#3f3f46;">{{ $doc->valid_until->format('d M Y') }}</strong></div>@endif
        @if($doc->payment_method)<div class="muted">Paid via: <strong style="color:#3f3f46;">{{ ucfirst($doc->payment_method) }}</strong></div>@endif
        @if($doc->invoice)<div class="muted">Against: <strong style="color:#3f3f46;">{{ $doc->invoice->number }}</strong></div>@endif
      </td>
    </tr>
  </table>

  <table class="lines" style="margin-top:18px;">
    <thead><tr><th>Description</th><th class="c">Qty</th><th class="r">Unit price</th><th class="r">Amount</th></tr></thead>
    <tbody>
      @foreach($doc->lines as $l)
      <tr>
        <td>{{ $l->description }}</td>
        <td class="c">{{ rtrim(rtrim(number_format($l->qty, 2), '0'), '.') }}</td>
        <td class="r">{{ number_format($l->unit_price) }}</td>
        <td class="r" style="font-weight:bold;">{{ number_format($l->line_total) }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <table style="margin-top:8px;">
    <tr>
      <td style="width:55%;"></td>
      <td>
        <table class="totals">
          <tr><td class="k">Subtotal</td><td class="r">{{ $settings->currency }} {{ number_format($doc->subtotal) }}</td></tr>
          @if($doc->discount > 0)<tr><td class="k">Discount</td><td class="r">−{{ $settings->currency }} {{ number_format($doc->discount) }}</td></tr>@endif
          @if($doc->tax_amount > 0)<tr><td class="k">Tax ({{ $doc->tax_rate }}%)</td><td class="r">{{ $settings->currency }} {{ number_format($doc->tax_amount) }}</td></tr>@endif
          <tr class="grand"><td>Total</td><td class="r">{{ $settings->currency }} {{ number_format($doc->total) }}</td></tr>
          @if($doc->type === 'invoice')
          <tr class="paid"><td>Paid</td><td class="r">{{ $settings->currency }} {{ number_format($doc->paid_amount) }}</td></tr>
          <tr><td><strong>Balance due</strong></td><td class="r"><strong>{{ $settings->currency }} {{ number_format($doc->balance()) }}</strong></td></tr>
          @endif
        </table>
      </td>
    </tr>
  </table>

  @if($doc->type === 'invoice' && $doc->receipts->count())
  <div style="margin-top:16px;">
    <div class="label">Payments received</div>
    <table style="margin-top:6px;">
      @foreach($doc->receipts as $r)
      <tr>
        <td style="border-bottom:1px solid #e4e4e7;padding:6px 0;font-weight:bold;">{{ $r->number }}</td>
        <td class="muted" style="border-bottom:1px solid #e4e4e7;padding:6px 0;">{{ $r->issue_date?->format('d M Y') }}</td>
        <td class="muted" style="border-bottom:1px solid #e4e4e7;padding:6px 0;">{{ ucfirst($r->payment_method ?? '') }}</td>
        <td style="border-bottom:1px solid #e4e4e7;padding:6px 0;text-align:right;font-weight:bold;">{{ $settings->currency }} {{ number_format($r->total) }}</td>
      </tr>
      @endforeach
    </table>
  </div>
  @endif

  @if($doc->notes)<p class="muted" style="margin-top:14px;font-size:11px;">{{ $doc->notes }}</p>@endif

  <table class="foot">
    <tr>
      <td style="width:110px;"><img src="{{ $qr }}" style="width:100px;height:100px;"></td>
      <td>
        <strong>Scan to verify this document</strong><br>
        {{ $verifyUrl }}<br><br>
        {{ $footer }}
      </td>
    </tr>
  </table>
</div>
</body>
</html>
