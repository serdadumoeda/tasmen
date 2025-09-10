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
        // Drop the old enum check constraint if it exists (for PostgreSQL)
        DB::statement('ALTER TABLE surat DROP CONSTRAINT IF EXISTS surat_status_check');

        Schema::table('surat', function (Blueprint $table) {
            // Add new file_path column
            $table->string('file_path')->nullable()->after('status');

            // Drop obsolete columns
            if (Schema::hasColumn('surat', 'jenis')) {
                $table->dropColumn('jenis');
            }
            if (Schema::hasColumn('surat', 'konten')) {
                $table->dropColumn('konten');
            }
            if (Schema::hasColumn('surat', 'final_pdf_path')) {
                $table->dropColumn('final_pdf_path');
            }
            if (Schema::hasColumn('surat', 'penyetuju_id')) {
                $table->dropConstrainedForeignId('penyetuju_id');
            }
        });

        // Change status column type to string
        Schema::table('surat', function (Blueprint $table) {
             $table->string('status')->default('Baru')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat', function (Blueprint $table) {
            // Re-add the columns
            $table->enum('jenis', ['masuk', 'keluar'])->default('masuk')->after('tanggal_surat');
            $table->longText('konten')->nullable()->after('status');
            $table->string('final_pdf_path')->nullable()->after('status');
            $table->foreignId('penyetuju_id')->nullable()->constrained('users')->after('pembuat_id');

            // Drop the new column
            $table->dropColumn('file_path');
        });

        // Revert status column change
        Schema::table('surat', function (Blueprint $table) {
            $table->string('status')->default('draft')->change();
        });
    }
};
