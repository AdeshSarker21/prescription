<?php

namespace App\Console\Commands;

use App\Models\Medicine;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupDuplicateMedicines extends Command
{
    protected $signature = 'medicines:cleanup-duplicates {--dry-run : Show duplicates without deleting}';
    protected $description = 'Find and merge duplicate medicines based on name + strength + generic_name, reassigning all references';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('DRY RUN — no changes will be made.');
            $this->newLine();
        }

        $duplicates = $this->findDuplicateGroups();

        if ($duplicates->isEmpty()) {
            $this->info('No duplicate medicines found.');
            return self::SUCCESS;
        }

        $totalGroups = $duplicates->count();
        $totalDuplicates = 0;

        $this->info("Found {$totalGroups} duplicate group(s):");
        $this->newLine();

        $bar = $this->output->createProgressBar($totalGroups);
        $bar->start();

        foreach ($duplicates as $group) {
            $keeper = $group->first();
            $dupes = $group->slice(1);
            $dupeIds = $dupes->pluck('id')->toArray();
            $totalDuplicates += count($dupeIds);

            $this->newLine();
            $this->line("  Keeper: [{$keeper->id}] {$keeper->name} | strength={$keeper->strength} | generic={$keeper->generic_name}");
            foreach ($dupes as $dupe) {
                $this->comment("  Remove: [{$dupe->id}] {$dupe->name} | strength={$dupe->strength} | generic={$dupe->generic_name}");
            }

            if (!$dryRun) {
                $this->reassignReferences($dupeIds, $keeper->id);
                Medicine::whereIn('id', $dupeIds)->delete();
                $this->line("    → Reassigned references and deleted " . count($dupeIds) . " duplicate(s).", 'green');
            } else {
                $this->line("    → Would reassign references and delete " . count($dupeIds) . " duplicate(s).", 'yellow');
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        if ($dryRun) {
            $this->info("Total: {$totalDuplicates} duplicate record(s) across {$totalGroups} group(s). Run without --dry-run to apply.");
        } else {
            $this->info("Done. Removed {$totalDuplicates} duplicate record(s) across {$totalGroups} group(s).");
        }

        return self::SUCCESS;
    }

    private function findDuplicateGroups()
    {
        $allMedicines = Medicine::select('id', 'name', 'strength', 'generic_name', 'brand_name', 'category_id', 'company_name')
            ->orderBy('id')
            ->get();

        return $allMedicines->groupBy(function ($medicine) {
            $name = mb_strtolower(trim($medicine->name ?? ''));
            $strength = mb_strtolower(trim($medicine->strength ?? ''));
            $generic = mb_strtolower(trim($medicine->generic_name ?? ''));
            return "{$name}|{$strength}|{$generic}";
        })->filter(fn($group) => $group->count() > 1);
    }

    private function reassignReferences(array $fromIds, int $toId): void
    {
        DB::table('prescription_items')
            ->whereIn('medicine_id', $fromIds)
            ->update(['medicine_id' => $toId]);

        DB::table('medicine_suggestions')
            ->whereIn('medicine_id', $fromIds)
            ->update(['medicine_id' => $toId]);
    }
}
