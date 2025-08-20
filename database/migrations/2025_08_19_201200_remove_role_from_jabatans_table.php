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
        Schema::table('jabatans', function (Blueprint $table) {
            // Drop the index first to avoid potential issues
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = $sm->listTableIndexes('jabatans');
            if (array_key_exists('jabatans_role_index', $indexes)) {
                $table->dropIndex('jabatans_role_index');
            }

            // Then drop the column
            if (Schema::hasColumn('jabatans', 'role')) {
                $table->dropColumn('role');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jabatans', function (Blueprint $table) {
            $table->string('role')->nullable()->after('type')->comment('The role associated with this position, defining permissions.');
            $table->index('role');
        });
    }
};
