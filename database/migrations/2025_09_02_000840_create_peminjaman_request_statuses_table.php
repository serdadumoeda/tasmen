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
        Schema::create('peminjaman_request_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->timestamps();
        });

        DB::table('peminjaman_request_statuses')->insert([
            ['key' => 'pending', 'label' => 'Pending', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'approved', 'label' => 'Approved', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'rejected', 'label' => 'Rejected', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peminjaman_request_statuses');
    }
};
