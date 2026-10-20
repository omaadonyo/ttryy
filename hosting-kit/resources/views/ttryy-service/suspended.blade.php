<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $notice['title'] ?? 'Website temporarily unavailable' }}</title>
    <style>
        :root { color-scheme: light dark; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px;
               font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
               background: #f4f4f5; color: #18181b; }
        .card { width: 100%; max-width: 520px; background: #fff; border: 1px solid rgba(0,0,0,.08);
                border-radius: 20px; padding: 40px 32px; text-align: center;
                box-shadow: 0 24px 60px -30px rgba(0,0,0,.35); }
        .badge { display: inline-block; font-size: 11px; font-weight: 800; letter-spacing: .14em;
                 text-transform: uppercase; color: #9e005d; background: #fce7f5;
                 padding: 5px 12px; border-radius: 999px; }
        h1 { font-size: 22px; line-height: 1.25; margin: 18px 0 10px; letter-spacing: -.01em; }
        p { font-size: 15px; line-height: 1.6; color: #52525b; margin: 0 0 18px; }
        a.contact { display: inline-block; margin-top: 6px; font-weight: 700; color: #9e005d;
                    text-decoration: none; }
        .fine { font-size: 12px; color: #a1a1aa; margin-top: 22px; }
        @media (prefers-color-scheme: dark) {
            body { background: #09090b; color: #fafafa; }
            .card { background: #18181b; border-color: rgba(255,255,255,.1); }
            p { color: #a1a1aa; }
            .fine { color: #71717a; }
            .badge { background: rgba(158,0,93,.2); color: #f0abfc; }
        }
    </style>
</head>
<body>
    <main class="card">
        <span class="badge">Temporarily paused</span>
        <h1>{{ $notice['title'] ?? 'This website is temporarily unavailable' }}</h1>
        <p>{{ $notice['body'] ?? 'This site is paused while its subscription is being settled. No content has been deleted and everything returns the moment the balance is cleared.' }}</p>
        @if(!empty($notice['contact']))
            <a class="contact" href="{{ $notice['contact'] }}">Contact the site owner</a>
        @endif
        <p class="fine">Nothing has been deleted. This page clears itself once the subscription is settled.</p>
    </main>
</body>
</html>
