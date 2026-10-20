<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family:Arial,sans-serif;color:#18181b;line-height:1.6;margin:0;padding:24px;background:#f4f4f5;">
<div style="max-width:560px;margin:0 auto;background:#ffffff;border-radius:12px;overflow:hidden;">
  <div style="background:#9e005d;color:#fff;padding:20px 28px;">
    <div style="font-size:22px;font-weight:800;letter-spacing:1px;">{{ $document->business?->name ?? 'TTRYY' }}</div>
    <div style="font-size:12px;">Payment reminder</div>
  </div>
  <div style="padding:24px 28px;">
    <p>{!! nl2br(e($body)) !!}</p>
    <table style="width:100%;border-collapse:collapse;font-size:13px;margin-top:16px;">
      <tr><td style="padding:6px 0;color:#71717a;">Invoice</td><td style="padding:6px 0;text-align:right;"><strong>{{ $document->number }}</strong></td></tr>
      <tr><td style="padding:6px 0;color:#71717a;">Issued</td><td style="padding:6px 0;text-align:right;"><strong>{{ $document->issue_date?->toFormattedDateString() }}</strong></td></tr>
      @if($document->due_date)
      <tr><td style="padding:6px 0;color:#71717a;">Due</td><td style="padding:6px 0;text-align:right;"><strong>{{ $document->due_date->toFormattedDateString() }}</strong></td></tr>
      @endif
      <tr><td style="padding:10px 0;color:#9e005d;"><strong>Balance due</strong></td><td style="padding:10px 0;text-align:right;font-size:18px;"><strong>{{ $document->business?->currency ?? 'UGX' }} {{ number_format($document->balance()) }}</strong></td></tr>
    </table>
  </div>
</div>
</body>
</html>
