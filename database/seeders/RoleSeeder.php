<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->truncate();

        $roles = [
            // Administrative & High-Level Roles
            ['name' => 'Menteri', 'level' => 0, 'can_manage_users_in_unit' => true],
            ['name' => 'Superadmin', 'level' => 0, 'can_manage_users_in_unit' => true],

            // Structural Roles (Eselon)
            ['name' => 'Eselon I', 'level' => 1, 'can_manage_users_in_unit' => false],
            ['name' => 'Eselon II', 'level' => 2, 'can_manage_users_in_unit' => false],
            ['name' => 'Eselon III', 'level' => 3, 'can_manage_users_in_unit' => false],
            ['name' => 'Eselon IV', 'level' => 4, 'can_manage_users_in_unit' => false],

            // Functional Roles
            ['name' => 'Koordinator', 'level' => 3, 'can_manage_users_in_unit' => false],
            ['name' => 'Sub Koordinator', 'level' => 4, 'can_manage_users_in_unit' => false],
            ['name' => 'Staf', 'level' => 5, 'can_manage_users_in_unit' => false],

            // Special Administrative Role
            ['name' => 'Sub Koordinator Admin', 'level' => 4, 'can_manage_users_in_unit' => true],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
