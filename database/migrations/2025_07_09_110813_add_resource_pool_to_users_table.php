<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Menandakan apakah user tersedia di resource pool
            $table->boolean('is_in_resource_pool')->default(false)->after('remember_token');
            // Catatan opsional dari atasan mengenai ketersediaan user
            $table->text('pool_availability_notes')->nullable()->after('is_in_resource_pool');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_in_resource_pool');
            $table->dropColumn('pool_availability_notes');
        });
    }
};