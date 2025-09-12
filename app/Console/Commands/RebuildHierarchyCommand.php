<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;

class RebuildHierarchyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hierarchy:rebuild';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rebuilds the unit hierarchy closure table (unit_paths) for data integrity.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting unit hierarchy rebuild...');

        try {
            $this->line('Calling Unit::rebuildHierarchy()...');

            $result = Unit::rebuildHierarchy();

            $this->info("\nUnit hierarchy rebuild completed successfully!");
            $this->info("Processed {$result['total_units']} units and created {$result['processed_paths']} paths.");

            return 0; // Success
        } catch (\Exception $e) {
            $this->error("\nAn error occurred during the hierarchy rebuild:");
            $this->error($e->getMessage());
            // Optionally log the full stack trace for debugging
            // \Log::error($e);
            return 1; // Failure
        }
    }
}
