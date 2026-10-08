<?php

namespace Database\Seeders;

use App\Models\MessageTemplate;
use App\Models\WhatsappGroup;
use Illuminate\Database\Seeder;

class MarketingSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            ['Uganda SME Network', 'SMEs', 'Buyers, suppliers and partners trading across Uganda.', 2400],
            ['Kampala Construction Deals', 'Construction', 'Tenders, plots, materials and contractor hookups.', 1800],
            ['NGO & Grants Hub Africa', 'NGOs & Charities', 'Grant alerts, partnerships and funding talk.', 3100],
            ['Agribusiness Marketplace UG', 'Agriculture & Agribusiness', 'Buyers, inputs and farmgate prices daily.', 1500],
            ['Solar & Energy Pros UG', 'Solar & Renewable Energy', 'Installers, importers and institutional buyers.', 900],
            ['Kampala Real Estate Wire', 'Construction', 'Plots, rentals and property investors.', 2700],
        ];

        foreach ($groups as [$name, $niche, $description, $members]) {
            WhatsappGroup::firstOrCreate(
                ['name' => $name],
                [
                    'niche' => $niche,
                    'description' => $description,
                    // Replace with the real invite link in production.
                    'invite_link' => 'https://chat.whatsapp.com/REPLACE-WITH-REAL-LINK',
                    'member_count' => $members,
                    'token_cost' => 10,
                    'is_active' => true,
                ]
            );
        }

        $templates = [
            ['Cold opener', "Hi {name}, I work with businesses like {business} in Uganda. We build professional websites and hand you ready-to-contact prospects for {need} — from UGX 700/day. Open to a quick look at sample prospects for your niche?"],
            ['Value first', "Hi {name}, quick question: how is {business} currently finding new customers for {need}? I can send 3 sample prospects today, free — no strings. Want them?"],
            ['Follow-up nudge', "Hi {name}, just floating this back up — businesses like {business} usually reply fastest in the first week. We help with {need} and can start small. Worth a 10-minute chat?"],
            ['Direct closer', "Hi {name}, here is the simple version: first payment gets {business} online plus your first prospects, then small scheduled payments after that. No hidden fees. Shall I prepare the order?"],
        ];

        foreach ($templates as [$name, $body]) {
            MessageTemplate::firstOrCreate(
                ['name' => $name, 'user_id' => null],
                ['body' => $body]
            );
        }
    }
}
