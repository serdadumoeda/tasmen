<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., 'eselon_i', 'superadmin'
            $table->string('label'); // e.g., 'Eselon I', 'Superadmin'
            $table->decimal('managerial_weight', 3, 2)->default(0.00);
            $table->timestamps();
        });

        // Seed the table with existing roles and their weights
        DB::table('roles')->insert([
            [
                'name' => 'menteri',
                'label' => 'Menteri',
                'managerial_weight' => 1.0, // Assuming top level has full weight
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'eselon_i',
                'label' => 'Eselon I',
                'managerial_weight' => 0.9,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'eselon_ii',
                'label' => 'Eselon II',
                'managerial_weight' => 0.8,
                'created_at' => now(),
                'updated_at' => now()
            ],
             [
                'name' => 'eselon_iii',
                'label' => 'Eselon III',
                'managerial_weight' => 0.7,
                'created_at' => now(),
                'updated_at' => now()
            ],
             [
                'name' => 'eselon_iv',
                'label' => 'Eselon IV',
                'managerial_weight' => 0.6,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'koordinator',
                'label' => 'Koordinator',
                'managerial_weight' => 0.5,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'sub_koordinator',
                'label' => 'Sub Koordinator',
                'managerial_weight' => 0.4,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'staf',
                'label' => 'Staf',
                'managerial_weight' => 0.0,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'superadmin',
                'label' => 'Superadmin',
                'managerial_weight' => 0.0,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
