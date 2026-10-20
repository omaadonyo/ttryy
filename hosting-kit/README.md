# ttryy hosting service kit

Drop-in code that lets any hosted website you build talk to the central
ttryy platform. Our server watches the subscription clock; when a site falls
past its renewal (after the grace period) it sends a **signed** command to
the site, the site flips one local flag, and visitors get a neutral 503
notice. When the balance is cleared, the platform sends `unsuspend` and the
site comes straight back.

**Nothing is ever deleted** — no files, databases, domains or content. Only
a single state value changes.

## What's in here

```
hosting-kit/
  app/Services/TtryyService/ServiceCommandProcessor.php   # command rules + acks
  app/Services/TtryyService/ServiceState.php              # local state store
  app/Services/TtryyService/ServiceAuthenticator.php      # HMAC verification
  app/Http/Controllers/TtryyServiceController.php         # 3 endpoints
  app/Http/Middleware/EnsureSiteActive.php                # 503 while suspended
  resources/views/ttryy-service/suspended.blade.php       # visitor notice
  config/ttryy-service.php                                # configuration
  routes/service.php                                      # route registrations
```

## Install (5 minutes)

1. **Copy the classes** into the hosted app, preserving namespaces:
   - `app/Services/TtryyService/*` → `app/Services/TtryyService/`
   - `app/Http/Controllers/TtryyServiceController.php` → `app/Http/Controllers/`
   - `app/Http/Middleware/EnsureSiteActive.php` → `app/Http/Middleware/`
   - `config/ttryy-service.php` → `config/`
   - `resources/views/ttryy-service/suspended.blade.php` → `resources/views/ttryy-service/`
   - `routes/service.php` → `routes/`

2. **Issue a credential** in central ttryy: *Hosting → your website →
   Service connection → Issue credential*. Copy the key id, secret and site
   UUID into the hosted site's `.env`:

   ```env
   TTRYY_SERVICE_KEY_ID=hs_xxxxxxxx
   TTRYY_SERVICE_SECRET=xxxxxxxxxxxxxxxxxxxxxxxx
   TTRYY_SERVICE_SITE=<website uuid>
   TTRYY_SERVICE_MODE=middleware
   ```

3. **Register the routes.** See the header comment in `routes/service.php`
   (Laravel 11+/12 `bootstrap/app.php` `then:` closure, or the
   `RouteServiceProvider` on older versions).

4. **Register the middleware** on your main `web` group so visitors are
   blocked while suspended, e.g. in `bootstrap/app.php`:

   ```php
   ->withMiddleware(function (Middleware $middleware) {
       $middleware->web(append: [
           \App\Http\Middleware\EnsureSiteActive::class,
       ]);
   })
   ```

   The middleware ignores `hosting-service/*`, so the platform can always
   reach the site to restore it. If you'd rather let your web server handle
   the block instead, set `TTRYY_SERVICE_MODE=flag` and the middleware
   becomes a no-op.

5. **Point central ttryy at the site.** In *Hosting → website → Service
   connection*, set the service URL (e.g. `https://client-site.com`) and save.
   The platform appends `/hosting-service/...` automatically.

6. **Test it.** From central ttryy, use *Suspend* then *Restore* on the
   website. Visiting the hosted site should show the 503 notice while
   suspended and load normally once restored. `GET /hosting-service/health`
   is public and returns `{"ok":true}`.

## How the handshake works

Each command is sent as `POST /hosting-service/commands` with a JSON body
(`operation_id`, `action`, `site_uuid`, `payload`) and these headers:

| Header | Meaning |
| --- | --- |
| `X-Service-Key` | issued key id |
| `X-Service-Timestamp` | unix seconds (rejected outside the tolerance window) |
| `X-Service-Nonce` | the operation uuid (replay-guarded centrally) |
| `X-Service-Site` | website uuid |
| `X-Service-Signature` | see below |

```
signature = HMAC_SHA256(secret,
    timestamp "\n" nonce "\n" METHOD "\n" PATH "\n" sha256(body) "\n" site_uuid)
```

`ServiceAuthenticator::verify()` reproduces this exactly, so the kit verifies
without ever trusting the network.

## Notes

- `health` is public and deliberately says nothing about the site.
- `status` and `commands` are both signature-protected.
- The state file lives at `storage/app/ttryy-service.json` by default
  (`TTRYY_SERVICE_STATE` overrides it). It is safe to delete — the site is
  then treated as active until the next command arrives.
