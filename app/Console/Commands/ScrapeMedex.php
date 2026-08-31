<?php

namespace App\Console\Commands;

use App\Models\Medicine;
use App\Models\MedicineCategory;
use App\Models\User;
use Illuminate\Console\Command;

class ScrapeMedex extends Command
{
    protected $signature = 'medicines:scrape-medex {--resume : Resume from last scraped page} {--detail-only : Only scrape detail pages for brands already in DB}';
    protected $description = 'Scrape all medicines from medex.com.bd (listing + detail pages)';

    private array $categoryMap = [];
    private ?User $admin = null;
    private int $totalFound = 0;
    private int $newImported = 0;
    private int $skipped = 0;
    private int $detailScraped = 0;
    private int $detailFailed = 0;
    private string $logFile;
    private string $stateFile;

    private array $dosageFormMap = [
        'Tablet' => 'tablet',
        'Tablet (Sustained Release)' => 'tablet',
        'Tablet (Extended Release)' => 'tablet',
        'Capsule' => 'capsule',
        'Syrup' => 'syrup',
        'Powder for Suspension' => 'syrup',
        'Oral Suspension' => 'syrup',
        'Oral Solution' => 'syrup',
        'Suspension' => 'syrup',
        'IM Injection' => 'injection',
        'IV Injection or Infusion' => 'injection',
        'IM/IV Injection' => 'injection',
        'Injection' => 'injection',
        'Cream' => 'cream',
        'Ointment' => 'cream',
        'Gel' => 'cream',
        'Oral Gel' => 'cream',
        'Pediatric Drops' => 'drops',
        'Drop' => 'drops',
        'Eye Drop' => 'drops',
        'Ophthalmic Solution' => 'drops',
        'Ear Drop' => 'drops',
        'Nasal Drop' => 'drops',
        'Inhaler' => 'inhaler',
        'Powder' => 'powder',
        'Lotion' => 'lotion',
        'Suppository' => 'suppository',
        'Patch' => 'patch',
        'Mouthwash' => 'mouthwash',
        'Shampoo' => 'shampoo',
        'Solution' => 'solution',
        'Spray' => 'inhaler',
        'Pessary' => 'suppository',
        'Cream (Topical)' => 'cream',
        'Ophthalmic Ointment' => 'cream',
        'Oral Gel' => 'cream',
    ];

    private array $categorySlugMap = [
        'tablet' => 'Tablet',
        'capsule' => 'Capsule',
        'syrup' => 'Syrup',
        'injection' => 'Injection',
        'cream' => 'Cream',
        'drops' => 'Drops',
        'inhaler' => 'Inhaler',
        'powder' => 'Powder',
        'lotion' => 'Lotion',
        'suppository' => 'Suppository',
        'patch' => 'Patch',
        'mouthwash' => 'Mouthwash',
        'shampoo' => 'Shampoo',
        'solution' => 'Solution',
        'other' => 'Other',
    ];

    public function handle(): int
    {
        $this->admin = User::where('email', 'admin@clinic.com')->first();
        $this->logFile = storage_path('logs/medex_scrape.log');
        $this->stateFile = storage_path('app/medex_scrape_state.json');
        $this->seedCategories();

        $this->log("=== MedEx Scraper Started at " . now()->toDateTimeString() . " ===");

        if ($this->option('detail-only')) {
            $this->scrapeDetailPagesForExisting();
            return 0;
        }

        $startPage = 1;
        if ($this->option('resume') && file_exists($this->stateFile)) {
            $state = json_decode(file_get_contents($this->stateFile), true);
            $startPage = ($state['last_page'] ?? 0) + 1;
            $this->newImported = $state['new_imported'] ?? 0;
            $this->skipped = $state['skipped'] ?? 0;
            $this->totalFound = $state['total_found'] ?? 0;
            $this->info("Resuming from page $startPage (prev: imported={$this->newImported}, skipped={$this->skipped})");
        }

        $this->phase1ScrapeListings($startPage);
        $this->phase2ScrapeDetails();

        $this->log("=== Done. Total found: {$this->totalFound}, Imported: {$this->newImported}, Skipped: {$this->skipped}, Details scraped: {$this->detailScraped}, Details failed: {$this->detailFailed} ===");
        $this->info("Done! Check log: {$this->logFile}");

        return 0;
    }

