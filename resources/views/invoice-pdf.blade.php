<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Invoice {{ $order->reference }} — Ttryy</title>
<style>
  body{ font-family:Helvetica,Arial,sans-serif; color:#18181b; font-size:12px; margin:0; padding:0; }
  .wrap{ padding:36px 40px; }
  .brand-bar{ background:#9e005d; color:#fff; padding:22px 40px; }
  .brand-bar h1{ margin:0; font-size:26px; letter-spacing:1px; }
  .brand-bar p{ margin:4px 0 0; font-size:11px; }
  table{ width:100%; border-collapse:collapse; }
  .meta td{ padding:3px 0; vertical-align:top; }
  .items th{ background:#f4f4f5; text-align:left; padding:9px 10px; font-size:11px; text-transform:uppercase; letter-spacing:.5px; }
  .items td{ padding:9px 10px; border-bottom:1px solid #e4e4e7; }
  .totals td{ padding:6px 10px; }
  .due{ background:#9e005d; color:#fff; }
  .muted{ color:#71717a; }
  .box{ border:1px solid #e4e4e7; border-radius:8px; padding:14px 16px; margin-top:18px; }
  .footer{ margin-top:26px; font-size:10px; color:#71717a; text-align:center; }
</style>
</head>
<body>
<div class="brand-bar">
  <h1>TTRYY</h1>
  <p>Websites · Prospects · Opportunities · Business tools — Kampala, Uganda</p>
</div>
<div class="wrap">
  <table class="meta">
    <tr>
      <td>
        <h2 style="margin:0 0 4px;font-size:20px;">INVOICE</h2>
        <div><strong>{{ $order->reference }}</strong></div>
        <div class="muted">Issued {{ $order->created_at->format('d M Y') }} · Status: {{ strtoupper($order->status) }}</div>
      </td>
      <td style="text-align:right;">
        <div><strong>Billed to</strong></div>
        <div>{{ $user->name }}</div>
        <div>{{ $order->business_name }}</div>
        <div>{{ $order->phone }}</div>
        <div class="muted">{{ $user->email }}</div>
      </td>
    </tr>
  </table>

  <table class="items" style="margin-top:22px;">
    <tr><th>Description</th><th style="text-align:right;">Amount (UGX)</th></tr>
    <tr>
      <td>{{ $order->package }} package — {{ ucfirst($order->billing_frequency) }} × {{ $order->periods }} @ {{ number_format($order->amount_per_period) }} ({{ $order->duration_months }} months)</td>
      <td style="text-align:right;">{{ number_format($order->amount_per_period * $order->periods) }}</td>
    </tr>
    <tr>
      <td>Domain ({{ $order->domain }}) — one-time fee</td>
      <td style="text-align:right;">{{ number_format($order->domain_fee) }}</td>
    </tr>
  </table>

  <table class="totals" style="margin-top:10px;">
    <tr><td class="muted">Grand total over term</td><td style="text-align:right;"><strong>UGX {{ number_format($order->total_amount) }}</strong></td></tr>
    <tr class="due"><td><strong>Due today (domain + first payment)</strong></td><td style="text-align:right;"><strong>UGX {{ number_format($order->due_today) }}</strong></td></tr>
  </table>

  <div class="box">
    <strong>How to pay</strong>
    <div style="margin-top:6px;">MTN Mobile Money → merchant payment → code <strong>{{ $momoCode }}</strong> (Ttryy), amount <strong>UGX {{ number_format($order->due_today) }}</strong>, narration <strong>{{ $order->reference }}</strong>. Card and mobile-money payments are also accepted online at checkout.</div>
  </div>

  <div class="footer">Thank you for choosing Ttryy. This invoice was generated automatically — please quote {{ $order->reference }} in all correspondence.</div>
</div>
</body>
</html>
