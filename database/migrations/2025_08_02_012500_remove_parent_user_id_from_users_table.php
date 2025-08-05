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
            // Cek jika kolomnya ada sebelum mencoba menghapusnya
            if (Schema::hasColumn('users', 'parent_user_id')) {
                // Pertama, hapus foreign key constraint jika ada
                // Nama constraint biasanya: table_column_foreign
                $table->dropForeign(['parent_user_id']);
                $table->dropColumn('parent_user_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('parent_user_id')->nullable()->constrained('users')->after('unit_id');
        });
    }
};
