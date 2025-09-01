<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            // Using a more generic name in case other formulas are added later.
            // We will add specific keys for 'iki_formula' and 'nkf_formula'.
            // This migration is to ensure the 'value' column can hold long text, which it already can.
            // Let's add the settings directly instead of altering the table.
        });

        // Add default formula settings
        \App\Models\Setting::updateOrCreate(
            ['key' => 'iki_formula'],
            ['value' => 'base_score * capped_efficiency_factor']
        );
        \App\Models\Setting::updateOrCreate(
            ['key' => 'nkf_formula_staf'],
            ['value' => 'individual_score']
        );
        \App\Models\Setting::updateOrCreate(
            ['key' => 'nkf_formula_pimpinan'],
            ['value' => '(individual_score * (1 - weight)) + (managerial_score * weight)']
        );
        \App\Models\Setting::updateOrCreate(
            ['key' => 'min_efficiency_factor'],
            ['value' => '0.9']
        );
        \App\Models\Setting::updateOrCreate(
            ['key' => 'max_efficiency_factor'],
            ['value' => '1.25']
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We don't need to alter the table down, but we can remove the keys.
        \App\Models\Setting::whereIn('key', [
            'iki_formula',
            'nkf_formula_staf',
            'nkf_formula_pimpinan',
            'min_efficiency_factor',
            'max_efficiency_factor'
        ])->delete();
    }
};
