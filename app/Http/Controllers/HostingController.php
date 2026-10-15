<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessHostingCommand;
use App\Models\HostedWebsite;
use App\Models\HostingAuditLog;
use App\Models\HostingCommand;
use App\Models\HostingPlan;
use App\Models\User;
use App\Models\WebsiteDailyAnalytics;
use App\Models\WebsiteDomain;
use App\Notifications\SiteAlert;
use App\Services\Hosting\AnalyticsRecorder;
use App\Services\Hosting\ServiceCommander;
use App\Services\Hosting\SubdomainGenerator;
use App\Services\Hosting\SubdomainProvisioner;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HostingController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $query = $request->user()->hostedWebsites()->with(['primaryDomain', 'plan'])->latest();

        if ($request->filled('q')) {
            $q = '%'.$request->string('q').'%';
            $query->where(fn ($w) => $w
                ->where('name', 'like', $q)
                ->orWhereHas('domains', fn ($d) => $d->where('domain', 'like', $q)));
        }

        if ($request->filled('status') && in_array($request->string('status'), HostedWebsite::STATUSES, true)) {
            $query->where('status', $request->string('status'));
        }

        $sort = $request->string('sort', 'latest');
        $query = match ($sort) {
            'name' => $query->orderBy('name'),
            'status' => $query->orderBy('status')->latest(),
            default => $query,
        };

        $base = $request->user()->hostedWebsites();

        return view('hosting.index', [
            'websites' => $query->paginate(12)->withQueryString(),
            'filters' => $request->only(['q', 'status', 'sort']),
            'stats' => [
                'total' => (clone $base)->count(),
                'active' => (clone $base)->where('status', HostedWebsite::STATUS_ACTIVE)->count(),
                'suspended' => (clone $base)->where('status', HostedWebsite::STATUS_SUSPENDED)->count(),
                'pending' => (clone $base)->whereIn('status', [HostedWebsite::STATUS_PENDING, HostedWebsite::STATUS_PROVISIONING])->count(),
                'domains' => WebsiteDomain::where('user_id', $request->user()->id)->count(),
            ],
            'plan' => $request->user()->packageOrders()->where('status', 'paid')->latest()->first()?->package,
            'outstanding' => (int) $request->user()->packageOrders()->whereIn('status', ['pending', 'submitted'])
                ->sum('total_amount'),
            'nextRenewal' => ($min = $request->user()->hostedWebsites()->whereNotNull('next_renewal_at')->min('next_renewal_at')) ? \Carbon\Carbon::parse($min) : null,
        ]);
    }

    public function create()
    {
        return view('hosting.create', [
            'plans' => HostingPlan::where('is_active', true)->orderBy('price')->get(),
            'baseDomain' => config('hosting.base_domain'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'site_type' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:1000'],
            'hosting_plan_id' => ['required', 'exists:hosting_plans,id'],
            'subdomain' => ['nullable', 'string', 'max:63'],
            'custom_domain' => ['nullable', 'string', 'max:253'],
        ]);

        $plan = HostingPlan::findOrFail($validated['hosting_plan_id']);

        if (! $plan->is_active) {
            return back()->withErrors(['hosting_plan_id' => 'That plan is no longer available.'])->withInput();
        }

        if ($validated['subdomain'] ?? null) {
            $label = strtolower(trim($validated['subdomain']));
            if (! SubdomainGenerator::validLabel($label)) {
                return back()->withErrors(['subdomain' => 'Use letters, numbers and hyphens only.'])->withInput();
            }
            if (SubdomainGenerator::isReserved($label)) {
                return back()->withErrors(['subdomain' => 'That subdomain is reserved.'])->withInput();
            }
        }

        if ($validated['custom_domain'] ?? null) {
            $domain = strtolower(trim($validated['custom_domain']));
            if (! self::validHostname($domain) || $domain === config('hosting.base_domain')
                || str_ends_with($domain, '.'.config('hosting.base_domain'))) {
                return back()->withErrors(['custom_domain' => 'Enter a valid domain you own (not a '.config('hosting.base_domain').' subdomain).'])->withInput();
            }
            if (WebsiteDomain::where('domain', $domain)->exists()) {
                return back()->withErrors(['custom_domain' => 'That domain is already registered on ttryy.'])->withInput();
            }
        }

        $website = DB::transaction(function () use ($request, $validated, $plan) {
            $website = $request->user()->hostedWebsites()->create([
                'uuid' => (string) Str::uuid(),
                'hosting_plan_id' => $plan->id,
                'name' => $validated['name'],
                'site_type' => $validated['site_type'] ?? null,
                'description' => $validated['description'] ?? null,
                'status' => HostedWebsite::STATUS_PROVISIONING,
                'next_renewal_at' => now()->addYear(),
            ]);

            $label = $validated['subdomain'] ?? null
                ? strtolower(trim($validated['subdomain']))
                : SubdomainGenerator::generate($validated['name']);

            if (SubdomainGenerator::isTaken(SubdomainGenerator::fullDomain($label))) {
                $label = SubdomainGenerator::generate($validated['name']);
            }

            $primary = $website->domains()->create([
                'user_id' => $request->user()->id,
                'domain' => SubdomainGenerator::fullDomain($label),
                'type' => WebsiteDomain::TYPE_SUBDOMAIN,
                'is_primary' => true,
                'verification' => 'verified',
            ]);

            $website->forceFill(['primary_domain_id' => $primary->id])->save();

            SubdomainProvisioner::provision($primary->fresh());

            if (! empty($validated['custom_domain'])) {
                $website->domains()->create([
                    'user_id' => $request->user()->id,
                    'domain' => strtolower(trim($validated['custom_domain'])),
                    'type' => WebsiteDomain::TYPE_CUSTOM,
                    'is_primary' => false,
                    'verification' => 'pending',
                    'verification_token' => 'ttryy-verification='.Str::random(32),
                ]);
            }

            return $website;
        });

        HostingAuditLog::log('website.registered', [
            'actor_type' => 'user',
            'actor_id' => $request->user()->id,
            'hosted_website_id' => $website->id,
            'client_id' => $request->user()->id,
            'meta' => ['plan' => $plan->code],
            'ip' => $request->ip(),
        ]);

        // Provisioning starts as a tracked command. With the default manual
        // driver there is no server automation, so the command records that
        // honestly and the site waits for team confirmation.
        $command = ServiceCommander::build(
            $website, HostingCommand::ACTION_PROVISION, 'system', null, ['plan' => $plan->code]
        );
        ProcessHostingCommand::dispatch($command->id);

        $request->user()->notify(new SiteAlert(
            "Website {$website->name} registered",
            'Provisioning started. We will confirm once your site is live.',
            route('hosting.show', $website),
            'hosting'
        ));

        foreach (User::where('is_admin', true)->get() as $admin) {
            $admin->notify(new SiteAlert(
                "New website: {$website->name}",
                $request->user()->name.' · '.$plan->code.' plan.',
                route('admin.hosting.websites.show', $website),
                'hosting'
            ));
        }

        return redirect()->route('hosting.show', $website)
            ->with('status', "Website {$website->name} registered — provisioning started.");
    }

    public function show(HostedWebsite $website)
    {
        $this->authorize('view', $website);

        $website->load(['domains', 'plan', 'orders' => fn ($q) => $q->latest()]);

        return view('hosting.show', [
            'website' => $website,
            'primary' => $website->primaryDomain,
            'recentChecks' => $website->healthChecks()->latest('checked_at')->take(10)->get(),
            'recentCommands' => $website->commands()->latest()->take(8)->get(),
            'recentAudit' => HostingAuditLog::where('hosted_website_id', $website->id)->latest()->take(10)->get(),
            'last30' => WebsiteDailyAnalytics::where('hosted_website_id', $website->id)
                ->orderBy('date', 'desc')->take(30)->get(),
            'totals' => [
                'paid' => (int) $website->orders()->where('status', 'paid')->sum('paid_amount'),
                'owed' => (int) $website->orders()->whereIn('status', ['pending', 'submitted'])
                    ->sum('total_amount'),
            ],
        ]);
    }

    public function destroy(HostedWebsite $website)
    {
        $this->authorize('delete', $website);

        $name = $website->name;

        HostingAuditLog::log('website.cancelled', [
            'actor_type' => request()->user()->is_admin ? 'admin' : 'user',
            'actor_id' => request()->user()->id,
            'hosted_website_id' => $website->id,
            'client_id' => $website->user_id,
            'meta' => ['status_before' => $website->status],
            'ip' => request()->ip(),
        ]);

        $website->delete();

        return redirect()->route('hosting.index')->with('status', "Website {$name} cancelled and removed.");
    }

    // ---------- Domains ----------

    public function domains(HostedWebsite $website)
    {
        $this->authorize('view', $website);

        return view('hosting.domains', [
            'website' => $website,
            'domains' => $website->domains()->latest()->get(),
            'baseDomain' => config('hosting.base_domain'),
            'suggested' => SubdomainGenerator::generate($website->name, $website->id),
        ]);
    }

    public function storeSubdomain(Request $request, HostedWebsite $website)
    {
        $this->authorize('update', $website);

        $validated = $request->validate([
            'label' => ['nullable', 'string', 'max:63'],
        ]);

        $label = $validated['label'] ?? null
            ? strtolower(trim($validated['label']))
            : SubdomainGenerator::generate($website->name, $website->id);

        if (! SubdomainGenerator::validLabel($label)) {
            return back()->withErrors(['label' => 'Use letters, numbers and hyphens only (no leading/trailing hyphen).']);
        }

        if (SubdomainGenerator::isReserved($label)) {
            return back()->withErrors(['label' => 'That subdomain is reserved for system use.']);
        }

        $domain = SubdomainGenerator::fullDomain($label);

        if (WebsiteDomain::where('domain', $domain)->exists()) {
            return back()->withErrors(['label' => 'That subdomain is already taken. Try another.']);
        }

        $record = $website->domains()->create([
            'user_id' => $request->user()->id,
            'domain' => $domain,
            'type' => WebsiteDomain::TYPE_SUBDOMAIN,
            'is_primary' => $website->domains()->count() === 0,
            'verification' => 'verified',
        ]);

        if ($website->domains()->count() === 1) {
            $website->forceFill(['primary_domain_id' => $record->id])->save();
        }

        $provision = SubdomainProvisioner::provision($record->fresh());

        HostingAuditLog::log('domain.subdomain_added', [
            'actor_type' => $request->user()->is_admin ? 'admin' : 'user',
            'actor_id' => $request->user()->id,
            'hosted_website_id' => $website->id,
            'client_id' => $website->user_id,
            'meta' => ['domain' => $domain, 'provision' => $provision['mode']],
            'ip' => $request->ip(),
        ]);

        return back()->with('status', "Subdomain {$domain} added. {$provision['message']}");
    }

    public function updateSubdomain(Request $request, HostedWebsite $website, WebsiteDomain $domain)
    {
        $this->authorize('update', $domain);
        abort_if($domain->hosted_website_id !== $website->id, 404);
        abort_if($domain->type !== WebsiteDomain::TYPE_SUBDOMAIN, 422, 'Only ttryy subdomains can be renamed here.');

        $validated = $request->validate(['label' => ['required', 'string', 'max:63']]);
        $label = strtolower(trim($validated['label']));

        if (! SubdomainGenerator::validLabel($label) || SubdomainGenerator::isReserved($label)) {
            return back()->withErrors(['label' => 'That subdomain label is not allowed.']);
        }

        $new = SubdomainGenerator::fullDomain($label);

        if ($new !== $domain->domain && WebsiteDomain::where('domain', $new)->exists()) {
            return back()->withErrors(['label' => 'That subdomain is already taken.']);
        }

        $old = $domain->domain;
        $domain->forceFill(['domain' => $new, 'dns_ok' => false, 'ssl_status' => 'pending'])->save();

        HostingAuditLog::log('domain.subdomain_changed', [
            'actor_type' => $request->user()->is_admin ? 'admin' : 'user',
            'actor_id' => $request->user()->id,
            'hosted_website_id' => $website->id,
            'client_id' => $website->user_id,
            'meta' => ['from' => $old, 'to' => $new],
            'ip' => $request->ip(),
        ]);

        return back()->with('status', "Subdomain changed to {$new}. DNS and SSL re-checks are pending.");
    }

    public function storeDomain(Request $request, HostedWebsite $website)
    {
        $this->authorize('update', $website);

        $validated = $request->validate([
            'domain' => ['required', 'string', 'max:253'],
        ]);

        $domain = strtolower(trim($validated['domain']));

        if (! self::validHostname($domain)) {
            return back()->withErrors(['domain' => 'Enter a valid domain name.']);
        }

        if ($domain === config('hosting.base_domain') || str_ends_with($domain, '.'.config('hosting.base_domain'))) {
            return back()->withErrors(['domain' => 'That belongs to ttryy — use subdomain generation instead.']);
        }

        if (WebsiteDomain::where('domain', $domain)->exists()) {
            return back()->withErrors(['domain' => 'That domain is already registered on ttryy.']);
        }

        $website->domains()->create([
            'user_id' => $request->user()->id,
            'domain' => $domain,
            'type' => WebsiteDomain::TYPE_CUSTOM,
            'is_primary' => false,
            'verification' => 'pending',
            'verification_token' => 'ttryy-verification='.Str::random(32),
        ]);

        HostingAuditLog::log('domain.added', [
            'actor_type' => $request->user()->is_admin ? 'admin' : 'user',
            'actor_id' => $request->user()->id,
            'hosted_website_id' => $website->id,
            'client_id' => $website->user_id,
            'meta' => ['domain' => $domain],
            'ip' => $request->ip(),
        ]);

        return back()->with('status', "Domain {$domain} added — verify ownership with the DNS TXT record below.");
    }

    public function verifyDomain(Request $request, HostedWebsite $website, WebsiteDomain $domain)
    {
        $this->authorize('update', $domain);
        abort_if($domain->hosted_website_id !== $website->id, 404);

        if ($domain->type === WebsiteDomain::TYPE_SUBDOMAIN) {
            return back()->with('status', 'ttryy subdomains are verified by definition.');
        }

        if ($domain->verification === 'verified') {
            return back()->with('status', 'Domain is already verified.');
        }

        $found = false;

        try {
            $records = dns_get_record('_ttryy-challenge.'.$domain->domain, DNS_TXT);

            foreach ($records ?: [] as $record) {
                if (isset($record['txt']) && str_contains($record['txt'], (string) $domain->verification_token)) {
                    $found = true;
                    break;
                }
            }
        } catch (\Throwable) {
            $found = false;
        }

        $domain->forceFill([
            'verification' => $found ? 'verified' : 'pending',
            'dns_ok' => $found,
            'dns_checked_at' => now(),
            'ssl_status' => $found ? 'pending' : $domain->ssl_status,
        ])->save();

        HostingAuditLog::log('domain.verification_checked', [
            'actor_type' => $request->user()->is_admin ? 'admin' : 'user',
            'actor_id' => $request->user()->id,
            'hosted_website_id' => $website->id,
            'client_id' => $website->user_id,
            'outcome' => $found ? 'success' : 'failure',
            'reason' => $found ? null : 'txt_not_found',
            'meta' => ['domain' => $domain->domain],
            'ip' => $request->ip(),
        ]);

        return back()->with(
            'status',
            $found
                ? "Domain {$domain->domain} verified — DNS confirmed, SSL provisioning queued."
                : 'Verification record not found yet. Add the TXT record and try again (DNS can take minutes to propagate).'
        );
    }

    public function setPrimary(Request $request, HostedWebsite $website, WebsiteDomain $domain)
    {
        $this->authorize('update', $domain);
        abort_if($domain->hosted_website_id !== $website->id, 404);

        if (! $domain->isVerified()) {
            return back()->withErrors(['domain' => 'Only verified domains can be primary.']);
        }

        DB::transaction(function () use ($website, $domain, $request) {
            // Direct queries: in-memory models may already read is_primary
            // as true, which would make save() a dirty-check no-op.
            $website->domains()->update(['is_primary' => false]);
            $website->domains()->where('id', $domain->id)->update(['is_primary' => true]);
            $website->forceFill(['primary_domain_id' => $domain->id])->save();

            HostingAuditLog::log('domain.primary_changed', [
                'actor_type' => $request->user()->is_admin ? 'admin' : 'user',
                'actor_id' => $request->user()->id,
                'hosted_website_id' => $website->id,
                'client_id' => $website->user_id,
                'meta' => ['domain' => $domain->domain],
                'ip' => $request->ip(),
            ]);
        });

        return back()->with('status', "Primary domain set to {$domain->domain}.");
    }

    public function destroyDomain(Request $request, HostedWebsite $website, WebsiteDomain $domain)
    {
        $this->authorize('delete', $domain);
        abort_if($domain->hosted_website_id !== $website->id, 404);

        $name = $domain->domain;
        $domain->delete();

        if ($website->primary_domain_id === $domain->id) {
            $replacement = $website->domains()->first();
            $website->forceFill(['primary_domain_id' => $replacement?->id])->save();
            $replacement?->forceFill(['is_primary' => true])->save();
        }

        HostingAuditLog::log('domain.removed', [
            'actor_type' => $request->user()->is_admin ? 'admin' : 'user',
            'actor_id' => $request->user()->id,
            'hosted_website_id' => $website->id,
            'client_id' => $website->user_id,
            'meta' => ['domain' => $name],
            'ip' => $request->ip(),
        ]);

        return back()->with('status', "Domain {$name} removed.");
    }

    // ---------- Analytics ----------

    public function analytics(Request $request, HostedWebsite $website)
    {
        $this->authorize('view', $website);

        $range = (int) $request->query('range', 30);
        $range = in_array($range, [7, 30, 90], true) ? $range : 30;

        $days = WebsiteDailyAnalytics::where('hosted_website_id', $website->id)
            ->where('date', '>=', now()->subDays($range)->toDateString())
            ->orderBy('date')
            ->get();

        $mergeTops = function ($key) use ($days) {
            $merged = [];
            foreach ($days as $day) {
                foreach ($day->{$key} ?? [] as $row) {
                    $label = (string) ($row['label'] ?? 'unknown');
                    $merged[$label] = ($merged[$label] ?? 0) + (int) ($row['count'] ?? 0);
                }
            }
            arsort($merged);

            return collect($merged)->take(10)->map(fn ($c, $label) => ['label' => $label, 'count' => $c])->values()->all();
        };

        return view('hosting.analytics', [
            'website' => $website,
            'range' => $range,
            'days' => $days,
            'totals' => [
                'uniques' => $days->sum('uniques'),
                'visits' => $days->sum('visits'),
                'pageviews' => $days->sum('pageviews'),
            ],
            'topPages' => $mergeTops('top_pages'),
            'sources' => $mergeTops('sources'),
            'devices' => $mergeTops('devices'),
        ]);
    }

    public function exportAnalytics(Request $request, HostedWebsite $website)
    {
        $this->authorize('view', $website);

        $range = (int) $request->query('range', 30);
        $range = in_array($range, [7, 30, 90], true) ? $range : 30;

        $days = WebsiteDailyAnalytics::where('hosted_website_id', $website->id)
            ->where('date', '>=', now()->subDays($range)->toDateString())
            ->orderBy('date')
            ->get();

        $filename = 'analytics-'.$website->uuid.'-'.$range.'d.csv';

        return response()->streamDownload(function () use ($days) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['date', 'unique_visitors', 'visits', 'pageviews']);
            foreach ($days as $day) {
                fputcsv($out, [$day->date->toDateString(), $day->uniques, $day->visits, $day->pageviews]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    // ---------- Health ----------

    public function health(HostedWebsite $website)
    {
        $this->authorize('view', $website);

        return view('hosting.health', [
            'website' => $website,
            'latest' => $website->latestHealthCheck(),
            'checks' => $website->healthChecks()->latest('checked_at')->paginate(20),
            'incidents' => $website->healthChecks()->where('incident', true)->latest('checked_at')->take(10)->get(),
            'uptime30' => $this->uptimePercent($website, 30),
        ]);
    }

    protected function uptimePercent(HostedWebsite $website, int $days): ?float
    {
        $checks = $website->healthChecks()
            ->where('checked_at', '>=', now()->subDays($days))
            ->whereNotNull('http_status')
            ->get(['status']);

        if ($checks->isEmpty()) {
            return null;
        }

        $ok = $checks->whereIn('status', [
            HostedWebsite::HEALTH_HEALTHY, HostedWebsite::HEALTH_DEGRADED,
        ])->count();

        return round($ok / $checks->count() * 100, 1);
    }

    // ---------- Billing ----------

    public function billing(HostedWebsite $website)
    {
        $this->authorize('view', $website);

        $orders = $website->orders()->latest()->get();

        return view('hosting.billing', [
            'website' => $website,
            'orders' => $orders,
            'paid' => (int) $orders->where('status', 'paid')->sum('paid_amount'),
            'owed' => (int) $orders->whereIn('status', ['pending', 'submitted'])
                ->sum(fn ($o) => $o->total_amount - $o->paid_amount),
        ]);
    }

    // ---------- Reports ----------

    public function reports(HostedWebsite $website)
    {
        $this->authorize('view', $website);

        $days = WebsiteDailyAnalytics::where('hosted_website_id', $website->id)
            ->orderBy('date', 'desc')->take(30)->get();

        return view('hosting.reports', [
            'website' => $website,
            'days' => $days,
            'checks' => $website->healthChecks()->latest('checked_at')->take(30)->get(),
            'orders' => $website->orders()->latest()->get(),
        ]);
    }

    public function reportPdf(HostedWebsite $website)
    {
        $this->authorize('view', $website);

        $pdf = Pdf::loadView('hosting.report-pdf', [
            'website' => $website,
            'days' => WebsiteDailyAnalytics::where('hosted_website_id', $website->id)
                ->orderBy('date', 'desc')->take(30)->get(),
            'orders' => $website->orders()->latest()->get(),
            'generated' => now(),
        ]);

        return $pdf->download('website-report-'.$website->uuid.'.pdf');
    }

    // ---------- Suspension requests ----------

    public function requestSuspend(Request $request, HostedWebsite $website)
    {
        $this->authorize('requestSuspend', $website);

        $validated = $request->validate(['reason' => ['nullable', 'string', 'max:255']]);

        $command = ServiceCommander::build(
            $website,
            HostingCommand::ACTION_SUSPEND,
            $request->user()->is_admin ? 'admin' : 'user',
            $request->user()->id,
            ['reason' => $validated['reason'] ?? 'Client request']
        );

        HostingAuditLog::log('suspension.requested', [
            'actor_type' => $request->user()->is_admin ? 'admin' : 'user',
            'actor_id' => $request->user()->id,
            'hosted_website_id' => $website->id,
            'client_id' => $website->user_id,
            'operation_id' => $command->uuid,
            'meta' => ['reason' => $validated['reason'] ?? 'Client request'],
            'ip' => $request->ip(),
        ]);

        ProcessHostingCommand::dispatch($command->id);

        return back()->with('status', 'Suspension requested — the website service must acknowledge before the status changes.');
    }

    public function requestReactivate(Request $request, HostedWebsite $website)
    {
        $this->authorize('view', $website);
        abort_unless($website->status === HostedWebsite::STATUS_SUSPENDED, 422, 'Only suspended websites can be reactivated.');
        abort_unless($request->user()->is_admin || $website->user_id === $request->user()->id, 403);

        $command = ServiceCommander::build(
            $website,
            HostingCommand::ACTION_UNSUSPEND,
            $request->user()->is_admin ? 'admin' : 'user',
            $request->user()->id,
            ['reason' => 'Reactivation requested']
        );

        HostingAuditLog::log('restoration.requested', [
            'actor_type' => $request->user()->is_admin ? 'admin' : 'user',
            'actor_id' => $request->user()->id,
            'hosted_website_id' => $website->id,
            'client_id' => $website->user_id,
            'operation_id' => $command->uuid,
            'ip' => $request->ip(),
        ]);

        ProcessHostingCommand::dispatch($command->id);

        return back()->with('status', 'Restoration requested — the website service must acknowledge before the status changes.');
    }

    // ---------- Tracking beacon (public) ----------

    public function beacon(Request $request, string $uuid)
    {
        $website = HostedWebsite::where('uuid', $uuid)->first();

        if ($website && config('hosting.analytics.enabled')) {
            try {
                AnalyticsRecorder::record($website, $request);
            } catch (\Throwable) {
            }
        }

        return response(base64_decode('R0lGODlhAQABAIAAAP///////yH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=='), 200, [
            'Content-Type' => 'image/gif',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }

    public static function validHostname(string $domain): bool
    {
        return (bool) preg_match('/^(?!-)[a-z0-9-]{1,63}(?<!-)(\.[a-z0-9-]{1,63})+$/i', $domain);
    }
}
