<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:make-admin {email}', function (string $email) {
    $user = User::where('email', $email)->first();

    if (! $user) {
        $this->error("No user found with email {$email}. They must register first.");
        return 1;
    }

    $user->forceFill(['is_admin' => true])->save();
    $this->info("{$email} is now an administrator.");

    return 0;
})->purpose('Grant administrator access to a registered user');

Artisan::command('app:scrape-catalogue {--limit=5000}', function () {
    $limit = max(1, (int) $this->option('limit'));
    $service = App\Services\DirectoryScraper::class;

    // Phase 1: seed searches discover real category slugs.
    $slugs = [];
    $seeds = [];
    foreach ($service::NICHE_TERMS as $terms) {
        foreach ($terms as $term) {
            $seeds[] = $term;
        }
    }
    $seeds = array_values(array_unique($seeds));

    $this->info('Phase 1: discovering categories from '.count($seeds).' seed searches…');
    foreach ($seeds as $term) {
        $html = $service::fetchUrl('https://www.yellowpages.co.ug/search-results/'.urlencode($term));
        if ($html) {
            foreach ($service::discoverCategorySlugs($html) as $slug => $name) {
                $slugs[$slug] = $name;
            }
        }
        if (count($slugs) >= 150) {
            break;
        }
    }
    $this->info('Found '.count($slugs).' categories.');

    // Phase 2: scrape each category page into the index.
    $this->info('Phase 2: scraping categories (limit '.$limit.')…');
    $bar = $this->output->createProgressBar(min($limit, max(1, count($slugs) * 10)));
    $bar->start();
    $total = App\Models\ScrapedProspect::count();
    foreach ($slugs as $slug => $name) {
        if (App\Models\ScrapedProspect::count() >= $limit) {
            break;
        }
        $before = App\Models\ScrapedProspect::count();
        $service::scrapeCategory($slug, $name);
        $bar->advance(App\Models\ScrapedProspect::count() - $before);
    }
    $bar->finish();
    $this->newLine(2);
    $this->info('Done. Index holds '.App\Models\ScrapedProspect::count().' prospects ('.$total.' before this run).');
})->purpose('Bulk-scrape directory categories into the prospect index');

Artisan::command('app:test-mail {email}', function (string $email) {
    try {
        Mail::raw('Ttryy mail test — if you read this, sending works.', function ($message) use ($email) {
            $message->to($email)->subject('Ttryy mail test');
        });
    } catch (Throwable $e) {
        $this->error('Send failed: '.$e->getMessage());

        return 1;
    }

    $this->info("Test email handed to the mailer for {$email}.");

    return 0;
})->purpose('Send a test email to verify mail delivery');
