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
            $table->float('individual_performance_index')->nullable()->after('pool_availability_notes');
            $table->float('final_performance_value')->nullable()->after('individual_performance_index');
            $table->string('work_result_rating')->nullable()->after('final_performance_value');
            $table->string('performance_predicate')->nullable()->after('work_result_rating');
            $table->timestamp('performance_data_updated_at')->nullable()->after('performance_predicate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'individual_performance_index',
                'final_performance_value',
                'work_result_rating',
                'performance_predicate',
                'performance_data_updated_at',
            ]);
        });
    }
};
