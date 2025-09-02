<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Services\OrganizationalDataImporterService;
use App\Models\Role;
use App\Models\User;
use App\Models\Unit;
use App\Models\Jabatan;

class OrganizationalDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $this->command->info('--- Starting Organizational Data Seeding ---');

        $dbDriver = DB::connection()->getDriverName();

        if ($dbDriver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        } elseif ($dbDriver === 'pgsql') {
            DB::statement("SET session_replication_role = 'replica';");
        }

        // Truncate tables for a clean slate
        User::truncate();
        Jabatan::truncate();
        Unit::truncate();
        DB::table('unit_paths')->truncate();
        $this->command->info('Old data truncated.');

        // Get data from JSON file
        $json = File::get(database_path('data/users_profile_data.json'));
        $data = json_decode($json); // Decode as an array of objects

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->command->error('JSON file is invalid: ' . json_last_error_msg());
            return;
        }

        // Disables the UnitObserver temporarily to prevent the error during seeding.
        Unit::withoutEvents(function () use ($data) {
            // Process data using the service
            $importer = new OrganizationalDataImporterService($this->command);
            $importer->processData($data);
        });
        
        // Re-enable foreign key checks
        if ($dbDriver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } elseif ($dbDriver === 'pgsql') {
            DB::statement("SET session_replication_role = 'origin';");
        }
        
        // Create a default Super Admin user
        $superAdminRole = Role::where('name', 'Superadmin')->first();
        if ($superAdminRole) {
            $admin = User::create([
                'name' => 'Super Admin',
                'email' => 'admin@example.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'status' => 'active',
            ]);
            $admin->roles()->attach($superAdminRole);
            $this->command->info('Default Super Admin created.');
        } else {
            $this->command->warn('Superadmin role not found. Skipping Super Admin creation.');
        }

        $this->command->info('--- Organizational Data Seeding Finished ---');
    }
}