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
            $table->string('color_class')->default('bg-gray-100 text-gray-800');
            $table->timestamps();
        });

        DB::table('task_statuses')->insert([
            ['key' => 'pending', 'label' => 'Pending', 'color_class' => 'bg-yellow-100 text-yellow-800', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'in_progress', 'label' => 'In Progress', 'color_class' => 'bg-blue-100 text-blue-800', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'for_review', 'label' => 'For Review', 'color_class' => 'bg-orange-100 text-orange-800', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'completed', 'label' => 'Completed', 'color_class' => 'bg-green-100 text-green-800', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'cancelled', 'label' => 'Cancelled', 'color_class' => 'bg-gray-100 text-gray-800', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('task_statuses');
    }
};
