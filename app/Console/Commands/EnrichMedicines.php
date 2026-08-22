<?php

namespace App\Console\Commands;

use App\Models\Medicine;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class EnrichMedicines extends Command
{
    protected $signature = 'medicines:enrich {--force : Re-enrich even if fields already filled}';
    protected $description = 'Enrich all medicines with dosage, warnings & safety, and smart feature data';

    public function handle(): void
    {
        $loader = new MedicineDataLoader();
        $loader->init();

        $total = Medicine::count();
        $this->info("Total medicines: {$total}");

        $generics = Medicine::select('generic_name', DB::raw('COUNT(*) as cnt'))
            ->whereNotNull('generic_name')->where('generic_name', '!=', '')
            ->groupBy('generic_name')->orderByDesc('cnt')->get();

        $bar = $this->output->createProgressBar($generics->count());
        $bar->start();
        $updated = 0;
        $force = $this->option('force');

        foreach ($generics as $g) {
            $data = $loader->resolve(strtolower(trim($g->generic_name)));
            if (!$data) { $bar->advance(); continue; }

            $q = Medicine::where('generic_name', $g->generic_name);
            if (!$force) $q->where(function($q2) { $q2->whereNull('adult_dose')->orWhere('adult_dose', ''); });
            $c = $q->update($data);
            if ($c > 0) $updated += $c;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Done! Updated {$updated} medicines.");
    }
}
