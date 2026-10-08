<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family:Arial,sans-serif;color:#18181b;line-height:1.6;margin:0;padding:24px;background:#f4f4f5;">
<div style="max-width:560px;margin:0 auto;background:#ffffff;border-radius:12px;overflow:hidden;">
  <div style="background:#9e005d;color:#fff;padding:20px 28px;">
    <div style="font-size:22px;font-weight:800;letter-spacing:1px;">TTRYY</div>
    <div style="font-size:12px;">{{ $toAdmin ? 'New order received' : 'Order received — thank you!' }}</div>
  </div>
  <div style="padding:24px 28px;">
    @if($toAdmin)
    <p>A new package order was placed and needs follow-up (payment confirmation + onboarding).</p>
    @else
    <p>Hi {{ $order->user->name }},</p>
    <p>Thanks for choosing Ttryy! Your order is received. A representative will contact you on <strong>{{ $order->phone }}</strong> to confirm and collect payment.</p>
    @endif
    <table style="width:100%;border-collapse:collapse;font-size:13px;margin-top:12px;">
      <tr><td style="padding:6px 0;color:#71717a;">Reference</td><td style="padding:6px 0;text-align:right;"><strong>{{ $order->reference }}</strong></td></tr>
      <tr><td style="padding:6px 0;color:#71717a;">Package</td><td style="padding:6px 0;text-align:right;"><strong>{{ $order->package }}</strong></td></tr>
      <tr><td style="padding:6px 0;color:#71717a;">Billing</td><td style="padding:6px 0;text-align:right;"><strong>{{ ucfirst($order->billing_frequency) }} × {{ $order->periods }} ({{ $order->duration_months }} months)</strong></td></tr>
      <tr><td style="padding:6px 0;color:#71717a;">Domain (one-time)</td><td style="padding:6px 0;text-align:right;"><strong>{{ $order->domain }} — UGX {{ number_format($order->domain_fee) }}</strong></td></tr>
      <tr><td style="padding:6px 0;color:#71717a;">Business</td><td style="padding:6px 0;text-align:right;"><strong>{{ $order->business_name }}</strong></td></tr>
      @if($order->niche)<tr><td style="padding:6px 0;color:#71717a;">Niche</td><td style="padding:6px 0;text-align:right;"><strong>{{ $order->niche }}</strong></td></tr>@endif
      <tr><td style="padding:6px 0;color:#71717a;">Payment method</td><td style="padding:6px 0;text-align:right;"><strong>{{ $order->payment_method === 'momo_manual' ? 'Mobile Money (manual)' : 'Online' }}</strong> · {{ $order->status }}</td></tr>
      <tr><td style="padding:10px 0;color:#9e005d;"><strong>Due today</strong></td><td style="padding:10px 0;text-align:right;font-size:18px;"><strong>UGX {{ number_format($order->due_today) }}</strong></td></tr>
      <tr><td style="padding:6px 0;color:#71717a;">Total over term</td><td style="padding:6px 0;text-align:right;"><strong>UGX {{ number_format($order->total_amount) }}</strong></td></tr>
    </table>
    @if($toAdmin)
    <p style="font-size:12px;color:#71717a;">Customer: {{ $order->user->name }} ({{ $order->user->email }}, {{ $order->phone }})</p>
    @else
    <p style="font-size:12px;color:#71717a;">Please quote <strong>{{ $order->reference }}</strong> in all correspondence. Pay via MTN MoMo merchant code <strong>{{ config('services.momo.merchant_code') }}</strong> or confirm on WhatsApp.</p>
    @endif
  </div>
</div>
</body>
</html>
