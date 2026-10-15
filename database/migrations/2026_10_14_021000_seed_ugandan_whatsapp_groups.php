<?php

use App\Models\WhatsappGroup;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Curated Ugandan business WhatsApp communities, collected from the
     * GroupSor public directory (join pages resolve to live invites in a
     * real browser). Links rot — entries whose join page dies should be
     * deactivated, and user suggestions keep the list growing.
     */
    public const GROUPS = [
        ['UGANDAN BUSINESS', 'SMEs', 'Ugandan traders marketplace — buy and sell with traders countrywide.', 'H0pulTXntJeGWm3B5Xktpv'],
        ['NEW BUSINESS LOADING', 'SMEs', 'Online business ideas and earning opportunities.', 'BSRPC4lztVm5Pnzx765vRf'],
        ['Jahaz SMM Hub', 'Marketing & Advertising', 'Social media marketing services hub.', 'Isz3bSUsae5ESOQGxPKPU3'],
        ["Hajjat's Restaurant 24/7", 'Catering & Food Services', 'Restaurant and food community.', 'LxmwbjVae5fEv3S9jNPZNX'],
        ['AIRPORT PICKUP', 'Logistics & Transport', 'Airport pickup and travel services.', 'EtXafbc5SUX1NTJS449hX2'],
        ['Digital Marketing & AI', 'Marketing & Advertising', 'Digital marketing and AI talk.', 'KAsiLRysci2BqmvHvscmkK'],
        ['THE TEACHERS STAFFROOM', 'Education', 'Teachers staffroom community.', 'HJAObnQqdWX88RdAb2uJtw'],
        ['Business Plug & Sell', 'SMEs', 'Buy-and-sell plug.', 'DfbVnQxJmeAJAQEo3FifQl'],
    ];

    public function up(): void
    {
        // Retire the old placeholder rows (they carried fake invite links).
        WhatsappGroup::where('invite_link', 'like', '%REPLACE-WITH-REAL-LINK%')->delete();

        foreach (self::GROUPS as [$name, $niche, $description, $code]) {
            WhatsappGroup::updateOrCreate(
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
    }

    public function down(): void
    {
        WhatsappGroup::where('source', 'groupsor.directory')->delete();
    }
};
