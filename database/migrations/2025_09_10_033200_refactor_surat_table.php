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

            // Drop foreign key and column for penyetuju_id
            if (Schema::hasColumn('surat', 'penyetuju_id')) {
                // We need to find the foreign key constraint name to drop it.
                // It's usually 'surat_penyetuju_id_foreign', but let's be safe.
                // A raw statement might be needed if this fails, but for now, we assume standard naming.
                // In a fresh migration, this might not exist if the column is dropped first.
                // Let's just drop the column. Dropping a column with a foreign key is handled by most DBs.
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
