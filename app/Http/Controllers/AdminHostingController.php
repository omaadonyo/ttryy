<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessHostingCommand;
use App\Models\CampaignRecipient;
use App\Models\EmailCampaign;
use App\Models\HostedWebsite;
use App\Models\HostingAuditLog;
use App\Models\HostingCommand;
use App\Models\HostingPlan;
use App\Models\LaunchSignup;
use App\Models\PackageOrder;
use App\Models\SavedContact;
use App\Models\ScrapedProspect;
use App\Models\TokenTopup;
use App\Models\TokenTransaction;
use App\Models\User;
use App\Models\WebsiteDomain;
use App\Models\WebsiteHealthCheck;
use App\Models\WhatsappGroup;
use App\Notifications\SiteAlert;
use App\Services\Hosting\DomainManager;
use App\Services\Hosting\HealthChecker;
use App\Services\Hosting\ServiceAuthenticator;
use App\Services\Hosting\ServiceCommander;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminHostingController extends Controller
{
    use AuthorizesRequests;

    public function dashboard()
    {
        $sites = HostedWebsite::query();

        $statusCounts = (clone $sites)->selectRaw('status, COUNT(*) as c')->groupBy('status')->pluck('c', 'status');

        $emailSent = (int) EmailCampaign::sum('sent');
        $emailOpened = (int) CampaignRecipient::whereNotNull('opened_at')->count();

        return view('admin.hosting.dashboard', [
            'clients' => User::whereHas('hostedWebsites')->count(),
            'total' => (clone $sites)->count(),
            'active' => (clone $sites)->where('status', HostedWebsite::STATUS_ACTIVE)->count(),
            'suspended' => (clone $sites)->where('status', HostedWebsite::STATUS_SUSPENDED)->count(),
            'provisioning' => (clone $sites)->whereIn('status', [HostedWebsite::STATUS_PENDING, HostedWebsite::STATUS_PROVISIONING])->count(),
            'failed' => (clone $sites)->where('status', HostedWebsite::STATUS_FAILED)->count(),
            'domains' => WebsiteDomain::count(),
            'pendingVerification' => WebsiteDomain::where('verification', 'pending')->count(),
            'revenue' => (int) PackageOrder::where('status', 'paid')->sum('paid_amount'),
            'outstanding' => (int) PackageOrder::whereIn('status', ['pending', 'submitted'])->sum('total_amount'),
            'failures' => WebsiteHealthCheck::where('incident', true)->latest('checked_at')->take(8)->get(),
            'statusCounts' => $statusCounts,
            'recentSignups' => User::latest()->take(5)->get(['id', 'name', 'email', 'created_at']),
            'recentPayments' => PackageOrder::with('user')->where('status', 'paid')->latest('paid_at')->take(5)->get(),
            'recentOps' => HostingCommand::with('website')->latest()->take(8)->get(),
            'recentAudit' => HostingAuditLog::latest()->take(10)->get(),
            'failedChecks' => WebsiteHealthCheck::where('status', HostedWebsite::HEALTH_UNAVAILABLE)->latest('checked_at')->take(5)->get(),
            'usage' => [
                'signups' => collect(range(5, 0))->map(fn ($i) => User::whereYear('created_at', now()->subMonths($i)->year)->whereMonth('created_at', now()->subMonths($i)->month)->count())->all(),
                'signupLabels' => collect(range(5, 0))->map(fn ($m) => now()->subMonths($m)->format('M'))->all(),
                'tokensSold' => (int) TokenTopup::where('status', 'paid')->sum('tokens'),
                'tokensUsed' => (int) abs(TokenTransaction::where('type', 'debit')->sum('amount')),
                'tokensHeld' => (int) User::sum('token_balance'),
                'topupRevenue' => (int) TokenTopup::where('status', 'paid')->sum('amount_ugx'),
                'campaigns' => EmailCampaign::count(),
                'emailSent' => $emailSent,
                'emailOpened' => $emailOpened,
                'emailRate' => $emailSent > 0 ? (int) round($emailOpened / $emailSent * 100) : 0,
                'prospectsIndexed' => ScrapedProspect::count(),
                'contactsSaved' => SavedContact::count(),
                'groupsListed' => WhatsappGroup::where('is_active', true)->count(),
            ],
        ]);
    }

    // ---------- Websites ----------

    public function websites(Request $request)
    {
        $query = HostedWebsite::with(['user', 'primaryDomain', 'plan'])->latest();

        if ($request->filled('q')) {
            $q = '%'.$request->string('q').'%';
            $query->where(fn ($w) => $w
                ->where('name', 'like', $q)
                ->orWhere('uuid', 'like', $q)
                ->orWhereHas('domains', fn ($d) => $d->where('domain', 'like', $q))
                ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $q)->orWhere('email', 'like', $q)));
        }

        foreach (['status', 'health', 'plan'] as $filter) {
            if ($request->filled($filter)) {
                match ($filter) {
                    'status' => $query->where('status', $request->string('status')),
                    'health' => $query->where('health_status', $request->string('health')),
                    'plan' => $query->where('hosting_plan_id', $request->integer('plan')),
                };
            }
        }

        return view('admin.hosting.websites', [
            'websites' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only(['q', 'status', 'health', 'plan']),
            'plans' => HostingPlan::orderBy('price')->get(),
        ]);
    }

    public function websiteShow(HostedWebsite $website)
    {
        $website->load(['user', 'plan', 'domains', 'credentials', 'orders' => fn ($q) => $q->latest()]);

        return view('admin.hosting.website-show', [
            'website' => $website,
            'checks' => $website->healthChecks()->latest('checked_at')->take(15)->get(),
            'commands' => $website->commands()->latest()->take(15)->get(),
            'audit' => HostingAuditLog::where('hosted_website_id', $website->id)->latest()->take(20)->get(),
            'newSecret' => session('new_service_secret'),
            'linkableOrders' => $website->user->packageOrders()->whereNull('hosted_website_id')->latest()->take(10)->get(),
        ]);
    }

    protected function queueCommand(Request $request, HostedWebsite $website, string $action, array $payload = []): HostingCommand
    {
        $command = ServiceCommander::build($website, $action, 'admin', $request->user()->id, $payload);

        HostingAuditLog::log(
            $action === HostingCommand::ACTION_SUSPEND ? 'suspension.requested' : 'restoration.requested',
            [
                'actor_type' => 'admin',
                'actor_id' => $request->user()->id,
                'hosted_website_id' => $website->id,
                'client_id' => $website->user_id,
                'operation_id' => $command->uuid,
                'meta' => $payload,
                'ip' => $request->ip(),
            ]
        );

        ProcessHostingCommand::dispatch($command->id);

        return $command;
    }

    public function suspend(Request $request, HostedWebsite $website)
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ]);

        abort_unless(in_array($website->status, [HostedWebsite::STATUS_ACTIVE, HostedWebsite::STATUS_FAILED], true), 422);

        $command = $this->queueCommand($request, $website, HostingCommand::ACTION_SUSPEND, [
            'reason' => $validated['reason'],
        ]);

        return back()->with('status', "Suspension queued as operation {$command->uuid} — status changes only on service acknowledgment.");
    }

    public function restore(Request $request, HostedWebsite $website)
    {
        abort_unless($website->status === HostedWebsite::STATUS_SUSPENDED, 422);

        $command = $this->queueCommand($request, $website, HostingCommand::ACTION_UNSUSPEND, [
            'reason' => 'Administrative restoration',
        ]);

        return back()->with('status', "Restoration queued as operation {$command->uuid} — status changes only on service acknowledgment.");
    }

    public function triggerHealthCheck(Request $request, HostedWebsite $website)
    {
        $check = HealthChecker::checkWebsite($website);

        HostingAuditLog::log('health.manual_check', [
            'actor_type' => 'admin',
            'actor_id' => $request->user()->id,
            'hosted_website_id' => $website->id,
            'client_id' => $website->user_id,
            'meta' => ['status' => $check->status, 'http' => $check->http_status, 'ms' => $check->response_ms],
            'ip' => $request->ip(),
        ]);

        return back()->with('status', "Health check complete: {$check->status}.");
    }

    public function retryProvision(Request $request, HostedWebsite $website)
    {
        $website->forceFill([
            'status' => HostedWebsite::STATUS_PROVISIONING,
            'failed_at' => null,
            'fail_reason' => null,
        ])->save();

        $command = ServiceCommander::build($website, HostingCommand::ACTION_PROVISION, 'admin', $request->user()->id, ['retry' => true]);

        HostingAuditLog::log('provisioning.retried', [
            'actor_type' => 'admin',
            'actor_id' => $request->user()->id,
            'hosted_website_id' => $website->id,
            'client_id' => $website->user_id,
            'operation_id' => $command->uuid,
            'ip' => $request->ip(),
        ]);

        ProcessHostingCommand::dispatch($command->id);

        return back()->with('status', "Provisioning retried as operation {$command->uuid}.");
    }

    public function markActive(Request $request, HostedWebsite $website)
    {
        $validated = $request->validate(['note' => ['nullable', 'string', 'max:255']]);

        $website->forceFill([
            'status' => HostedWebsite::STATUS_ACTIVE,
            'failed_at' => null,
            'fail_reason' => null,
        ])->save();

        HostingAuditLog::log('website.manually_activated', [
            'actor_type' => 'admin',
            'actor_id' => $request->user()->id,
            'hosted_website_id' => $website->id,
            'client_id' => $website->user_id,
            'reason' => $validated['note'] ?? 'Manual confirmation',
            'ip' => $request->ip(),
        ]);

        $website->user->notify(new SiteAlert(
            "Website {$website->name} is live",
            'Confirmed by the ttryy team.',
            route('hosting.show', $website),
            'hosting'
        ));

        return back()->with('status', "Website {$website->name} marked active.");
    }

    public function updateServiceUrl(Request $request, HostedWebsite $website)
    {
        $validated = $request->validate(['service_url' => ['nullable', 'url', 'max:255']]);

        $website->forceFill([
            'service_url' => $validated['service_url'] ?: null,
            'service_connected' => false,
        ])->save();

        HostingAuditLog::log('service.endpoint_updated', [
            'actor_type' => 'admin',
            'actor_id' => $request->user()->id,
            'hosted_website_id' => $website->id,
            'client_id' => $website->user_id,
            'meta' => ['service_url' => $validated['service_url'] ?: null],
            'ip' => $request->ip(),
        ]);

        return back()->with('status', 'Service endpoint updated. Issue fresh credentials for the new endpoint.');
    }

    public function issueCredential(Request $request, HostedWebsite $website)
    {
        // Issue exactly once and show the secret a single time.
        [$credential, $secret] = ServiceAuthenticator::issueCredential($website);

        // Revoke superseded active credentials is a manual, explicit step
        // (see revokeCredential) so rotation never locks a site out silently.

        HostingAuditLog::log('service.credential_issued', [
            'actor_type' => 'admin',
            'actor_id' => $request->user()->id,
            'hosted_website_id' => $website->id,
            'client_id' => $website->user_id,
            'meta' => ['key_id' => $credential->key_id],
            'ip' => $request->ip(),
        ]);

        return back()->with('new_service_secret', [
            'key_id' => $credential->key_id,
            'secret' => $secret,
        ])->with('status', "Credential {$credential->key_id} issued — copy the secret now, it is never shown again.");
    }

    public function revokeCredential(Request $request, HostedWebsite $website, int $credentialId)
    {
        $credential = $website->credentials()->where('id', $credentialId)->firstOrFail();
        $credential->forceFill(['revoked_at' => now()])->save();

        HostingAuditLog::log('service.credential_revoked', [
            'actor_type' => 'admin',
            'actor_id' => $request->user()->id,
            'hosted_website_id' => $website->id,
            'client_id' => $website->user_id,
            'meta' => ['key_id' => $credential->key_id],
            'ip' => $request->ip(),
        ]);

        return back()->with('status', "Credential {$credential->key_id} revoked.");
    }

    // ---------- Admin domain management (clients are read-only) ----------

    protected function auditDomain(Request $request, HostedWebsite $website, string $action, array $meta = [], string $outcome = 'success', ?string $reason = null): void
    {
        HostingAuditLog::log($action, [
            'actor_type' => 'admin',
            'actor_id' => $request->user()->id,
            'hosted_website_id' => $website->id,
            'client_id' => $website->user_id,
            'outcome' => $outcome,
            'reason' => $reason,
            'meta' => $meta,
            'ip' => $request->ip(),
        ]);
    }

    public function adminStoreSubdomain(Request $request, HostedWebsite $website)
    {
        $validated = $request->validate(['label' => ['nullable', 'string', 'max:63']]);

        $record = DomainManager::addSubdomain($website, $website->user_id, $validated['label'] ?? null);
        $this->auditDomain($request, $website, 'domain.subdomain_added', ['domain' => $record->domain]);

        return back()->with('status', "Subdomain {$record->domain} added and provisioning attempted.");
    }

    public function adminStoreDomain(Request $request, HostedWebsite $website)
    {
        $validated = $request->validate(['domain' => ['required', 'string', 'max:253']]);

        $record = DomainManager::addCustomDomain($website, $website->user_id, $validated['domain']);
        $this->auditDomain($request, $website, 'domain.added', ['domain' => $record->domain]);

        return back()->with('status', "Domain {$record->domain} added — verify it with Check DNS.");
    }

    public function adminVerifyDomain(Request $request, HostedWebsite $website, WebsiteDomain $domain)
    {
        abort_if($domain->hosted_website_id !== $website->id, 404);

        $found = DomainManager::verify($domain);
        $this->auditDomain($request, $website, 'domain.verification_checked', ['domain' => $domain->domain], $found ? 'success' : 'failure', $found ? null : 'txt_not_found');

        return back()->with('status', $found ? "Domain {$domain->domain} verified." : 'TXT record not found yet — DNS may still be propagating.');
    }

    public function adminSetPrimary(Request $request, HostedWebsite $website, WebsiteDomain $domain)
    {
        abort_if($domain->hosted_website_id !== $website->id, 404);

        DomainManager::setPrimary($website, $domain);
        $this->auditDomain($request, $website, 'domain.primary_changed', ['domain' => $domain->domain]);

        return back()->with('status', "Primary domain set to {$domain->domain}.");
    }

    public function adminDestroyDomain(Request $request, HostedWebsite $website, WebsiteDomain $domain)
    {
        abort_if($domain->hosted_website_id !== $website->id, 404);

        $name = DomainManager::destroy($website, $domain);

        if ($website->primary_domain_id === $domain->id) {
            $replacement = $website->domains()->first();
            $website->forceFill(['primary_domain_id' => $replacement?->id])->save();
            $replacement?->forceFill(['is_primary' => true])->save();
        }

        $this->auditDomain($request, $website, 'domain.removed', ['domain' => $name]);

        return back()->with('status', "Domain {$name} removed.");
    }

    public function linkOrder(Request $request, HostedWebsite $website)
    {
        $validated = $request->validate(['order_id' => ['required', 'exists:package_orders,id']]);

        $order = PackageOrder::where('id', $validated['order_id'])
            ->where('user_id', $website->user_id)
            ->firstOrFail();

        $order->forceFill(['hosted_website_id' => $website->id])->save();

        if (! $website->next_renewal_at) {
            $website->forceFill(['next_renewal_at' => now()->addMonth()])->save();
        }

        $this->auditDomain($request, $website, 'order.linked', ['reference' => $order->reference]);

        return back()->with('status', "Order {$order->reference} linked — billing now runs off its {$order->billing_frequency} rhythm.");
    }

    // ---------- Clients ----------

    public function clients(Request $request)
    {
        $query = User::withCount('hostedWebsites')->withSum('packageOrders', 'total_amount')->latest();

        if ($request->filled('q')) {
            $q = '%'.$request->string('q').'%';
            $query->where(fn ($u) => $u->where('name', 'like', $q)->orWhere('email', 'like', $q));
        }

        return view('admin.hosting.clients', [
            'clients' => $query->paginate(15)->withQueryString(),
            'q' => (string) $request->string('q'),
        ]);
    }

    public function clientShow(User $user)
    {
        return view('admin.hosting.client-show', [
            'client' => $user,
            'websites' => $user->hostedWebsites()->with(['primaryDomain', 'plan'])->latest()->get(),
            'orders' => $user->packageOrders()->latest()->take(10)->get(),
            'audit' => HostingAuditLog::where('client_id', $user->id)->latest()->take(15)->get(),
            'balance' => (int) $user->token_balance,
        ]);
    }

    // ---------- Domains ----------

    public function domains(Request $request)
    {
        $query = WebsiteDomain::with(['website', 'user'])->latest();

        if ($request->filled('type') && in_array($request->string('type'), ['subdomain', 'custom'], true)) {
            $query->where('type', $request->string('type'));
        }

        if ($request->filled('verification')) {
            $query->where('verification', $request->string('verification'));
        }

        if ($request->filled('q')) {
            $query->where('domain', 'like', '%'.$request->string('q').'%');
        }

        return view('admin.hosting.domains', [
            'domains' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only(['q', 'type', 'verification']),
            'sslCounts' => WebsiteDomain::selectRaw('ssl_status, COUNT(*) as c')->groupBy('ssl_status')->pluck('c', 'ssl_status'),
        ]);
    }

    // ---------- Operations ----------

    public function operations(Request $request)
    {
        $query = HostingCommand::with(['website', 'website.user'])->latest();

        if ($request->filled('state')) {
            $query->where('state', $request->string('state'));
        }

        if ($request->filled('action')) {
            $query->where('action', $request->string('action'));
        }

        return view('admin.hosting.operations', [
            'commands' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only(['state', 'action']),
        ]);
    }

    public function retryOperation(Request $request, HostingCommand $command)
    {
        abort_unless(in_array($command->state, [HostingCommand::STATE_FAILED, HostingCommand::STATE_RETRY], true), 422);

        $command->forceFill(['state' => HostingCommand::STATE_PENDING, 'error' => null])->save();

        HostingAuditLog::log('command.retried', [
            'actor_type' => 'admin',
            'actor_id' => $request->user()->id,
            'hosted_website_id' => $command->hosted_website_id,
            'operation_id' => $command->uuid,
            'ip' => $request->ip(),
        ]);

        ProcessHostingCommand::dispatch($command->id);

        return back()->with('status', "Operation {$command->uuid} re-queued.");
    }

    // ---------- Health & audit ----------

    public function health(Request $request)
    {
        $query = WebsiteHealthCheck::with('website')->latest('checked_at');

        if ($request->string('filter') === 'failures') {
            $query->where('status', HostedWebsite::HEALTH_UNAVAILABLE);
        }

        if ($request->string('filter') === 'incidents') {
            $query->where('incident', true);
        }

        return view('admin.hosting.health', [
            'checks' => $query->paginate(20)->withQueryString(),
            'filter' => (string) $request->string('filter', 'all'),
        ]);
    }

    public function audit(Request $request)
    {
        $query = HostingAuditLog::with('website')->latest();

        if ($request->filled('action')) {
            $query->where('action', 'like', '%'.$request->string('action').'%');
        }

        if ($request->filled('outcome')) {
            $query->where('outcome', $request->string('outcome'));
        }

        if ($request->filled('q')) {
            $query->where('reason', 'like', '%'.$request->string('q').'%');
        }

        return view('admin.hosting.audit', [
            'logs' => $query->paginate(20)->withQueryString(),
            'filters' => $request->only(['action', 'outcome', 'q']),
        ]);
    }

    public function integrations()
    {
        $base = config('hosting.base_domain');

        $status = fn ($ok, $label, $hint = '') => ['ok' => (bool) $ok, 'label' => $label, 'hint' => $hint];

        return view('admin.hosting.integrations', ['items' => [
            $status(true, 'Base domain: '.$base, 'Subdomains are issued as ‹label›.'.$base.'.'),
            $status(
                config('hosting.provisioning.driver') !== 'manual',
                'Provisioning driver: '.config('hosting.provisioning.driver'),
                config('hosting.provisioning.driver') === 'manual'
                    ? 'Manual mode — the team confirms provisioning in this panel. Set HOSTING_PROVISION_DRIVER only with a tested driver.'
                    : 'Automated provisioning driver active.'
            ),
            $status(
                (bool) config('hosting.dns.driver'),
                'DNS driver: '.(config('hosting.dns.driver') ?: 'not configured'),
                config('hosting.dns.driver') ? 'DNS automation active.' : 'Set HOSTING_DNS_DRIVER to enable automated verification and routing. TXT checks still run live.'
            ),
            $status(
                (bool) config('hosting.ssl.driver'),
                'SSL driver: '.(config('hosting.ssl.driver') ?: 'not configured'),
                config('hosting.ssl.driver') ? 'SSL automation active.' : 'Set HOSTING_SSL_DRIVER to enable automated issuance. Statuses stay honest until then.'
            ),
            $status(config('services.flutterwave.secret_key'), 'Flutterwave payments', config('services.flutterwave.secret_key') ? 'Online payments verified server-side.' : 'Set FLW_PUBLIC_KEY / FLW_SECRET_KEY to accept online payments.'),
            $status(true, 'Queue: '.config('queue.default'), 'Commands dispatch through ProcessHostingCommand. Run a queue worker in production.'),
            $status(true, 'Scheduler', 'Add cron: * * * * * php artisan schedule:run. Health checks run every 30 minutes; analytics aggregate daily.'),
        ]]);
    }

    // ---------- Launch 10K campaign ----------

    public function launchSignups(Request $request)
    {
        $query = LaunchSignup::latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return view('admin.hosting.launch', [
            'signups' => $query->paginate(15)->withQueryString(),
            'status' => (string) $request->string('status', ''),
            'counts' => [
                'new' => LaunchSignup::where('status', 'new')->count(),
                'contacted' => LaunchSignup::where('status', 'contacted')->count(),
                'converted' => LaunchSignup::where('status', 'converted')->count(),
            ],
            'newSecret' => session('new_launch_user'),
        ]);
    }

    public function launchStatus(Request $request, LaunchSignup $signup)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['new', 'contacted', 'converted', 'declined'])],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $signup->forceFill([
            'status' => $validated['status'],
            'admin_note' => $validated['admin_note'] ?? $signup->admin_note,
        ])->save();

        return back()->with('status', "Application #{$signup->id} marked {$validated['status']}.");
    }

    public function launchCreateUser(Request $request, LaunchSignup $signup)
    {
        abort_if($signup->user_id, 422, 'This application already has an account.');

        $email = $signup->email ?: 'launch'.$signup->id.'@ttryy.com';

        if (User::where('email', $email)->exists()) {
            return back()->withErrors(['email' => 'An account with that email already exists — link it manually.']);
        }

        $password = Str::random(14);

        $user = User::create([
            'name' => $signup->name,
            'email' => $email,
            'password' => $password,
            'email_verified_at' => now(),
        ]);

        if ($signup->phone && ! $user->phone) {
            // Best effort: keep the contact phone where the profile supports it.
        }

        $signup->forceFill(['user_id' => $user->id, 'status' => 'converted'])->save();

        HostingAuditLog::log('launch.user_created', [
            'actor_type' => 'admin',
            'actor_id' => $request->user()->id,
            'client_id' => $user->id,
            'meta' => ['signup_id' => $signup->id, 'email' => $email],
            'ip' => $request->ip(),
        ]);

        return back()->with('new_launch_user', [
            'email' => $email,
            'password' => $password,
        ])->with('status', "Account created for {$signup->name} — copy the password now, it is never shown again.");
    }
}
