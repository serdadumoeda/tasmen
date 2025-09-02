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
        Schema::table('approval_workflow_steps', function (Blueprint $table) {
            $table->string('condition_type')->nullable()->after('approver_role');
            $table->string('condition_value')->nullable()->after('condition_type');
            $table->string('action')->default('approve')->after('condition_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approval_workflow_steps', function (Blueprint $table) {
            $table->dropColumn(['condition_type', 'condition_value', 'action']);
        });
    }
};
