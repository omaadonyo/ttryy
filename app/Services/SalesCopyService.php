<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SalesCopyService
{
    /**
     * Generate a sales message. Uses live AI when an API key is configured,
     * otherwise falls back to the curated in-house sales library.
     *
     * @return array{message:string, source:string, tokens_used:int}
     */
    public static function generate(?string $contactName, ?string $business, ?string $need, string $tone = 'professional', string $goal = 'first_outreach'): array
    {
        $first = $contactName ? explode(' ', trim($contactName))[0] : 'there';
        $business = $business ?: 'your business';
        $need = $need ?: 'growing your sales';

        $live = self::generateWithAi($first, $business, $need, $tone, $goal);

        if ($live !== null) {
            return $live;
        }

        $library = self::library($goal);
        $template = $library[abs(crc32($first.$business.$goal)) % count($library)];

        return [
            'message' => strtr($template, [
                '{name}' => $first,
                '{business}' => $business,
                '{need}' => $need,
            ]),
            'source' => 'library',
            'tokens_used' => 0,
        ];
    }

    protected static function generateWithAi(string $first, string $business, string $need, string $tone, string $goal): ?array
    {
        $key = config('services.openai.key');

        if (! $key) {
            return null;
        }

        try {
            $response = Http::withToken($key)->acceptJson()->post(
                rtrim((string) config('services.openai.base_url', 'https://api.openai.com/v1'), '/').'/chat/completions',
                [
                    'model' => config('services.openai.model', 'gpt-4o-mini'),
                    'max_tokens' => 220,
                    'temperature' => 0.8,
                    'messages' => [
                        ['role' => 'system', 'content' => 'You write short WhatsApp sales messages for Ugandan small businesses. Under 60 words. Friendly, direct, one clear call to action. No hashtags, no emojis overload (max one). Plain text only.'],
                        ['role' => 'user', 'content' => "Write a {$tone} sales message. Goal: ".str_replace('_', ' ', $goal).". Recipient first name: {$first}. Their business: {$business}. What we can help with: {$need}. Sign off as a Ttryy business partner."],
                    ],
                ]
            );

            if (! $response->successful()) {
                return null;
            }

            $message = trim((string) ($response->json('choices.0.message.content') ?? ''));

            if ($message === '') {
                return null;
            }

            return [
                'message' => $message,
                'source' => 'ai',
                'tokens_used' => (int) ($response->json('usage.total_tokens') ?? 0),
            ];
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Curated in-house closers: hook, proof, offer, urgency, CTA.
     *
     * @return string[]
     */
    protected static function library(string $goal): array
    {
        return match ($goal) {
            'follow_up' => [
                "Hi {name}, just floating this back up — businesses like {business} usually reply fastest in the first week, so I wanted you to see this before Friday. We help with {need} and can start small. Worth a 10-minute chat?",
                "Hi {name}, I know inboxes get busy. Quick one: we recently helped a business like {business} line up new work around {need}. If timing was the issue, what would make next week work for a short call?",
                "Hi {name}, last nudge from me — I have two onboarding slots left this month for {need}. Want me to hold one for {business}, or should I close the loop?",
            ],
            'closing' => [
                "Hi {name}, great speaking. To lock this in for {business}: we start on {need} within 5 days of your first payment, and you can track everything from your dashboard. Shall I send the payment link now?",
                "Hi {name}, here is the simple version: first payment gets {business} online plus your first prospects, then small scheduled payments after that. No hidden fees. Ready when you are — want me to prepare the order?",
                "Hi {name}, most clients hesitate at the same point, then wish they had started a month earlier. For {need}, starting this week means {business} is visible before month-end. Shall we go ahead?",
            ],
            default => [
                "Hi {name}, I work with businesses like {business} in Uganda. We build professional websites and hand you ready-to-contact prospects for {need} — from UGX 700/day. Open to a quick look at sample prospects for your niche?",
                "Hi {name}, quick question: how is {business} currently finding new customers for {need}? We help businesses get online and line up real buyers to call. Can I send you 3 sample prospects, free?",
                "Hi {name}, {business} caught my eye because demand for {need} is rising right now. We get businesses like yours online in 5 days with prospects included. Worth a 10-minute chat this week?",
            ],
        };
    }
}
