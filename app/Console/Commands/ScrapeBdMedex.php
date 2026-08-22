<?php

namespace App\Console\Commands;

use App\Models\Medicine;
use App\Models\MedicineCategory;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ScrapeBdMedex extends Command
{
    protected $signature = 'medicines:scrape-bdmedex';
    protected $description = 'Scrape Bangladeshi medicines from bdmedex.com (35k+ brands)';

    private array $categoryMap = [];
    private ?User $admin = null;
    private int $totalFound = 0;
    private int $newImported = 0;
    private int $skipped = 0;

    private array $formMapping = [
        'Tablet' => 'Tablet',
        'Capsule' => 'Capsule',
        'Syrup' => 'Syrup',
        'Injection' => 'Injection',
        'Cream' => 'Cream',
        'Ointment' => 'Cream',
        'Gel' => 'Cream',
        'Drop' => 'Drops',
        'Suspension' => 'Syrup',
        'Powder' => 'Powder',
        'Inhaler' => 'Inhaler',
        'Suppository' => 'Suppository',
        'Lotion' => 'Lotion',
        'Solution' => 'Solution',
        'Spray' => 'Inhaler',
        'Patch' => 'Patch',
        'Mouthwash' => 'Mouthwash',
        'Shampoo' => 'Shampoo',
    ];

    public function handle(): void
    {
        $this->admin = User::where('email', 'admin@clinic.com')->first();
        $this->seedCategories();

        $letters = array_merge(['9'], range('a', 'z'));

        foreach ($letters as $letter) {
            $this->info("Scraping letter: $letter");
            $this->scrapeLetter($letter);
        }

        $this->info("---- Done ----");
        $this->info("Total entries found: $this->totalFound");
        $this->info("Newly imported: $this->newImported");
        $this->info("Already existed: $this->skipped");
    }

    private function scrapeLetter(string $letter, int $page = 1): void
    {
        $url = "https://bdmedex.com/brand/list-$letter/";
        if ($page > 1) {
            $url = "https://bdmedex.com/brand/list-$letter/page-$page/";
        }

        $html = @file_get_contents($url, false, stream_context_create([
            'http' => ['timeout' => 15, 'user_agent' => 'Mozilla/5.0'],
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
        ]));

        if ($html === false) {
            $this->warn("Failed to fetch: $url");
            return;
        }

        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML($html, LIBXML_NOWARNING | LIBXML_NOERROR);
        $xpath = new \DOMXPath($dom);

        $brandLinks = $xpath->query("//a[contains(@class, 'link-brand')]");

        if ($brandLinks->length === 0) {
            return;
        }

        foreach ($brandLinks as $brandLink) {
            $this->totalFound++;

            $brandName = trim($brandLink->textContent);
            $href = $brandLink->getAttribute('href');
            $this->extractBrandData($brandName, $href, $brandLink, $xpath, $dom);
        }

        $this->info("  Page $page: {$brandLinks->length} entries (letter $letter)");

        $navDiv = $xpath->query("//div[@id='medexNav']");
        if ($navDiv->length > 0) {
            $links = $navDiv->item(0)->getElementsByTagName('a');
            $maxPage = 0;
            foreach ($links as $link) {
                if (preg_match('/page-(\d+)/', $link->getAttribute('href'), $pm)) {
                    $maxPage = max($maxPage, (int)$pm[1]);
                }
            }
            if ($page < $maxPage) {
                $this->scrapeLetter($letter, $page + 1);
            }
        }
    }

    private function extractBrandData(string $brandName, string $href, \DOMElement $brandLink, \DOMXPath $xpath, \DOMDocument $dom): void
    {
        // Parse brand name and dosage form/strength
        preg_match('/^(.+?)\s*\((.+?)\)\s*(.+)?$/', $brandName, $m);
        $name = trim($m[1] ?? $brandName);
        $dosageForm = trim($m[2] ?? '');
        $strength = trim($m[3] ?? '');

        // Find parent container to get generic and manufacturer
        $parent = $brandLink->parentNode;
        while ($parent && $parent->nodeName !== 'div' && $parent->nodeName !== 'li') {
            $parent = $parent->parentNode;
        }

        if (!$parent) return;

        $generic = '';
        $manufacturer = '';

        $genericLinks = $xpath->query(".//a[contains(@class, 'medex-gen')]", $parent);
        if ($genericLinks->length > 0) {
            $generic = trim($genericLinks->item(0)->textContent);
        }

        $mfrLinks = $xpath->query(".//a[contains(@class, 'medex-mfr')]", $parent);
        if ($mfrLinks->length > 0) {
            $manufacturer = trim($mfrLinks->item(0)->textContent);
        }

        if (empty($name)) return;

        // Check if already in DB
        $existing = Medicine::where('name', $name)
            ->where('company_name', $manufacturer ?: null)
            ->where('strength', $strength ?: null)
            ->first();

        if ($existing) {
            $this->skipped++;
            return;
        }

        // Map category
        $categoryName = $this->formMapping[$dosageForm] ?? 'Other';
        $category = $this->getCategory($categoryName);

        try {
            Medicine::create([
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
                'created_by' => $this->admin?->id,
            ]);
            $this->newImported++;
        } catch (\Exception $e) {
            $this->warn("Error importing '$name': {$e->getMessage()}");
        }
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
            MedicineCategory::firstOrCreate(['slug' => $cat['slug']], $cat);
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
