<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('priority_levels', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->unsignedInteger('weight');
            $table->timestamps();
        });

        DB::table('priority_levels')->insert([
            ['key' => 'low', 'label' => 'Low', 'weight' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'medium', 'label' => 'Medium', 'weight' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'high', 'label' => 'High', 'weight' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'critical', 'label' => 'Critical', 'weight' => 4, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('priority_levels');
    }
};
