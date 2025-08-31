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
            $table->unsignedBigInteger('suratable_id')->nullable()->after('penyetuju_id');
            $table->string('suratable_type')->nullable()->after('suratable_id');
            $table->index(['suratable_id', 'suratable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat', function (Blueprint $table) {
            $table->dropIndex(['suratable_id', 'suratable_type']);
            $table->dropColumn('suratable_type');
            $table->dropColumn('suratable_id');
        });
    }
};
