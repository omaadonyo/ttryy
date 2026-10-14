<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>{{ $doc->label() }} {{ $doc->number }}</title>
<style>
  body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #18181b; margin: 0; }
  .wrap { padding: 28px 32px; }
  .brand { color: #9e005d; }
  table { border-collapse: collapse; width: 100%; }
  .head td { vertical-align: top; }
  .title { font-size: 26px; font-weight: bold; }
  .meta td { padding: 3px 0; }
  .lines th { background: #18181b; color: #fff; text-align: left; padding: 8px 10px; font-size: 11px; }
  .lines td { border-bottom: 1px solid #e4e4e7; padding: 8px 10px; }
  .right { text-align: right; }
  .totals td { padding: 4px 10px; }
  .grand td { border-top: 2px solid #18181b; font-size: 15px; font-weight: bold; padding-top: 8px; }
  .foot { margin-top: 26px; border-top: 1px solid #e4e4e7; padding-top: 10px; font-size: 10px; color: #52525b; }
  .status { display: inline-block; border: 1.5px solid #18181b; padding: 3px 12px; font-size: 11px; font-weight: bold; }
</style>
</head>
<body>
<div class="wrap">
  <table class="head">
    <tr>
      <td>
        <div class="title brand">{{ $settings->business_name ?? 'My Business' }}</div>
        <div>{{ $settings->address ?? '' }}</div>
        <div>{{ $settings->phone ?? '' }}{{ $settings->email ? ' · '.$settings->email : '' }}</div>
      </td>
      <td class="right">
        <div class="title">{{ strtoupper($doc->label()) }}</div>
        <div style="font-size:15px;font-weight:bold;">{{ $doc->number }}</div>
        <div style="margin-top:6px;"><span class="status">{{ strtoupper($doc->status) }}</span></div>
      </td>
    </tr>
  </table>

  <table class="meta" style="margin-top:18px;">
    <tr>
      <td style="width:55%;">
        <strong>Billed to</strong><br>
        {{ $doc->contact?->name ?? 'Walk-in customer' }}<br>
        {{ $doc->contact?->address ?? '' }}<br>
        {{ $doc->contact?->phone ?? '' }}{{ $doc->contact?->email ? ' · '.$doc->contact->email : '' }}
      </td>
      <td>
        <strong>Issued:</strong> {{ $doc->issue_date->format('d M Y') }}<br>
        @if($doc->due_date)<strong>Due:</strong> {{ $doc->due_date->format('d M Y') }}<br>@endif
        @if($doc->valid_until)<strong>Valid until:</strong> {{ $doc->valid_until->format('d M Y') }}<br>@endif
        @if($doc->payment_method)<strong>Paid via:</strong> {{ ucfirst($doc->payment_method) }}<br>@endif
        @if($doc->invoice)<strong>Against:</strong> {{ $doc->invoice->number }}<br>@endif
      </td>
    </tr>
  </table>

  <table class="lines" style="margin-top:16px;">
    <thead><tr><th>#</th><th>Description</th><th class="right">Qty</th><th class="right">Unit price</th><th class="right">Amount</th></tr></thead>
    <tbody>
      @foreach($doc->lines as $i => $l)
      <tr>
        <td>{{ $i + 1 }}</td>
        <td>{{ $l->description }}</td>
        <td class="right">{{ rtrim(rtrim(number_format($l->qty, 2), '0'), '.') }}</td>
        <td class="right">{{ number_format($l->unit_price) }}</td>
        <td class="right">{{ number_format($l->line_total) }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <table style="margin-top:10px;">
    <tr>
      <td style="width:55%;"></td>
      <td>
        <table class="totals">
          <tr><td>Subtotal</td><td class="right">{{ $settings->currency }} {{ number_format($doc->subtotal) }}</td></tr>
          @if($doc->discount > 0)<tr><td>Discount</td><td class="right">−{{ $settings->currency }} {{ number_format($doc->discount) }}</td></tr>@endif
          @if($doc->tax_amount > 0)<tr><td>Tax ({{ $doc->tax_rate }}%)</td><td class="right">{{ $settings->currency }} {{ number_format($doc->tax_amount) }}</td></tr>@endif
          <tr class="grand"><td>Total</td><td class="right">{{ $settings->currency }} {{ number_format($doc->total) }}</td></tr>
          @if($doc->type === 'invoice')
          <tr><td>Paid</td><td class="right">{{ $settings->currency }} {{ number_format($doc->paid_amount) }}</td></tr>
          <tr><td><strong>Balance due</strong></td><td class="right"><strong>{{ $settings->currency }} {{ number_format($doc->balance()) }}</strong></td></tr>
          @endif
        </table>
      </td>
    </tr>
  </table>

  @if($doc->notes)<p style="margin-top:12px;font-size:11px;">{{ $doc->notes }}</p>@endif

  <table class="foot">
    <tr>
      <td style="width:110px;"><img src="{{ $qr }}" style="width:100px;height:100px;"></td>
      <td>
        <strong>Verify this document:</strong> scan the QR code or visit<br>
        {{ $verifyUrl }}<br><br>
        {{ $settings->receipt_footer ?? 'Thank you for your business!' }}
      </td>
    </tr>
  </table>
</div>
</body>
</html>
