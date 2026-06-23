<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Platform\Plan;

class PlatformPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'Idéal pour les petites boutiques individuelles.',
                'price_monthly' => 15000.00,
                'price_yearly' => 150000.00,
                'currency' => 'FCFA',
                'max_users' => 3,
                'max_products' => 100,
                'max_clients' => 50,
                'max_storage_mb' => 500,
                'max_branches' => 1,
                'features' => [
                    'basic_reports',
                    'email_support',
                    'single_branch',
                ],
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'description' => 'Pour les commerces en croissance avec plusieurs vendeurs.',
                'price_monthly' => 30000.00,
                'price_yearly' => 300000.00,
                'currency' => 'FCFA',
                'max_users' => 10,
                'max_products' => 1000,
                'max_clients' => 500,
                'max_storage_mb' => 2000,
                'max_branches' => 3,
                'features' => [
                    'advanced_reports',
                    'chat_support',
                    'multiple_branches',
                    'crm_features',
                ],
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Business',
                'slug' => 'business',
                'description' => 'Parfait pour les réseaux de boutiques et la gestion avancée.',
                'price_monthly' => 60000.00,
                'price_yearly' => 600000.00,
                'currency' => 'FCFA',
                'max_users' => 30,
                'max_products' => 10000,
                'max_clients' => 5000,
                'max_storage_mb' => 10000,
                'max_branches' => 10,
                'features' => [
                    'advanced_reports',
                    'priority_support',
                    'multiple_branches',
                    'crm_features',
                    'api_access',
                    'custom_domain',
                ],
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'Solution sur-mesure pour les grandes entreprises.',
                'price_monthly' => 150000.00,
                'price_yearly' => 1500000.00,
                'currency' => 'FCFA',
                'max_users' => null,
                'max_products' => null,
                'max_clients' => null,
                'max_storage_mb' => null,
                'max_branches' => null,
                'features' => [
                    'all_features',
                    'dedicated_support',
                    'unlimited_everything',
                    'custom_integrations',
                    'sla_guarantee',
                ],
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($plans as $planData) {
            Plan::updateOrCreate(
                ['slug' => $planData['slug']],
                $planData
            );
        }
    }
}