    private function phase1ScrapeListings(int $startPage = 1): void
    {
        $this->info("--- Phase 1: Scraping brand listings ---");
        $this->log("Phase 1 started from page $startPage");

        $lastPage = 845;
        $bar = $this->output->createProgressBar($lastPage);
        $bar->setProgress($startPage - 1);
        $bar->start();

        for ($page = $startPage; $page <= $lastPage; $page++) {
            $this->scrapeListingPage($page);
            $this->saveState($page);
            $bar->advance();

            if ($page % 50 === 0) {
                $bar->setMessage(" Found: {$this->totalFound} | Imported: {$this->newImported} | Skipped: {$this->skipped}");
            }

            usleep(500000); // 0.5s delay between listing pages
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Phase 1 complete. Found: {$this->totalFound}, Imported: {$this->newImported}, Skipped: {$this->skipped}");
    }

    private function scrapeListingPage(int $page): void
    {
        $url = "https://medex.com.bd/brands?page={$page}";
        $html = $this->fetchUrl($url);

        if ($html === null) {
            $this->log("FAILED listing page $page: $url");
            return;
        }

        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML($html, LIBXML_NOWARNING | LIBXML_NOERROR);
        $xpath = new \DOMXPath($dom);

        $brandCards = $xpath->query("//a[contains(@class, 'brand-card')]");

        if ($brandCards->length === 0) {
            $this->log("No brand cards on page $page");
            return;
        }

        foreach ($brandCards as $card) {
            $this->totalFound++;

            $name = $this->extractText($xpath, ".//span[contains(@class, 'brand-card__name')]", $card);
            $strength = $this->extractText($xpath, ".//div[contains(@class, 'brand-card__strength')]", $card);
            $generic = $this->extractText($xpath, ".//div[contains(@class, 'brand-card__generic')]", $card);
            $company = $this->extractText($xpath, ".//div[contains(@class, 'brand-card__company')]", $card);

            $dosageForm = '';
            $dosageIcons = $xpath->query(".//img[contains(@class, 'dosage-icon')]", $card);
            if ($dosageIcons->length > 0) {
                $dosageForm = $dosageIcons->item(0)->getAttribute('alt') ?? '';
            }

            $href = $card->getAttribute('href') ?? '';

            if (empty($name)) continue;

            $existing = Medicine::where('name', $name)
                ->where('company_name', $company ?: null)
                ->where('strength', $strength ?: null)
                ->first();

            if ($existing) {
                $this->skipped++;
                continue;
            }

            $categorySlug = $this->dosageFormMap[$dosageForm] ?? 'other';
            $categoryName = $this->categorySlugMap[$categorySlug] ?? 'Other';
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
                    'company_name' => $company ?: null,
                    'country' => 'Bangladesh',
                    'is_global' => true,
                    'status' => 'active',
                    'created_by' => $this->admin?->id,
                    'medex_url' => $href ?: null,
                ]);
                $this->newImported++;
            } catch (\Exception $e) {
                $this->log("Error importing '$name': {$e->getMessage()}");
            }
        }
    }

    private function phase2ScrapeDetails(): void
    {
        $this->info("--- Phase 2: Scraping detail pages for clinical data ---");
        $this->log("Phase 2 started");

        $medicines = Medicine::whereNull('indication')
            ->where('country', 'Bangladesh')
            ->where('is_global', true)
            ->whereNotNull('medex_url')
            ->get();

        $total = $medicines->count();
        if ($total === 0) {
            $this->info("No medicines need detail scraping.");
            return;
        }

        $this->info("Found {$total} medicines to enrich with detail data.");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($medicines as $medicine) {
            $this->scrapeDetailPage($medicine);
            $bar->advance();

            if ($this->detailScraped % 50 === 0 && $this->detailScraped > 0) {
                $bar->setMessage(" Scraped: {$this->detailScraped} | Failed: {$this->detailFailed}");
            }

            usleep(1500000); // 1.5s delay between detail requests
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Phase 2 complete. Details scraped: {$this->detailScraped}, Failed: {$this->detailFailed}");
    }

    private function scrapeDetailPage(Medicine $medicine): void
    {
        $url = $medicine->medex_url;
        if (!str_starts_with($url, 'http')) {
            $url = 'https://medex.com.bd' . $url;
        }

        $html = $this->fetchUrl($url);
        if ($html === null) {
            $this->detailFailed++;
            $this->log("FAILED detail: {$medicine->name} - $url");
            return;
        }

        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML($html, LIBXML_NOWARNING | LIBXML_NOERROR);
        $xpath = new \DOMXPath($dom);

        $data = [];

        $sectionMap = [
            'indications' => 'indication',
            'composition' => 'composition',
            'mode_of_action' => 'pharmacology',
            'dosage' => 'adult_dose',
            'interaction' => 'drug_interaction_notes',
            'contraindications' => 'contraindications',
            'side_effects' => 'side_effects',
            'pregnancy_cat' => 'pregnancy_cat_text',
            'precautions' => 'allergy_warning',
            'overdose_effects' => 'overdose_effects',
            'drug_classes' => 'therapeutic_class',
            'storage_conditions' => 'storage_conditions',
        ];

        foreach ($sectionMap as $sectionId => $field) {
            $content = $this->extractSection($xpath, $sectionId);
            if (!empty($content)) {
                $data[$field] = $content;
            }
        }

        if (!empty($data)) {
            // Parse dosage for child_dose
            if (isset($data['adult_dose'])) {
                $dosageText = $data['adult_dose'];
                if (preg_match('/child|pediatric|infant/i', $dosageText)) {
                    $data['child_dose'] = $dosageText;
                }
            }

            // Map pregnancy text to pregnancy_safe boolean
            if (isset($data['pregnancy_cat_text'])) {
                $data['pregnancy_safe'] = stripos($data['pregnancy_cat_text'], 'contraindicated') !== false ? false : true;
                unset($data['pregnancy_cat_text']);
            }

            $updateData = array_filter($data, fn($v) => !empty($v));
            if (!empty($updateData)) {
                $medicine->update($updateData);
                $this->detailScraped++;
            }
        }
    }

    private function extractSection(\DOMXPath $xpath, string $sectionId): string
    {
        $nodes = $xpath->query("//*[@id='{$sectionId}']");
        if ($nodes->length === 0) return '';

        $headerNode = $nodes->item(0);
        $parent = $headerNode->parentNode;
        if (!$parent) return '';

        $nextSibling = $headerNode->nextSibling;
        $content = '';

        while ($nextSibling) {
            if ($nextSibling->nodeName === 'div' && strpos($nextSibling->getAttribute('class') ?? '', 'ac-body') !== false) {
                $content = trim($nextSibling->textContent);
                break;
            }
            if ($nextSibling->nodeName === 'h3') break;
            $nextSibling = $nextSibling->nextSibling;
        }

        if (empty($content)) {
            $bodyNodes = $xpath->query("//*[@id='{$sectionId}']/following-sibling::div[contains(@class, 'ac-body')][1]");
            if ($bodyNodes->length > 0) {
                $content = trim($bodyNodes->item(0)->textContent);
            }
        }

        return $this->cleanHtml($content);
    }

    private function extractText(\DOMXPath $xpath, string $query, \DOMNode $context): string
    {
        $nodes = $xpath->query($query, $context);
        if ($nodes->length > 0) {
            return trim($nodes->item(0)->textContent);
        }
        return '';
    }

    private function cleanHtml(string $text): string
    {
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    private function fetchUrl(string $url): ?string
    {
        $context = stream_context_create([
            'http' => [
                'timeout' => 20,
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'header' => "Accept: text/html,application/xhtml+xml\r\nAccept-Language: en-US,en;q=0.9\r\n",
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);

        $retries = 3;
        for ($i = 0; $i < $retries; $i++) {
            $html = @file_get_contents($url, false, $context);
            if ($html !== false) return $html;
            sleep(2 * ($i + 1));
        }
        return null;
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

    private function saveState(int $page): void
    {
        $state = [
            'last_page' => $page,
            'total_found' => $this->totalFound,
            'new_imported' => $this->newImported,
            'skipped' => $this->skipped,
            'updated_at' => now()->toDateTimeString(),
        ];
        file_put_contents($this->stateFile, json_encode($state));
    }

    private function log(string $message): void
    {
        $line = "[" . now()->toDateTimeString() . "] $message" . PHP_EOL;
        file_put_contents($this->logFile, $line, FILE_APPEND | LOCK_EX);
    }

    private function scrapeDetailPagesForExisting(): void
    {
        $this->info("--- Enriching existing medicines with detail data ---");
        $this->phase2ScrapeDetails();
    }
}
