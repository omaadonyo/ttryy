<?php

namespace App\Http\Controllers;

use App\Models\ScrapedProspect;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Backs the ⌘K command palette: searches the signed-in user's own records
 * (orders, notifications, websites, saved contacts) plus the shared prospect
 * index — and, for admins, users and all orders. Pages/actions are matched
 * client-side; this only returns live records.
 */
class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $q = trim((string) $request->string('q'));

        if (mb_strlen($q) < 2) {
            return response()->json(['groups' => []]);
        }

        $user = $request->user();
        $like = '%'.$q.'%';
        $needle = mb_strtolower($q);
        $contains = fn (?string $value): bool => $value !== null && str_contains(mb_strtolower($value), $needle);

        $groups = [];

        // Orders — matched on reference, package and business name.
        $orders = $user->packageOrders()
            ->where(fn ($w) => $w
                ->where('reference', 'like', $like)
                ->orWhere('package', 'like', $like)
                ->orWhere('business_name', 'like', $like))
            ->latest()
            ->take(5)
            ->get();

        $groups[] = [
            'group' => 'Your orders',
            'items' => $orders->map(fn ($o) => [
                'label' => $o->reference.' · '.$o->package,
                'sub' => $o->business_name ?: 'UGX '.number_format((int) $o->total_amount).' · '.$o->status,
                'href' => route('packages.index'),
            ])->all(),
        ];

        // Notifications — filtered in PHP so it works on any database driver.
        $notifications = $user->notifications()->latest()->take(60)->get()
            ->filter(fn ($n) => $contains($n->data['title'] ?? null) || $contains($n->data['body'] ?? null))
            ->take(5);

        $groups[] = [
            'group' => 'Notifications',
            'items' => $notifications->map(fn ($n) => [
                'label' => $n->data['title'] ?? 'Notification',
                'sub' => $n->data['body'] ?? '',
                'href' => route('notifications.open', $n->id),
            ])->values()->all(),
        ];

        // Hosted websites — matched on name or any attached domain.
        $websites = $user->hostedWebsites()
            ->where(fn ($w) => $w
                ->where('name', 'like', $like)
                ->orWhereHas('domains', fn ($d) => $d->where('domain', 'like', $like)))
            ->latest()
            ->take(5)
            ->get();

        $groups[] = [
            'group' => 'Your websites',
            'items' => $websites->map(fn ($w) => [
                'label' => $w->name,
                'sub' => $w->primaryDomain?->domain ?? ucfirst(str_replace('_', ' ', $w->status)),
                'href' => route('hosting.show', $w),
            ])->all(),
        ];

        // Saved contacts — matched on name, phone, email or niche.
        $contacts = $user->savedContacts()
            ->where(fn ($w) => $w
                ->where('name', 'like', $like)
                ->orWhere('phone', 'like', $like)
                ->orWhere('email', 'like', $like)
                ->orWhere('niche', 'like', $like))
            ->latest()
            ->take(5)
            ->get();

        $groups[] = [
            'group' => 'Saved contacts',
            'items' => $contacts->map(fn ($c) => [
                'label' => $c->name,
                'sub' => collect([$c->phone, $c->email, $c->niche])->filter()->implode(' · '),
                'href' => route('contacts.index'),
            ])->all(),
        ];

        // Shared prospect index — anyone can search it.
        $prospects = ScrapedProspect::query()
            ->where(fn ($w) => $w
                ->where('name', 'like', $like)
                ->orWhere('category', 'like', $like)
                ->orWhere('phone', 'like', $like))
            ->latest('fetched_at')
            ->take(5)
            ->get();

        $groups[] = [
            'group' => 'Prospect index',
            'items' => $prospects->map(fn ($p) => [
                'label' => $p->name,
                'sub' => collect([$p->category, $p->phone])->filter()->implode(' · '),
                'href' => route('prospects.index', ['q' => $q]),
            ])->all(),
        ];

        if ($user->is_admin) {
            $users = User::query()
                ->where(fn ($w) => $w
                    ->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like))
                ->latest()
                ->take(5)
                ->get();

            $groups[] = [
                'group' => 'Users (admin)',
                'items' => $users->map(fn ($u) => [
                    'label' => $u->name,
                    'sub' => $u->email,
                    'href' => route('admin.users', ['search' => $u->email]),
                ])->all(),
            ];
        }

        return response()->json([
            'groups' => collect($groups)
                ->filter(fn ($g) => count($g['items']) > 0)
                ->values()
                ->all(),
        ]);
    }
}
