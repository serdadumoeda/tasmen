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
            ['name' => 'Menteri', 'level' => 0],
            ['name' => 'Superadmin', 'level' => 0],
            ['name' => 'Eselon I', 'level' => 1],
            ['name' => 'Eselon II', 'level' => 2],
            ['name' => 'Eselon III', 'level' => 3],
            ['name' => 'Eselon IV', 'level' => 4],
            ['name' => 'Koordinator', 'level' => 3],
            ['name' => 'Sub Koordinator', 'level' => 4],
            ['name' => 'Staf', 'level' => 5],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
