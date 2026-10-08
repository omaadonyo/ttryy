<?php

namespace App\Http\Controllers;

use App\Models\MessageTemplate;
use App\Models\SavedContact;
use App\Models\WhatsappGroup;
use App\Services\SalesCopyService;
use App\Services\Wallet;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MarketingController extends Controller
{
    public function index(Request $request)
    {
        $contacts = $request->user()->savedContacts()->latest()->take(200)->get();

        return view('marketing', [
            'contacts' => $contacts,
            'contactsJson' => $contacts->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->contact,
                'business' => $c->name,
                'phone' => $c->phone,
                'wa' => $c->waNumber(),
                'type' => $c->type,
                'need' => $c->need,
                'niche' => $c->niche,
                'district' => $c->district,
            ])->values(),
            'templates' => MessageTemplate::whereNull('user_id')
                ->orWhere('user_id', $request->user()->id)
                ->latest()->get(),
            'groups' => WhatsappGroup::where('is_active', true)->latest()->get(),
            'unlockedGroupIds' => $request->user()->groupUnlocks()->pluck('whatsapp_group_id')->all(),
            'balance' => (int) $request->user()->token_balance,
            'aiCost' => config('tokens.costs.ai_message'),
            'flwKey' => config('services.flutterwave.public_key'),
        ]);
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'contact_id' => ['nullable', 'integer'],
            'business' => ['nullable', 'string', 'max:255'],
            'niche' => ['nullable', 'string', 'max:255'],
            'tone' => ['nullable', Rule::in(['friendly', 'professional', 'urgent'])],
            'goal' => ['nullable', Rule::in(['first_outreach', 'follow_up', 'closing'])],
        ]);

        $cost = (int) config('tokens.costs.ai_message');

        if (! Wallet::canAfford($request->user(), $cost)) {
            return response()->json(['message' => 'Insufficient token balance. Top up your wallet to generate AI messages.'], 422);
        }

        $contact = null;
        if (! empty($validated['contact_id'])) {
            $contact = SavedContact::where('id', $validated['contact_id'])
                ->where('user_id', $request->user()->id)
                ->firstOrFail();
        }

        $result = SalesCopyService::generate(
            $contact?->contact,
            $contact?->name ?? $validated['business'] ?? null,
            $contact?->need ?? $validated['niche'] ?? null,
            $validated['tone'] ?? 'professional',
            $validated['goal'] ?? 'first_outreach'
        );

        Wallet::debit($request->user(), $cost, 'AI sales message'.($contact ? " for {$contact->name}" : ''));

        return response()->json([
            'message' => $result['message'],
            'source' => $result['source'],
            'balance' => (int) $request->user()->fresh()->token_balance,
        ]);
    }

    public function unlockGroup(Request $request, WhatsappGroup $group)
    {
        abort_if(! $group->is_active, 404);

        $existing = $request->user()->groupUnlocks()->where('whatsapp_group_id', $group->id)->first();

        if ($existing) {
            return response()->json(['invite_link' => $group->invite_link, 'already' => true]);
        }

        if (! Wallet::canAfford($request->user(), $group->token_cost)) {
            return response()->json(['message' => 'Insufficient token balance. Top up your wallet to unlock this group.'], 422);
        }

        Wallet::debit($request->user(), $group->token_cost, "Unlocked WhatsApp group: {$group->name}");

        $request->user()->groupUnlocks()->create([
            'whatsapp_group_id' => $group->id,
            'tokens_charged' => $group->token_cost,
        ]);

        return response()->json([
            'invite_link' => $group->invite_link,
            'balance' => (int) $request->user()->fresh()->token_balance,
        ]);
    }

    public function storeTemplate(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $template = $request->user()->messageTemplates()->create($validated);

        return response()->json(['id' => $template->id, 'name' => $template->name, 'body' => $template->body]);
    }

    public function destroyTemplate(Request $request, MessageTemplate $template)
    {
        abort_if($template->user_id === null || $template->user_id !== $request->user()->id, 403);
        $template->delete();

        return response()->json(['deleted' => true]);
    }
}
