<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::create('performance_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->json('value');
            $table->timestamps();
        });

        // nilai awal: bobot peran, batas efisiensi, ambang rating, ambang beban kerja mingguan
        DB::table('performance_settings')->insert([
            [
                'key' => 'manager_weights',
                'value' => json_encode([
                    'ESELON_I' => 0.9,
                    'ESELON_II' => 0.8,
                    'ESELON_III' => 0.7,
                    'ESELON_IV' => 0.6,
                    'KOORDINATOR' => 0.5,
                    'SUB_KOORDINATOR' => 0.4,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'efficiency_cap',
                'value' => json_encode(['min' => 0.5, 'max' => 1.5]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'rating_thresholds',
                'value' => json_encode([
                    'excellent' => 1.15,
                    'satisfactory' => 0.9,
                    'needs_improvement' => 0,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'weekly_workload_thresholds',
                'value' => json_encode([
                    'green' => 75,
                    'yellow' => 100,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void {
        Schema::dropIfExists('performance_settings');
    }
};
