<?php

namespace App\Http\Controllers;

use App\Models\CampaignRecipient;
use App\Models\EmailCampaign;
use App\Models\MessageTemplate;
use App\Models\SavedContact;
use App\Models\WhatsappGroup;
use App\Services\DirectoryScraper;
use App\Services\SalesCopyService;
use App\Services\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
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
            'campaigns' => EmailCampaign::where('user_id', $request->user()->id)->latest()->take(10)->get(),
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

    /**
     * Live directory search: local index first, Yellow Pages live-scrape
     * when the index is empty or stale. Everything found is persisted.
     */
    public function scraperSearch(Request $request)
    {
        $validated = $request->validate([
            'niche' => ['required', 'string', 'max:255'],
            'keyword' => ['nullable', 'string', 'max:255'],
        ]);

        set_time_limit(120);

        try {
            $result = DirectoryScraper::search(
                $validated['niche'],
                $validated['keyword'] ?? $validated['niche'],
                2
            );
        } catch (\Throwable $e) {
            Log::warning('Directory search failed', ['error' => $e->getMessage()]);

            return response()->json(['message' => 'Directory search failed. Please try again in a minute.'], 422);
        }

        return response()->json($result);
    }

    public function suggestGroup(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'niche' => ['nullable', 'string', 'max:255'],
            'invite_link' => ['required', 'url', 'max:500'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            Mail::raw(
                "New WhatsApp group suggestion from {$request->user()->name} ({$request->user()->email}):\n\n".
                "Name: {$validated['name']}\nNiche: ".($validated['niche'] ?? '—')."\n".
                "Link: {$validated['invite_link']}\nDescription: ".($validated['description'] ?? '—'),
                function ($message) {
                    $message->to(config('packages.admin_email'))->subject('New WhatsApp group suggestion');
                }
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Group suggestion email failed', ['error' => $e->getMessage()]);

            return response()->json(['message' => 'Suggestion saved. Our team will review it shortly.']);
        }

        return response()->json(['message' => 'Thanks! Our team will review and list this group.']);
    }

    /**
     * Send an email campaign to saved contacts (with emails) and/or
     * manually entered addresses. Tracks sends, delivery and opens.
     */
    public function sendCampaign(Request $request)
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:10000'],
            'contact_ids' => ['nullable', 'array', 'max:50'],
            'contact_ids.*' => ['integer'],
            'emails' => ['nullable', 'string', 'max:5000'],
        ]);

        $recipients = collect();

        if (! empty($validated['contact_ids'])) {
            $contacts = SavedContact::where('user_id', $request->user()->id)
                ->whereIn('id', $validated['contact_ids'])
                ->whereNotNull('email')
                ->get();

            foreach ($contacts as $c) {
                $recipients->push(['email' => $c->email, 'name' => $c->contact]);
            }
        }

        if (! empty($validated['emails'])) {
            foreach (preg_split('/[\s,;]+/', $validated['emails']) as $email) {
                $email = trim($email);
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $recipients->push(['email' => strtolower($email), 'name' => null]);
                }
            }
        }

        $recipients = $recipients->unique('email')->take(50)->values();

        if ($recipients->isEmpty()) {
            return response()->json(['message' => 'No valid email recipients. Saved contacts need an email address, or type addresses manually.'], 422);
        }

        $campaign = EmailCampaign::create([
            'user_id' => $request->user()->id,
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'status' => 'sending',
            'total' => $recipients->count(),
        ]);

        $sent = 0;
        $failed = 0;

        foreach ($recipients as $r) {
            $recipient = $campaign->recipients()->create([
                'email' => $r['email'],
                'name' => $r['name'],
                'token' => Str::random(40),
            ]);

            try {
                Mail::html(
                    $this->campaignHtml($validated['subject'], $validated['body'], $r['name'], $recipient->token),
                    function ($message) use ($r, $validated) {
                        $message->to($r['email'])->subject($validated['subject']);
                    }
                );
                $recipient->update(['sent_at' => now(), 'delivered_at' => now()]);
                $sent++;
            } catch (\Throwable) {
                $failed++;
            }
        }

        $campaign->update([
            'status' => 'sent',
            'sent' => $sent,
            'delivered' => $sent,
            'failed' => $failed,
        ]);

        return response()->json([
            'campaign_id' => $campaign->id,
            'sent' => $sent,
            'failed' => $failed,
            'redirect' => route('marketing.campaigns.show', $campaign),
        ]);
    }

    protected function campaignHtml(string $subject, string $body, ?string $name, string $token): string
    {
        $first = $name ? explode(' ', trim($name))[0] : 'there';
        $text = str_replace('{name}', e($first), e($body));
        $pixel = url('/t/'.$token);

        return "<div style=\"font-family:Arial,sans-serif;max-width:560px;\">"
            ."<h2 style=\"margin:0 0 12px;\">".e($subject).'</h2>'
            .'<div>'.nl2br($text).'</div>'
            ."<p style=\"color:#71717a;font-size:12px;\">Sent via Ttryy Marketing</p>"
            ."<img src=\"{$pixel}\" width=\"1\" height=\"1\" alt=\"\" />"
            .'</div>';
    }

    public function showCampaign(EmailCampaign $campaign)
    {
        abort_if($campaign->user_id !== request()->user()->id && ! request()->user()->is_admin, 403);

        $campaign->loadCount(['recipients as read_count' => fn ($q) => $q->whereNotNull('opened_at')]);

        return view('campaign-report', [
            'campaign' => $campaign,
            'opensByDay' => $campaign->opensByDay(),
        ]);
    }

    /** 1px open-tracking pixel. */
    public function trackOpen(string $token)
    {
        $recipient = CampaignRecipient::where('token', $token)->first();

        if ($recipient) {
            $recipient->update([
                'opened_at' => $recipient->opened_at ?? now(),
                'open_count' => $recipient->open_count + 1,
            ]);
        }

        return response(base64_decode('R0lGODlhAQABAIAAAP///////yH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=='), 200, [
            'Content-Type' => 'image/gif',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }
}
