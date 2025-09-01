<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat', function (Blueprint $table) {
            // menambahkan kolom polymorphic
            $table->unsignedBigInteger('suratable_id')->nullable()->after('id');
            $table->string('suratable_type')->nullable()->after('suratable_id');
            $table->index(['suratable_id', 'suratable_type'], 'suratable_index');
        });
    }

    public function down(): void
    {
        Schema::table('surat', function (Blueprint $table) {
            $table->dropIndex('suratable_index');
            $table->dropColumn(['suratable_id', 'suratable_type']);
        });
    }
};
