<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Angka 8 berarti total digit, 2 berarti 2 digit di belakang koma.
            // Cukup untuk mencatat hingga 999,999.99 jam.
            $table->decimal('estimated_hours', 8, 2)->nullable()->after('status');
        });
    }
    
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('estimated_hours');
        });
    }
};
