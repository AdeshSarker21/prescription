<?php

namespace App\Console\Commands;

use App\Models\Medicine;
use App\Models\MedicineCategory;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportBangladeshMedicines extends Command
{
    protected $signature = 'medicines:import-bd {file? : Path to CSV file}';
    protected $description = 'Import all Bangladeshi medicines from CSV dataset';

    private array $categoryMap = [];

    private array $formToCategory = [
        'Tablet' => 'Tablet',
        'Tablet (Sustained Release)' => 'Tablet',
        'Tablet (Modified Release)' => 'Tablet',
        'Tablet (Extended Release)' => 'Tablet',
        'Tablet (Enteric Coated)' => 'Tablet',
        'Tablet (Delayed Release)' => 'Tablet',
        'Tablet (Controlled Release)' => 'Tablet',
        'Tablet (Prolonged Release)' => 'Tablet',
        'Tablet (Immediate Release)' => 'Tablet',
        'Dispersible Tablet' => 'Tablet',
        'Chewable Tablet' => 'Tablet',
        'Effervescent Tablet' => 'Tablet',
        'Sublingual Tablet' => 'Tablet',
        'Retard Tablet' => 'Tablet',
        'Long Acting Tablet' => 'Tablet',
        'MUPS Tablet' => 'Tablet',
        'Flash Tablet' => 'Tablet',
        'OROS Tablet' => 'Tablet',
        'Bolus Tablet' => 'Tablet',

        'Capsule' => 'Capsule',
        'Capsule (Sustained Release)' => 'Capsule',
        'Capsule (Modified Release)' => 'Capsule',
        'Capsule (Extended Release)' => 'Capsule',
        'Capsule (Delayed Release)' => 'Capsule',
        'Capsule (Controlled Release)' => 'Capsule',
        'Capsule (Timed Release)' => 'Capsule',
        'Sprinkle Capsule' => 'Capsule',
        'Inhalation Capsule' => 'Capsule',
        'Eye Capsule' => 'Capsule',

        'Syrup' => 'Syrup',

        'Injection' => 'Injection',
        'IM/IV Injection' => 'Injection',
        'IM/SC Injection' => 'Injection',
        'IM/IA Injection' => 'Injection',
        'IM Injection' => 'Injection',
        'IV Injection' => 'Injection',
        'IV Infusion' => 'Injection',
        'IV Injection or Infusion' => 'Injection',
        'IV/SC Injection' => 'Injection',
        'SC Injection' => 'Injection',
        'Long Acting Injection' => 'Injection',
        'Intravitreal Injection' => 'Injection',
        'Intraspinal Injection' => 'Injection',
        'Intracameral Injection' => 'Injection',
        'Intra-articular Injection' => 'Injection',
        'Intratracheal Suspension' => 'Injection',
        'Powder for Injection' => 'Injection',
        'Emulsion for infusion' => 'Injection',

        'Cream' => 'Cream',
        'Ointment' => 'Cream',
        'Gel' => 'Cream',
        'Topical Gel' => 'Cream',
        'Topical Cream' => 'Cream',
        'Vaginal Cream' => 'Cream',
        'Rectal Ointment' => 'Cream',
        'Scalp Ointment' => 'Cream',
        'Nasal Ointment' => 'Cream',
        'Ophthalmic Ointment' => 'Cream',
        'Dental Gel' => 'Cream',
        'Vaginal Gel' => 'Cream',
        'Oral Gel' => 'Cream',
        'Ophthalmic Gel' => 'Cream',
        'Muscle Rub' => 'Cream',
        'Medicated Bar' => 'Cream',
        'Hand Rub' => 'Cream',
        'Topical Spray' => 'Cream',
        'Topical Solution' => 'Cream',
        'Topical Powder' => 'Cream',
        'Topical Suspension' => 'Cream',

        'Drops' => 'Drops',
        'Pediatric Drops' => 'Drops',
        'Nasal Drop' => 'Drops',
        'Ear Drop' => 'Drops',
        'Viscous Eye Drop' => 'Drops',

        'Ophthalmic Solution' => 'Drops',
        'Ophthalmic Suspension' => 'Drops',
        'Ophthalmic Emulsion' => 'Drops',
        'Ocular Spray' => 'Drops',

        'Oral Suspension' => 'Syrup',
        'Oral Solution' => 'Syrup',
        'Oral Powder' => 'Powder',
        'Powder for Suspension' => 'Powder',
        'Powder for Solution' => 'Powder',
        'Effervescent Powder' => 'Powder',
        'Effervescent Granules' => 'Powder',
        'Oral Emulsion' => 'Syrup',
        'Oral Paste' => 'Cream',
        'Oral Soluble Film' => 'Tablet',

        'Inhaler' => 'Inhaler',
        'Solution for Inhalation' => 'Inhaler',
        'Nebuliser Solution' => 'Inhaler',
        'Nebuliser Suspension' => 'Inhaler',
        'Respirator Solution' => 'Inhaler',

        'Lotion' => 'Lotion',
        'Scalp Lotion' => 'Lotion',
        'Scalp Solution' => 'Lotion',

        'Suppository' => 'Suppository',
        'Vaginal Suppository' => 'Suppository',
        'Vaginal Pessary' => 'Suppository',
        'Vaginal Tablet' => 'Suppository',

        'Transdermal Patch' => 'Patch',
        'Nasal Spray' => 'Inhaler',
        'Mouthwash' => 'Mouthwash',
        'Shampoo' => 'Shampoo',
        'Liquid' => 'Syrup',
        'Solution' => 'Solution',
        'Dialysis Solution' => 'Solution',
        'Irrigation Solution' => 'Solution',
        'Viscoelastic Solution' => 'Solution',
        'Nail Lacquer' => 'Solution',
        'Rectal Saline' => 'Solution',
        'Surgical Scrub' => 'Solution',
        'Liquid Cleanser Soap' => 'Solution',
        'Microgranules' => 'Powder',
        'Chewing Gum Tablet' => 'Tablet',
    ];

    public function handle(): void
    {
        $file = $this->argument('file')
            ?? storage_path('app/bangladesh_medicines.csv');

        if (!file_exists($file)) {
            $this->error("File not found: $file");
            return;
        }

        $admin = User::where('email', 'admin@clinic.com')->first();
        if (!$admin) {
            $this->warn('Admin user not found. created_by will be null.');
        }

        $this->seedCategories();
        $this->info('Categories ready.');

        $handle = fopen($file, 'r');
        $header = fgetcsv($handle);

        $total = 0;
        $imported = 0;
        $skipped = 0;
        $errors = 0;

        $batch = [];
        $batchSize = 200;

        DB::disableQueryLog();

        while (($row = fgetcsv($handle)) !== false) {
            $total++;
            $data = array_combine($header, $row);

            $name = trim($data['brand name'] ?? '');
            $generic = trim($data['generic'] ?? '');
            $strength = trim($data['strength'] ?? '');
            $manufacturer = trim($data['manufacturer'] ?? '');
            $dosageForm = trim($data['dosage form'] ?? '');
            $type = trim($data['type'] ?? 'allopathic');

            if (empty($name)) {
                $skipped++;
                continue;
            }

            $categoryName = $this->formToCategory[$dosageForm] ?? 'Other';
            $category = $this->getCategory($categoryName);

            $medicineData = [
                'name' => $name,
                'generic_name' => $generic ?: null,
                'brand_name' => $name,
                'category_id' => $category?->id,
                'strength' => $strength ?: null,
                'salt_composition' => $generic ?: null,
                'active_ingredients' => $generic ?: null,
                'company_name' => $manufacturer ?: null,
                'country' => 'Bangladesh',
                'is_global' => true,
                'status' => 'active',
                'created_by' => $admin?->id,
            ];

            try {
                $existing = Medicine::where('name', $name)
                    ->where('company_name', $manufacturer)
                    ->where('strength', $strength)
                    ->first();

                if ($existing) {
                    $existing->update($medicineData);
                    $imported++;
                } else {
                    Medicine::create($medicineData);
                    $imported++;
                }
            } catch (\Exception $e) {
                $errors++;
                if ($errors <= 5) {
                    $this->warn("Error importing '$name': {$e->getMessage()}");
                }
            }

            if ($total % 500 === 0) {
                $this->info("Processed $total records...");
            }
        }

        fclose($handle);

        $this->info("Import complete!");
        $this->info("Total processed: $total");
        $this->info("Imported/Updated: $imported");
        $this->info("Skipped: $skipped");
        $this->info("Errors: $errors");
    }

    private function seedCategories(): void
    {
        $categories = [
            ['name' => 'Tablet', 'slug' => 'tablet', 'description' => 'Solid oral dosage form'],
            ['name' => 'Capsule', 'slug' => 'capsule', 'description' => 'Gelatin shell medication'],
            ['name' => 'Syrup', 'slug' => 'syrup', 'description' => 'Liquid oral medication'],
            ['name' => 'Injection', 'slug' => 'injection', 'description' => 'Parenteral medication'],
            ['name' => 'Cream', 'slug' => 'cream', 'description' => 'Topical semi-solid medication'],
            ['name' => 'Drops', 'slug' => 'drops', 'description' => 'Liquid drops medication'],
            ['name' => 'Inhaler', 'slug' => 'inhaler', 'description' => 'Inhaled medication'],
            ['name' => 'Powder', 'slug' => 'powder', 'description' => 'Powder form medication'],
            ['name' => 'Lotion', 'slug' => 'lotion', 'description' => 'Topical liquid medication'],
            ['name' => 'Suppository', 'slug' => 'suppository', 'description' => 'Rectal or vaginal medication'],
            ['name' => 'Patch', 'slug' => 'patch', 'description' => 'Transdermal patch'],
            ['name' => 'Mouthwash', 'slug' => 'mouthwash', 'description' => 'Oral rinse medication'],
            ['name' => 'Shampoo', 'slug' => 'shampoo', 'description' => 'Medicated shampoo'],
            ['name' => 'Solution', 'slug' => 'solution', 'description' => 'Solution form medication'],
            ['name' => 'Other', 'slug' => 'other', 'description' => 'Other dosage forms'],
        ];

        foreach ($categories as $cat) {
            MedicineCategory::firstOrCreate(
                ['slug' => $cat['slug']],
                $cat
            );
        }
    }

    private function getCategory(string $name): ?MedicineCategory
    {
        if (!isset($this->categoryMap[$name])) {
            $this->categoryMap[$name] = MedicineCategory::where('name', $name)->first();
        }
        return $this->categoryMap[$name];
    }
}
