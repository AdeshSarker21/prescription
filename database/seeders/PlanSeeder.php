<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Basic',
                'slug' => 'basic',
                'description' => 'Perfect for individual doctors starting their digital practice.',
                'monthly_price' => 0,
                'quarterly_price' => 0,
                'semi_annual_price' => 0,
                'yearly_price' => 0,
                'lifetime_price' => 0,
                'max_patients' => 500,
                'features' => [
                    'Digital prescription creation',
                    'Manual prescription system',
                    'PDF prescription export & print',
                    'Basic patient records',
                    'Up to 500 patients',
                ],
                'limitations' => [
                    'max_patients' => 500,
                    'ai_assistant' => false,
                    'analytics' => false,
                    'multi_doctor' => false,
                ],
                'sort_order' => 1,
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'description' => 'For growing clinics needing AI assistance and analytics.',
                'monthly_price' => 29.99,
                'quarterly_price' => 79.99,
                'semi_annual_price' => 149.99,
                'yearly_price' => 299.99,
                'lifetime_price' => 999.99,
                'max_patients' => 5000,
                'features' => [
                    'Everything in Basic',
                    'Up to 5000 patients',
                    'AI-assisted medicine suggestions (basic)',
                    'Prescription history & analytics',
                    'Advanced patient search & filters',
                    'Symptom-based suggestions',
                ],
                'limitations' => [
                    'max_patients' => 5000,
                    'ai_assistant' => 'basic',
                    'analytics' => true,
                    'multi_doctor' => false,
                ],
                'sort_order' => 2,
            ],
            [
                'name' => 'Premium',
                'slug' => 'premium',
                'description' => 'For hospitals and multi-doctor clinics with full capabilities.',
                'monthly_price' => 79.99,
                'quarterly_price' => 219.99,
                'semi_annual_price' => 419.99,
                'yearly_price' => 799.99,
                'lifetime_price' => 2999.99,
                'max_patients' => null,
                'features' => [
                    'Everything in Pro',
                    'Unlimited patients',
                    'Advanced AI prescription assistant',
                    'Drug interaction warnings',
                    'Smart diagnosis assistance',
                    'Full analytics & hospital reporting',
                    'Multi-doctor clinic support',
                    'Prescription sharing via link/SMS',
                ],
                'limitations' => [
                    'max_patients' => null,
                    'ai_assistant' => 'advanced',
                    'analytics' => true,
                    'multi_doctor' => true,
                ],
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $data) {
            Plan::firstOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }

        $this->command->info('Seeded ' . count($plans) . ' subscription plans.');
    }
}
