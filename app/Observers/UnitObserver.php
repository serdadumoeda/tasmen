<?php

namespace App\Observers;

use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class UnitObserver
{
    /**
     * Handle the Unit "created" event.
     */
    public function created(Unit $unit): void
    {
        $this->clearHierarchyCache($unit);

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
            // The logic for incrementally updating a closure table is complex.
            // For simplicity and guaranteed correctness, we will rebuild the entire
            // table on any parent_unit_id change. This is less performant on
            // large datasets but ensures data integrity.
            Unit::rebuildPaths();

            // Clear all relevant caches after the rebuild
            $this->clearHierarchyCache($unit);
            $oldParentId = $unit->getOriginal('parent_unit_id');
            if ($oldParentId) {
                $this->clearHierarchyCache(Unit::find($oldParentId));
            }
        }
    }

    /**
     * Handle the Unit "deleted" event.
     */
    public function deleted(Unit $unit): void
    {
        $this->clearHierarchyCache($unit);

        // When a unit is deleted, we must remove all path entries where it was
        // either an ancestor or a descendant to prevent orphaned data.
        DB::table('unit_paths')->where('descendant_id', $unit->id)
                                ->orWhere('ancestor_id', $unit->id)
                                ->delete();
    }

    /**
     * Helper to rebuild paths for a single unit.
     */
    protected function rebuildPathsFor(Unit $unit)
    {
        $this->created($unit); // The logic is the same as creating a new unit
    }

    /**
     * Clears the cached hierarchy for all managers affected by a unit change.
     */
    private function clearHierarchyCache(Unit $unit)
    {
        if (!$unit) {
            return;
        }

        // Eager load kepalaUnit to avoid N+1 queries
        $ancestors = $unit->ancestors()->with('kepalaUnit')->get();

        // Add the unit itself to the collection, also with kepalaUnit loaded
        $unit->load('kepalaUnit');
        $affectedUnits = $ancestors->push($unit);

        foreach ($affectedUnits as $affectedUnit) {
            if ($affectedUnit->kepalaUnit) {
                Cache::forget('subordinate_unit_ids_for_user_' . $affectedUnit->kepalaUnit->id);
            }
        }
    }
}
