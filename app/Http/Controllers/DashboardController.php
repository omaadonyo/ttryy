<?php

namespace App\Http\Controllers;

use App\Models\CampaignRecipient;
use App\Models\EmailCampaign;
use App\Models\HostedWebsite;
use App\Models\SavedContact;
use App\Models\ScrapedProspect;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $orders = $user->packageOrders()->latest()->get();

        $campaigns = EmailCampaign::where('user_id', $user->id)
            ->withCount(['recipients as read_count' => fn ($q) => $q->whereNotNull('opened_at')])
            ->latest()->get();
        $mktSent = (int) $campaigns->sum('sent');
        $mktOpened = (int) $campaigns->sum('read_count');
        $chartCampaigns = $campaigns->take(6)->reverse()->values();

        $now = now();
        $curStart = $now->copy()->subDays(30);
        $prevStart = $now->copy()->subDays(60);
        $delta = fn ($c, $p) => $p > 0 ? (int) round(($c - $p) / $p * 100) : ($c > 0 ? 100 : 0);

        $ordersCur = $user->packageOrders()->where('created_at', '>=', $curStart)->count();
        $ordersPrev = $user->packageOrders()->where('created_at', '>=', $prevStart)->where('created_at', '<', $curStart)->count();
        $spendCur = (int) $user->packageOrders()->where('created_at', '>=', $curStart)->sum('total_amount');
        $spendPrev = (int) $user->packageOrders()->where('created_at', '>=', $prevStart)->where('created_at', '<', $curStart)->sum('total_amount');
        $emailsCur = (int) EmailCampaign::where('user_id', $user->id)->where('created_at', '>=', $curStart)->sum('sent');
        $emailsPrev = (int) EmailCampaign::where('user_id', $user->id)->where('created_at', '>=', $prevStart)->where('created_at', '<', $curStart)->sum('sent');

        $days = collect(range(13, 0))->map(fn ($i) => $now->copy()->subDays($i));
        $activityChart = [
            'labels' => $days->map(fn ($d) => $d->format('d M'))->all(),
            'orders' => $days->map(fn ($d) => $user->packageOrders()->whereDate('created_at', $d->toDateString())->count())->all(),
            'emails' => $days->map(fn ($d) => (int) EmailCampaign::where('user_id', $user->id)->whereDate('created_at', $d->toDateString())->sum('sent'))->all(),
        ];

        $checklist = [
            ['label' => 'Order your website', 'blurb' => 'Pick a package and pay a deposit. We confirm on WhatsApp and your site goes live in about 5 days.', 'done' => $orders->isNotEmpty(), 'url' => route('checkout')],
            ['label' => 'Find customers', 'blurb' => 'Scrape real businesses in your niche with the Pro tool — fresh pulls cost a few tokens, repeats from the index are free.', 'done' => $user->savedContacts()->exists(), 'url' => route('scraper.index')],
            ['label' => 'Send your first campaign', 'blurb' => 'Reach saved contacts on WhatsApp or email, then watch opens land on the report.', 'done' => $campaigns->isNotEmpty(), 'url' => route('marketing.index')],
            ['label' => 'Top up your wallet', 'blurb' => 'Tokens power AI messages and community access. Top up with Mobile Money or card.', 'done' => $user->tokenTopups()->where('status', 'paid')->exists(), 'url' => route('wallet.index')],
        ];

        return view('dashboard', [
            'orders' => $orders,
            'recentOrders' => $orders->take(3),
            'totalOrders' => $orders->count(),
            'pendingOrders' => $orders->where('status', 'pending')->count(),
            'totalCommitted' => $orders->sum('total_amount'),
            'campaigns' => $campaigns->take(4),
            'mktStats' => [
                'campaigns' => $campaigns->count(),
                'sent' => $mktSent,
                'opened' => $mktOpened,
                'rate' => $mktSent > 0 ? (int) round($mktOpened / $mktSent * 100) : 0,
            ],
            'mktChart' => [
                'labels' => $chartCampaigns->map(fn ($c) => mb_substr($c->subject, 0, 18))->all(),
                'sent' => $chartCampaigns->map(fn ($c) => (int) $c->sent)->all(),
                'opened' => $chartCampaigns->map(fn ($c) => (int) $c->read_count)->all(),
            ],
            'kpis' => [
                ['orders' => $ordersCur, 'delta' => $delta($ordersCur, $ordersPrev)],
                ['spend' => $spendCur, 'delta' => $delta($spendCur, $spendPrev)],
                ['emails' => $emailsCur, 'delta' => $delta($emailsCur, $emailsPrev)],
            ],
            'activityChart' => $activityChart,
            'activity' => $user->notifications()->latest()->take(6)->get(),
            'hosting' => [
                'total' => $user->hostedWebsites()->count(),
                'active' => $user->hostedWebsites()->where('status', HostedWebsite::STATUS_ACTIVE)->count(),
                'attention' => $user->hostedWebsites()->whereIn('status', [HostedWebsite::STATUS_SUSPENDED, HostedWebsite::STATUS_FAILED])->count(),
                'renewal' => $user->hostedWebsites()->whereNotNull('next_renewal_at')->min('next_renewal_at'),
            ],
            'checklist' => $checklist,
            'checklistDone' => collect($checklist)->every(fn ($s) => $s['done']),
            'isNewUser' => $orders->isEmpty() && $campaigns->isEmpty() && ! $user->savedContacts()->exists(),
            'tokenBalance' => (int) $user->token_balance,
        ]);
    }

    public function analytics(Request $request)
    {
        $user = $request->user();

        $months = collect(range(5, 0))->map(fn ($i) => now()->subMonths($i));
        $spendByMonth = $months->mapWithKeys(function ($m) use ($user) {
            $sum = $user->packageOrders()
                ->whereYear('created_at', $m->year)->whereMonth('created_at', $m->month)
                ->sum('total_amount');

            return [$m->format('M') => (int) $sum];
        });

        $campaigns = EmailCampaign::where('user_id', $user->id)
            ->withCount(['recipients as read_count' => fn ($q) => $q->whereNotNull('opened_at')])
            ->latest()->take(8)->get()->reverse()->values();

        $days = collect(range(29, 0))->map(fn ($i) => now()->subDays($i)->toDateString());
        $tokensByDay = $days->mapWithKeys(function ($d) use ($user) {
            $spent = $user->tokenTransactions()->where('type', 'debit')->whereDate('created_at', $d)->sum('amount');

            return [$d => (int) abs($spent)];
        });

        $savedByNiche = SavedContact::where('user_id', $user->id)
            ->selectRaw('niche, COUNT(*) as c')->groupBy('niche')->orderByDesc('c')->take(6)->get();

        $totalSent = (int) EmailCampaign::where('user_id', $user->id)->sum('sent');
        $totalOpened = (int) CampaignRecipient::whereHas('campaign', fn ($q) => $q->where('user_id', $user->id))
            ->whereNotNull('opened_at')->count();

        return view('analytics', [
            'spendByMonth' => $spendByMonth,
            'totalCommitted' => (int) $user->packageOrders()->sum('total_amount'),
            'totalPaid' => (int) $user->packageOrders()->sum('paid_amount'),
            'campaigns' => $campaigns,
            'mktTotals' => [
                'sent' => $totalSent,
                'opened' => $totalOpened,
                'rate' => $totalSent > 0 ? (int) round($totalOpened / $totalSent * 100) : 0,
            ],
            'tokensByDay' => $tokensByDay,
            'tokenBalance' => (int) $user->token_balance,
            'savedByNiche' => $savedByNiche,
            'savedTotal' => $user->savedContacts()->count(),
        ]);
    }

    public function openNotification(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->where('id', $id)->firstOrFail();
        $notification->markAsRead();

        $url = $notification->data['url'] ?? null;

        return $url ? redirect()->away($url) : back();
    }

    public function notifications(Request $request)
    {
        $tab = (string) $request->string('tab', 'all');
        $query = $request->user()->notifications()->latest();
        if ($tab === 'unread') {
            $query->whereNull('read_at');
        }

        return view('notifications', [
            'items' => $query->paginate(15)->withQueryString(),
            'tab' => $tab,
            'unreadCount' => $request->user()->unreadNotifications()->count(),
            'totalCount' => $request->user()->notifications()->count(),
        ]);
    }

    public function readAllNotifications(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return back();
    }

    public function scraper()
    {
        return view('scraper', [
            'indexStats' => [
                'indexed' => ScrapedProspect::count(),
                'verified' => ScrapedProspect::where('verified', true)->count(),
                'niches' => ScrapedProspect::select('niche')->distinct()->count(),
                'saved' => request()->user()->savedContacts()->count(),
            ],
            'searchCost' => (int) config('tokens.costs.scraper_search', 5),
            'tokenBalance' => (int) request()->user()->token_balance,
        ]);
    }

    public function prospects(Request $request)
    {
        $query = ScrapedProspect::query()->latest('fetched_at');

        if ($request->filled('q')) {
            $q = '%'.$request->string('q').'%';
            $query->where(fn ($w) => $w
                ->where('name', 'like', $q)
                ->orWhere('category', 'like', $q)
                ->orWhere('phone', 'like', $q));
        }

        if ($request->filled('niche')) {
            $query->where('niche', $request->string('niche'));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->string('category'));
        }

        if ($request->boolean('verified')) {
            $query->where('verified', true);
        }

        $categories = ScrapedProspect::selectRaw('category, COUNT(*) as c')
            ->whereNotNull('category')
            ->groupBy('category')
            ->orderBy('category')
            ->get()
            ->groupBy(fn ($r) => strtoupper(mb_substr((string) $r->category, 0, 1)));

        return view('prospects', [
            'prospects' => $query->paginate(20)->withQueryString(),
            'niches' => ScrapedProspect::select('niche')->distinct()->orderBy('niche')->pluck('niche'),
            'categories' => $categories,
            'filters' => $request->only(['q', 'niche', 'category', 'verified']),
            'totals' => [
                'indexed' => ScrapedProspect::count(),
                'categories' => ScrapedProspect::whereNotNull('category')->select('category')->distinct()->count(),
                'verified' => ScrapedProspect::where('verified', true)->count(),
                'showing' => (clone $query)->count(),
            ],
        ]);
    }

    public function contacts(Request $request)
    {
        $contacts = $request->user()->savedContacts()->latest()->paginate(20);

        return view('saved-contacts', [
            'contacts' => $contacts,
            'emailCount' => $request->user()->savedContacts()->whereNotNull('email')->count(),
            'phoneCount' => $request->user()->savedContacts()->whereNotNull('phone')->count(),
            'topNiches' => $request->user()->savedContacts()
                ->selectRaw('niche, COUNT(*) as c')->groupBy('niche')->orderByDesc('c')->take(5)->get(),
        ]);
    }

    public function storeContacts(Request $request)
    {
        $validated = $request->validate([
            'niche' => ['required', 'string', 'max:255'],
            'source' => ['nullable', 'string', 'max:50'],
            'contacts' => ['required', 'array', 'max:50'],
            'contacts.*.name' => ['required', 'string', 'max:255'],
            'contacts.*.type' => ['nullable', 'string', 'max:255'],
            'contacts.*.district' => ['nullable', 'string', 'max:255'],
            'contacts.*.contact' => ['nullable', 'string', 'max:255'],
            'contacts.*.phone' => ['nullable', 'string', 'max:50'],
            'contacts.*.email' => ['nullable', 'email', 'max:255'],
            'contacts.*.need' => ['nullable', 'string', 'max:255'],
            'contacts.*.source_url' => ['nullable', 'url', 'max:500'],
        ]);

        $rows = collect($validated['contacts'])->map(fn ($c) => [
            'user_id' => $request->user()->id,
            'niche' => $validated['niche'],
            'name' => $c['name'],
            'type' => $c['type'] ?? null,
            'district' => $c['district'] ?? null,
            'contact' => $c['contact'] ?? null,
            'phone' => $c['phone'] ?? null,
            'email' => $c['email'] ?? null,
            'need' => $c['need'] ?? null,
            'source' => $validated['source'] ?? 'yellow-pages',
            'source_url' => $c['source_url'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ])->all();

        SavedContact::insert($rows);

        return response()->json(['saved' => count($rows)]);
    }

    public function destroyContact(SavedContact $contact)
    {
        abort_if($contact->user_id !== request()->user()->id, 403);
        $contact->delete();

        return back()->with('status', 'Contact removed.');
    }

    public function exportContacts(Request $request)
    {
        $contacts = $request->user()->savedContacts()->latest()->get(['name', 'type', 'district', 'contact', 'phone', 'need', 'niche', 'source', 'source_url', 'created_at']);

        $csv = "Name,Type,District,Contact,Phone,Need,Niche,Source,Source URL,Saved\n";
        foreach ($contacts as $c) {
            $csv .= implode(',', array_map(
                fn ($v) => '"'.str_replace('"', '""', (string) $v).'"',
                [$c->name, $c->type, $c->district, $c->contact, $c->phone, $c->need, $c->niche, $c->source, $c->source_url, $c->created_at?->format('Y-m-d')]
            ))."\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="ttryy-contacts.csv"',
        ]);
    }

    public function payments(Request $request)
    {
        $orders = $request->user()->packageOrders()->latest()->get();

        $months = collect(range(5, 0))->map(fn ($i) => now()->subMonths($i));
        $paidByMonth = [
            'labels' => $months->map(fn ($m) => $m->format('M'))->all(),
            'values' => $months->map(fn ($m) => (int) $request->user()->packageOrders()
                ->where('status', 'paid')
                ->whereYear('paid_at', $m->year)->whereMonth('paid_at', $m->month)
                ->sum('paid_amount'))->all(),
        ];

        return view('payments', [
            'orders' => $orders,
            'paidTotal' => $orders->where('status', 'paid')->sum('paid_amount'),
            'outstanding' => $orders->whereIn('status', ['pending', 'submitted'])->sum(fn ($o) => $o->total_amount - $o->paid_amount),
            'paidByMonth' => $paidByMonth,
            'walletBalance' => (int) $request->user()->balance_ugx,
        ]);
    }
}
