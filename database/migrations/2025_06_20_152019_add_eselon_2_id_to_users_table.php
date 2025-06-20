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
        Schema::table('users', function (Blueprint $table) {
            // ID ini menandakan user berada di bawah 'payung' Eselon II yang mana.
            $table->foreignId('eselon_2_id')
                  ->nullable()
                  ->after('parent_id')
                  ->constrained('users')
                  ->onDelete('set null');
        });
    }
    
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['eselon_2_id']);
            $table->dropColumn('eselon_2_id');
        });
    }
};
