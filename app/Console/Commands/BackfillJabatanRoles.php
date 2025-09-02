<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Jabatan;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BackfillJabatanRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jabatans:backfill-roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate the new `role` column on existing jabatans based on their unit depth.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to backfill roles for jabatans...');

        $jabatansToUpdate = Jabatan::whereNull('role')->with('unit')->get();

        if ($jabatansToUpdate->isEmpty()) {
            $this->info('No jabatans found that need a role backfill. All roles are already populated.');
            return 0;
        }

        $progressBar = $this->output->createProgressBar($jabatansToUpdate->count());
        $updatedCount = 0;

        DB::transaction(function () use ($jabatansToUpdate, $progressBar, &$updatedCount) {
            foreach ($jabatansToUpdate as $jabatan) {
                if (!$jabatan->unit) {
                    $this->warn("\nSkipping Jabatan ID: {$jabatan->id} ('{$jabatan->name}') as it has no associated unit.");
                    $progressBar->advance();
                    continue;
                }

                // Determine the role based on the unit's depth.
                // The unit's depth is determined by its path in the closure table.
                $depth = $jabatan->unit->ancestors()->count();
                $role = $this->getRoleFromDepth($depth, $jabatan->type);

                $jabatan->role = $role;
                $jabatan->save();

                $updatedCount++;
                $progressBar->advance();
            }
        });

        $progressBar->finish();
        $this->info("\nSuccessfully backfilled roles for {$updatedCount} jabatans.");

        return 0;
    }

    /**
     * Determines the role based on the unit's depth in the hierarchy.
     * Structural positions get roles based on unit level.
     * Functional positions are always 'Staf' unless specified otherwise.
     */
    private function getRoleFromDepth(int $depth, string $jabatanType): string
    {
        if ($jabatanType === 'fungsional') {
            return 'staf';
        }

        return match ($depth) {
            0 => 'menteri',
            1 => 'eselon_i',
            2 => 'eselon_ii',
            3 => 'koordinator',
            4 => 'sub_koordinator',
            default => 'staf',
        };
    }
}
