<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('role')->constrained('roles');
        });

        // Data migration
        $roles = DB::table('roles')->pluck('id', 'label');

        User::all()->each(function ($user) use ($roles) {
            // The old 'role' column stores the label (e.g., 'Eselon I')
            $role_label = $user->role;
            $role_id = $roles[$role_label] ?? null;

            // Fallback for safety, e.g., assign 'Staf' role if not found
            if (!$role_id) {
                $staf_role_id = DB::table('roles')->where('name', 'staf')->value('id');
                $role_id = $staf_role_id;
            }

            $user->update(['role_id' => $role_id]);
        });

        // Now that data is migrated, we can drop the old column and make the new one non-nullable
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
            $table->foreignId('role_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->nullable()->after('role_id');
        });

        // Restore old data
        $roles = DB::table('roles')->pluck('label', 'id');
        User::all()->each(function ($user) use ($roles) {
            $user->update(['role' => $roles[$user->role_id] ?? 'Staf']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });
    }
};
