<?php

namespace Database\Seeders;

use App\Models\MessageTemplate;
use App\Models\WhatsappGroup;
use Illuminate\Database\Seeder;

class MarketingSeeder extends Seeder
{
    public function run(): void
    {
        // Curated Ugandan business communities (join pages resolve to live
        // invites in a real browser). Source: GroupSor public directory.
        $groups = [
            ['UGANDAN BUSINESS', 'SMEs', 'Ugandan traders marketplace — buy and sell with traders countrywide.', 'H0pulTXntJeGWm3B5Xktpv'],
            ['NEW BUSINESS LOADING', 'SMEs', 'Online business ideas and earning opportunities.', 'BSRPC4lztVm5Pnzx765vRf'],
            ['Jahaz SMM Hub', 'Marketing & Advertising', 'Social media marketing services hub.', 'Isz3bSUsae5ESOQGxPKPU3'],
            ["Hajjat's Restaurant 24/7", 'Catering & Food Services', 'Restaurant and food community.', 'LxmwbjVae5fEv3S9jNPZNX'],
            ['AIRPORT PICKUP', 'Logistics & Transport', 'Airport pickup and travel services.', 'EtXafbc5SUX1NTJS449hX2'],
            ['Digital Marketing & AI', 'Marketing & Advertising', 'Digital marketing and AI talk.', 'KAsiLRysci2BqmvHvscmkK'],
            ['THE TEACHERS STAFFROOM', 'Education', 'Teachers staffroom community.', 'HJAObnQqdWX88RdAb2uJtw'],
            ['Business Plug & Sell', 'SMEs', 'Buy-and-sell plug.', 'DfbVnQxJmeAJAQEo3FifQl'],
        ];

        foreach ($groups as [$name, $niche, $description, $code]) {
            WhatsappGroup::firstOrCreate(
                ['name' => $name],
                [
                    'niche' => $niche,
                    'description' => $description,
                    'invite_link' => 'https://groupsor.link/group/join/'.$code,
                    'source' => 'groupsor.directory',
                    'member_count' => 0,
                    'token_cost' => 10,
                    'is_active' => true,
                ]
            );
        }

        $templates = [
            ['Cold opener', 'Hi {name}, I work with businesses like {business} in Uganda. We build professional websites and hand you ready-to-contact prospects for {need} — from UGX 700/day. Open to a quick look at sample prospects for your niche?'],
            ['Value first', 'Hi {name}, quick question: how is {business} currently finding new customers for {need}? I can send 3 sample prospects today, free — no strings. Want them?'],
            ['Follow-up nudge', 'Hi {name}, just floating this back up — businesses like {business} usually reply fastest in the first week. We help with {need} and can start small. Worth a 10-minute chat?'],
            ['Direct closer', 'Hi {name}, here is the simple version: first payment gets {business} online plus your first prospects, then small scheduled payments after that. No hidden fees. Shall I prepare the order?'],
        ];

        foreach ($templates as [$name, $body]) {
            MessageTemplate::firstOrCreate(
                ['name' => $name, 'user_id' => null],
                ['body' => $body]
            );
        }
    }
}
