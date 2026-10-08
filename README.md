# Ttryy — African Business Technology Platform

> **We build your website. We find your potential customers. You close the deals.**

Ttryy helps African businesses get online with a professional website **plus**
niche-specific prospecting resources: potential customers, business directories,
and tender / grant / procurement opportunity resources. The customer contacts
prospects and closes the deal — Ttryy provides the website, the market
intelligence, and the business tools.

This repository contains the Ttryy marketing landing page, built on
[Laravel](https://laravel.com) with Blade + Tailwind CSS + Vite.

## What the landing page includes

- **Hero** — "We Build Your Website. We Find the Customers. You Close Them."
  with an interactive **prospect-scraper demo**: pick a niche (or describe the
  business), watch 10 sample records stream into a PDF preview, with 500–1,000
  further records blurred until a UGX 10,000 unlock (demo data only)
- **The Problem** — why a website alone doesn't bring business
- **How It Works** — 4-step process (niche → website → market → close)
- **More Than a Website** — website, custom CMS (no WordPress), SEO,
  prospects, directories, opportunities, sales resources, business tools
- **Niche Lead Engine** — searchable grid covering 14 industries
  (NGOs, Construction, IT, Marketing, Cleaning, Security, Catering, Printing,
  Furniture, Accounting, Logistics, Agriculture, Solar, Medical)
- **Business Development Model** — what Ttryy does vs. what you do
- **Pricing** — Start (UGX 250,000) / Grow (UGX 350,000) / Business (UGX 650,000)
  + comparison table
- **Application Support**, **Portfolio**, **Why Ttryy**, **Business Tools preview**,
  **FAQ**, final CTA, and a full ecosystem **footer**
- **Lead-capture modal** on every package CTA, WhatsApp CTAs throughout,
  sticky mobile CTA bar, scroll-reveal animations

All prospecting copy deliberately uses "potential customers / prospects /
opportunities" — Ttryy never guarantees customers, contracts, grants, or funding.

## Tech stack

| Layer    | Technology                          |
| -------- | ----------------------------------- |
| Backend  | PHP 8.3+, Laravel 13, Livewire 4    |
| Styling  | Tailwind CSS 4 (via Vite plugin)    |
| Build    | Vite (`vite-plus`)                  |
| Auth kit | Laravel Fortify + Flux UI (unused by landing page) |

The landing page itself is a single Blade view with vanilla JS for the mobile
menu, lead modal, niche search/filter, FAQ accordion, and scroll animations:

```
resources/views/welcome.blade.php   # entire landing page
routes/web.php                      # Route::view('/', 'welcome')
resources/css/app.css               # Tailwind entry
```

## Accounts, dashboard & checkout

Choosing a package sends the visitor to checkout, where guests can **create
an account or log in inline via Livewire** without losing their selections
(choices persist in `localStorage` across sign-in). There they pick a
package (START / GROW / BUSINESS / CORPORATE), a domain (.xyz/.online/.shop
UGX 29,000 one-time or .com/.org UGX 80,000 one-time), a billing rhythm
(full / monthly / weekly / daily), and **how long they want to pay to keep
their website running** (3, 6 or 12 months; full payment covers 12 months).
The domain fee is one-time, always due upfront with the first payment, and
the summary shows the full breakdown: package total, domain fee, first
payment due today, remaining schedule, and grand total. Totals are always
recomputed server-side (`CheckoutController::quote()`), stored as
`package_orders` (`pending` status), and confirmed with an order reference
plus a WhatsApp follow-up link. Every order can download a branded **PDF
invoice**. All account pages live under `/dashboard`
in the app sidebar layout: overview, `packages`, and `checkout`.

**Payments.** Checkout integrates **Flutterwave** (card + Mobile Money
popup, amount = first payment due today, verified server-side before an
order is marked `paid`) with a manual fallback: direct MTN Mobile Money to
merchant code **236512** (order reference as narration), after which the
customer marks the order `submitted` for Ttryy to confirm.

```bash
php artisan migrate                 # creates package_orders (use XAMPP's PHP on Windows: C:\xampp\php\php.exe)
php artisan test --filter=CheckoutTest
```

Configure payments in `.env` (see `.env.example`):

```
FLW_PUBLIC_KEY=FLWPUBK-...     # Flutterwave inline checkout (omit to use manual-only mode)
FLW_SECRET_KEY=FLWSECK-...     # server-side transaction verification
MOMO_MERCHANT_CODE=236512      # MTN MoMo merchant code shown to customers
```

## Getting started

Requirements: PHP 8.3+, Composer, Node.js 20+.

```bash
# 1. Install dependencies
composer install
npm install

# 2. Environment
cp .env.example .env
php artisan key:generate

# 3. Database (SQLite by default)
php artisan migrate

# 4a. Develop (hot reload)
composer dev        # or: npm run dev + php artisan serve

# 4b. Production build
npm run build
php artisan serve
```

Then visit `http://localhost:8000`.

## Configuration

- **WhatsApp number** — the page currently uses the placeholder `256700000000`
  in all `wa.me` links (top bar, nav, hero, floating button, footer, modal).
  Search the view for `256700000000` and replace it with the real business number.
- **Portfolio** — the "Websites We've Built" cards are placeholders; swap the
  `$works` array in `welcome.blade.php` for real projects and screenshots.
- **Lead form** — the modal currently confirms client-side only. Wire
  `leadForm` to a `POST` route / controller + notification (mail/WhatsApp) to
  receive enquiries.

## Tests & lint

```bash
php artisan test        # Pest suite
./vendor/bin/pint --test  # code style (use --parallel on large repos)
```

## License

MIT. © 2026 Ttryy. All rights reserved.
