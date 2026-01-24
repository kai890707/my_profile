<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactEvent;
use App\Models\ContactRequest;
use App\Models\DailyAnalytics;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DashboardController extends Controller
{
    /**
     * Get salesperson statistics (profile views, contacts, growth).
     * Endpoint: GET /api/salesperson/analytics/stats
     */
    public function salespersonStats(Request $request): JsonResponse
    {
        $request->validate([
            'range' => ['nullable', 'string', Rule::in(['today', '7days', '30days'])],
        ]);

        $user = $request->user();
        $range = $request->input('range', '7days');

        [$startDate, $endDate] = $this->calculateDateRange($range);
        $today = Carbon::today()->toDateString();

        // 1. Query historical aggregated data (yesterday and earlier)
        $historicalStats = DailyAnalytics::forSalesperson($user->id)
            ->where('date', '>=', $startDate->toDateString())
            ->where('date', '<', $today)
            ->selectRaw('
                COALESCE(SUM(profile_views_count), 0) as profile_views,
                COALESCE(SUM(contact_requests_count), 0) as contact_requests,
                COALESCE(SUM(unique_visitors_count), 0) as unique_visitors
            ')
            ->first();

        // 2. Query today's real-time data
        $todayProfileViews = ContactEvent::where('salesperson_id', $user->id)
            ->where('event_type', 'profile_view')
            ->whereDate('created_at', $today)
            ->count();

        $todayContactRequests = ContactRequest::where('salesperson_id', $user->id)
            ->whereDate('created_at', $today)
            ->count();

        $todayUniqueVisitors = ContactEvent::where('salesperson_id', $user->id)
            ->where('event_type', 'profile_view')
            ->whereDate('created_at', $today)
            ->distinct('ip_address_hash')
            ->count('ip_address_hash');

        // 3. Combine data
        $totalProfileViews = ($historicalStats->profile_views ?? 0) + $todayProfileViews;
        $totalContactRequests = ($historicalStats->contact_requests ?? 0) + $todayContactRequests;
        $totalUniqueVisitors = ($historicalStats->unique_visitors ?? 0) + $todayUniqueVisitors;

        // 4. Calculate conversion rate
        $conversionRate = $this->calculateConversionRate($totalProfileViews, $totalContactRequests);

        // 5. Calculate previous period stats and growth
        [$previousPeriodData, $growth] = $this->calculatePreviousPeriodAndGrowth(
            $user->id,
            $range,
            $totalProfileViews,
            $totalContactRequests,
            $totalUniqueVisitors,
            $conversionRate
        );

        return response()->json([
            'success' => true,
            'data' => [
                'profile_views' => $totalProfileViews,
                'contact_requests' => $totalContactRequests,
                'unique_visitors' => $totalUniqueVisitors,
                'conversion_rate' => $conversionRate,
                'previous_period' => $previousPeriodData,
                'growth' => $growth,
                'range' => $range,
            ],
            'meta' => [
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Get salesperson trends (daily data array).
     * Endpoint: GET /api/salesperson/analytics/trends
     */
    public function salespersonTrends(Request $request): JsonResponse
    {
        $request->validate([
            'range' => ['nullable', 'string', Rule::in(['today', '7days', '30days'])],
        ]);

        $user = $request->user();
        $range = $request->input('range', '7days');

        [$startDate, $endDate] = $this->calculateDateRange($range);
        $today = Carbon::today()->toDateString();

        // 1. Query historical aggregated data
        $historicalTrends = DailyAnalytics::forSalesperson($user->id)
            ->where('date', '>=', $startDate->toDateString())
            ->where('date', '<', $today)
            ->get()
            ->keyBy('date');

        // 2. Query today's real-time data
        $todayData = [
            'date' => $today,
            'profile_views' => ContactEvent::where('salesperson_id', $user->id)
                ->where('event_type', 'profile_view')
                ->whereDate('created_at', $today)
                ->count(),
            'contact_requests' => ContactRequest::where('salesperson_id', $user->id)
                ->whereDate('created_at', $today)
                ->count(),
            'unique_visitors' => ContactEvent::where('salesperson_id', $user->id)
                ->where('event_type', 'profile_view')
                ->whereDate('created_at', $today)
                ->distinct('ip_address_hash')
                ->count('ip_address_hash'),
        ];

        // 3. Fill all dates with zero values if missing
        $trends = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            $dateString = $currentDate->toDateString();

            if ($dateString === $today) {
                $trends[] = $todayData;
            } elseif (isset($historicalTrends[$dateString])) {
                $trends[] = [
                    'date' => $dateString,
                    'profile_views' => $historicalTrends[$dateString]->profile_views_count,
                    'contact_requests' => $historicalTrends[$dateString]->contact_requests_count,
                    'unique_visitors' => $historicalTrends[$dateString]->unique_visitors_count,
                ];
            } else {
                // Fill with zero values
                $trends[] = [
                    'date' => $dateString,
                    'profile_views' => 0,
                    'contact_requests' => 0,
                    'unique_visitors' => 0,
                ];
            }

            $currentDate->addDay();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'trends' => $trends,
                'range' => $range,
            ],
            'meta' => [
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Get recent contact requests (latest 10).
     * Endpoint: GET /api/salesperson/analytics/recent-contacts
     */
    public function recentContacts(Request $request): JsonResponse
    {
        $user = $request->user();

        $contacts = ContactRequest::where('salesperson_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(fn ($contact) => [
                'id' => $contact->id,
                'customer_name' => $contact->customer_name,
                'customer_email' => $contact->customer_email,
                'customer_phone' => $contact->customer_phone,
                'message' => $contact->message,
                'status' => $contact->status,
                'created_at' => $contact->created_at->toIso8601String(),
            ]);

        return response()->json([
            'success' => true,
            'data' => [
                'contacts' => $contacts,
                'total' => $contacts->count(),
            ],
            'meta' => [
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Get admin platform overview (total salespersons, views, contacts, conversion rate).
     * Endpoint: GET /api/admin/analytics/overview
     */
    public function adminOverview(Request $request): JsonResponse
    {
        $request->validate([
            'range' => ['nullable', 'string', Rule::in(['7days', '30days'])],
        ]);

        $range = $request->input('range', '30days');

        [$startDate, $endDate] = $this->calculateDateRange($range);
        $today = Carbon::today()->toDateString();

        // Total salespersons
        $totalSalespersons = User::where('role', 'salesperson')
            ->where('salesperson_status', 'approved')
            ->count();

        // Historical aggregated data
        $historicalStats = DailyAnalytics::where('date', '>=', $startDate->toDateString())
            ->where('date', '<', $today)
            ->selectRaw('
                COALESCE(SUM(profile_views_count), 0) as profile_views,
                COALESCE(SUM(contact_requests_count), 0) as contact_requests,
                COALESCE(SUM(unique_visitors_count), 0) as unique_visitors
            ')
            ->first();

        // Today's real-time data
        $todayProfileViews = ContactEvent::where('event_type', 'profile_view')
            ->whereDate('created_at', $today)
            ->count();

        $todayContactRequests = ContactRequest::whereDate('created_at', $today)->count();

        $todayUniqueVisitors = ContactEvent::where('event_type', 'profile_view')
            ->whereDate('created_at', $today)
            ->distinct('ip_address_hash')
            ->count('ip_address_hash');

        // Combine data
        $totalProfileViews = ($historicalStats->profile_views ?? 0) + $todayProfileViews;
        $totalContactRequests = ($historicalStats->contact_requests ?? 0) + $todayContactRequests;
        $totalUniqueVisitors = ($historicalStats->unique_visitors ?? 0) + $todayUniqueVisitors;

        $platformConversionRate = $this->calculateConversionRate($totalProfileViews, $totalContactRequests);

        // Active salespersons (has at least 1 profile_view in the range)
        $activeSalespersonIds = DailyAnalytics::where('date', '>=', $startDate->toDateString())
            ->where('date', '<', $today)
            ->where('profile_views_count', '>', 0)
            ->distinct('salesperson_id')
            ->pluck('salesperson_id');

        // Add today's active salespersons
        $todayActiveSalespersonIds = ContactEvent::where('event_type', 'profile_view')
            ->whereDate('created_at', $today)
            ->distinct('salesperson_id')
            ->pluck('salesperson_id');

        $allActiveSalespersonIds = $activeSalespersonIds->merge($todayActiveSalespersonIds)->unique();
        $activeSalespersons = $allActiveSalespersonIds->count();
        $inactiveSalespersons = $totalSalespersons - $activeSalespersons;
        $activityRate = $totalSalespersons > 0
            ? round(($activeSalespersons / $totalSalespersons) * 100, 2)
            : 0.0;

        // Previous period
        [$previousPeriodData, $growth] = $this->calculatePreviousPeriodAndGrowthForAdmin(
            $range,
            $totalProfileViews,
            $totalContactRequests,
            $platformConversionRate,
            $activeSalespersons
        );

        return response()->json([
            'success' => true,
            'data' => [
                'total_salespersons' => $totalSalespersons,
                'total_profile_views' => $totalProfileViews,
                'total_contact_requests' => $totalContactRequests,
                'total_unique_visitors' => $totalUniqueVisitors,
                'platform_conversion_rate' => $platformConversionRate,
                'active_salespersons' => $activeSalespersons,
                'inactive_salespersons' => $inactiveSalespersons,
                'activity_rate' => $activityRate,
                'previous_period' => $previousPeriodData,
                'growth' => $growth,
                'range' => $range,
            ],
            'meta' => [
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Get top 10 salespersons by profile views.
     * Endpoint: GET /api/admin/analytics/top-salespersons
     */
    public function topSalespersons(Request $request): JsonResponse
    {
        $request->validate([
            'range' => ['nullable', 'string', Rule::in(['7days', '30days'])],
        ]);

        $range = $request->input('range', '30days');

        [$startDate, $endDate] = $this->calculateDateRange($range);
        $today = Carbon::today()->toDateString();

        // Historical aggregated data grouped by salesperson
        $historicalTopSalespersons = DailyAnalytics::where('date', '>=', $startDate->toDateString())
            ->where('date', '<', $today)
            ->select('salesperson_id')
            ->selectRaw('SUM(profile_views_count) as total_views')
            ->selectRaw('SUM(contact_requests_count) as total_contacts')
            ->selectRaw('SUM(unique_visitors_count) as total_visitors')
            ->groupBy('salesperson_id')
            ->get()
            ->keyBy('salesperson_id');

        // Today's data grouped by salesperson
        $todayStats = ContactEvent::where('event_type', 'profile_view')
            ->whereDate('created_at', $today)
            ->select('salesperson_id')
            ->selectRaw('COUNT(*) as today_views')
            ->selectRaw('COUNT(DISTINCT ip_address_hash) as today_visitors')
            ->groupBy('salesperson_id')
            ->get()
            ->keyBy('salesperson_id');

        $todayContacts = ContactRequest::whereDate('created_at', $today)
            ->select('salesperson_id')
            ->selectRaw('COUNT(*) as today_contacts')
            ->groupBy('salesperson_id')
            ->pluck('today_contacts', 'salesperson_id');

        // Merge and calculate totals
        $allSalespersonIds = $historicalTopSalespersons->keys()
            ->merge($todayStats->keys())
            ->unique();

        $topSalespersons = $allSalespersonIds->map(function ($salespersonId) use ($historicalTopSalespersons, $todayStats, $todayContacts) {
            $historical = $historicalTopSalespersons->get($salespersonId);
            $todayStat = $todayStats->get($salespersonId);

            $totalViews = ($historical->total_views ?? 0) + ($todayStat->today_views ?? 0);
            $totalContacts = ($historical->total_contacts ?? 0) + ($todayContacts->get($salespersonId) ?? 0);
            $totalVisitors = ($historical->total_visitors ?? 0) + ($todayStat->today_visitors ?? 0);

            return [
                'salesperson_id' => $salespersonId,
                'profile_views' => $totalViews,
                'contact_requests' => $totalContacts,
                'unique_visitors' => $totalVisitors,
                'conversion_rate' => $this->calculateConversionRate($totalViews, $totalContacts),
            ];
        })
            ->sortByDesc('profile_views')
            ->take(10)
            ->values();

        // Load salesperson details
        $salespersonIds = $topSalespersons->pluck('salesperson_id');
        $salespersons = User::whereIn('id', $salespersonIds)
            ->get(['id', 'name', 'email'])
            ->keyBy('id');

        $topSalespersons = $topSalespersons->map(function ($item) use ($salespersons) {
            $salesperson = $salespersons->get($item['salesperson_id']);

            return [
                'salesperson_id' => $item['salesperson_id'],
                'name' => $salesperson->name ?? 'Unknown',
                'email' => $salesperson->email ?? '',
                'profile_views' => $item['profile_views'],
                'contact_requests' => $item['contact_requests'],
                'unique_visitors' => $item['unique_visitors'],
                'conversion_rate' => $item['conversion_rate'],
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'top_salespersons' => $topSalespersons,
                'range' => $range,
            ],
            'meta' => [
                'timestamp' => now()->toIso8601String(),
                'total_count' => $topSalespersons->count(),
            ],
        ]);
    }

    /**
     * Get salesperson activity breakdown.
     * Endpoint: GET /api/admin/analytics/activity
     */
    public function adminActivity(Request $request): JsonResponse
    {
        // Fixed to last 7 days
        $startDate = Carbon::today()->subDays(6)->toDateString();
        $endDate = Carbon::today()->toDateString();
        $today = Carbon::today()->toDateString();

        // Total salespersons
        $totalSalespersons = User::where('role', 'salesperson')
            ->where('salesperson_status', 'approved')
            ->count();

        // Get all salespersons' view counts
        $historicalViewCounts = DailyAnalytics::where('date', '>=', $startDate)
            ->where('date', '<', $today)
            ->select('salesperson_id')
            ->selectRaw('SUM(profile_views_count) as total_views')
            ->groupBy('salesperson_id')
            ->pluck('total_views', 'salesperson_id');

        $todayViewCounts = ContactEvent::where('event_type', 'profile_view')
            ->whereDate('created_at', $today)
            ->select('salesperson_id')
            ->selectRaw('COUNT(*) as today_views')
            ->groupBy('salesperson_id')
            ->pluck('today_views', 'salesperson_id');

        // Merge view counts
        $allSalespersonIds = User::where('role', 'salesperson')
            ->where('salesperson_status', 'approved')
            ->pluck('id');

        $viewCounts = $allSalespersonIds->mapWithKeys(function ($id) use ($historicalViewCounts, $todayViewCounts) {
            $total = ($historicalViewCounts->get($id) ?? 0) + ($todayViewCounts->get($id) ?? 0);

            return [$id => $total];
        });

        // Calculate activity breakdown
        $breakdown = [
            ['range' => '0 views', 'count' => 0, 'percentage' => 0.0],
            ['range' => '1-10 views', 'count' => 0, 'percentage' => 0.0],
            ['range' => '11-50 views', 'count' => 0, 'percentage' => 0.0],
            ['range' => '51-100 views', 'count' => 0, 'percentage' => 0.0],
            ['range' => '100+ views', 'count' => 0, 'percentage' => 0.0],
        ];

        foreach ($viewCounts as $count) {
            if ($count === 0) {
                $breakdown[0]['count']++;
            } elseif ($count <= 10) {
                $breakdown[1]['count']++;
            } elseif ($count <= 50) {
                $breakdown[2]['count']++;
            } elseif ($count <= 100) {
                $breakdown[3]['count']++;
            } else {
                $breakdown[4]['count']++;
            }
        }

        // Calculate percentages
        foreach ($breakdown as &$item) {
            $item['percentage'] = $totalSalespersons > 0
                ? round(($item['count'] / $totalSalespersons) * 100, 2)
                : 0.0;
        }

        $activeSalespersons = $viewCounts->filter(fn ($count) => $count > 0)->count();
        $inactiveSalespersons = $totalSalespersons - $activeSalespersons;
        $activityRate = $totalSalespersons > 0
            ? round(($activeSalespersons / $totalSalespersons) * 100, 2)
            : 0.0;

        return response()->json([
            'success' => true,
            'data' => [
                'total_salespersons' => $totalSalespersons,
                'active_salespersons' => $activeSalespersons,
                'inactive_salespersons' => $inactiveSalespersons,
                'activity_rate' => $activityRate,
                'activity_breakdown' => $breakdown,
            ],
            'meta' => [
                'timestamp' => now()->toIso8601String(),
                'period' => 'Last 7 days',
            ],
        ]);
    }

    /**
     * Get platform growth trends.
     * Endpoint: GET /api/admin/analytics/growth
     */
    public function adminTrends(Request $request): JsonResponse
    {
        $request->validate([
            'range' => ['nullable', 'string', Rule::in(['7days', '30days'])],
        ]);

        $range = $request->input('range', '30days');

        [$startDate, $endDate] = $this->calculateDateRange($range);
        $today = Carbon::today()->toDateString();

        // Historical aggregated data grouped by date
        $historicalTrends = DailyAnalytics::where('date', '>=', $startDate->toDateString())
            ->where('date', '<', $today)
            ->select('date')
            ->selectRaw('SUM(profile_views_count) as profile_views')
            ->selectRaw('SUM(contact_requests_count) as contact_requests')
            ->selectRaw('SUM(unique_visitors_count) as unique_visitors')
            ->selectRaw('COUNT(DISTINCT salesperson_id) as active_salespersons')
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        // Today's real-time data
        $todayData = [
            'date' => $today,
            'profile_views' => ContactEvent::where('event_type', 'profile_view')
                ->whereDate('created_at', $today)
                ->count(),
            'contact_requests' => ContactRequest::whereDate('created_at', $today)
                ->count(),
            'unique_visitors' => ContactEvent::where('event_type', 'profile_view')
                ->whereDate('created_at', $today)
                ->distinct('ip_address_hash')
                ->count('ip_address_hash'),
            'active_salespersons' => ContactEvent::where('event_type', 'profile_view')
                ->whereDate('created_at', $today)
                ->distinct('salesperson_id')
                ->count('salesperson_id'),
        ];

        // Fill all dates
        $trends = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            $dateString = $currentDate->toDateString();

            if ($dateString === $today) {
                $trends[] = $todayData;
            } elseif (isset($historicalTrends[$dateString])) {
                /** @var object{date: string, profile_views: int, contact_requests: int, unique_visitors: int, active_salespersons: int} $trend */
                $trend = $historicalTrends[$dateString];
                $trends[] = [
                    'date' => $dateString,
                    'profile_views' => $trend->profile_views,
                    'contact_requests' => $trend->contact_requests,
                    'unique_visitors' => $trend->unique_visitors,
                    'active_salespersons' => $trend->active_salespersons,
                ];
            } else {
                $trends[] = [
                    'date' => $dateString,
                    'profile_views' => 0,
                    'contact_requests' => 0,
                    'unique_visitors' => 0,
                    'active_salespersons' => 0,
                ];
            }

            $currentDate->addDay();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'trends' => $trends,
                'range' => $range,
            ],
            'meta' => [
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Calculate date range based on range parameter.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    private function calculateDateRange(string $range): array
    {
        $endDate = Carbon::today();

        $startDate = match ($range) {
            'today' => Carbon::today(),
            '7days' => Carbon::today()->subDays(6), // Today + 6 days ago = 7 days
            '30days' => Carbon::today()->subDays(29), // Today + 29 days ago = 30 days
            default => Carbon::today()->subDays(6),
        };

        return [$startDate, $endDate];
    }

    /**
     * Calculate previous period date range.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    private function calculatePreviousPeriod(string $range): array
    {
        return match ($range) {
            'today' => [
                Carbon::yesterday(),
                Carbon::yesterday(),
            ],
            '7days' => [
                Carbon::today()->subDays(14),
                Carbon::today()->subDays(8),
            ],
            '30days' => [
                Carbon::today()->subDays(60),
                Carbon::today()->subDays(31),
            ],
            default => [
                Carbon::today()->subDays(14),
                Carbon::today()->subDays(8),
            ],
        };
    }

    /**
     * Calculate conversion rate.
     */
    private function calculateConversionRate(int $profileViews, int $contactRequests): float
    {
        return $profileViews > 0
            ? round(($contactRequests / $profileViews) * 100, 2)
            : 0.0;
    }

    /**
     * Calculate growth rate percentage.
     */
    private function calculateGrowthRate(int|float $current, int|float $previous): ?float
    {
        if ($previous == 0) {
            return null;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }

    /**
     * Calculate previous period data and growth for salesperson.
     *
     * @return array{0: array, 1: array}
     */
    private function calculatePreviousPeriodAndGrowth(
        int $salespersonId,
        string $range,
        int $currentViews,
        int $currentContacts,
        int $currentVisitors,
        float $currentConversionRate
    ): array {
        [$prevStartDate, $prevEndDate] = $this->calculatePreviousPeriod($range);

        // Query previous period data (no today involved in previous period)
        $previousStats = DailyAnalytics::forSalesperson($salespersonId)
            ->where('date', '>=', $prevStartDate->toDateString())
            ->where('date', '<=', $prevEndDate->toDateString())
            ->selectRaw('
                COALESCE(SUM(profile_views_count), 0) as profile_views,
                COALESCE(SUM(contact_requests_count), 0) as contact_requests,
                COALESCE(SUM(unique_visitors_count), 0) as unique_visitors
            ')
            ->first();

        $prevViews = $previousStats->profile_views ?? 0;
        $prevContacts = $previousStats->contact_requests ?? 0;
        $prevVisitors = $previousStats->unique_visitors ?? 0;
        $prevConversionRate = $this->calculateConversionRate($prevViews, $prevContacts);

        $previousPeriodData = [
            'profile_views' => $prevViews,
            'contact_requests' => $prevContacts,
            'unique_visitors' => $prevVisitors,
            'conversion_rate' => $prevConversionRate,
        ];

        $growth = [
            'profile_views_percent' => $this->calculateGrowthRate($currentViews, $prevViews),
            'contact_requests_percent' => $this->calculateGrowthRate($currentContacts, $prevContacts),
            'unique_visitors_percent' => $this->calculateGrowthRate($currentVisitors, $prevVisitors),
            'conversion_rate_percent' => $this->calculateGrowthRate($currentConversionRate, $prevConversionRate),
        ];

        return [$previousPeriodData, $growth];
    }

    /**
     * Calculate previous period data and growth for admin.
     *
     * @return array{0: array, 1: array}
     */
    private function calculatePreviousPeriodAndGrowthForAdmin(
        string $range,
        int $currentViews,
        int $currentContacts,
        float $currentConversionRate,
        int $currentActiveSalespersons
    ): array {
        [$prevStartDate, $prevEndDate] = $this->calculatePreviousPeriod($range);

        // Query previous period data
        $previousStats = DailyAnalytics::where('date', '>=', $prevStartDate->toDateString())
            ->where('date', '<=', $prevEndDate->toDateString())
            ->selectRaw('
                COALESCE(SUM(profile_views_count), 0) as profile_views,
                COALESCE(SUM(contact_requests_count), 0) as contact_requests
            ')
            ->first();

        $prevViews = $previousStats->profile_views ?? 0;
        $prevContacts = $previousStats->contact_requests ?? 0;
        $prevConversionRate = $this->calculateConversionRate($prevViews, $prevContacts);

        // Previous period active salespersons
        $prevActiveSalespersons = DailyAnalytics::where('date', '>=', $prevStartDate->toDateString())
            ->where('date', '<=', $prevEndDate->toDateString())
            ->where('profile_views_count', '>', 0)
            ->distinct('salesperson_id')
            ->count('salesperson_id');

        $previousPeriodData = [
            'total_profile_views' => $prevViews,
            'total_contact_requests' => $prevContacts,
            'platform_conversion_rate' => $prevConversionRate,
            'active_salespersons' => $prevActiveSalespersons,
        ];

        $growth = [
            'profile_views_percent' => $this->calculateGrowthRate($currentViews, $prevViews),
            'contact_requests_percent' => $this->calculateGrowthRate($currentContacts, $prevContacts),
            'conversion_rate_percent' => $this->calculateGrowthRate($currentConversionRate, $prevConversionRate),
            'active_salespersons_percent' => $this->calculateGrowthRate($currentActiveSalespersons, $prevActiveSalespersons),
        ];

        return [$previousPeriodData, $growth];
    }
}
