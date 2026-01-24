<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ContactEvent;
use App\Models\ContactRequest;
use App\Models\DailyAnalytics;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AggregateDailyAnalytics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analytics:aggregate-daily
                            {--date= : Date to aggregate (YYYY-MM-DD), defaults to yesterday}
                            {--force : Force re-aggregation even if data exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Aggregate daily analytics from contact events and requests';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dateInput = $this->option('date');
        $force = $this->option('force');

        try {
            $date = $dateInput
                ? Carbon::parse($dateInput)
                : Carbon::yesterday();
        } catch (\Exception $e) {
            $this->error("Invalid date format: {$dateInput}");
            $this->info('Please use YYYY-MM-DD format');

            return 1;
        }

        $dateString = $date->toDateString();
        $this->info("Aggregating analytics for {$dateString}...");

        if ($date->isFuture()) {
            $this->error('Cannot aggregate data for future dates');

            return 1;
        }

        if ($date->isToday()) {
            $this->warn('Aggregating today\'s data. This is not recommended as data is still being collected.');
            if (! $force && ! $this->confirm('Do you want to continue?')) {
                $this->info('Aggregation cancelled');

                return 0;
            }
        }

        DB::beginTransaction();

        try {
            $this->aggregateForDate($date);
            DB::commit();

            $this->info('✅ Daily analytics aggregated successfully!');

            // Log success
            Log::info('Daily analytics aggregation completed', [
                'date' => $dateString,
                'timestamp' => now()->toIso8601String(),
            ]);

            return 0;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Daily analytics aggregation failed', [
                'date' => $dateString,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->error("❌ Aggregation failed: {$e->getMessage()}");

            return 1;
        }
    }

    /**
     * Aggregate analytics for a specific date.
     */
    private function aggregateForDate(Carbon $date): void
    {
        $dateString = $date->toDateString();

        // Get all approved salespersons
        $salespeople = User::where('role', 'salesperson')
            ->where('salesperson_status', 'approved')
            ->get();

        $this->info("Processing {$salespeople->count()} salespersons...");

        $progressBar = $this->output->createProgressBar($salespeople->count());
        $progressBar->start();

        $aggregated = 0;
        $skipped = 0;

        foreach ($salespeople as $salesperson) {
            $result = $this->aggregateForSalesperson($salesperson->id, $dateString);

            if ($result) {
                $aggregated++;
            } else {
                $skipped++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("✓ Aggregated: {$aggregated} salespersons");
        if ($skipped > 0) {
            $this->warn("⊘ Skipped: {$skipped} salespersons (no activity)");
        }
    }

    /**
     * Aggregate analytics for a specific salesperson and date.
     */
    private function aggregateForSalesperson(int $salespersonId, string $date): bool
    {
        // Count profile views
        $profileViews = ContactEvent::where('salesperson_id', $salespersonId)
            ->where('event_type', 'profile_view')
            ->whereDate('created_at', $date)
            ->count();

        // Count contact requests
        $contactRequests = ContactRequest::where('salesperson_id', $salespersonId)
            ->whereDate('created_at', $date)
            ->count();

        // Count unique visitors
        $uniqueVisitors = ContactEvent::where('salesperson_id', $salespersonId)
            ->where('event_type', 'profile_view')
            ->whereDate('created_at', $date)
            ->distinct('ip_address_hash')
            ->count('ip_address_hash');

        // Skip if no activity
        if ($profileViews === 0 && $contactRequests === 0) {
            return false;
        }

        // Use updateOrCreate to handle duplicates
        DailyAnalytics::updateOrCreate(
            [
                'salesperson_id' => $salespersonId,
                'date' => $date,
            ],
            [
                'profile_views_count' => $profileViews,
                'contact_requests_count' => $contactRequests,
                'unique_visitors_count' => $uniqueVisitors,
            ]
        );

        return true;
    }
}
