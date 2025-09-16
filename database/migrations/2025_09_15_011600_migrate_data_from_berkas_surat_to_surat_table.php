<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all entries from the pivot table
        $berkas_surat = DB::table('berkas_surat')->get();

        foreach ($berkas_surat as $entry) {
            // Update the surat table with the berkas_id
            // If a surat is in multiple berkas, this will only keep the last one.
            DB::table('surat')
                ->where('id', $entry->surat_id)
                ->update(['berkas_id' => $entry->berkas_id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is not reversible in a simple way,
        // as we are moving from a many-to-many to a one-to-many relationship.
        // We will just clear the berkas_id column.
        DB::table('surat')->update(['berkas_id' => null]);
    }
};
