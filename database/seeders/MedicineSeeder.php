<?php

namespace Database\Seeders;

use App\Models\Medicine;
use App\Models\MedicineCategory;
use Illuminate\Database\Seeder;

class MedicineSeeder extends Seeder
{
    public function run(): void
    {
        $cats = [
            ['name' => 'Tablet', 'slug' => 'tablet', 'description' => 'Solid oral dosage form'],
            ['name' => 'Capsule', 'slug' => 'capsule', 'description' => 'Gelatin shell medication'],
            ['name' => 'Syrup', 'slug' => 'syrup', 'description' => 'Liquid oral medication'],
            ['name' => 'Injection', 'slug' => 'injection', 'description' => 'Parenteral medication'],
            ['name' => 'Cream', 'slug' => 'cream', 'description' => 'Topical medication'],
            ['name' => 'Drops', 'slug' => 'drops', 'description' => 'Liquid drops medication'],
        ];

        foreach ($cats as $c) {
            MedicineCategory::firstOrCreate(['slug' => $c['slug']], $c);
        }

        $medicines = [
            [
                'name' => 'Napa Extra',
                'generic_name' => 'Paracetamol + Caffeine',
                'brand_name' => 'Napa Extra',
                'strength' => '500mg + 65mg',
                'category_slug' => 'tablet',
                'company_name' => 'Beximco Pharmaceuticals',
                'country' => 'Bangladesh',
                'adult_dose' => '1-2 tablets every 4-6 hours',
                'max_daily_dose' => '8 tablets in 24 hours',
                'side_effects' => 'Nausea, vomiting, allergic reactions',
                'pregnancy_safe' => true,
            ],
            [
                'name' => 'Alera 10mg',
                'generic_name' => 'Cetirizine Dihydrochloride',
                'brand_name' => 'Alera',
                'strength' => '10mg',
                'category_slug' => 'tablet',
                'company_name' => 'Incepta Pharmaceuticals',
                'country' => 'Bangladesh',
                'adult_dose' => '1 tablet daily',
                'child_dose' => '5mg for children 2-6 years',
                'side_effects' => 'Drowsiness, dry mouth',
                'pregnancy_safe' => true,
            ],
            [
                'name' => 'Seclo 20mg',
                'generic_name' => 'Omeprazole',
                'brand_name' => 'Seclo',
                'strength' => '20mg',
                'category_slug' => 'capsule',
                'company_name' => 'Square Pharmaceuticals',
                'country' => 'Bangladesh',
                'adult_dose' => '20mg once daily before meal',
                'max_daily_dose' => '40mg',
                'food_interaction' => 'before_food',
                'side_effects' => 'Headache, nausea, abdominal pain',
                'pregnancy_safe' => true,
            ],
            [
                'name' => 'Fexo 120mg',
                'generic_name' => 'Fexofenadine Hydrochloride',
                'brand_name' => 'Fexo',
                'strength' => '120mg',
                'category_slug' => 'tablet',
                'company_name' => 'Healthcare Pharmaceuticals',
                'country' => 'Bangladesh',
                'adult_dose' => '120mg once daily',
                'side_effects' => 'Headache, dizziness',
                'pregnancy_safe' => true,
            ],
            [
                'name' => 'Zimax 250mg',
                'generic_name' => 'Azithromycin',
                'brand_name' => 'Zimax',
                'strength' => '250mg',
                'category_slug' => 'capsule',
                'company_name' => 'Opsonin Pharma',
                'country' => 'Bangladesh',
                'adult_dose' => '500mg once daily for 3 days',
                'side_effects' => 'Nausea, diarrhea, abdominal pain',
                'pregnancy_safe' => false,
                'alcohol_warning' => true,
            ],
        ];

        $admin = \App\Models\User::where('email', 'admin@clinic.com')->first();

        foreach ($medicines as $data) {
            $cat = MedicineCategory::where('slug', $data['category_slug'])->first();
            $data['category_id'] = $cat?->id;
            unset($data['category_slug']);
            $data['is_global'] = true;
            $data['status'] = 'active';
            $data['created_by'] = $admin?->id;

            Medicine::firstOrCreate(
                ['name' => $data['name']],
                $data
            );
        }

        $this->command->info('Seeded ' . count($medicines) . ' medicines and categories.');
    }
}
