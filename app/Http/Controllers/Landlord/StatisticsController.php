<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Services\Platform\PlatformStatsService;

class StatisticsController extends Controller
{
    protected PlatformStatsService $statsService;

    public function __construct(PlatformStatsService $statsService)
    {
        $this->statsService = $statsService;
    }

    /**
     * Display detailed platform statistics.
     */
    public function index()
    {
        $tenantStats = $this->statsService->tenantStats();
        $subscriptionStats = $this->statsService->subscriptionStats();
        $paymentStats = $this->statsService->paymentStats();
        $supportStats = $this->statsService->supportStats();
        $backupStats = $this->statsService->backupStats();
        $growthStats = $this->statsService->growthStats();
        $businessTypeStats = $this->statsService->businessTypeStats();
        $planDistribution = $this->statsService->planDistribution();
        $healthSummary = $this->statsService->healthSummary();

        return view('landlord.statistics.index', compact(
            'tenantStats',
            'subscriptionStats',
            'paymentStats',
            'supportStats',
            'backupStats',
            'growthStats',
            'businessTypeStats',
            'planDistribution',
            'healthSummary'
        ));
    }
}
