<?php

use App\Models\HostedWebsite;
use App\Models\ScrapedProspect;
use App\Models\User;
use App\Services\DirectoryScraper;
use App\Services\Hosting\AnalyticsRecorder;
use App\Services\Hosting\HealthChecker;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schedule;

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
    $service = DirectoryScraper::class;

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
    $total = ScrapedProspect::count();
    foreach ($slugs as $slug => $name) {
        if (ScrapedProspect::count() >= $limit) {
            break;
        }
        $before = ScrapedProspect::count();
        $service::scrapeCategory($slug, $name);
        $bar->advance(ScrapedProspect::count() - $before);
    }
    $bar->finish();
    $this->newLine(2);
    $this->info('Done. Index holds '.ScrapedProspect::count().' prospects ('.$total.' before this run).');
})->purpose('Bulk-scrape directory categories into the prospect index');

Artisan::command('app:test-mail {email}', function (string $email) {
    try {
        Mail::raw('ttryy mail test — if you read this, sending works.', function ($message) use ($email) {
            $message->to($email)->subject('ttryy mail test');
        });
    } catch (Throwable $e) {
        $this->error('Send failed: '.$e->getMessage());

        return 1;
    }

    $this->info("Test email handed to the mailer for {$email}.");

    return 0;
})->purpose('Send a test email to verify mail delivery');

Artisan::command('hosting:health-check', function () {
    $sites = HostedWebsite::whereIn('status', [
        HostedWebsite::STATUS_ACTIVE,
        HostedWebsite::STATUS_PROVISIONING,
    ])->get();

    $counts = ['healthy' => 0, 'degraded' => 0, 'unavailable' => 0, 'unknown' => 0];

    foreach ($sites as $site) {
        $check = HealthChecker::checkWebsite($site);
        $counts[$check->status] = ($counts[$check->status] ?? 0) + 1;
    }

    $this->info('Checked '.$sites->count().' websites: '.collect($counts)->map(fn ($c, $s) => "{$s}={$c}")->implode(', '));

    return 0;
})->purpose('Run central availability checks for hosted websites');

Artisan::command('hosting:aggregate-analytics {--date=}', function () {
    $date = $this->option('date') ?: now()->subDay()->toDateString();
    $sites = HostedWebsite::all();
    $events = 0;

    foreach ($sites as $site) {
        $day = AnalyticsRecorder::aggregateDay($site, $date);
        $events += $day->pageviews;
    }

    $this->info("Aggregated {$date}: {$events} pageviews across ".$sites->count().' websites.');

    return 0;
})->purpose('Roll raw analytics events into daily aggregates');

Schedule::command('hosting:health-check')->everyThirtyMinutes();
Schedule::command('hosting:aggregate-analytics')->dailyAt('01:30');

Artisan::command('hosting:check-renewals', function () {
    $queued = App\Services\Hosting\HostingSubscription::suspendOverdue();
    $this->info("Queued {$queued} overdue suspension(s).");

    return 0;
})->purpose('Suspend hosting accounts past renewal with balance outstanding');

Schedule::command('hosting:check-renewals')->dailyAt('02:00');
