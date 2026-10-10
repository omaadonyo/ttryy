<?php

namespace App\Http\Controllers;

use App\Models\SavedContact;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $orders = $request->user()->packageOrders()->latest()->get();

        return view('dashboard', [
            'orders' => $orders,
            'recentOrders' => $orders->take(3),
            'totalOrders' => $orders->count(),
            'pendingOrders' => $orders->where('status', 'pending')->count(),
            'totalCommitted' => $orders->sum('total_amount'),
        ]);
    }

    public function scraper()
    {
        return view('scraper');
    }

    public function prospects(Request $request)
    {
        $query = \App\Models\ScrapedProspect::query()->latest('fetched_at');

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

        $categories = \App\Models\ScrapedProspect::selectRaw('category, COUNT(*) as c')
            ->whereNotNull('category')
            ->groupBy('category')
            ->orderBy('category')
            ->get()
            ->groupBy(fn ($r) => strtoupper(mb_substr((string) $r->category, 0, 1)));

        return view('prospects', [
            'prospects' => $query->paginate(20)->withQueryString(),
            'niches' => \App\Models\ScrapedProspect::select('niche')->distinct()->orderBy('niche')->pluck('niche'),
            'categories' => $categories,
            'filters' => $request->only(['q', 'niche', 'category', 'verified']),
        ]);
    }

    public function contacts(Request $request)
    {
        $contacts = $request->user()->savedContacts()->latest()->paginate(20);

        return view('saved-contacts', ['contacts' => $contacts]);
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
        $contacts = $request->user()->savedContacts()->latest()->get(['name', 'type', 'district', 'contact', 'phone', 'need', 'niche', 'created_at']);

        $csv = "Name,Type,District,Contact,Phone,Need,Niche,Saved\n";
        foreach ($contacts as $c) {
            $csv .= implode(',', array_map(
                fn ($v) => '"'.str_replace('"', '""', (string) $v).'"',
                [$c->name, $c->type, $c->district, $c->contact, $c->phone, $c->need, $c->niche, $c->created_at?->format('Y-m-d')]
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

        return view('payments', [
            'orders' => $orders,
            'paidTotal' => $orders->where('status', 'paid')->sum('paid_amount'),
            'outstanding' => $orders->whereIn('status', ['pending', 'submitted'])->sum(fn ($o) => $o->total_amount - $o->paid_amount),
        ]);
    }
}
