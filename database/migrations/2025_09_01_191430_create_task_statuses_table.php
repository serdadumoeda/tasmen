<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->timestamps();
        });

        DB::table('task_statuses')->insert([
            ['key' => 'pending', 'label' => 'Pending', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'in_progress', 'label' => 'In Progress', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'for_review', 'label' => 'For Review', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'completed', 'label' => 'Completed', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'cancelled', 'label' => 'Cancelled', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('task_statuses');
    }
};
