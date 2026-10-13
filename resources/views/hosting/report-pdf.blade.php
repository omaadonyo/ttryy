<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  body { font-family: sans-serif; color: #222; font-size: 12px; }
  h1 { font-size: 22px; margin-bottom: 2px; }
  h2 { font-size: 14px; margin: 18px 0 6px; color: #9e005d; }
  table { width: 100%; border-collapse: collapse; margin-top: 6px; }
  th, td { border: 1px solid #ddd; padding: 5px 7px; text-align: left; }
  th { background: #f5f5f5; }
  .muted { color: #777; }
</style>
</head>
<body>
  <h1>Website report — {{ $website->name }}</h1>
  <p class="muted">Generated {{ $generated->format('d M Y H:i') }} · ID {{ substr($website->uuid, 0, 8) }} · Status {{ $website->status }}</p>

  <h2>Traffic (last 30 days)</h2>
  <table>
    <tr><th>Date</th><th>Uniques</th><th>Visits</th><th>Page views</th></tr>
    @foreach($days as $d)
    <tr><td>{{ $d->date->format('d M Y') }}</td><td>{{ $d->uniques }}</td><td>{{ $d->visits }}</td><td>{{ $d->pageviews }}</td></tr>
    @endforeach
  </table>

  <h2>Orders</h2>
  <table>
    <tr><th>Reference</th><th>Total (UGX)</th><th>Paid (UGX)</th><th>Status</th></tr>
    @foreach($orders as $o)
    <tr><td>{{ $o->reference }}</td><td>{{ number_format($o->total_amount) }}</td><td>{{ number_format($o->paid_amount) }}</td><td>{{ $o->status }}</td></tr>
    @endforeach
  </table>
</body>
</html>
