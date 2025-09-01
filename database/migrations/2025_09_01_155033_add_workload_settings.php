<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Setting::updateOrCreate(
            ['key' => 'workload_standard_hours'],
            ['value' => '37.5']
        );
        Setting::updateOrCreate(
            ['key' => 'workload_threshold_normal'],
            ['value' => '0.75']
        );
        Setting::updateOrCreate(
            ['key' => 'workload_threshold_warning'],
            ['value' => '1.0']
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Setting::whereIn('key', [
            'workload_standard_hours',
            'workload_threshold_normal',
            'workload_threshold_warning'
        ])->delete();
    }
};
