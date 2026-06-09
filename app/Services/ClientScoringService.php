<?php

namespace App\Services;

use App\Models\Client;
use App\Models\CreditSetting;
use Carbon\Carbon;

class ClientScoringService
{
    public function calculate(Client $client): array
    {
        $sales = $client->sales()
            ->where('status', '!=', 'annulee')
            ->get();

        $salesCount = $sales->count();
        $totalAmount = (float) $sales->sum('total_amount');

        $firstSale = $sales->sortBy('created_at')->first();
        $lastSale = $sales->sortByDesc('created_at')->first();

        $monthsActive = 0;

        if ($firstSale) {
            $monthsActive = max(
                1,
                Carbon::parse($firstSale->created_at)->diffInMonths(now()) + 1
            );
        }

        $monthlyAverage = $monthsActive > 0 ? $totalAmount / $monthsActive : 0;
        $monthlyFrequency = $monthsActive > 0 ? $salesCount / $monthsActive : 0;

        $creditUsed = (float) $client->creditSales()
            ->whereIn('status', ['en_attente', 'partiel'])
            ->sum('amount_due');

        $score = 0;

        if ($monthlyFrequency >= 6) {
            $score += 30;
        } elseif ($monthlyFrequency >= 3) {
            $score += 22;
        } elseif ($monthlyFrequency >= 1) {
            $score += 14;
        } elseif ($salesCount > 0) {
            $score += 6;
        }

        if ($monthlyAverage >= 2000000) {
            $score += 30;
        } elseif ($monthlyAverage >= 800000) {
            $score += 22;
        } elseif ($monthlyAverage >= 300000) {
            $score += 14;
        } elseif ($monthlyAverage > 0) {
            $score += 6;
        }

        if ($lastSale) {
            $daysSinceLastSale = Carbon::parse($lastSale->created_at)->diffInDays(now());

            if ($daysSinceLastSale <= 30) {
                $score += 20;
            } elseif ($daysSinceLastSale <= 90) {
                $score += 12;
            } elseif ($daysSinceLastSale <= 180) {
                $score += 5;
            }
        }

        if ($creditUsed <= 0) {
            $score += 20;
        } elseif ($monthlyAverage > 0 && $creditUsed <= ($monthlyAverage * 0.5)) {
            $score += 12;
        } elseif ($monthlyAverage > 0 && $creditUsed <= $monthlyAverage) {
            $score += 5;
        }

        $score = min($score, 100);

        $status = match (true) {
            $score >= 90 => 'premium',
            $score >= 70 => 'fidele',
            $score >= 40 => 'regulier',
            default => 'occasionnel',
        };

        $risk = match (true) {
            $score >= 70 && $creditUsed <= $monthlyAverage => 'faible',
            $score >= 40 => 'moyen',
            default => 'eleve',
        };

        $settings = CreditSetting::current();

        $coefficient = match ($status) {
            'premium' => (float) $settings->premium_coefficient,
            'fidele' => (float) $settings->loyal_coefficient,
            'regulier' => (float) $settings->regular_coefficient,
            default => 0,
        };

        $statusAllowed = match ($status) {
            'premium' => $settings->allow_premium,
            'fidele' => $settings->allow_loyal,
            'regulier' => $settings->allow_regular,
            default => false,
        };

        $riskAllowed = $risk !== 'eleve' || $settings->allow_high_risk;

        $isEligibleForCredit =
            $salesCount >= $settings->min_sales
            && $monthsActive >= $settings->min_months
            && $score >= $settings->min_score
            && $statusAllowed
            && $riskAllowed
            && !$client->credit_blocked;

        $recommendedCredit = $isEligibleForCredit
            ? $monthlyAverage * $coefficient
            : 0;

        if ((float) $settings->max_credit_limit > 0) {
            $recommendedCredit = min($recommendedCredit, (float) $settings->max_credit_limit);
        }

        $available = max($recommendedCredit - $creditUsed, 0);

        return [
            'loyalty_score' => round($score),
            'loyalty_status' => $status,
            'risk_level' => $risk,
            'recommended_credit_limit' => round($recommendedCredit),
            'credit_used' => round($creditUsed),
            'credit_available' => round($available),
            'last_score_calculated_at' => now(),
        ];
    }

    public function update(Client $client): Client
    {
        $client->update($this->calculate($client));

        return $client->fresh();
    }
}
