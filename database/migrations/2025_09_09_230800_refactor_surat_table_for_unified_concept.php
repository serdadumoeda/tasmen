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
        Schema::table('surat', function (Blueprint $table) {
            // 1. Add file_path column
            $table->string('file_path')->nullable()->after('konten');

            // 2. Modify status column
            $table->string('status')->default('Baru')->change();

            // 3. Drop unnecessary columns
            $table->dropColumn('jenis');
            $table->dropColumn('konten');

            // 4. Drop penyetuju_id foreign key and column
            // Assuming the foreign key name follows Laravel's convention: table_column_foreign
            $table->dropForeign(['penyetuju_id']);
            $table->dropColumn('penyetuju_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat', function (Blueprint $table) {
            // 1. Re-add the columns that were dropped.
            $table->foreignId('penyetuju_id')->nullable()->constrained('users')->after('pembuat_id');
            $table->longText('konten')->nullable()->after('penyetuju_id');
            $table->enum('jenis', ['masuk', 'keluar'])->default('masuk')->after('tanggal_surat');

            // 2. Drop the new file_path column.
            $table->dropColumn('file_path');
        });

        // 3. Change the status column back to its original state in a separate call for safety.
        Schema::table('surat', function (Blueprint $table) {
            $table->string('status')->default('draft')->change();
        });
    }
};
