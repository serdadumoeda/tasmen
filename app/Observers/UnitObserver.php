<?php

namespace App\Observers;

use App\Models\Unit;
use Illuminate\Support\Facades\DB;

class UnitObserver
{
    /**
     * Handle the Unit "created" event.
     */
    public function created(Unit $unit): void
    {
        // Insert the path to itself
        DB::table('unit_paths')->insert([
            'ancestor_id' => $unit->id,
            'descendant_id' => $unit->id,
            'depth' => 0,
        ]);

        // Insert paths from its ancestors
        if ($unit->parent_unit_id) {
            $parentPaths = DB::table('unit_paths')
                ->where('descendant_id', $unit->parent_unit_id)
                ->get();

            foreach ($parentPaths as $path) {
                DB::table('unit_paths')->insert([
                    'ancestor_id' => $path->ancestor_id,
                    'descendant_id' => $unit->id,
                    'depth' => $path->depth + 1,
                ]);
            }
        }
    }

    /**
     * Handle the Unit "updating" event.
     * This is complex, for now we will rebuild on updated.
     */
    public function updating(Unit $unit): void
    {
        if ($unit->isDirty('parent_unit_id')) {
            // Logic to disconnect and reconnect paths is complex.
            // A simpler, albeit less performant approach for now, is to rebuild paths
            // for the entire subtree on the 'updated' event.
        }
    }

    /**
     * Handle the Unit "updated" event.
     */
    public function updated(Unit $unit): void
    {
        if ($unit->isDirty('parent_unit_id')) {
            // A simple approach: rebuild paths for the moved unit and all its descendants.
            $descendantIds = $unit->descendants()->pluck('id')->toArray();

            // Delete old paths for the entire subtree
            DB::table('unit_paths')->whereIn('descendant_id', $descendantIds)->delete();

            // Rebuild paths for the moved unit and its descendants
            $allUnitsInSubtree = Unit::whereIn('id', $descendantIds)->get();
            foreach ($allUnitsInSubtree as $u) {
                // Manually call the path insertion logic for each unit in the subtree
                $this->rebuildPathsFor($u);
            }
        }
    }

    /**
     * Handle the Unit "deleted" event.
     */
    public function deleted(Unit $unit): void
    {
        // The cascading delete on the foreign key in the migration should handle this.
        // But as a fallback, we can explicitly delete.
        DB::table('unit_paths')->where('descendant_id', $unit->id)->delete();
    }

    /**
     * Helper to rebuild paths for a single unit.
     */
    protected function rebuildPathsFor(Unit $unit)
    {
        $this->created($unit); // The logic is the same as creating a new unit
    }
}
